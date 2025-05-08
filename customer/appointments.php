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

    <!-- Navigation Buttons -->
    <div class="mb-4 d-flex justify-content-end">
        <a href="booked_appointments.php" class="btn btn-success">
            <i class="bi bi-calendar-check"></i> View Booked Appointments
        </a>
    </div>

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
                            <?php if (empty($vehicles)): ?>
                                <optgroup label="Example Vehicles">
                                    <option value="example1">Toyota Camry (ABC123)</option>
                                    <option value="example2">Honda Civic (XYZ789)</option>
                                    <option value="example3">Ford F-150 (DEF456)</option>
                                    <option value="example4">Chevrolet Silverado (GHI789)</option>
                                    <option value="example5">Nissan Altima (JKL012)</option>
                                </optgroup>
                            <?php else: ?>
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <option value="<?php echo $vehicle['id']; ?>" 
                                        <?php echo $edit_appointment && $edit_appointment['vehicle_id'] == $vehicle['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['license_plate'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php if (empty($vehicles)): ?>
                            <div class="alert alert-info mt-2">
                                <i class="bi bi-info-circle"></i> These are example vehicles. To book an appointment, please add your vehicle below.
                            </div>
                        <?php else: ?>
                            <div class="mt-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#addVehicleForm">
                                    <i class="bi bi-plus-circle"></i> Add Another Vehicle
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Add Vehicle Form -->
                    <div class="collapse mb-3" id="addVehicleForm">
                        <div class="card card-body">
                            <h6 class="mb-3">Add New Vehicle</h6>
                            <form id="quickAddVehicleForm" class="row g-3">
                                <div class="col-md-6">
                                    <label for="make" class="form-label">Make *</label>
                                    <input type="text" class="form-control" id="make" name="make" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="model" class="form-label">Model *</label>
                                    <input type="text" class="form-control" id="model" name="model" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="year" class="form-label">Year *</label>
                                    <input type="number" class="form-control" id="year" name="year" min="1900" max="2099" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="license_plate" class="form-label">License Plate *</label>
                                    <input type="text" class="form-control" id="license_plate" name="license_plate" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="vehicle_type" class="form-label">Vehicle Type</label>
                                    <select class="form-control" id="vehicle_type" name="vehicle_type">
                                        <option value="car">Car</option>
                                        <option value="truck">Truck</option>
                                        <option value="suv">SUV</option>
                                        <option value="van">Van</option>
                                        <option value="motorcycle">Motorcycle</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Add Vehicle</button>
                                    <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#addVehicleForm">Cancel</button>
                                </div>
                            </form>
                        </div>
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
                    
                    <div class="text-center">
                        <form method="POST" action="process_booking.php" id="bookingForm">
                            <input type="hidden" name="vehicle_id" id="hidden_vehicle_id">
                            <input type="hidden" name="service_id" id="hidden_service_id">
                            <input type="hidden" name="appointment_date" id="hidden_appointment_date">
                            <input type="hidden" name="notes" id="hidden_notes">
                            <button type="button" class="btn btn-primary btn-lg" onclick="submitBooking()">
                                <i class="bi bi-calendar-check"></i> Book & View All
                            </button>
                            <a href="appointments.php" class="btn btn-secondary btn-lg ms-2">Cancel</a>
                        </form>
                    </div>

                    <script>
                    function submitBooking() {
                        // Get values from the main form
                        const vehicle = document.getElementById('vehicle_id');
                        const service = document.getElementById('service_id');
                        const date = document.getElementById('appointment_date');
                        const notes = document.getElementById('notes');
                        
                        // Debug logging
                        console.log('Vehicle:', vehicle.value);
                        console.log('Service:', service.value);
                        console.log('Date:', date.value);
                        console.log('Notes:', notes.value);
                        
                        // Clear any previous error messages
                        const errorDivs = document.querySelectorAll('.alert-danger');
                        errorDivs.forEach(div => div.remove());
                        
                        // Reset field highlights
                        vehicle.classList.remove('is-invalid');
                        service.classList.remove('is-invalid');
                        date.classList.remove('is-invalid');
                        
                        let isValid = true;
                        let errorMessage = '';
                        
                        // Validate each field
                        if (!vehicle.value) {
                            vehicle.classList.add('is-invalid');
                            errorMessage += 'Please select a vehicle.<br>';
                            isValid = false;
                        }
                        
                        if (!service.value) {
                            service.classList.add('is-invalid');
                            errorMessage += 'Please select a service.<br>';
                            isValid = false;
                        }
                        
                        if (!date.value) {
                            date.classList.add('is-invalid');
                            errorMessage += 'Please select a date and time.<br>';
                            isValid = false;
                        }
                        
                        if (!isValid) {
                            // Show error message
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'alert alert-danger mt-3';
                            errorDiv.innerHTML = errorMessage;
                            document.getElementById('bookingForm').insertAdjacentElement('beforebegin', errorDiv);
                            return;
                        }

                        // If all valid, set values and submit
                        document.getElementById('hidden_vehicle_id').value = vehicle.value;
                        document.getElementById('hidden_service_id').value = service.value;
                        document.getElementById('hidden_appointment_date').value = date.value;
                        document.getElementById('hidden_notes').value = notes.value;
                        
                        // Add save_and_view field
                        const saveAndView = document.createElement('input');
                        saveAndView.type = 'hidden';
                        saveAndView.name = 'save_and_view';
                        saveAndView.value = 'true';
                        document.getElementById('bookingForm').appendChild(saveAndView);
                        
                        // Debug logging before submit
                        console.log('Submitting form with values:');
                        console.log('Vehicle ID:', document.getElementById('hidden_vehicle_id').value);
                        console.log('Service ID:', document.getElementById('hidden_service_id').value);
                        console.log('Appointment Date:', document.getElementById('hidden_appointment_date').value);
                        console.log('Notes:', document.getElementById('hidden_notes').value);
                        
                        // Submit the form
                        document.getElementById('bookingForm').submit();
                    }
                    </script>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php
    // Handle appointment booking
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_appointment'])) {
        $vehicle_id = $_POST['vehicle_id'] ?? '';
        $service_id = $_POST['service_id'] ?? '';
        $appointment_date = $_POST['appointment_date'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // Validate required fields
        if (empty($vehicle_id) || empty($service_id) || empty($appointment_date)) {
            echo '<div class="alert alert-danger">Please fill in all required fields.</div>';
            exit;
        }

        try {
            // Check if the selected time slot is available
            $query = "SELECT COUNT(*) as count FROM appointments 
                     WHERE appointment_date = :appointment_date 
                     AND status != 'cancelled'";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':appointment_date', $appointment_date);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                echo '<div class="alert alert-danger">This time slot is already booked. Please choose another time.</div>';
                exit;
            }

            // Begin transaction
            $db->beginTransaction();
            
            try {
                // Insert the new appointment
                $query = "INSERT INTO appointments (customer_id, vehicle_id, service_id, appointment_date, notes, status) 
                         VALUES (:customer_id, :vehicle_id, :service_id, :appointment_date, :notes, 'scheduled')";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':customer_id', $_SESSION['user_id']);
                $stmt->bindParam(':vehicle_id', $vehicle_id);
                $stmt->bindParam(':service_id', $service_id);
                $stmt->bindParam(':appointment_date', $appointment_date);
                $stmt->bindParam(':notes', $notes);
                
                if ($stmt->execute()) {
                    $db->commit();
                    $_SESSION['booking_success'] = true;
                    echo 'success';
                    exit;
                } else {
                    throw new Exception("Failed to insert appointment");
                }
            } catch (Exception $e) {
                $db->rollBack();
                echo '<div class="alert alert-danger">Error saving appointment: ' . htmlspecialchars($e->getMessage()) . '</div>';
                exit;
            }
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            exit;
        }
    }
    ?>
</div>

<script>
document.getElementById('quickAddVehicleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'add');
    
    fetch('vehicles.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Reload the page to show the new vehicle in the dropdown
        window.location.reload();
    })
    .catch(error => {
        alert('Error adding vehicle. Please try again.');
    });
});
</script>

<?php require_once '../includes/footer.php'; ?> 