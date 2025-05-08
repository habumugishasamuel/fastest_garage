<?php
session_start();
require_once '../customer/includes/Database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->connect();

// Fetch all vehicles with customer info
$query = "SELECT v.*, u.username, u.email FROM vehicles v JOIN users u ON v.customer_id = u.id ORDER BY v.make, v.model ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Vehicles</title>
    <link rel="stylesheet" href="../customer/includes/styles.css">
</head>
<body>
    <h1>All Vehicles</h1>
    <a href="dashboard.php">&larr; Back to Admin Dashboard</a>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Email</th>
                <th>Make</th>
                <th>Model</th>
                <th>Year</th>
                <th>License Plate</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($vehicles)): ?>
                <tr><td colspan="7">No vehicles found.</td></tr>
            <?php else: ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vehicle['username']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['email']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['make']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['model']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['year']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['license_plate']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html> 