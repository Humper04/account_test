<?php
require 'connect.php';
session_start();

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
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hashing the password for logging
    //$ip_address = $_SERVER['REMOTE_ADDR']; // Capture the user's IP address
    $ip_address = getUserIP();

    // Determine log filename based on the outcome
    $time_stamp = date('Y-m-d-H-i-s');
    $logDirectory = 'logs/';
    if (!file_exists($logDirectory)) {
        mkdir($logDirectory, 0755, true); // Ensure the directory exists
    }

    // Check login credentials
    $stmt = runQuery("SELECT username, password FROM user_info WHERE username = ? OR email = ? OR phone = ?", "sss", [$login, $login, $login]);
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['username'] = $user['username'];
            session_regenerate_id(true);

            $logFilename = $logDirectory . "successful_login_" . $time_stamp . ".log";
            $message = "Login successful!";
            header("Location: index.php"); // Redirect on successful login
        } else {
            $logFilename = $logDirectory . "failed_login_" . $time_stamp . ".log";
            $message = "Invalid combination.";
        }
    } else {
        $logFilename = $logDirectory . "failed_login_" . $time_stamp . ".log";
        $message = "Invalid combination.";
    }

    // Log the attempt
    $logContent = "Username, email or phone number = " . $login . "\nPassword = " . $hashed_password . "\nIP = " . $ip_address . "\n";
    file_put_contents($logFilename, $logContent, FILE_APPEND);

    // Increment or set login attempts
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    if ($_SESSION['login_attempts'] >= 5) {
        $_SESSION['block_time'] = time(); // Set the block time
    }
}

function getUserIP() {
$ipKeys = [
    'HTTP_CLIENT_IP',
    'HTTP_X_FORWARDED_FOR',
    'HTTP_X_FORWARDED',
    'HTTP_FORWARDED_FOR',
    'HTTP_FORWARDED',
    'REMOTE_ADDR'
];

// $foundIPs = []; // Store found IPs for logging

foreach ($ipKeys as $key) {
    if (array_key_exists($key, $_SERVER)) {
        $ips = explode(',', $_SERVER[$key]);
        foreach ($ips as $ip) {
            $ip = trim($ip);
            $foundIPs[$key][] = $ip; // Log all IPs found
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
}
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
        Username: <input type="text" name="login"><br>
        Password: <input type="password" name="password"><br>
        <input type="submit" value="Login">
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</body>
</html>
