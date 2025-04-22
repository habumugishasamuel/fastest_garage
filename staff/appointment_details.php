<?php
require_once '../includes/header.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

$appointment_id = $_GET['id'] ?? 0;

if (!$appointment_id) {
    header('Location: appointments.php');
    exit;
}

// Get appointment details
$query = "SELECT a.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
          v.make, v.model, v.year, v.license_plate, v.vin,
          s.name as service_name, s.description as service_description, s.price as service_price
          FROM appointments a 
          JOIN users u ON a.customer_id = u.id 
          JOIN vehicles v ON a.vehicle_id = v.id 
          JOIN services s ON a.service_id = s.id 
          WHERE a.id = :appointment_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':appointment_id', $appointment_id);
$stmt->execute();
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appointment) {
    header('Location: appointments.php');
    exit;
}
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Appointment Details</h6>
            <div>
                <a href="appointments.php" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Appointments
                </a>
                <a href="appointments.php?edit=<?php echo $appointment_id; ?>" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Appointment Information -->
                <div class="col-md-6">
                    <h5 class="mb-3">Appointment Information</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Appointment ID</th>
                            <td><?php echo $appointment['id']; ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $appointment['status'] == 'completed' ? 'success' : 
                                        ($appointment['status'] == 'cancelled' ? 'danger' : 
                                        ($appointment['status'] == 'no_show' ? 'warning' : 'info')); 
                                ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $appointment['status'])); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Date & Time</th>
                            <td><?php echo date('M d, Y h:i A', strtotime($appointment['appointment_date'])); ?></td>
                        </tr>
                        <tr>
                            <th>Notes</th>
                            <td><?php echo nl2br(htmlspecialchars($appointment['notes'])); ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Customer Information -->
                <div class="col-md-6">
                    <h5 class="mb-3">Customer Information</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Name</th>
                            <td><?php echo htmlspecialchars($appointment['customer_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo htmlspecialchars($appointment['customer_email']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?php echo htmlspecialchars($appointment['customer_phone']); ?></td>
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
                            <td><?php echo htmlspecialchars($appointment['make']); ?></td>
                        </tr>
                        <tr>
                            <th>Model</th>
                            <td><?php echo htmlspecialchars($appointment['model']); ?></td>
                        </tr>
                        <tr>
                            <th>Year</th>
                            <td><?php echo htmlspecialchars($appointment['year']); ?></td>
                        </tr>
                        <tr>
                            <th>License Plate</th>
                            <td><?php echo htmlspecialchars($appointment['license_plate']); ?></td>
                        </tr>
                        <tr>
                            <th>VIN</th>
                            <td><?php echo htmlspecialchars($appointment['vin']); ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Service Information -->
                <div class="col-md-6">
                    <h5 class="mb-3">Service Information</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Service Name</th>
                            <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td><?php echo htmlspecialchars($appointment['service_description']); ?></td>
                        </tr>
                        <tr>
                            <th>Price</th>
                            <td>$<?php echo number_format($appointment['service_price'], 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 