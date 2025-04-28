<?php
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check if services table exists
    $tables = $conn->query("SHOW TABLES LIKE 'services'")->fetchAll();
    if (empty($tables)) {
        echo "Services table does not exist!<br>";
        exit;
    }
    
    // Get all services
    $stmt = $conn->query("SELECT * FROM services");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($services)) {
        echo "No services found in the database!<br>";
        echo "Running database initialization...<br>";
        require_once 'config/init_database.php';
        echo "Please refresh this page to see the results.<br>";
    } else {
        echo "<h2>Found " . count($services) . " services:</h2>";
        echo "<pre>";
        print_r($services);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 