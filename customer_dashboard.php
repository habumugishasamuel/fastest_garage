<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Ensure user is logged in and is a customer
if (!isLoggedIn() || !isCustomer()) {
    header("Location: login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$database = new Database();
$conn = $database->getConnection();

// Get available services
$services_query = "SELECT * FROM garage_services WHERE is_active = 1";
$services_stmt = $conn->prepare($services_query);
$services_stmt->execute();
$services = $services_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's bookings
$bookings_query = "SELECT b.*, gs.name as service_name, gs.price 
                  FROM bookings b 
                  JOIN garage_services gs ON b.service_id = gs.id 
                  WHERE b.user_id = :user_id 
                  ORDER BY b.booking_date DESC";
$bookings_stmt = $conn->prepare($bookings_query);
$bookings_stmt->bindParam(':user_id', $user_id);
$bookings_stmt->execute();
$bookings = $bookings_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's invoices
$invoices_query = "SELECT i.*, b.booking_date, gs.name as service_name 
                  FROM invoices i 
                  JOIN bookings b ON i.booking_id = b.id 
                  JOIN garage_services gs ON b.service_id = gs.id 
                  WHERE b.user_id = :user_id 
                  ORDER BY i.created_at DESC";
$invoices_stmt = $conn->prepare($invoices_query);
$invoices_stmt->bindParam(':user_id', $user_id);
$invoices_stmt->execute();
$invoices = $invoices_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Garage Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .service-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .service-card:hover {
            transform: translateY(-5px);
        }
        .service-image {
            height: 200px;
            object-fit: cover;
        }
        .booking-card {
            border-left: 4px solid #007bff;
        }
        .invoice-card {
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Garage Services</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#bookings">My Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#invoices">Invoices</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
            </div>
        </div>

        <!-- Services Section -->
        <section id="services" class="mt-4">
            <h3>Available Services</h3>
            <div class="row">
                <?php foreach ($services as $service): ?>
                <div class="col-md-4">
                    <div class="card service-card">
                        <img src="assets/images/services/<?php echo htmlspecialchars($service['image_url']); ?>" 
                             class="card-img-top service-image" 
                             alt="<?php echo htmlspecialchars($service['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($service['description']); ?></p>
                            <p class="card-text"><strong>Price: $<?php echo number_format($service['price'], 2); ?></strong></p>
                            <p class="card-text"><small class="text-muted">Duration: <?php echo $service['duration']; ?> minutes</small></p>
                            <button class="btn btn-primary" data-bs-toggle="modal" 
                                    data-bs-target="#bookingModal" 
                                    data-service-id="<?php echo $service['id']; ?>"
                                    data-service-name="<?php echo htmlspecialchars($service['name']); ?>"
                                    data-service-price="<?php echo $service['price']; ?>">
                                Book Now
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Bookings Section -->
        <section id="bookings" class="mt-4">
            <h3>My Bookings</h3>
            <div class="row">
                <?php foreach ($bookings as $booking): ?>
                <div class="col-md-6">
                    <div class="card booking-card mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($booking['service_name']); ?></h5>
                            <p class="card-text">
                                <strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($booking['booking_date'])); ?><br>
                                <strong>Status:</strong> <span class="badge bg-<?php 
                                    echo $booking['status'] == 'confirmed' ? 'success' : 
                                        ($booking['status'] == 'pending' ? 'warning' : 
                                        ($booking['status'] == 'completed' ? 'info' : 'danger')); 
                                ?>"><?php echo ucfirst($booking['status']); ?></span>
                            </p>
                            <?php if ($booking['status'] == 'pending'): ?>
                            <button class="btn btn-danger btn-sm cancel-booking" 
                                    data-booking-id="<?php echo $booking['id']; ?>">
                                Cancel Booking
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Invoices Section -->
        <section id="invoices" class="mt-4">
            <h3>My Invoices</h3>
            <div class="row">
                <?php foreach ($invoices as $invoice): ?>
                <div class="col-md-6">
                    <div class="card invoice-card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Invoice #<?php echo htmlspecialchars($invoice['invoice_number']); ?></h5>
                            <p class="card-text">
                                <strong>Service:</strong> <?php echo htmlspecialchars($invoice['service_name']); ?><br>
                                <strong>Amount:</strong> $<?php echo number_format($invoice['total_amount'], 2); ?><br>
                                <strong>Due Date:</strong> <?php echo date('F j, Y', strtotime($invoice['due_date'])); ?><br>
                                <strong>Status:</strong> <span class="badge bg-<?php 
                                    echo $invoice['status'] == 'paid' ? 'success' : 
                                        ($invoice['status'] == 'pending' ? 'warning' : 
                                        ($invoice['status'] == 'overdue' ? 'danger' : 'secondary')); 
                                ?>"><?php echo ucfirst($invoice['status']); ?></span>
                            </p>
                            <?php if ($invoice['status'] == 'pending' || $invoice['status'] == 'overdue'): ?>
                            <button class="btn btn-primary btn-sm pay-invoice" 
                                    data-invoice-id="<?php echo $invoice['id']; ?>"
                                    data-amount="<?php echo $invoice['total_amount']; ?>">
                                Pay Now
                            </button>
                            <?php endif; ?>
                            <button class="btn btn-info btn-sm view-invoice" 
                                    data-invoice-id="<?php echo $invoice['id']; ?>">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Book Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="bookingForm">
                        <input type="hidden" id="serviceId" name="service_id">
                        <div class="mb-3">
                            <label class="form-label">Service</label>
                            <input type="text" class="form-control" id="serviceName" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date and Time</label>
                            <input type="datetime-local" class="form-control" id="bookingDateTime" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="bookingNotes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="confirmBooking">Confirm Booking</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Make Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm">
                        <input type="hidden" id="invoiceId" name="invoice_id">
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="text" class="form-control" id="paymentAmount" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" id="paymentMethod" required>
                                <option value="credit_card">Credit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>
                        <div id="creditCardFields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="cardNumber">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="text" class="form-control" id="expiryDate">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cvv">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="confirmPayment">Confirm Payment</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Booking Modal
        document.querySelectorAll('[data-bs-target="#bookingModal"]').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('serviceId').value = this.dataset.serviceId;
                document.getElementById('serviceName').value = this.dataset.serviceName;
            });
        });

        // Payment Modal
        document.querySelectorAll('.pay-invoice').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('invoiceId').value = this.dataset.invoiceId;
                document.getElementById('paymentAmount').value = this.dataset.amount;
                new bootstrap.Modal(document.getElementById('paymentModal')).show();
            });
        });

        // Show/hide credit card fields
        document.getElementById('paymentMethod').addEventListener('change', function() {
            document.getElementById('creditCardFields').style.display = 
                this.value === 'credit_card' ? 'block' : 'none';
        });

        // Handle booking confirmation
        document.getElementById('confirmBooking').addEventListener('click', function() {
            const formData = new FormData(document.getElementById('bookingForm'));
            fetch('api/book_service.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Booking confirmed successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });

        // Handle payment confirmation
        document.getElementById('confirmPayment').addEventListener('click', function() {
            const formData = new FormData(document.getElementById('paymentForm'));
            fetch('api/process_payment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Payment processed successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });

        // Handle booking cancellation
        document.querySelectorAll('.cancel-booking').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to cancel this booking?')) {
                    fetch('api/cancel_booking.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            booking_id: this.dataset.bookingId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Booking cancelled successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
                }
            });
        });
    </script>
</body>
</html> 