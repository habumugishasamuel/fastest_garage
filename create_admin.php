<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$database = new Database();
$conn = $database->getConnection();

// Check if admin user exists
$check_query = "SELECT * FROM users WHERE role = 'admin' LIMIT 1";
$check_stmt = $conn->prepare($check_query);
$check_stmt->execute();

if ($check_stmt->rowCount() > 0) {
    echo "Admin user already exists. You can log in with the existing admin credentials.";
    exit();
}

// Create admin user
$name = "Admin";
$email = "admin@garage.com";
$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$role = "admin";

$insert_query = "INSERT INTO users (name, email, username, password, role) 
                 VALUES (:name, :email, :username, :password, :role)";
$insert_stmt = $conn->prepare($insert_query);
$insert_stmt->bindParam(':name', $name);
$insert_stmt->bindParam(':email', $email);
$insert_stmt->bindParam(':username', $username);
$insert_stmt->bindParam(':password', $password);
$insert_stmt->bindParam(':role', $role);

if ($insert_stmt->execute()) {
    echo "Admin user created successfully!<br>";
    echo "You can now log in with:<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "<a href='login.php'>Go to Login Page</a>";
} else {
    echo "Error creating admin user.";
}
?> 