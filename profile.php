<?php
require 'connect.php';
session_start();

// Debugging session
if (!isset($_SESSION['username'])) {
    echo "Session error: Username is not set.";
    exit();
}

$username = $_SESSION['username'];

// Debugging database query
$stmt = runQuery("SELECT email, phone, username FROM user_info WHERE username = ?", "s", [$username]);

if (!$stmt) {
    echo "Database query failed. Check error logs.";
    exit();
}

$result = $stmt->get_result();

if (!$result) {
    echo "Failed to fetch result.";
    exit();
}

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "Email: " . htmlspecialchars($user['email']) . "<br>";
    echo "Phone: " . htmlspecialchars($user['phone']) . "<br>";
    echo "Username: " . htmlspecialchars($user['username']) . "<br>";
    echo "<a href='modify_information.php'><button>Modify Information</button></a><br>";
    echo "<a href='logout.php'>Logout</a>";
} else {
    echo "No user information found.";
}
?>
