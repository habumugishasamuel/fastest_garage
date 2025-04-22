<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Ensure user is admin
if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}

// Get database connection
$db = new Database();
$conn = $db->getConnection();

// Get statistics
$stats = [
    'total_jobs' => 0,
    'active_jobs' => 0,
    'total_customers' => 0,
    'total_staff' => 0,
    'total_appointments' => 0,
    'today_appointments' => 0
];

// Get total jobs
$query = "SELECT COUNT(*) as count FROM jobs";
$stmt = $conn->query($query);
$stats['total_jobs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get active jobs
$query = "SELECT COUNT(*) as count FROM jobs WHERE status = 'in_progress'";
$stmt = $conn->query($query);
$stats['active_jobs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get total customers
$query = "SELECT COUNT(*) as count FROM users WHERE role = 'customer'";
$stmt = $conn->query($query);
$stats['total_customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get total staff
$query = "SELECT COUNT(*) as count FROM users WHERE role = 'staff'";
$stmt = $conn->query($query);
$stats['total_staff'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get total appointments
$query = "SELECT COUNT(*) as count FROM appointments";
$stmt = $conn->query($query);
$stats['total_appointments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get today's appointments
$query = "SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = CURDATE()";
$stmt = $conn->query($query);
$stats['today_appointments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get recent jobs
$query = "SELECT j.*, u.name as customer_name, v.make, v.model 
          FROM jobs j 
          JOIN users u ON j.customer_id = u.id 
          JOIN vehicles v ON j.vehicle_id = v.id 
          ORDER BY j.created_at DESC 
          LIMIT 5";
$recent_jobs = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Get recent appointments
$query = "SELECT a.*, u.name as customer_name, v.make, v.model, s.name as service_name 
          FROM appointments a 
          JOIN users u ON a.customer_id = u.id 
          JOIN vehicles v ON a.vehicle_id = v.id 
          JOIN services s ON a.service_id = s.id 
          ORDER BY a.appointment_date DESC 
          LIMIT 5";
$recent_appointments = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Auto Repair Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <h3>Auto Repair Shop</h3>
                <p>Admin Panel</p>
            </div>
            <nav>
                <ul class="admin-nav">
                    <li class="admin-nav-item">
                        <a href="dashboard.php" class="admin-nav-link active">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a href="services.php" class="admin-nav-link">
                            <i class="fas fa-cogs"></i>
                            <span>Services</span>
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a href="users.php" class="admin-nav-link">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a href="jobs.php" class="admin-nav-link">
                            <i class="fas fa-tools"></i>
                            <span>Jobs</span>
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a href="appointments.php" class="admin-nav-link">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Appointments</span>
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a href="../logout.php" class="admin-nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Dashboard</h1>
                <div class="admin-user-menu">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <i class="fas fa-tools"></i>
                    <h3><?php echo $stats['total_jobs']; ?></h3>
                    <p>Total Jobs</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3><?php echo $stats['active_jobs']; ?></h3>
                    <p>Active Jobs</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3><?php echo $stats['total_customers']; ?></h3>
                    <p>Total Customers</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user-tie"></i>
                    <h3><?php echo $stats['total_staff']; ?></h3>
                    <p>Total Staff</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar-alt"></i>
                    <h3><?php echo $stats['total_appointments']; ?></h3>
                    <p>Total Appointments</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar-day"></i>
                    <h3><?php echo $stats['today_appointments']; ?></h3>
                    <p>Today's Appointments</p>
                </div>
            </div>

            <!-- Recent Jobs -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Jobs</h5>
                    <a href="jobs.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table admin-table">
                            <thead>
                                <tr>
                                    <th>Job ID</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_jobs as $job): ?>
                                <tr>
                                    <td>#<?php echo $job['id']; ?></td>
                                    <td><?php echo htmlspecialchars($job['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($job['make'] . ' ' . $job['model']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $job['status'] === 'completed' ? 'success' : 
                                                ($job['status'] === 'in_progress' ? 'primary' : 
                                                ($job['status'] === 'pending' ? 'warning' : 'danger')); 
                                        ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $job['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                                    <td>
                                        <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Appointments</h5>
                    <a href="appointments.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table admin-table">
                            <thead>
                                <tr>
                                    <th>Appointment ID</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Service</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_appointments as $appointment): ?>
                                <tr>
                                    <td>#<?php echo $appointment['id']; ?></td>
                                    <td><?php echo htmlspecialchars($appointment['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['make'] . ' ' . $appointment['model']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($appointment['appointment_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $appointment['status'] === 'completed' ? 'success' : 
                                                ($appointment['status'] === 'confirmed' ? 'primary' : 
                                                ($appointment['status'] === 'pending' ? 'warning' : 'danger')); 
                                        ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="appointment_details.php?id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 