<?php
require_once __DIR__ . '/auth.php';
$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Repair Shop - <?php echo ucfirst($_SESSION['role']); ?> Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
        }
        .sidebar .nav-link:hover {
            color: white;
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,.1);
        }
        .main-content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4>Auto Repair Shop</h4>
                        <p class="text-muted"><?php echo ucfirst($_SESSION['role']); ?> Dashboard</p>
                    </div>
                    <ul class="nav flex-column">
                        <?php if ($auth->isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" href="users.php">
                                    <i class="bi bi-people"></i> Users
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page == 'services.php' ? 'active' : ''; ?>" href="services.php">
                                    <i class="bi bi-tools"></i> Services
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                                    <i class="bi bi-graph-up"></i> Reports
                                </a>
                            </li>
                        <?php elseif ($auth->isStaff()): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page == 'jobs.php' ? 'active' : ''; ?>" href="jobs.php">
                                    <i class="bi bi-wrench"></i> Jobs
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page == 'appointments.php' ? 'active' : ''; ?>" href="appointments.php">
                                    <i class="bi bi-calendar"></i> Appointments
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page == 'vehicles.php' ? 'active' : ''; ?>" href="vehicles.php">
                                    <i class="bi bi-car-front"></i> My Vehicles
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page == 'services.php' ? 'active' : ''; ?>" href="services.php">
                                    <i class="bi bi-tools"></i> Our Services
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page == 'appointments.php' ? 'active' : ''; ?>" href="appointments.php">
                                    <i class="bi bi-calendar"></i> Appointments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page == 'invoices.php' ? 'active' : ''; ?>" href="invoices.php">
                                    <i class="bi bi-receipt"></i> Invoices
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo ucfirst($current_page); ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="text-muted">Welcome, <?php echo $_SESSION['name']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 