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
        $make = $_POST['make'] ?? '';
        $model = $_POST['model'] ?? '';
        $year = $_POST['year'] ?? '';
        $license_plate = $_POST['license_plate'] ?? '';
        $vin = $_POST['vin'] ?? '';
        $vehicle_type = $_POST['vehicle_type'] ?? '';
        
        if (empty($make) || empty($model) || empty($year) || empty($license_plate) || empty($vin)) {
            $error = 'Please fill in all required fields';
        } else {
            if ($action == 'add') {
                $query = "INSERT INTO vehicles (customer_id, make, model, year, license_plate, vin, vehicle_type) 
                         VALUES (:customer_id, :make, :model, :year, :license_plate, :vin, :vehicle_type)";
            } else {
                $id = $_POST['id'] ?? 0;
                $query = "UPDATE vehicles SET make = :make, model = :model, year = :year, 
                         license_plate = :license_plate, vin = :vin, vehicle_type = :vehicle_type 
                         WHERE id = :id AND customer_id = :customer_id";
            }
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':make', $make);
            $stmt->bindParam(':model', $model);
            $stmt->bindParam(':year', $year);
            $stmt->bindParam(':license_plate', $license_plate);
            $stmt->bindParam(':vin', $vin);
            $stmt->bindParam(':vehicle_type', $vehicle_type);
            
            if ($action == 'add') {
                $stmt->bindParam(':customer_id', $_SESSION['user_id']);
            } else {
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':customer_id', $_SESSION['user_id']);
            }
            
            if ($stmt->execute()) {
                $message = 'Vehicle ' . ($action == 'add' ? 'added' : 'updated') . ' successfully';
            } else {
                $error = 'Error ' . ($action == 'add' ? 'adding' : 'updating') . ' vehicle';
            }
        }
    } elseif ($action == 'delete') {
        $id = $_POST['id'] ?? 0;
        
        $query = "DELETE FROM vehicles WHERE id = :id AND customer_id = :customer_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':customer_id', $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $message = 'Vehicle deleted successfully';
        } else {
            $error = 'Error deleting vehicle';
        }
    }
}

// Get all vehicles for the customer
$query = "SELECT * FROM vehicles WHERE customer_id = :customer_id ORDER BY make, model ASC";
$stmt = $db->prepare($query);
$stmt->bindParam(':customer_id', $_SESSION['user_id']);
$stmt->execute();
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get vehicle for editing
$edit_vehicle = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM vehicles WHERE id = :id AND customer_id = :customer_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':customer_id', $_SESSION['user_id']);
    $stmt->execute();
    $edit_vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container-fluid">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Vehicles List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">My Vehicles</h6>
            <a href="vehicles.php?action=new" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Add Vehicle
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Make</th>
                            <th>Model</th>
                            <th>Year</th>
                            <th>License Plate</th>
                            <th>VIN</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($vehicle['make']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['model']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['year']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['license_plate']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['vin']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($vehicle['vehicle_type'])); ?></td>
                                <td>
                                    <a href="vehicles.php?edit=<?php echo $vehicle['id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $vehicle['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
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

    <!-- Add/Edit Vehicle Form -->
    <?php if (isset($_GET['action']) && $_GET['action'] == 'new' || $edit_vehicle): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <?php echo $edit_vehicle ? 'Edit Vehicle' : 'Add New Vehicle'; ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $edit_vehicle ? 'edit' : 'add'; ?>">
                    <?php if ($edit_vehicle): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_vehicle['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group mb-3">
                        <label for="make">Make *</label>
                        <input type="text" class="form-control" id="make" name="make" 
                               value="<?php echo $edit_vehicle ? htmlspecialchars($edit_vehicle['make']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="model">Model *</label>
                        <input type="text" class="form-control" id="model" name="model" 
                               value="<?php echo $edit_vehicle ? htmlspecialchars($edit_vehicle['model']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="year">Year *</label>
                        <input type="number" class="form-control" id="year" name="year" min="1900" max="2099" 
                               value="<?php echo $edit_vehicle ? htmlspecialchars($edit_vehicle['year']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="license_plate">License Plate *</label>
                        <input type="text" class="form-control" id="license_plate" name="license_plate" 
                               value="<?php echo $edit_vehicle ? htmlspecialchars($edit_vehicle['license_plate']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="vin">VIN *</label>
                        <input type="text" class="form-control" id="vin" name="vin" 
                               value="<?php echo $edit_vehicle ? htmlspecialchars($edit_vehicle['vin']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="vehicle_type">Vehicle Type</label>
                        <select class="form-control" id="vehicle_type" name="vehicle_type">
                            <option value="car" <?php echo $edit_vehicle && $edit_vehicle['vehicle_type'] == 'car' ? 'selected' : ''; ?>>Car</option>
                            <option value="truck" <?php echo $edit_vehicle && $edit_vehicle['vehicle_type'] == 'truck' ? 'selected' : ''; ?>>Truck</option>
                            <option value="suv" <?php echo $edit_vehicle && $edit_vehicle['vehicle_type'] == 'suv' ? 'selected' : ''; ?>>SUV</option>
                            <option value="van" <?php echo $edit_vehicle && $edit_vehicle['vehicle_type'] == 'van' ? 'selected' : ''; ?>>Van</option>
                            <option value="motorcycle" <?php echo $edit_vehicle && $edit_vehicle['vehicle_type'] == 'motorcycle' ? 'selected' : ''; ?>>Motorcycle</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_vehicle ? 'Update Vehicle' : 'Add Vehicle'; ?>
                    </button>
                    
                    <a href="vehicles.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?> 