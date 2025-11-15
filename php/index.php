<?php
session_start();
require_once __DIR__ . "/inc/config.php";

// PHPMailer
require_once __DIR__ . "/PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/PHPMailer/src/SMTP.php";
require_once __DIR__ . "/PHPMailer/src/Exception.php";

// Composer autoloader — make sure this path is correct
require_once __DIR__ . "/../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

$error = null;

// ---------- EMAIL/PASSWORD + OTP LOGIN ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Find user by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // If instructor, check status
        if ($user['role'] === 'instructor' && $user['status'] !== 'active') {
            $error = "Your account is still pending approval by a coordinator.";
        } else {
            // Generate OTP
            $otp = rand(100000, 999999);

            // Store OTP in DB
            $otpStmt = $pdo->prepare("UPDATE users SET otp_code = ? WHERE id = ?");
            $otpStmt->execute([$otp, $user['id']]);

            // Store in session temporarily
            $_SESSION['temp_user_id'] = $user['id'];

            // Send OTP via email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = "smtp.gmail.com";
                $mail->SMTPAuth   = true;
                $mail->Username   = "gaudicosmickelangelo@gmail.com";       // change this
                $mail->Password   = "zpdc qvzx oahp eprr";          // change this
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom("yourgmail@gmail.com", "NBSC Login Verification");
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = "Your NBSC Login Verification Code";
                $mail->Body    = "
                    <h3>Your Verification Code:</h3>
                    <h1 style='letter-spacing:4px;'>$otp</h1>
                    <p>Enter this code to complete your login.</p>
                ";

                $mail->send();
            } catch (MailException $e) {
                $error = "Failed to send OTP email: " . $mail->ErrorInfo;
            }

            if (!$error) {
                // Redirect to OTP verification page
                header("Location: verify.php");
                exit;
            }
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
    <style>
        .footer {
            text-align: center;
            font-size: 12px;
            color: #888;
            margin-top: 40px;
        }

        .footer a {
            color: #2e6c8b;
            text-decoration: none;
        }

        .footer p {
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Login</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- EMAIL/PASSWORD LOGIN FORM -->
        <form method="post" action="">
            <label>Email:</label>
            <input type="email" name="email" required><br>
            <label>Password:</label>
            <input type="password" name="password" required><br>
            <button type="submit">Login</button>
        </form>

        <!-- Forgot Password Link -->
        <p><a href="forgot_password.php">Forgot your password?</a></p>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const message = urlParams.get('message');
            if (message) {
                switch (message) {
                    case 'invalid_email':
                        alert('Only nbsc.edu.ph emails are allowed for login.');
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