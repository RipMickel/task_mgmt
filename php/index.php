<?php
session_start();
require_once "inc/config.php";

// PHPMailer
require_once "PHPMailer/src/PHPMailer.php";
require_once "PHPMailer/src/SMTP.php";
require_once "PHPMailer/src/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        // Check pending instructor
        if ($user['role'] === 'instructor' && $user['status'] !== 'active') {
            $error = "Your account is still pending approval by a coordinator.";
        } else {

            // 🔐 Generate 2FA OTP
            $otp = rand(100000, 999999);

            // Store OTP in DB
            $otpStmt = $pdo->prepare("UPDATE users SET otp_code=? WHERE id=?");
            $otpStmt->execute([$otp, $user['id']]);

            // Store temporary session
            $_SESSION['temp_user_id'] = $user['id'];

            // 🔥 Send OTP using Gmail SMTP
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = "smtp.gmail.com";
                $mail->SMTPAuth = true;

                // TODO: change these ↓↓↓
                $mail->Username = "gaudicosmickelangelo@gmail.com";  // Gmail
                $mail->Password = "zpdc qvzx oahp eprr";    // App password

                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom("yourgmail@gmail.com", "NBSC Login Verification");
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = "Your ICS CID TM Login Verification Code";
                $mail->Body = "
                    <h3>Your Verification Code:</h3>
                    <h1 style='letter-spacing:4px;'>$otp</h1>
                    <p>Enter this code to complete your login.</p>
                ";

                $mail->send();
            } catch (Exception $e) {
                $error = "Failed to send OTP email: " . $mail->ErrorInfo;
            }

            // Redirect to OTP page
            header("Location: verify.php");
            exit;
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>

    <div class="container">
        <h2>Login</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
            Email: <input type="email" name="email" required><br>
            Password: <input type="password" name="password" required><br>
            <button type="submit">Login</button>
        </form>
    </div>

    <script src="assets/js/main.js"></script>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const message = urlParams.get('message');

            if (message) {
                switch (message) {
                    case 'invalid_email':
                        alert('Only nbsc.edu.ph emails are allowed for registration.');
                        break;
                    case 'already_registered':
                        alert('Email already exists. Please login.');
                        break;
                    case 'registered_success':
                        alert('Registration successful! Please login.');
                        break;
                }
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>

</body>

</html>