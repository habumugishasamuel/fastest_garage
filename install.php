<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

try {
    // Create database connection
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h2>Installing Simple CMS...</h2>";
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "<p style='color: green;'>✓ Users table created successfully</p>";
    
    // Create pages table
    $sql = "CREATE TABLE IF NOT EXISTS pages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content TEXT,
        status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "<p style='color: green;'>✓ Pages table created successfully</p>";
    
    // Create menu_items table
    $sql = "CREATE TABLE IF NOT EXISTS menu_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        url VARCHAR(255) NOT NULL,
        parent_id INT DEFAULT NULL,
        position INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "<p style='color: green;'>✓ Menu items table created successfully</p>";
    
    // Create services table
    $sql = "CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        duration INT NOT NULL DEFAULT 60,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "<p style='color: green;'>✓ Services table created successfully</p>";
    
    // Create settings table
    $sql = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) NOT NULL UNIQUE,
        setting_value TEXT,
        setting_description VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "<p style='color: green;'>✓ Settings table created successfully</p>";
    
    // Check if admin user exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    $adminExists = $stmt->fetchColumn() > 0;
    
    if (!$adminExists) {
        // Create admin user
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@example.com', :password, 'admin')");
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        echo "<p style='color: green;'>✓ Admin user created successfully</p>";
    } else {
        echo "<p style='color: blue;'>ℹ Admin user already exists</p>";
    }
    
    // Insert default settings if they don't exist
    $default_settings = [
        ['site_name', 'Simple CMS', 'The name of your website'],
        ['site_description', 'A simple content management system', 'A brief description of your website'],
        ['contact_email', 'admin@example.com', 'Primary contact email address'],
        ['items_per_page', '10', 'Number of items to display per page'],
        ['maintenance_mode', '0', 'Enable maintenance mode (0 = off, 1 = on)']
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO settings (setting_key, setting_value, setting_description) VALUES (?, ?, ?)");
    foreach ($default_settings as $setting) {
        $stmt->execute($setting);
    }
    echo "<p style='color: green;'>✓ Default settings created successfully</p>";
    
    echo "<div style='margin-top: 20px; padding: 10px; background-color: #dff0d8; border: 1px solid #d6e9c6; border-radius: 4px;'>";
    echo "<h3 style='color: #3c763d;'>Installation Completed Successfully!</h3>";
    echo "<p>You can now <a href='login.php'>login</a> with:</p>";
    echo "<p>Username: <strong>admin</strong><br>";
    echo "Password: <strong>admin123</strong></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='margin-top: 20px; padding: 10px; background-color: #f2dede; border: 1px solid #ebccd1; border-radius: 4px;'>";
    echo "<h3 style='color: #a94442;'>Installation Error</h3>";
    $errorCode = $e->getCode();
    if ($errorCode == 1045) {
        echo "<p>Database connection failed: Access denied. Please check your username and password.</p>";
    } elseif ($errorCode == 1049) {
        echo "<p>Database connection failed: Database not found. Please create the database first.</p>";
    } elseif ($errorCode == 2002) {
        echo "<p>Database connection failed: Could not connect to MySQL server. Please check if MySQL is running.</p>";
    } else {
        echo "<p>Database Error: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
}
?> 