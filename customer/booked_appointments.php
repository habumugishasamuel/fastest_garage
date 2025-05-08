<?php
require_once '../includes/Database.php';
require_once '../includes/header.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Show success message if redirected from booking
if (isset($_SESSION['booking_success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
        <h5><i class="bi bi-check-circle"></i> Booking Successful!</h5>
        <p class="mb-0">Your appointment has been scheduled successfully.</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    // Clear the success message
    unset($_SESSION['booking_success']);
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
?>

<div class="container-fluid">
    <!-- Navigation Buttons -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h4 class="text-primary"><i class="bi bi-calendar-check"></i> Booked Appointments</h4>
        <div>
            <a href="appointments.php?action=new" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Book New Appointment
            </a>
            <a href="appointments.php" class="btn btn-secondary ms-2">
                <i class="bi bi-arrow-left"></i> Back to Appointments
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointments)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No appointments booked yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr class="appointment-row">
                                    <td>
                                        <?php echo date('M d, Y h:i A', strtotime($appointment['appointment_date'])); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($appointment['make'] . ' ' . $appointment['model']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($appointment['license_plate']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                    <td>$<?php echo number_format($appointment['service_price'], 2); ?></td>
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
                                            <a href="appointments.php?edit=<?php echo $appointment['id']; ?>" 
                                               class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <form method="POST" action="appointments.php" class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                                <input type="hidden" name="action" value="cancel">
                                                <input type="hidden" name="id" value="<?php echo $appointment['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-x-circle"></i> Cancel
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="appointment_details.php?id=<?php echo $appointment['id']; ?>" 
                                           class="btn btn-info btn-sm">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Check for new booking data
window.onload = function() {
    const newBooking = localStorage.getItem('newBooking');
    if (newBooking) {
        const bookingData = JSON.parse(newBooking);
        
        // Find the newly added booking row and highlight it
        const rows = document.querySelectorAll('.appointment-row');
        rows.forEach(row => {
            const dateCell = row.cells[0].textContent.trim();
            const vehicleCell = row.cells[1].textContent.trim();
            const serviceCell = row.cells[2].textContent.trim();
            
            if (dateCell.includes(bookingData.date) && 
                vehicleCell.includes(bookingData.vehicle) && 
                serviceCell.includes(bookingData.service)) {
                
                // Highlight the new booking
                row.style.animation = 'highlight 2s';
                row.classList.add('table-success');
                
                // Scroll to the new booking
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
        
        // Clear the stored booking data
        localStorage.removeItem('newBooking');
    }
};
</script>

<style>
@keyframes highlight {
    0% { background-color: #d4edda; }
    50% { background-color: #d4edda; }
    100% { background-color: transparent; }
}

.table-success {
    background-color: #d4edda;
    transition: background-color 0.5s ease;
}
</style>

<?php require_once '../includes/footer.php'; ?> 