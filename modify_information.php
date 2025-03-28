<?php
require 'connect.php';
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
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
    $new_username = trim($_POST['new_username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($new_username) && empty($email) && empty($phone) && empty($password)) {
        $message = "Please fill in at least one field to update.";
    } else {
        if (!empty($email) && !isValidEmail($email)) {
            $message = "Invalid email format.";
        } elseif (!empty($phone) && !isValidPhone($phone)) {
            $message = "Invalid phone number format.";
        } else {
            if (!empty($password)) {
                $password = password_hash($password, PASSWORD_DEFAULT);
            }

            $current_username = $_SESSION['username'];
            $stmt = runQuery(
                "UPDATE user_info SET username = COALESCE(NULLIF(?, ''), username), 
                 email = COALESCE(NULLIF(?, ''), email), 
                 phone = COALESCE(NULLIF(?, ''), phone), 
                 password = COALESCE(NULLIF(?, ''), password) 
                 WHERE username = ?",
                "sssss",
                [$new_username, $email, $phone, $password, $current_username]
            );

            if ($stmt && $stmt->affected_rows > 0) {
                $_SESSION['username'] = !empty($new_username) ? $new_username : $_SESSION['username'];
                $message = "Information updated successfully.";
            } else {
                $message = "No changes were made.";
            }
        }
    }
}
?>

<form method="post">
    New Username: <input type="text" name="new_username" value="<?= htmlspecialchars($_SESSION['username']) ?>"><br>
    Email: <input type="text" name="email"><br>
    Phone: <input type="text" name="phone"><br>
    Password: <input type="password" name="password"><br>
    <input type="submit" value="Update Information">
</form>
<div><?= htmlspecialchars($message) ?></div>
