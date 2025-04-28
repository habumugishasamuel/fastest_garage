<?php
require_once '../includes/Database.php';
// Add error handling for missing includes
try {
    require_once '../includes/header.php';
} catch (Exception $e) {
    die('<div class="alert alert-danger">Header include failed: ' . htmlspecialchars($e->getMessage()) . '</div>');
}

// Get database connection
$database = new Database();
$db = $database->getConnection();
if (!$db) {
    die('<div class="alert alert-danger">Database connection failed.</div>');
}

// Get all services
$query = "SELECT * FROM services ORDER BY name ASC";
$stmt = $db->prepare($query);
if (!$stmt->execute()) {
    die('<div class="alert alert-danger">Failed to fetch services.</div>');
}
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Default service images mapping
$service_images = [
    'Oil Change' => 'oil-change.jpg',
    'Brake Service' => 'brake-service.jpg',
    'Tire Service' => 'tire-service.jpg',
    'Engine Repair' => 'engine-repair.jpg',
    'Transmission Service' => 'transmission-service.jpg',
    'Battery Service' => 'battery-service.jpg',
    'AC Service' => 'ac-service.jpg',
    'General Maintenance' => 'general-maintenance.jpg'
];
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Our Services</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($services as $service): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php 
                            $image_name = $service_images[$service['name']] ?? 'default-service.jpg';
                            $image_path = "../img/services/{$image_name}";
                            $absolute_image_path = $_SERVER['DOCUMENT_ROOT'] . "/img/services/{$image_name}";
                            ?>
                            <?php if (file_exists($absolute_image_path)): ?>
                                <img src="<?php echo $image_path; ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($service['name']); ?>"
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="bi bi-tools text-muted" style="font-size: 4rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($service['description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-primary fw-bold">$<?php echo number_format($service['price'], 2); ?></span>
                                    <span class="text-muted"><?php echo $service['duration']; ?> minutes</span>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="appointments.php?service_id=<?php echo $service['id']; ?>" class="btn btn-primary w-100">
                                    <i class="bi bi-calendar-plus"></i> Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Add error handling for missing footer include
try {
    require_once '../includes/footer.php';
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Footer include failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
} 