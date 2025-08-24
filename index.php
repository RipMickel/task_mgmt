<?php
session_start();
require_once "inc/config.php";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

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
        }
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Task Manager Login</title>
</head>

<body>
    <h2>Login</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit" name="login">Login</button>
    </form>
</body>

</html>