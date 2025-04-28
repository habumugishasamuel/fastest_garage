<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

header('Content-Type: application/json');

// Ensure user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $database = new Database();
    $conn = $database->getConnection();

    $customer_id = $_GET['customer_id'] ?? null;

    if (!$customer_id) {
        throw new Exception('Customer ID is required');
    }

    // Get customer's bookings
    $bookings_query = "SELECT b.*, gs.name as service_name 
                      FROM bookings b 
                      JOIN garage_services gs ON b.service_id = gs.id 
                      WHERE b.user_id = :user_id 
                      ORDER BY b.booking_date DESC";
    $bookings_stmt = $conn->prepare($bookings_query);
    $bookings_stmt->bindParam(':user_id', $customer_id);
    $bookings_stmt->execute();
    $bookings = $bookings_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'bookings' => $bookings
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 