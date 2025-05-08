<?php
session_start();
require_once 'includes/Database.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = 'Please enter both username and password.';
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT id, username, password, role FROM users WHERE username = :username";
$stmt = $db->prepare($query);
$stmt->bindParam(':username', $username);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    if ($user['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: customer/dashboard.php');
    }
    exit;
} else {
    $_SESSION['login_error'] = 'Invalid username or password.';
    header('Location: login.php');
    exit;
} 