<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header("Location: index.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published'");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    header("Location: index.php");
    exit();
}

// Get menu items
$stmt = $conn->query("SELECT * FROM menu_items WHERE status = 'active' ORDER BY position");
$menu_items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $page['meta_description']; ?>">
    <title><?php echo $page['title']; ?> - Simple CMS</title>
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
                    <?php if (isLoggedIn()): ?>
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
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <article class="card animate-fade-in">
                    <div class="card-body">
                        <h1 class="card-title mb-4"><?php echo $page['title']; ?></h1>
                        <div class="d-flex align-items-center text-muted mb-4">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <span>Posted on <?php echo date('F j, Y', strtotime($page['created_at'])); ?></span>
                        </div>
                        <div class="content">
                            <?php echo nl2br($page['content']); ?>
                        </div>
                    </div>
                </article>
            </div>
            <div class="col-md-4">
                <div class="card mb-4 animate-fade-in">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-info-circle me-2"></i>About
                        </h5>
                        <p class="card-text">This is a simple content management system built with PHP and MySQL. It allows you to create, edit, and manage your website content easily.</p>
                    </div>
                </div>

                <div class="card animate-fade-in">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-share-alt me-2"></i>Share This Page
                        </h5>
                        <div class="d-flex gap-2">
                            <a href="#" class="btn btn-outline-primary">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="btn btn-outline-info">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="btn btn-outline-danger">
                                <i class="fab fa-pinterest-p"></i>
                            </a>
                            <a href="#" class="btn btn-outline-success">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
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