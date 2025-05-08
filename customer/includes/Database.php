<?php
// includes/Database.php

class Database {
    private static $conn = null;

    public static function connect() {
        if (self::$conn === null) {
            $host = 'localhost';
            $dbname = 'check'; // Updated to match our project database name
            $user = 'root';
            $pass = '';

            try {
                // Enable error reporting
                error_reporting(E_ALL);
                ini_set('display_errors', 1);

                // Create connection
                self::$conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Test the connection
                $test = self::$conn->query("SELECT DATABASE()")->fetchColumn();
                if ($test !== $dbname) {
                    throw new PDOException("Connected to wrong database: $test");
                }
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$conn;
    }
}
