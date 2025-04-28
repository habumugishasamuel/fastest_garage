<?php
require_once '../includes/Database.php';
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
        $service_id = $_POST['service_id'] ?? 0;
        $appointment_date = $_POST['appointment_date'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        if (empty($vehicle_id) || empty($service_id) || empty($appointment_date)) {
            $error = 'Please fill in all required fields';
        } else {
            if ($action == 'add') {
                $query = "INSERT INTO appointments (customer_id, vehicle_id, service_id, appointment_date, notes, status) 
                         VALUES (:customer_id, :vehicle_id, :service_id, :appointment_date, :notes, 'scheduled')";
            } else {
                $id = $_POST['id'] ?? 0;
                $query = "UPDATE appointments SET vehicle_id = :vehicle_id, service_id = :service_id, 
                         appointment_date = :appointment_date, notes = :notes 
                         WHERE id = :id AND customer_id = :customer_id";
            }
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':vehicle_id', $vehicle_id);
            $stmt->bindParam(':service_id', $service_id);
            $stmt->bindParam(':appointment_date', $appointment_date);
            $stmt->bindParam(':notes', $notes);
            
            if ($action == 'add') {
                $stmt->bindParam(':customer_id', $_SESSION['user_id']);
            } else {
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':customer_id', $_SESSION['user_id']);
            }
            
            if ($stmt->execute()) {
                $message = 'Appointment ' . ($action == 'add' ? 'booked' : 'updated') . ' successfully';
            } else {
                $error = 'Error ' . ($action == 'add' ? 'booking' : 'updating') . ' appointment';
            }
        }
    } elseif ($action == 'cancel') {
        $id = $_POST['id'] ?? 0;
        
        $query = "UPDATE appointments SET status = 'cancelled' WHERE id = :id AND customer_id = :customer_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':customer_id', $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $message = 'Appointment cancelled successfully';
        } else {
            $error = 'Error cancelling appointment';
        }
    }
}

// Get all appointments for the customer
$query = "SELECT a.*, v.make, v.model, v.license_plate, s.name as service_name, s.price as service_price 
          FROM appointments a 
          JOIN vehicles v ON a.vehicle_id = v.id 
          JOIN services s ON a.service_id = s.id 
          WHERE a.customer_id = :customer_id 
          ORDER BY a.appointment_date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':customer_id', $_SESSION['user_id']);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all vehicles for the customer
$query = "SELECT * FROM vehicles WHERE customer_id = :customer_id ORDER BY make, model ASC";
$stmt = $db->prepare($query);
$stmt->bindParam(':customer_id', $_SESSION['user_id']);
$stmt->execute();
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all services
$query = "SELECT * FROM services ORDER BY name ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get appointment for editing
$edit_appointment = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM appointments WHERE id = :id AND customer_id = :customer_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':customer_id', $_SESSION['user_id']);
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
            <h6 class="m-0 font-weight-bold text-primary">My Appointments</h6>
            <a href="appointments.php?action=new" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Book Appointment
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
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
                                <td>
                                    <?php echo htmlspecialchars($appointment['make'] . ' ' . $appointment['model']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($appointment['license_plate']); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($appointment['service_name']); ?><br>
                                    <small class="text-muted">$<?php echo number_format($appointment['service_price'], 2); ?></small>
                                </td>
                                <td><?php echo date('M d, Y h:i A', strtotime($appointment['appointment_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $appointment['status'] == 'completed' ? 'success' : 
                                            ($appointment['status'] == 'cancelled' ? 'danger' : 
                                            ($appointment['status'] == 'no_show' ? 'warning' : 'info')); 
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $appointment['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($appointment['status'] == 'scheduled'): ?>
                                        <a href="appointments.php?edit=<?php echo $appointment['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="id" value="<?php echo $appointment['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="bi bi-x-circle"></i> Cancel
                                            </button>
                                        </form>
                                    <?php endif; ?>
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
                    <?php echo $edit_appointment ? 'Edit Appointment' : 'Book New Appointment'; ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $edit_appointment ? 'edit' : 'add'; ?>">
                    <?php if ($edit_appointment): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_appointment['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group mb-3">
                        <label for="vehicle_id">Vehicle *</label>
                        <select class="form-control" id="vehicle_id" name="vehicle_id" required>
                            <option value="">Select Vehicle</option>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?php echo $vehicle['id']; ?>" 
                                    <?php echo $edit_appointment && $edit_appointment['vehicle_id'] == $vehicle['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['license_plate'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
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
                        <?php echo $edit_appointment ? 'Update Appointment' : 'Book Appointment'; ?>
                    </button>
                    
                    <a href="appointments.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?> 