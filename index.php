<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$db = new Database();
$conn = $db->getConnection();

// Get published pages
$stmt = $conn->query("SELECT * FROM pages WHERE status = 'published' ORDER BY created_at DESC");
$pages = $stmt->fetchAll();

// Get menu items
$stmt = $conn->query("SELECT * FROM menu_items WHERE status = 'active' ORDER BY position");
$menu_items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-newspaper me-2"></i>Simple CMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php foreach ($menu_items as $item): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $item['url']; ?>">
                                <i class="fas fa-link me-1"></i><?php echo $item['title']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/">
                                <i class="fas fa-cog me-1"></i>Admin Panel
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4 animate-fade-in">
                    <div class="card-body">
                        <h1 class="card-title mb-4">Welcome to Simple CMS</h1>
                        <p class="lead">Manage your content with ease using our simple and intuitive interface.</p>
                    </div>
                </div>

                <h2 class="mb-4">Recent Pages</h2>
                <div class="list-group">
                    <?php foreach ($pages as $page): ?>
                        <a href="page.php?slug=<?php echo $page['slug']; ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1"><?php echo $page['title']; ?></h5>
                                <small class="text-muted"><?php echo date('M j, Y', strtotime($page['created_at'])); ?></small>
                            </div>
                            <p class="mb-1"><?php echo substr($page['content'], 0, 150) . '...'; ?></p>
                            <small class="text-primary">Read more <i class="fas fa-arrow-right ms-1"></i></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4 animate-fade-in">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-user-circle me-2"></i>Quick Actions
                        </h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <a href="profile.php" class="text-decoration-none">
                                    <i class="fas fa-user me-2"></i>My Profile
                                </a>
                            </li>
                            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'editor'): ?>
                                <li class="mb-2">
                                    <a href="admin/pages.php" class="text-decoration-none">
                                        <i class="fas fa-file-alt me-2"></i>Manage Pages
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div class="card animate-fade-in">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-info-circle me-2"></i>About
                        </h5>
                        <p class="card-text">This is a simple content management system built with PHP and MySQL. It allows you to create, edit, and manage your website content easily.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Simple CMS</h5>
                    <p>A simple and efficient content management system.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?php echo date('Y'); ?> Simple CMS. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 