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
        $service_id = $_POST['service_id'] ?? 0;
        $appointment_date = $_POST['appointment_date'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        if (empty($vehicle_id) || empty($customer_id) || empty($service_id) || empty($appointment_date)) {
            $error = 'Please fill in all required fields';
        } else {
            if ($action == 'add') {
                $query = "INSERT INTO appointments (vehicle_id, customer_id, service_id, appointment_date, notes) 
                         VALUES (:vehicle_id, :customer_id, :service_id, :appointment_date, :notes)";
            } else {
                $id = $_POST['id'] ?? 0;
                $query = "UPDATE appointments SET vehicle_id = :vehicle_id, customer_id = :customer_id, 
                         service_id = :service_id, appointment_date = :appointment_date, notes = :notes 
                         WHERE id = :id";
            }
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':vehicle_id', $vehicle_id);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->bindParam(':service_id', $service_id);
            $stmt->bindParam(':appointment_date', $appointment_date);
            $stmt->bindParam(':notes', $notes);
            
            if ($action == 'edit') {
                $stmt->bindParam(':id', $id);
            }
            
            if ($stmt->execute()) {
                $message = 'Appointment ' . ($action == 'add' ? 'added' : 'updated') . ' successfully';
            } else {
                $error = 'Error ' . ($action == 'add' ? 'adding' : 'updating') . ' appointment';
            }
        }
    } elseif ($action == 'update_status') {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        $query = "UPDATE appointments SET status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        
        if ($stmt->execute()) {
            $message = 'Appointment status updated successfully';
        } else {
            $error = 'Error updating appointment status';
        }
    }
}

// Get all appointments
$query = "SELECT a.*, u.name as customer_name, v.make, v.model, v.license_plate, s.name as service_name 
          FROM appointments a 
          JOIN users u ON a.customer_id = u.id 
          JOIN vehicles v ON a.vehicle_id = v.id 
          JOIN services s ON a.service_id = s.id 
          ORDER BY a.appointment_date DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all services
$query = "SELECT * FROM services ORDER BY name ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get appointment for editing
$edit_appointment = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM appointments WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $edit_appointment = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container-fluid">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Appointments List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Appointments</h6>
            <a href="appointments.php?action=new" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> New Appointment
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Service</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['customer_name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($appointment['make'] . ' ' . $appointment['model']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($appointment['license_plate']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($appointment['appointment_date'])); ?></td>
                                <td>
                                    <form method="POST" action="" class="d-inline">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="id" value="<?php echo $appointment['id']; ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="scheduled" <?php echo $appointment['status'] == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                            <option value="completed" <?php echo $appointment['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $appointment['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            <option value="no_show" <?php echo $appointment['status'] == 'no_show' ? 'selected' : ''; ?>>No Show</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <a href="appointment_details.php?id=<?php echo $appointment['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="appointments.php?edit=<?php echo $appointment['id']; ?>" class="btn btn-warning btn-sm">
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

    <!-- Add/Edit Appointment Form -->
    <?php if (isset($_GET['action']) && $_GET['action'] == 'new' || $edit_appointment): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <?php echo $edit_appointment ? 'Edit Appointment' : 'Add New Appointment'; ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $edit_appointment ? 'edit' : 'add'; ?>">
                    <?php if ($edit_appointment): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_appointment['id']; ?>">
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
                                    <?php echo $edit_appointment && $edit_appointment['customer_id'] == $customer['id'] ? 'selected' : ''; ?>>
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
                        <label for="service_id">Service *</label>
                        <select class="form-control" id="service_id" name="service_id" required>
                            <option value="">Select Service</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?php echo $service['id']; ?>" 
                                    <?php echo $edit_appointment && $edit_appointment['service_id'] == $service['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($service['name']); ?> 
                                    ($<?php echo number_format($service['price'], 2); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="appointment_date">Date & Time *</label>
                        <input type="datetime-local" class="form-control" id="appointment_date" name="appointment_date" 
                               value="<?php echo $edit_appointment ? date('Y-m-d\TH:i', strtotime($edit_appointment['appointment_date'])) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?php 
                            echo $edit_appointment ? htmlspecialchars($edit_appointment['notes']) : ''; 
                        ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_appointment ? 'Update Appointment' : 'Add Appointment'; ?>
                    </button>
                    
                    <a href="appointments.php" class="btn btn-secondary">Cancel</a>
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
<?php if ($edit_appointment): ?>
document.getElementById('customer_id').dispatchEvent(new Event('change'));
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?> 