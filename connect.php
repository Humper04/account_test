<?php

require_once '/var/www/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable('/var/www/');
$dotenv->load();

$host = 'localhost';  // Server IP address or hostname
$db = 'PPP4';         // Updated database name
$user = 'login';
$pass = $_ENV['DB_PASSWORD_LOGIN'];
$charset = 'utf8mb4';

// Debug output - Uncomment if needed for debugging
/*
var_dump($pass);

if (!$pass) {
    echo "Error: Environment variable 'DB_PASSWORD_ROOT' not set or empty.";
    exit;
}

echo "Database password loaded successfully: " . htmlspecialchars($pass, ENT_QUOTES, 'UTF-8');
*/

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Success message - Uncomment if needed for debugging
// echo "Connected successfully!";

$conn->set_charset($charset);

function runQuery($sql, $types = null, $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        // Handle errors in preparation
        error_log("MySQL prepare error: " . $conn->error); // Log error to server's error log
        return false; // Return false to indicate failure
    }

    if ($types && $params) {
        if (!$stmt->bind_param($types, ...$params)) {
            // Handle binding errors
            error_log("MySQL bind_param error: " . $stmt->error);
            return false;
        }
    }

    if (!$stmt->execute()) {
        // Handle execution errors
        error_log("MySQL execute error: " . $stmt->error);
        return false;
    }

    return $stmt;
}
