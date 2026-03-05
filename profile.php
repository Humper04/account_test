<?php
require 'connect.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    echo "You must be logged in to view this page.";
    exit;
}

// Fetch user details using user_id from session
$userId = $_SESSION['user_id'] ?? 0;
$stmt = runQuery("SELECT username, email, phone, role FROM user_info WHERE id = ?", "i", $userId);

if (!$stmt) {
    echo "Database query failed. Check error logs.";
    exit;
}

$result = $stmt->get_result();

if (!$result) {
    echo "Failed to fetch result.";
    exit;
}

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "No user information found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="profile.css">
</head>
<body>

<div class="profile-container">
    <h1>User Profile</h1>

    <div class="profile-info">
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
        <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
    </div>

    <div class="profile-links">
        <a href="index.php">Home</a>
        <p><a href="modify_information.php">Modify Information</a></p>
        <p><a href="logout.php" class="logout">Logout</a></p>
    </div>
</div>

</body>
</html>
