<?php
session_start();
require_once __DIR__ . "/includes/config.php"; // Database connection

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Validate token and check expiration time
    $stmt = $pdo->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($user['reset_expires'] >= time()) {
            // Token is valid
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

                // Update password and clear the reset token
                $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
                $stmt->execute([$new_password, $user['id']]);

                echo "Your password has been reset successfully!";
                header("Location: index.php"); // Redirect to login page
                exit;
            }
        } else {
            echo "This password reset link has expired.";
        }
    } else {
        echo "Invalid token.";
    }
} else {
    echo "No token provided.";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Reset Password</title>
</head>

<body>
    <div class="container">
        <h2>Reset Password</h2>
        <form method="post" action="">
            <label for="password">New Password:</label>
            <input type="password" name="password" required><br>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>

</html>