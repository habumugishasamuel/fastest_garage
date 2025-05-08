<?php
require_once '../includes/Database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_and_view'])) {
    $vehicle_id = $_POST['vehicle_id'] ?? '';
    $service_id = $_POST['service_id'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Debug logging
    error_log("Booking attempt - User ID: " . $_SESSION['user_id']);
    error_log("Vehicle ID: " . $vehicle_id);
    error_log("Service ID: " . $service_id);
    error_log("Appointment Date: " . $appointment_date);
    
    // Validate required fields
    if (empty($vehicle_id) || empty($service_id) || empty($appointment_date)) {
        error_log("Validation failed - Missing required fields");
        $_SESSION['error'] = 'Please fill in all required fields.';
        header('Location: appointments.php');
        exit;
    }

    try {
        // Check if the selected time slot is available
        $query = "SELECT COUNT(*) as count FROM appointments 
                 WHERE appointment_date = :appointment_date 
                 AND status != 'cancelled'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':appointment_date', $appointment_date);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            error_log("Time slot already booked");
            $_SESSION['error'] = 'This time slot is already booked. Please choose another time.';
            header('Location: appointments.php');
            exit;
        }

        // Begin transaction
        $db->beginTransaction();
        
        try {
            // Insert the new appointment
            $query = "INSERT INTO appointments (customer_id, vehicle_id, service_id, appointment_date, notes, status) 
                     VALUES (:customer_id, :vehicle_id, :service_id, :appointment_date, :notes, 'scheduled')";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':customer_id', $_SESSION['user_id']);
            $stmt->bindParam(':vehicle_id', $vehicle_id);
            $stmt->bindParam(':service_id', $service_id);
            $stmt->bindParam(':appointment_date', $appointment_date);
            $stmt->bindParam(':notes', $notes);
            
            if ($stmt->execute()) {
                $db->commit();
                error_log("Appointment successfully saved");
                $_SESSION['booking_success'] = true;
                
                // Force redirect to booked appointments page
                header('Location: booked_appointments.php');
                exit;
            } else {
                error_log("Failed to execute appointment insert");
                throw new Exception("Failed to insert appointment");
            }
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Error in transaction: " . $e->getMessage());
            $_SESSION['error'] = 'Error saving appointment: ' . $e->getMessage();
            header('Location: appointments.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header('Location: appointments.php');
        exit;
    }
} else {
    error_log("Invalid request method or missing save_and_view parameter");
    // If accessed directly without POST data
    header('Location: appointments.php');
    exit;
} 