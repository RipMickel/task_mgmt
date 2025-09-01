<?php
session_start();
require_once "inc/config.php"; // Adjusted path for root

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['profile_image'] = !empty($user['profile_image']) ? $user['profile_image'] : null;


        // Role-based redirection
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
                $error = "Unknown role.";
        }
        exit;
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
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

        <p>Don't have an account? <a href="auth/register.php" class="btn">Register Here</a></p>
    </div>

    <script src="assets/js/main.js"></script>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Parse URL parameters
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

                // Remove the query string so it doesn't alert again on refresh
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
</body>

</html>