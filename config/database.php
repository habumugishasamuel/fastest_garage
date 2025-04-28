<?php
class Database {
    private $host = "127.0.0.1";
    private $db_name = "simple_cms";
    private $username = "root";
    private $password = "";
    private $port = "3306";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // First check if MySQL server is running with explicit port and host
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port;
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 5,
                )
            );

            // Check if database exists
            $stmt = $this->conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$this->db_name}'");
            $databaseExists = $stmt->fetch();

            if (!$databaseExists) {
                // Create database if it doesn't exist
                $this->conn->exec("CREATE DATABASE IF NOT EXISTS `" . $this->db_name . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
            
            // Select the database
            $this->conn->exec("USE `" . $this->db_name . "`");
            
            // Set character set
            $this->conn->exec("SET NAMES utf8mb4");
            
            return $this->conn;
        } catch(PDOException $e) {
            $errorMessage = "Database connection failed: ";
            
            // Provide more specific error messages
            switch($e->getCode()) {
                case 2002:
                    $errorMessage .= "Could not connect to MySQL server. Please check that: \n";
                    $errorMessage .= "1. XAMPP's MySQL service is running\n";
                    $errorMessage .= "2. MySQL is running on port {$this->port}\n";
                    $errorMessage .= "3. No firewall is blocking the connection";
                    break;
                case 1045:
                    $errorMessage .= "Access denied. Please check your username and password.";
                    break;
                case 1049:
                    $errorMessage .= "Database '{$this->db_name}' does not exist and could not be created.";
                    break;
                default:
                    $errorMessage .= $e->getMessage();
            }
            
            error_log("Connection Error: " . $e->getMessage());
            throw new Exception($errorMessage);
        }
    }
}
?> 