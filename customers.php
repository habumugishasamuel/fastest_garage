<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Ensure user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Get all customers
$customers_query = "SELECT u.*, 
                   COUNT(b.id) as total_bookings,
                   COUNT(CASE WHEN b.status = 'completed' THEN 1 END) as completed_bookings,
                   COUNT(CASE WHEN b.status = 'pending' THEN 1 END) as pending_bookings
                   FROM users u 
                   LEFT JOIN bookings b ON u.id = b.user_id 
                   WHERE u.role = 'customer'
                   GROUP BY u.id
                   ORDER BY u.name ASC";
$customers_stmt = $conn->prepare($customers_query);
$customers_stmt->execute();
$customers = $customers_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Garage Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .customer-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .customer-card:hover {
            transform: translateY(-5px);
        }
        .stats-card {
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Garage Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="customers.php">Customers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings.php">Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2>Customers</h2>
                <p class="text-muted">Manage and view customer information</p>
            </div>
        </div>

        <div class="row">
            <?php foreach ($customers as $customer): ?>
            <div class="col-md-6">
                <div class="card customer-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php echo htmlspecialchars($customer['name']); ?>
                            <small class="text-muted">(<?php echo htmlspecialchars($customer['username']); ?>)</small>
                        </h5>
                        <p class="card-text">
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($customer['email']); ?><br>
                            <i class="fas fa-calendar"></i> Member since: <?php echo date('F j, Y', strtotime($customer['created_at'])); ?>
                        </p>
                        
                        <div class="card stats-card mb-3">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Booking Statistics</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h4><?php echo $customer['total_bookings']; ?></h4>
                                            <small class="text-muted">Total Bookings</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h4><?php echo $customer['completed_bookings']; ?></h4>
                                            <small class="text-muted">Completed</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h4><?php echo $customer['pending_bookings']; ?></h4>
                                            <small class="text-muted">Pending</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary btn-sm view-bookings" 
                                data-customer-id="<?php echo $customer['id']; ?>"
                                data-customer-name="<?php echo htmlspecialchars($customer['name']); ?>">
                            <i class="fas fa-calendar-alt"></i> View Bookings
                        </button>
                        <button class="btn btn-info btn-sm view-invoices" 
                                data-customer-id="<?php echo $customer['id']; ?>"
                                data-customer-name="<?php echo htmlspecialchars($customer['name']); ?>">
                            <i class="fas fa-file-invoice"></i> View Invoices
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bookings Modal -->
    <div class="modal fade" id="bookingsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Customer Bookings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="bookingsList"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Modal -->
    <div class="modal fade" id="invoicesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Customer Invoices</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="invoicesList"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // View Bookings
        document.querySelectorAll('.view-bookings').forEach(button => {
            button.addEventListener('click', function() {
                const customerId = this.dataset.customerId;
                const customerName = this.dataset.customerName;
                
                fetch(`api/get_customer_bookings.php?customer_id=${customerId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const bookingsList = document.getElementById('bookingsList');
                            bookingsList.innerHTML = `
                                <h6>Bookings for ${customerName}</h6>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Service</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${data.bookings.map(booking => `
                                                <tr>
                                                    <td>${booking.service_name}</td>
                                                    <td>${new Date(booking.booking_date).toLocaleString()}</td>
                                                    <td>
                                                        <span class="badge bg-${booking.status === 'completed' ? 'success' : 
                                                                           booking.status === 'pending' ? 'warning' : 
                                                                           booking.status === 'cancelled' ? 'danger' : 'info'}">
                                                            ${booking.status}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-info view-booking-details" 
                                                                data-booking-id="${booking.id}">
                                                            Details
                                                        </button>
                                                    </td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            `;
                            new bootstrap.Modal(document.getElementById('bookingsModal')).show();
                        }
                    });
            });
        });

        // View Invoices
        document.querySelectorAll('.view-invoices').forEach(button => {
            button.addEventListener('click', function() {
                const customerId = this.dataset.customerId;
                const customerName = this.dataset.customerName;
                
                fetch(`api/get_customer_invoices.php?customer_id=${customerId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const invoicesList = document.getElementById('invoicesList');
                            invoicesList.innerHTML = `
                                <h6>Invoices for ${customerName}</h6>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Invoice #</th>
                                                <th>Service</th>
                                                <th>Amount</th>
                                                <th>Due Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${data.invoices.map(invoice => `
                                                <tr>
                                                    <td>${invoice.invoice_number}</td>
                                                    <td>${invoice.service_name}</td>
                                                    <td>$${parseFloat(invoice.total_amount).toFixed(2)}</td>
                                                    <td>${new Date(invoice.due_date).toLocaleDateString()}</td>
                                                    <td>
                                                        <span class="badge bg-${invoice.status === 'paid' ? 'success' : 
                                                                           invoice.status === 'pending' ? 'warning' : 
                                                                           invoice.status === 'overdue' ? 'danger' : 'secondary'}">
                                                            ${invoice.status}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-info view-invoice-details" 
                                                                data-invoice-id="${invoice.id}">
                                                            Details
                                                        </button>
                                                    </td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            `;
                            new bootstrap.Modal(document.getElementById('invoicesModal')).show();
                        }
                    });
            });
        });
    </script>
</body>
</html> 