<?php
$pageTitle = "Dashboard";

require_once '../config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Get statistics
$stats = [
    'total_pages' => 0,
    'published_pages' => 0,
    'total_users' => 0,
    'total_menu_items' => 0,
    'total_services' => 0
];

// Function to safely get count from a table
function getTableCount($conn, $table, $condition = '') {
    try {
        $query = "SELECT COUNT(*) FROM " . $table . ($condition ? ' WHERE ' . $condition : '');
        return $conn->query($query)->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

// Get counts safely
$stats['total_pages'] = getTableCount($conn, 'pages');
$stats['published_pages'] = getTableCount($conn, 'pages', "status = 'published'");
$stats['total_users'] = getTableCount($conn, 'users');
$stats['total_menu_items'] = getTableCount($conn, 'menu_items');
$stats['total_services'] = getTableCount($conn, 'services');

// Function to safely get recent items
function getRecentItems($conn, $table, $orderBy = 'created_at', $limit = 5) {
    try {
        $stmt = $conn->query("SELECT * FROM " . $table . " ORDER BY " . $orderBy . " DESC LIMIT " . $limit);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Get recent items safely
$recent_pages = getRecentItems($conn, 'pages');
$recent_users = getRecentItems($conn, 'users');
$recent_services = getRecentItems($conn, 'services');

require_once 'layout.php';
?>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo $stats['total_pages']; ?></h3>
                        <div class="text-white-50">Total Pages</div>
                    </div>
                    <i class="bi bi-file-text fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="pages.php">View Details</a>
                <i class="bi bi-chevron-right text-white"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo $stats['published_pages']; ?></h3>
                        <div class="text-white-50">Published Pages</div>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="pages.php">View Details</a>
                <i class="bi bi-chevron-right text-white"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-info text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo $stats['total_users']; ?></h3>
                        <div class="text-white-50">Total Users</div>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="users.php">View Details</a>
                <i class="bi bi-chevron-right text-white"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-warning text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo $stats['total_services']; ?></h3>
                        <div class="text-white-50">Services</div>
                    </div>
                    <i class="bi bi-briefcase fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="services.php">View Details</a>
                <i class="bi bi-chevron-right text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Pages</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_pages as $page): ?>
                                <tr>
                                    <td>
                                        <a href="pages.php?edit=<?php echo $page['id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($page['title']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $page['status'] == 'published' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($page['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($page['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Users</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_users as $user): ?>
                                <tr>
                                    <td>
                                        <a href="users.php?edit=<?php echo $user['id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'info'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Services</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_services as $service): ?>
                                <tr>
                                    <td>
                                        <a href="services.php?edit=<?php echo $service['id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($service['name']); ?>
                                        </a>
                                    </td>
                                    <td>$<?php echo number_format($service['price'], 2); ?></td>
                                    <td><?php echo $service['duration']; ?> min</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="pages.php?action=new" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle me-2"></i>Add New Page
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="services.php" class="btn btn-warning w-100">
                            <i class="bi bi-briefcase me-2"></i>Manage Services
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="menu.php" class="btn btn-info w-100 text-white">
                            <i class="bi bi-list me-2"></i>Manage Menu
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="settings.php" class="btn btn-secondary w-100">
                            <i class="bi bi-gear me-2"></i>Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout_end.php'; ?> 