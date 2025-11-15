<?php
session_start();
require_once "inc/config.php";

if (!isset($_SESSION['temp_user_id'])) {
    // User cannot verify without login attempt
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = $_POST['otp'];
    $userId = $_SESSION['temp_user_id'];

    // Fetch user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['otp_code'] == $otp) {

        // Clear OTP after successful use
        $clear = $pdo->prepare("UPDATE users SET otp_code=NULL WHERE id=?");
        $clear->execute([$userId]);

        // Complete login session
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['name']      = $user['name'];
        $_SESSION['profile_image'] = !empty($user['profile_image']) ? $user['profile_image'] : null;

        // Remove temp ID
        unset($_SESSION['temp_user_id']);

        // Log the login
        $logStmt = $pdo->prepare("INSERT INTO user_logs (user_id, action) VALUES (?, ?)");
        $logStmt->execute([$user['id'], 'User logged in via 2FA']);

        // Redirect by role
        switch ($user['role']) {
            case 'admin':
                header("Location: admin/dashboard.php");
                break;
            case 'coordinator':
                header("Location: coordinator/dashboard.php");
                break;
            case 'instructor':
                header("Location: instructor/dashboard.php");
                break;
            default:
                die("Unknown role.");
        }
        exit;
    } else {
        $error = "Invalid verification code. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Enter Verification Code</title>
    <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>

    <div class="container">
        <h2>Two-Factor Authentication</h2>
        <p>A verification code was sent to your email.</p>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
            <label>Verification Code:</label><br>
            <input type="text" name="otp" maxlength="6" required><br><br>

            <button type="submit">Verify</button>
        </form>
    </div>

</body>

</html>