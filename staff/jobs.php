<?php
require_once '../includes/header.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add' || $action == 'edit') {
        $vehicle_id = $_POST['vehicle_id'] ?? 0;
        $customer_id = $_POST['customer_id'] ?? 0;
        $description = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 'pending';
        $services = $_POST['services'] ?? [];
        
        if (empty($vehicle_id) || empty($customer_id) || empty($description)) {
            $error = 'Please fill in all required fields';
        } else {
            if ($action == 'add') {
                $query = "INSERT INTO jobs (vehicle_id, customer_id, staff_id, status, description) 
                         VALUES (:vehicle_id, :customer_id, :staff_id, :status, :description)";
            } else {
                $id = $_POST['id'] ?? 0;
                $query = "UPDATE jobs SET vehicle_id = :vehicle_id, customer_id = :customer_id, 
                         status = :status, description = :description WHERE id = :id";
            }
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':vehicle_id', $vehicle_id);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':description', $description);
            
            if ($action == 'add') {
                $stmt->bindParam(':staff_id', $_SESSION['user_id']);
            } else {
                $stmt->bindParam(':id', $id);
            }
            
            if ($stmt->execute()) {
                $job_id = $action == 'add' ? $db->lastInsertId() : $id;
                
                // Update job services
                if ($action == 'edit') {
                    $query = "DELETE FROM job_services WHERE job_id = :job_id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':job_id', $job_id);
                    $stmt->execute();
                }
                
                foreach ($services as $service_id) {
                    $query = "INSERT INTO job_services (job_id, service_id) VALUES (:job_id, :service_id)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':job_id', $job_id);
                    $stmt->bindParam(':service_id', $service_id);
                    $stmt->execute();
                }
                
                $message = 'Job ' . ($action == 'add' ? 'added' : 'updated') . ' successfully';
            } else {
                $error = 'Error ' . ($action == 'add' ? 'adding' : 'updating') . ' job';
            }
        }
    } elseif ($action == 'update_status') {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        $query = "UPDATE jobs SET status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        
        if ($stmt->execute()) {
            $message = 'Job status updated successfully';
        } else {
            $error = 'Error updating job status';
        }
    }
}

// Get all jobs for the staff member
$query = "SELECT j.*, u.name as customer_name, v.make, v.model, v.license_plate 
          FROM jobs j 
          JOIN users u ON j.customer_id = u.id 
          JOIN vehicles v ON j.vehicle_id = v.id 
          WHERE j.staff_id = :staff_id 
          ORDER BY j.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':staff_id', $_SESSION['user_id']);
$stmt->execute();
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all services
$query = "SELECT * FROM services ORDER BY name ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get job for editing
$edit_job = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM jobs WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $edit_job = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get job services
    $query = "SELECT service_id FROM job_services WHERE job_id = :job_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':job_id', $id);
    $stmt->execute();
    $job_services = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<div class="container-fluid">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Jobs List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Jobs</h6>
            <a href="jobs.php?action=new" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> New Job
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($job['customer_name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($job['make'] . ' ' . $job['model']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($job['license_plate']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($job['description']); ?></td>
                                <td>
                                    <form method="POST" action="" class="d-inline">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="id" value="<?php echo $job['id']; ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $job['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="in_progress" <?php echo $job['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo $job['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $job['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                                <td>
                                    <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="jobs.php?edit=<?php echo $job['id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Job Form -->
    <?php if (isset($_GET['action']) && $_GET['action'] == 'new' || $edit_job): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <?php echo $edit_job ? 'Edit Job' : 'Add New Job'; ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $edit_job ? 'edit' : 'add'; ?>">
                    <?php if ($edit_job): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_job['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group mb-3">
                        <label for="customer_id">Customer *</label>
                        <select class="form-control" id="customer_id" name="customer_id" required>
                            <option value="">Select Customer</option>
                            <?php
                            $query = "SELECT id, name FROM users WHERE role = 'customer' ORDER BY name ASC";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($customers as $customer):
                            ?>
                                <option value="<?php echo $customer['id']; ?>" 
                                    <?php echo $edit_job && $edit_job['customer_id'] == $customer['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($customer['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="vehicle_id">Vehicle *</label>
                        <select class="form-control" id="vehicle_id" name="vehicle_id" required>
                            <option value="">Select Vehicle</option>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="description">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php 
                            echo $edit_job ? htmlspecialchars($edit_job['description']) : ''; 
                        ?></textarea>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Services *</label>
                        <div class="row">
                            <?php foreach ($services as $service): ?>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="services[]" 
                                               value="<?php echo $service['id']; ?>" id="service_<?php echo $service['id']; ?>"
                                               <?php echo isset($job_services) && in_array($service['id'], $job_services) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="service_<?php echo $service['id']; ?>">
                                            <?php echo htmlspecialchars($service['name']); ?> 
                                            ($<?php echo number_format($service['price'], 2); ?>)
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_job ? 'Update Job' : 'Add Job'; ?>
                    </button>
                    
                    <a href="jobs.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Load vehicles based on selected customer
document.getElementById('customer_id').addEventListener('change', function() {
    const customerId = this.value;
    const vehicleSelect = document.getElementById('vehicle_id');
    vehicleSelect.innerHTML = '<option value="">Select Vehicle</option>';
    
    if (customerId) {
        fetch(`get_vehicles.php?customer_id=${customerId}`)
            .then(response => response.json())
            .then(vehicles => {
                vehicles.forEach(vehicle => {
                    const option = document.createElement('option');
                    option.value = vehicle.id;
                    option.textContent = `${vehicle.make} ${vehicle.model} (${vehicle.license_plate})`;
                    vehicleSelect.appendChild(option);
                });
            });
    }
});

// If editing, trigger the customer change event to load vehicles
<?php if ($edit_job): ?>
document.getElementById('customer_id').dispatchEvent(new Event('change'));
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?> 