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

    // Get customer's invoices
    $invoices_query = "SELECT i.*, b.booking_date, gs.name as service_name 
                      FROM invoices i 
                      JOIN bookings b ON i.booking_id = b.id 
                      JOIN garage_services gs ON b.service_id = gs.id 
                      WHERE b.user_id = :user_id 
                      ORDER BY i.created_at DESC";
    $invoices_stmt = $conn->prepare($invoices_query);
    $invoices_stmt->bindParam(':user_id', $customer_id);
    $invoices_stmt->execute();
    $invoices = $invoices_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'invoices' => $invoices
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 