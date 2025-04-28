<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Get all services
    $services_query = "SELECT * FROM services ORDER BY name ASC";
    $services_stmt = $conn->prepare($services_query);
    $services_stmt->execute();
    $services = $services_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - Garage Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .service-card {
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 30px;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: #fff;
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .service-image-container {
            position: relative;
            height: 200px;
            overflow: hidden;
            background-color: #f8f9fa;
            border-radius: 15px 15px 0 0;
        }
        .service-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.3s ease;
            display: block;
        }
        .service-image-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.1);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .service-card:hover .service-image {
            transform: scale(1.05);
        }
        .service-card:hover .service-image-container::after {
            opacity: 1;
        }
        .service-header {
            background: linear-gradient(to right, #f8f9fa, #ffffff);
            border-bottom: 1px solid #eee;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .service-price {
            background: #0d6efd;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 1.2rem;
            box-shadow: 0 2px 5px rgba(13, 110, 253, 0.2);
        }
        .service-price .currency {
            font-size: 0.8rem;
            margin-right: 2px;
        }
        .card-body {
            padding: 1.25rem;
        }
        .service-features {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin: 1rem 0;
        }
        .service-features li {
            padding: 0.5rem 0;
            border-bottom: 1px dashed #dee2e6;
        }
        .service-features li:last-child {
            border-bottom: none;
        }
        .book-now-btn {
            background: #0d6efd;
            border: none;
            padding: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .book-now-btn:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        .page-header {
            background: #f8f9fa;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-bottom: 1px solid #dee2e6;
        }
        .card-title {
            color: #2c3e50;
            margin: 0;
            font-size: 1.4rem;
        }
        .card-text {
            color: #666;
            margin: 1rem 0;
            padding: 0 1rem;
        }
        .footer {
            margin-top: 4rem;
        }
        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        .social-links a:hover {
            background: #0d6efd;
            transform: translateY(-3px);
        }
        .footer-links a:hover {
            color: #0d6efd !important;
            padding-left: 5px;
            transition: all 0.3s ease;
        }
        .contact-info li {
            color: #adb5bd;
        }
        .contact-info a:hover {
            color: #0d6efd !important;
        }
        .bg-darker {
            background-color: rgba(0, 0, 0, 0.2);
        }
        .footer h5 {
            position: relative;
            padding-bottom: 10px;
        }
        .footer h5::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background: #0d6efd;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-wrench me-2"></i>
                Garage Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="my_bookings.php">My Bookings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <header class="page-header">
        <div class="container">
            <h1 class="text-center mb-0">Our Services</h1>
        </div>
    </header>

    <!-- Services Section -->
    <section class="container mb-5">
        <?php if (empty($services)): ?>
            <div class="alert alert-info">
                No services available at the moment. Please check back later.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($services as $service): ?>
                <div class="col-md-4">
                    <div class="card service-card">
                        <div class="service-image-container">
                            <?php
                            // Debug information
                            error_reporting(E_ALL);
                            ini_set('display_errors', 1);
                            
                            $defaultImage = 'imgs/n.jpeg';
                            $imagePath = 'imgs/' . (!empty($service['image_url']) ? $service['image_url'] : 'n.jpeg');
                            
                            // Debug output
                            echo "<!-- Debug: Image path = $imagePath -->";
                            echo "<!-- Debug: File exists = " . (file_exists($imagePath) ? 'true' : 'false') . " -->";
                            ?>
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                 class="service-image" 
                                 alt="<?php echo htmlspecialchars($service['name']); ?>"
                                 onerror="this.onerror=null; this.src='<?php echo $defaultImage; ?>'; console.log('Image failed to load:', this.src);"
                                 loading="lazy">
                        </div>
                        <div class="service-header">
                            <h4 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h4>
                            <div class="service-price">
                                <span class="currency">$</span><?php echo number_format($service['price'], 2); ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?php echo htmlspecialchars($service['description']); ?></p>
                            <ul class="service-features">
                                <li>
                                    <i class="fas fa-clock"></i>
                                    <span>Duration: <?php echo $service['duration']; ?> minutes</span>
                                </li>
                                <li>
                                    <i class="fas fa-tools"></i>
                                    <span>Professional Equipment</span>
                                </li>
                                <li>
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Quality Guaranteed</span>
                                </li>
                            </ul>
                            <button class="btn btn-primary w-100 book-service book-now-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#bookingModal"
                                    data-service-id="<?php echo $service['id']; ?>"
                                    data-service-name="<?php echo htmlspecialchars($service['name']); ?>"
                                    data-service-price="<?php echo $service['price']; ?>">
                                <i class="fas fa-calendar-plus me-2"></i>
                                Book Now
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <footer class="footer bg-dark text-light mt-5">
        <div class="container py-5">
            <div class="row">
                <!-- Company Info -->
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="mb-4 text-white">
                        <i class="fas fa-wrench me-2"></i>
                        Pro Garage Services
                    </h5>
                    <p class="mb-3">Professional auto repair and maintenance services you can trust. Our certified technicians are here to keep your vehicle running smoothly.</p>
                    <div class="d-flex gap-3 social-links">
                        <a href="#" class="text-light"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="mb-4 text-white">Quick Links</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2">
                            <a href="about.php" class="text-light text-decoration-none">
                                <i class="fas fa-angle-right me-2"></i>About Us
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="services.php" class="text-light text-decoration-none">
                                <i class="fas fa-angle-right me-2"></i>Our Services
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="booking.php" class="text-light text-decoration-none">
                                <i class="fas fa-angle-right me-2"></i>Book Appointment
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="contact.php" class="text-light text-decoration-none">
                                <i class="fas fa-angle-right me-2"></i>Contact Us
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-4">
                    <h5 class="mb-4 text-white">Contact Us</h5>
                    <ul class="list-unstyled contact-info">
                        <li class="mb-3">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            123 Garage Street, Auto City, AC 12345
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-phone me-2"></i>
                            <a href="tel:+1234567890" class="text-light text-decoration-none">
                                (123) 456-7890
                            </a>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-envelope me-2"></i>
                            <a href="mailto:info@progarage.com" class="text-light text-decoration-none">
                                info@progarage.com
                            </a>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-clock me-2"></i>
                            Mon - Sat: 8:00 AM - 6:00 PM
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="bg-darker py-3">
            <div class="container text-center">
                <small class="text-muted">
                    © <?php echo date('Y'); ?> Pro Garage Services. All rights reserved.
                </small>
            </div>
        </div>
    </footer>

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
                            <label class="form-label">Price</label>
                            <input type="text" class="form-control" id="servicePrice" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Preferred Date and Time</label>
                            <input type="datetime-local" class="form-control" id="bookingDateTime" name="booking_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="bookingNotes" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="confirmBooking">
                        <i class="fas fa-check me-2"></i>Confirm Booking
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize booking modal
        document.querySelectorAll('.book-service').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('serviceId').value = this.dataset.serviceId;
                document.getElementById('serviceName').value = this.dataset.serviceName;
                document.getElementById('servicePrice').value = '$' + parseFloat(this.dataset.servicePrice).toFixed(2);
                
                // Set minimum date to today
                const today = new Date();
                const tomorrow = new Date(today);
                tomorrow.setDate(tomorrow.getDate() + 1);
                const minDateTime = tomorrow.toISOString().slice(0, 16);
                document.getElementById('bookingDateTime').min = minDateTime;
            });
        });

        // Handle booking submission
        document.getElementById('confirmBooking').addEventListener('click', function() {
            if (!isLoggedIn()) {
                alert('Please log in to book a service');
                window.location.href = 'login.php';
                return;
            }

            const formData = new FormData(document.getElementById('bookingForm'));
            fetch('api/book_service.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Booking confirmed successfully!');
                    window.location.href = 'my_bookings.php';
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });

        function isLoggedIn() {
            return <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
        }
    </script>
</body>
</html> 