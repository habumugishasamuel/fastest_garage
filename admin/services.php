<?php
require_once '../includes/header.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

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
            
            $stmt = $db->prepare($query);
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
        $stmt = $db->prepare($query);
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
$stmt = $db->prepare($query);
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get service for editing
$edit_service = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM services WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $edit_service = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container-fluid">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Add/Edit Service Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <?php echo $edit_service ? 'Edit Service' : 'Add New Service'; ?>
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $edit_service ? 'edit' : 'add'; ?>">
                <?php if ($edit_service): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_service['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group mb-3">
                    <label for="name">Service Name *</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?php echo $edit_service ? htmlspecialchars($edit_service['name']) : ''; ?>" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php 
                        echo $edit_service ? htmlspecialchars($edit_service['description']) : ''; 
                    ?></textarea>
                </div>
                
                <div class="form-group mb-3">
                    <label for="price">Price ($) *</label>
                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0"
                           value="<?php echo $edit_service ? $edit_service['price'] : ''; ?>" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="duration">Duration (minutes) *</label>
                    <input type="number" class="form-control" id="duration" name="duration" min="1"
                           value="<?php echo $edit_service ? $edit_service['duration'] : ''; ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <?php echo $edit_service ? 'Update Service' : 'Add Service'; ?>
                </button>
                
                <?php if ($edit_service): ?>
                    <a href="services.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Services List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Services</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($service['name']); ?></td>
                                <td><?php echo htmlspecialchars($service['description']); ?></td>
                                <td>$<?php echo number_format($service['price'], 2); ?></td>
                                <td><?php echo $service['duration']; ?> minutes</td>
                                <td>
                                    <a href="services.php?edit=<?php echo $service['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Are you sure you want to delete this service?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 