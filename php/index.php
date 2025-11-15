<?php
session_start();
require_once "inc/config.php";

// PHPMailer
require_once "PHPMailer/src/PHPMailer.php";
require_once "PHPMailer/src/SMTP.php";
require_once "PHPMailer/src/Exception.php";

require_once "vendor/autoload.php"; // Composer autoload for OAuth

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use League\OAuth2\Client\Provider\Google;

$error = null;

// ---------- GOOGLE OAUTH SETUP ----------
$googleProvider = new Google([
    'clientId'     => '1013090884414-qn4nj1oj419hoj72ehhv33kdvc8gqfs7.apps.googleusercontent.com',        // replace with your Client ID
    'clientSecret' => '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',    // replace with your Client Secret
    'redirectUri'  => 'http://localhost/task_mgmt/php/index.php', // must match Google console
]);

// Handle Google OAuth callback
if (isset($_GET['code'])) {
    try {
        if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            throw new Exception('Invalid OAuth state');
        }

        $token = $googleProvider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        $googleUser = $googleProvider->getResourceOwner($token);
        $email = $googleUser->getEmail();

        // Only allow nbsc.edu.ph emails
        if (!str_ends_with($email, '@nbsc.edu.ph')) {
            throw new Exception("Only nbsc.edu.ph emails are allowed for login");
        }

        // Check if user exists in DB
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new Exception("Email not registered. Please contact admin.");
        }

        // Check pending instructor
        if ($user['role'] === 'instructor' && $user['status'] !== 'active') {
            throw new Exception("Your account is still pending approval by a coordinator.");
        }

        // Login successful
        $_SESSION['user_id'] = $user['id'];
        header("Location: dashboard.php");
        exit;
    } catch (Exception $e) {
        $error = "Google login failed: " . $e->getMessage();
    }
}

// Generate Google OAuth URL if no code
if (!isset($_GET['code'])) {
    $googleAuthUrl = $googleProvider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $googleProvider->getState();
}

// ---------- EXISTING EMAIL/PASSWORD + OTP LOGIN ----------
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
                $mail->Subject = "Your NBSC Login Verification Code";
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
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- EMAIL/PASSWORD LOGIN FORM -->
        <form method="post">
            Email: <input type="email" name="email" required><br>
            Password: <input type="password" name="password" required><br>
            <button type="submit">Login</button>
        </form>

        <hr>

        <!-- GOOGLE LOGIN BUTTON -->
        <div style="text-align:center; margin-top:20px;">
            <a href="<?= htmlspecialchars($googleAuthUrl) ?>" class="google-btn">Login with Google</a>
        </div>

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

    <style>
        .google-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4285F4;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }

        .google-btn:hover {
            background-color: #357ae8;
        }
    </style>

</body>

</html>