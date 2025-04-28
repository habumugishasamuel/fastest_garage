<?php
require_once '../includes/auth.php';
requireAdmin();

if (!isset($pageTitle)) {
    $pageTitle = "Admin Panel";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Simple CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #4e54c8, #8f94fb);
            padding-top: 20px;
        }
        .admin-sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 10px 20px;
            margin: 5px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: rgba(255,255,255,.1);
            color: white;
        }
        .admin-sidebar .nav-link i {
            margin-right: 10px;
        }
        .admin-content {
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .admin-header {
            background: white;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
            margin-bottom: 20px;
        }
        .card {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
            border: none;
            margin-bottom: 20px;
        }
        .card-header {
            background: white;
            border-bottom: 1px solid rgba(0,0,0,.1);
        }
        .table {
            margin-bottom: 0;
        }
        .btn-icon {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block admin-sidebar">
                <div class="text-center mb-4">
                    <h4 class="text-white">Simple CMS</h4>
                    <p class="text-white-50">Admin Panel</p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" 
                           href="index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pages.php' ? 'active' : ''; ?>" 
                           href="pages.php">
                            <i class="bi bi-file-text"></i> Pages
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'menu.php' ? 'active' : ''; ?>" 
                           href="menu.php">
                            <i class="bi bi-list"></i> Menu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" 
                           href="users.php">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" 
                           href="settings.php">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>" 
                           href="services.php">
                            <i class="bi bi-briefcase"></i> Services
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <a class="nav-link" href="../index.php">
                            <i class="bi bi-arrow-left"></i> Back to Site
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 admin-content">
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0"><?php echo $pageTitle; ?></h1>
                    <div class="user-info">
                        <span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    </div>
                </div>

                <?php if (isset($message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?> 