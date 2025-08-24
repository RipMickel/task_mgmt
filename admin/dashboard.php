<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('admin')) {
    echo "Access Denied";
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
</head>

<body>
    <h2>Welcome Admin, <?= $_SESSION['name'] ?></h2>
    <ul>
        <li><a href="manage_users.php">Manage Users</a></li>
        <li><a href="roles.php">Manage Roles</a></li>

        <li><a href="../auth/logout.php">Logout</a></li>
    </ul>
</body>

</html>