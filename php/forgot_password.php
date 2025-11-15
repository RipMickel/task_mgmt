<?php
session_start();
require_once __DIR__ . "/inc/config.php"; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    // Find user by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generate reset token (unique and expires in 1 hour)
        $token = bin2hex(random_bytes(50)); // Random token
        $expires = date("U") + 3600; // Token expiration time (1 hour)

        // Store token and expiration time in DB
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->execute([$token, $expires, $email]);

        // Send reset email via Gmail SMTP
        require_once __DIR__ . "/PHPMailer/src/PHPMailer.php";
        require_once __DIR__ . "/PHPMailer/src/SMTP.php";
        require_once __DIR__ . "/PHPMailer/src/Exception.php";

        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "gaudicosmickelangelo@gmail.com"; // Your Gmail address
            $mail->Password = "zpdc qvzx oahp eprr";          // Your Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom("yourgmail@gmail.com", "Password Reset Request");
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Password Reset Request";
            $resetLink = "http://localhost/your_project/reset_password.php?token=$token";
            $mail->Body = "
                <h3>Click the link below to reset your password:</h3>
                <a href='$resetLink'>Reset Password</a>
            ";

            $mail->send();
            echo "A password reset link has been sent to your email.";
        } catch (Exception $e) {
            echo "Error sending email: " . $mail->ErrorInfo;
        }
    } else {
        echo "No user found with that email.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Forgot Password</title>
</head>

<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <form method="post" action="">
            <label for="email">Enter your email:</label>
            <input type="email" name="email" required><br>
            <button type="submit">Submit</button>
        </form>
    </div>
</body>

</html>
