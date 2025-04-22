<?php
require_once '../includes/auth.php';
header('Content-Type: application/json');

// Get database connection
$database = new Database();
$db = $database->getConnection();

$customer_id = $_GET['customer_id'] ?? 0;

if (!$customer_id) {
    echo json_encode([]);
    exit;
}

$query = "SELECT id, make, model, license_plate FROM vehicles WHERE customer_id = :customer_id ORDER BY make, model ASC";
$stmt = $db->prepare($query);
$stmt->bindParam(':customer_id', $customer_id);
$stmt->execute();

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)); 