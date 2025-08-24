<?php
require_once "../inc/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Allow only nbsc.edu.ph emails
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@nbsc\.edu\.ph$/", $email)) {
        header("Location: ../index.php?message=invalid_email");
        exit;
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        // Redirect to login if already registered
        header("Location: ../index.php?message=already_registered");
        exit;
    }

    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
    $stmt->execute([$name, $email, $password, $role]);

    // Redirect to login after successful registration
    header("Location: ../index.php?message=registered_success");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <h2>Register</h2>

        <form method="post">
            Name: <input type="text" name="name" required><br>
            Email: <input type="email" name="email" required><br>
            Password: <input type="password" name="password" required><br>
            Role:
            <select name="role">
                <option value="instructor">Instructor</option>
                <option value="coordinator">Coordinator</option>
                <option value="admin">Admin</option>
            </select><br>
            <button type="submit">Register</button>
        </form>

        <p>Already have an account? <a href="../index.php" class="btn">Login Here</a></p>
    </div>

    <script src="../assets/js/main.js"></script>

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