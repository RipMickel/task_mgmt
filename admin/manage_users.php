<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('admin')) {
    echo "Access Denied";
    exit;
}

// Add new user
if (isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
    $stmt->execute([$name, $email, $password, $role]);
}

// Delete user
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
}

// Fetch users
$users = $pdo->query("SELECT * FROM users")->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Users</title>
</head>

<body>
    <h2>Manage Users</h2>

    <form method="post">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role">
            <option value="admin">Admin</option>
            <option value="coordinator">Coordinator</option>
            <option value="instructor">Instructor</option>
        </select>
        <button type="submit" name="add_user">Add User</button>
    </form>

    <h3>Existing Users</h3>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= $user['name'] ?></td>
                <td><?= $user['email'] ?></td>
                <td><?= $user['role'] ?></td>
                <td><a href="?delete=<?= $user['id'] ?>" onclick="return confirm('Delete user?')">Delete</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <a href="dashboard.php">Back to Dashboard</a>
</body>

</html>