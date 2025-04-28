<?php
$pageTitle = "Manage Services";

require_once '../config/database.php';
require_once '../includes/auth.php';
requireAdmin();

$db = new Database();
$conn = $db->getConnection();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add' || $action == 'edit') {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $duration = $_POST['duration'] ?? 0;
        
        if (empty($name) || empty($price) || empty($duration)) {
            $error = 'Please fill in all required fields';
        } else {
            if ($action == 'add') {
                $query = "INSERT INTO services (name, description, price, duration) VALUES (:name, :description, :price, :duration)";
            } else {
                $id = $_POST['id'] ?? 0;
                $query = "UPDATE services SET name = :name, description = :description, price = :price, duration = :duration WHERE id = :id";
            }
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':duration', $duration);
            
            if ($action == 'edit') {
                $stmt->bindParam(':id', $id);
            }
            
            if ($stmt->execute()) {
                $message = 'Service ' . ($action == 'add' ? 'added' : 'updated') . ' successfully';
            } else {
                $error = 'Error ' . ($action == 'add' ? 'adding' : 'updating') . ' service';
            }
        }
    } elseif ($action == 'delete') {
        $id = $_POST['id'] ?? 0;
        $query = "DELETE FROM services WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $message = 'Service deleted successfully';
        } else {
            $error = 'Error deleting service';
        }
    }
}

// Get all services
$query = "SELECT * FROM services ORDER BY name ASC";
$stmt = $conn->prepare($query);
$stmt->execute();
$services = $stmt->fetchAll();

// Get service for editing
$edit_service = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM services WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $edit_service = $stmt->fetch();
}

require_once 'layout.php';
?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Add/Edit Service Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <?php echo $edit_service ? 'Edit Service' : 'Add New Service'; ?>
        </h6>
        <?php if ($edit_service): ?>
            <a href="services.php" class="btn btn-sm btn-secondary">
                <i class="bi bi-plus-circle"></i> Add New Service
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="action" value="<?php echo $edit_service ? 'edit' : 'add'; ?>">
            <?php if ($edit_service): ?>
                <input type="hidden" name="id" value="<?php echo $edit_service['id']; ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Service Name *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo $edit_service ? htmlspecialchars($edit_service['name']) : ''; ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="price" class="form-label">Price ($) *</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0"
                               value="<?php echo $edit_service ? $edit_service['price'] : ''; ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="duration" class="form-label">Duration (minutes) *</label>
                        <input type="number" class="form-control" id="duration" name="duration" min="1"
                               value="<?php echo $edit_service ? $edit_service['duration'] : ''; ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php 
                    echo $edit_service ? htmlspecialchars($edit_service['description']) : ''; 
                ?></textarea>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>
                    <?php echo $edit_service ? 'Update Service' : 'Add Service'; ?>
                </button>
                
                <?php if ($edit_service): ?>
                    <a href="services.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Services List -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Services</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($services as $service): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-img-top position-relative" style="height: 200px; background-color: #f8f9fa;">
                            <?php
                            // Debug information
                            error_reporting(E_ALL);
                            ini_set('display_errors', 1);
                            
                            $defaultImage = '../imgs/n.jpeg';
                            $imagePath = '../imgs/' . (!empty($service['image_url']) ? $service['image_url'] : 'n.jpeg');
                            
                            // Debug output
                            echo "<!-- Debug: Image path = $imagePath -->";
                            echo "<!-- Debug: File exists = " . (file_exists($imagePath) ? 'true' : 'false') . " -->";
                            ?>
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                 class="w-100 h-100 object-fit-cover" 
                                 alt="<?php echo htmlspecialchars($service['name']); ?>"
                                 style="object-position: center"
                                 onerror="this.onerror=null; this.src='<?php echo $defaultImage; ?>'; console.log('Image failed to load:', this.src);"
                                 loading="lazy">
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($service['name']); ?></h5>
                                <span class="badge bg-primary fs-5">$<?php echo number_format($service['price'], 2); ?></span>
                            </div>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($service['description']); ?></p>
                            <div class="service-details mb-3">
                                <div class="d-flex align-items-center text-muted mb-2">
                                    <i class="bi bi-clock me-2"></i>
                                    <span>Duration: <?php echo $service['duration']; ?> minutes</span>
                                </div>
                                <div class="d-flex align-items-center text-muted">
                                    <i class="bi bi-tools me-2"></i>
                                    <span>Professional Equipment</span>
                                </div>
                            </div>
                            <div class="btn-group w-100">
                                <a href="services.php?edit=<?php echo $service['id']; ?>" 
                                   class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="if(confirm('Are you sure you want to delete this service?')) {
                                            document.getElementById('delete-form-<?php echo $service['id']; ?>').submit();
                                        }"
                                        data-bs-toggle="tooltip" title="Delete">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                            <form id="delete-form-<?php echo $service['id']; ?>" method="POST" action="" style="display: none;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-5px);
}
.object-fit-cover {
    object-fit: cover;
}
.badge {
    font-weight: 500;
}
.service-details {
    font-size: 0.9rem;
}
</style>

<?php require_once 'layout_end.php'; ?> 