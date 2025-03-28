<?php
require 'connect.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: index.php');
    exit();
}

$message = '';

// Rate Limiting Check
if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
    if (isset($_SESSION['block_time']) && (time() - $_SESSION['block_time']) <= 60) {
        die('You have been locked out of your account for 60 seconds due to multiple failed login attempts.');
    } else {
        unset($_SESSION['login_attempts'], $_SESSION['block_time']);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hashing for logging purposes
    $ip_address = getUserIP();

    // Determine log filename based on the outcome
    $time_stamp = date('Y-m-d-H-i-s');
    $logDirectory = 'logs/';
    if (!file_exists($logDirectory)) {
        mkdir($logDirectory, 0755, true);
    }

    // Check login credentials
    $stmt = runQuery("SELECT * FROM user_info WHERE username = ? OR email = ? OR phone = ?", "sss", [$login, $login, $login]);
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Store all user data in session
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_data'] = $user;

            session_regenerate_id(true); // Security improvement

            // Successful login logging
            $logFilename = $logDirectory . "successful_login_" . $time_stamp . ".log";
            $message = "Login successful!";
            header("Location: index.php");
            exit;
        } else {
            $logFilename = $logDirectory . "failed_login_" . $time_stamp . ".log";
            $message = "Invalid combination.";
        }
    } else {
        $logFilename = $logDirectory . "failed_login_" . $time_stamp . ".log";
        $message = "Invalid combination.";
    }

    // Log the attempt
    $logContent = "Username, email, or phone = " . $login . "\nPassword = " . $hashed_password . "\nIP = " . $ip_address . "\n";
    file_put_contents($logFilename, $logContent, FILE_APPEND);

    // Increment or set login attempts
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    if ($_SESSION['login_attempts'] >= 5) {
        $_SESSION['block_time'] = time();
    }
}

function getUserIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ips = explode(',', $_SERVER[$key]);
            foreach ($ips as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
    }
    return 'UNKNOWN';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <?php if (!empty($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="post">
        Username/Email/Phone: <input type="text" name="login" required><br>
        Password: <input type="password" name="password" required><br>
        <input type="submit" value="Login">
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</body>
</html>
