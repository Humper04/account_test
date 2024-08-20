<?php
require 'connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Get user data from the database using the username stored in session
$username = $_SESSION['username'];
$stmt = runQuery("SELECT email, phone, username FROM user_info WHERE username = ?", "s", [$username]);
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "Email: " . htmlspecialchars($user['email']) . "<br>";
    echo "Phone: " . htmlspecialchars($user['phone']) . "<br>";
    echo "Username: " . htmlspecialchars($user['username']) . "<br>";
    // Optionally, show a logout link
    echo "<a href='logout.php'>Logout</a><br>";
    // Add a button or link to modify_information.php
    echo "<a href='modify_information.php'><button>Modify Information</button></a>";
} else {
    echo "No user information found.";
}
?>
