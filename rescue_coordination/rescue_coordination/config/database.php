<?php
/**
 * Database configuration
 */

// Database credentials
define('DB_HOST', get_env('DB_HOST', 'localhost'));
define('DB_NAME', get_env('DB_NAME', 'rescue_coordination'));
define('DB_USER', get_env('DB_USER', 'root'));
define('DB_PASS', get_env('DB_PASS', 'Uday@4351r'));

// Create a database connection
function getDbConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            )
        );
        return $conn;
    } catch (PDOException $e) {
        // Log the error and display a user-friendly message
        error_log("Database Connection Error: " . $e->getMessage());
        return null;
    }
}

// Check database connection
function checkDatabaseConnection() {
    $conn = getDbConnection();
    return $conn !== null;
}
