<?php
require_once '../includes/header.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

$job_id = $_GET['id'] ?? 0;

if (!$job_id) {
    header('Location: jobs.php');
    exit;
}

// Get job details
$query = "SELECT j.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
          v.make, v.model, v.year, v.license_plate, v.vin,
          s.name as staff_name
          FROM jobs j 
          JOIN users u ON j.customer_id = u.id 
          JOIN vehicles v ON j.vehicle_id = v.id 
          JOIN users s ON j.staff_id = s.id 
          WHERE j.id = :job_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':job_id', $job_id);
$stmt->execute();
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header('Location: jobs.php');
    exit;
}

// Get job services
$query = "SELECT s.* FROM services s 
          JOIN job_services js ON s.id = js.service_id 
          WHERE js.job_id = :job_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':job_id', $job_id);
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total cost
$total_cost = array_sum(array_column($services, 'price'));
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Job Details</h6>
            <div>
                <a href="jobs.php" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Jobs
                </a>
                <a href="jobs.php?edit=<?php echo $job_id; ?>" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Job Information -->
                <div class="col-md-6">
                    <h5 class="mb-3">Job Information</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Job ID</th>
                            <td><?php echo $job['id']; ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $job['status'] == 'completed' ? 'success' : 
                                        ($job['status'] == 'in_progress' ? 'warning' : 
                                        ($job['status'] == 'cancelled' ? 'danger' : 'info')); 
                                ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $job['status'])); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Created Date</th>
                            <td><?php echo date('M d, Y h:i A', strtotime($job['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td><?php echo nl2br(htmlspecialchars($job['description'])); ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Customer Information -->
                <div class="col-md-6">
                    <h5 class="mb-3">Customer Information</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Name</th>
                            <td><?php echo htmlspecialchars($job['customer_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo htmlspecialchars($job['customer_email']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?php echo htmlspecialchars($job['customer_phone']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Vehicle Information -->
                <div class="col-md-6">
                    <h5 class="mb-3">Vehicle Information</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Make</th>
                            <td><?php echo htmlspecialchars($job['make']); ?></td>
                        </tr>
                        <tr>
                            <th>Model</th>
                            <td><?php echo htmlspecialchars($job['model']); ?></td>
                        </tr>
                        <tr>
                            <th>Year</th>
                            <td><?php echo htmlspecialchars($job['year']); ?></td>
                        </tr>
                        <tr>
                            <th>License Plate</th>
                            <td><?php echo htmlspecialchars($job['license_plate']); ?></td>
                        </tr>
                        <tr>
                            <th>VIN</th>
                            <td><?php echo htmlspecialchars($job['vin']); ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Assigned Staff -->
                <div class="col-md-6">
                    <h5 class="mb-3">Assigned Staff</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Staff Name</th>
                            <td><?php echo htmlspecialchars($job['staff_name']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Services -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5 class="mb-3">Services</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Service Name</th>
                                <th>Description</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($service['name']); ?></td>
                                    <td><?php echo htmlspecialchars($service['description']); ?></td>
                                    <td>$<?php echo number_format($service['price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="2" class="text-end"><strong>Total Cost:</strong></td>
                                <td><strong>$<?php echo number_format($total_cost, 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 