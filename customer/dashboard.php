<?php
require_once '../includes/header.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get customer's vehicles
$query = "SELECT * FROM vehicles WHERE customer_id = :customer_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':customer_id', $_SESSION['user_id']);
$stmt->execute();
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get customer's appointments
$query = "SELECT a.*, v.make, v.model, s.name as service_name, s.price 
          FROM appointments a 
          JOIN vehicles v ON a.vehicle_id = v.id 
          JOIN services s ON a.service_id = s.id 
          WHERE a.customer_id = :customer_id 
          ORDER BY a.appointment_date DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(':customer_id', $_SESSION['user_id']);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get customer's recent invoices
$query = "SELECT i.*, j.description as job_description, v.make, v.model 
          FROM invoices i 
          JOIN jobs j ON i.job_id = j.id 
          JOIN vehicles v ON j.vehicle_id = v.id 
          WHERE j.customer_id = :customer_id 
          ORDER BY i.created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(':customer_id', $_SESSION['user_id']);
$stmt->execute();
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <!-- My Vehicles -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">My Vehicles</h6>
            <a href="vehicles.php?action=new" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Add Vehicle
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($vehicles as $vehicle): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></h5>
                                <p class="card-text">
                                    <strong>Year:</strong> <?php echo $vehicle['year']; ?><br>
                                    <strong>License Plate:</strong> <?php echo htmlspecialchars($vehicle['license_plate']); ?><br>
                                    <strong>Type:</strong> <?php echo ucfirst($vehicle['vehicle_type']); ?>
                                </p>
                                <a href="vehicles.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="appointments.php?vehicle_id=<?php echo $vehicle['id']; ?>" class="btn btn-success btn-sm">
                                    <i class="bi bi-calendar-plus"></i> Book Service
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- My Appointments -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">My Appointments</h6>
            <a href="appointments.php?action=new" class="btn btn-primary btn-sm">
                <i class="bi bi-calendar-plus"></i> New Appointment
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Vehicle</th>
                            <th>Service</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo date('M d, Y h:i A', strtotime($appointment['appointment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($appointment['make'] . ' ' . $appointment['model']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                <td>$<?php echo number_format($appointment['price'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $appointment['status'] == 'completed' ? 'success' : 
                                            ($appointment['status'] == 'cancelled' ? 'danger' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="appointment_details.php?id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Invoices -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recent Invoices</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Vehicle</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($invoice['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($invoice['make'] . ' ' . $invoice['model']); ?></td>
                                <td><?php echo htmlspecialchars($invoice['job_description']); ?></td>
                                <td>$<?php echo number_format($invoice['total_amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $invoice['payment_status'] == 'paid' ? 'success' : 
                                            ($invoice['payment_status'] == 'cancelled' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($invoice['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="invoice_details.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 