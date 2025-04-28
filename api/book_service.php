<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

header('Content-Type: application/json');

// Ensure user is logged in and is a customer
if (!isLoggedIn() || !isCustomer()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Get POST data
    $service_id = $_POST['service_id'] ?? null;
    $booking_date = $_POST['booking_date'] ?? null;
    $notes = $_POST['notes'] ?? '';

    // Validate input
    if (!$service_id || !$booking_date) {
        throw new Exception('Missing required fields');
    }

    // Check if service exists and is active
    $service_query = "SELECT * FROM garage_services WHERE id = :id AND is_active = 1";
    $service_stmt = $conn->prepare($service_query);
    $service_stmt->bindParam(':id', $service_id);
    $service_stmt->execute();

    if ($service_stmt->rowCount() === 0) {
        throw new Exception('Service not found or inactive');
    }

    // Check for booking conflicts
    $conflict_query = "SELECT COUNT(*) FROM bookings 
                      WHERE service_id = :service_id 
                      AND booking_date = :booking_date 
                      AND status != 'cancelled'";
    $conflict_stmt = $conn->prepare($conflict_query);
    $conflict_stmt->bindParam(':service_id', $service_id);
    $conflict_stmt->bindParam(':booking_date', $booking_date);
    $conflict_stmt->execute();

    if ($conflict_stmt->fetchColumn() > 0) {
        throw new Exception('Time slot already booked');
    }

    // Create booking
    $booking_query = "INSERT INTO bookings (user_id, service_id, booking_date, notes) 
                     VALUES (:user_id, :service_id, :booking_date, :notes)";
    $booking_stmt = $conn->prepare($booking_query);
    $booking_stmt->bindParam(':user_id', $_SESSION['user_id']);
    $booking_stmt->bindParam(':service_id', $service_id);
    $booking_stmt->bindParam(':booking_date', $booking_date);
    $booking_stmt->bindParam(':notes', $notes);

    if (!$booking_stmt->execute()) {
        throw new Exception('Failed to create booking');
    }

    $booking_id = $conn->lastInsertId();

    // Create invoice
    $service = $service_stmt->fetch(PDO::FETCH_ASSOC);
    $invoice_number = 'INV-' . str_pad($booking_id, 6, '0', STR_PAD_LEFT);
    $due_date = date('Y-m-d', strtotime('+7 days'));

    $invoice_query = "INSERT INTO invoices (booking_id, invoice_number, total_amount, due_date) 
                     VALUES (:booking_id, :invoice_number, :total_amount, :due_date)";
    $invoice_stmt = $conn->prepare($invoice_query);
    $invoice_stmt->bindParam(':booking_id', $booking_id);
    $invoice_stmt->bindParam(':invoice_number', $invoice_number);
    $invoice_stmt->bindParam(':total_amount', $service['price']);
    $invoice_stmt->bindParam(':due_date', $due_date);

    if (!$invoice_stmt->execute()) {
        throw new Exception('Failed to create invoice');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully',
        'booking_id' => $booking_id,
        'invoice_number' => $invoice_number
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 