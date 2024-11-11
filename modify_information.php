<?php
require 'connect.php';
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php'); // Redirect if not logged in
    exit();
}

$message = '';

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidPhone($phone) {
    return preg_match('/^(\+(\d{1,3})\s?)?(\d{10,12})$|^0(\d{9,11})$/', $phone);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = $_POST['new_username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validate inputs
    if (!empty($email) && !isValidEmail($email)) {
        $message = "Invalid email format. Please enter a valid email address.";
    } elseif (!empty($phone) && !isValidPhone($phone)) {
        $message = "Invalid phone number format. Please enter a valid phone number.";
    } elseif (!empty($password) && $password !== $password_confirm) {
        $message = "Passwords do not match.";
    } else {
        $current_username = $_SESSION['username'];
        
        // Hash the password if provided
        $hashed_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

        $update_successful = updateUserInfo($current_username, $new_username, $email, $phone, $hashed_password);
        
        if ($update_successful) {
            $_SESSION['username'] = $new_username; // Update session username
            $message = 'Information updated successfully.';
        } else {
            $message = 'No changes were made to your information.';
        }
    }
}

function updateUserInfo($current_username, $new_username, $email, $phone, $hashed_password) {
    global $conn;

    // Retrieve current user information from the database
    $sql = "SELECT username, email, phone, password FROM user_info WHERE username = ?";
    $stmt = runQuery($sql, 's', [$current_username]);
    $stmt->bind_result($current_username_db, $current_email, $current_phone, $current_password);
    $stmt->fetch();
    $stmt->close();

    // Check for differences
    $updates = [];
    $params = [];
    $types = '';

    if (!empty($new_username) && $new_username !== $current_username_db) {
        $updates[] = "username = ?";
        $params[] = $new_username;
        $types .= 's';
    }

    if (!empty($email) && $email !== $current_email) {
        $updates[] = "email = ?";
        $params[] = $email;
        $types .= 's';
    }

    if (!empty($phone) && $phone !== $current_phone) {
        $updates[] = "phone = ?";
        $params[] = $phone;
        $types .= 's';
    }

    if (!empty($hashed_password) && !password_verify($hashed_password, $current_password)) {
        $updates[] = "password = ?";
        $params[] = $hashed_password;
        $types .= 's';
    }

    // If no updates are needed, return false
    if (empty($updates)) {
        return false;
    }

    // Build update query dynamically
    $sql = "UPDATE user_info SET " . implode(", ", $updates) . " WHERE username = ?";
    $params[] = $current_username;
    $types .= 's';

    $stmt = runQuery($sql, $types, $params);
    return $stmt && $stmt->affected_rows > 0;
}

?>
<form method="post">
    New Username: <input type="text" name="new_username" value="<?= htmlspecialchars($_SESSION['username']) ?>"><br>
    Email: <input type="text" name="email"><br>
    Phone: <input type="text" name="phone"><br>
    Password: <input type="password" name="password"><br>
    Confirm Password: <input type="password" name="password_confirm"><br>
    <input type="submit" value="Update Information">
</form>
<div><?= htmlspecialchars($message) ?></div>
