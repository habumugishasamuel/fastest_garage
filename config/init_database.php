<?php
require_once 'database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Disable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Drop existing tables in correct order
    $conn->exec("DROP TABLE IF EXISTS appointments");
    $conn->exec("DROP TABLE IF EXISTS services");
    $conn->exec("DROP TABLE IF EXISTS users");
    
    // Create services table with additional columns
    $conn->exec("CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        duration INT NOT NULL,
        image_url VARCHAR(255) NOT NULL DEFAULT 'n.jpeg',
        is_active BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Create users table
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Create appointments table
    $conn->exec("CREATE TABLE IF NOT EXISTS appointments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        service_id INT NOT NULL,
        appointment_date DATE NOT NULL,
        appointment_time TIME NOT NULL,
        status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Insert default admin user
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->exec("INSERT INTO users (username, password, email, role) 
                VALUES ('admin', '$password', 'admin@example.com', 'admin')");

    // Insert default services
    $services = [
        [
            'name' => 'Brake Repair',
            'description' => 'Expert brake repair service including pad replacement, rotor inspection, and complete brake system diagnostics. We ensure your vehicle stops safely every time.',
            'price' => 50.75,
            'duration' => 45,
            'image_url' => 'brake-repair.jpg'
        ],
        [
            'name' => 'Tire Repair',
            'description' => 'Professional tire repair service including puncture repair, tire rotation, balancing, and alignment. We handle all types of tire issues with precision and care.',
            'price' => 35.99,
            'duration' => 30,
            'image_url' => 'tire-repair.jpg'
        ],
        [
            'name' => 'Car Wash & Detailing',
            'description' => 'Premium car wash and detailing service. Includes exterior wash, interior cleaning, waxing, and premium finish protection. Make your car shine like new!',
            'price' => 45.99,
            'duration' => 60,
            'image_url' => 'n.jpeg'
        ]
    ];

    // Begin transaction for services
    $conn->beginTransaction();

    // Insert default services
    $stmt = $conn->prepare("INSERT INTO services (name, description, price, duration, image_url) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($services as $service) {
        if (!$stmt->execute([
            $service['name'],
            $service['description'],
            $service['price'],
            $service['duration'],
            $service['image_url']
        ])) {
            throw new Exception("Failed to insert service: " . $service['name']);
        }
    }
    
    // Re-enable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    $conn->commit();
    echo "Database initialized successfully! All tables created and populated with default data.";
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error: " . $e->getMessage();
}
?> 