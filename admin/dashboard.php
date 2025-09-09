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
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        header {
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            text-align: center;
        }

        .dashboard {
            display: flex;
            height: calc(100vh - 60px);
        }

        .sidebar {
            width: 250px;
            background: #34495e;
            color: #ecf0f1;
            display: flex;
            flex-direction: column;
            padding: 20px 0;
        }

        .sidebar h3 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background: #2c3e50;
        }

        .main-content {
            flex: 1;
            padding: 30px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card h2 {
            margin: 0;
            font-size: 22px;
            color: #2c3e50;
        }

        .logout-btn {
            margin-top: auto;
            background: #e74c3c;
            text-align: center;
            padding: 12px;
            color: white;
            font-weight: bold;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: #c0392b;
        }
    </style>

</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                    <a href="dashboard.php">Dashboard</a>
                </li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="roles.php">Manage Roles</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>
        <!-- Main Content -->
        <main class="main-content">
            <div class="welcome-container">
                <?php
                $profilePic = !empty($_SESSION['profile_image'])
                    ? "../uploads/profiles/" . $_SESSION['profile_image']
                    : "../assets/images/default.png";
                ?>
                <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile">
                <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?> (Admin)</h1>
            </div>
            <!-- Cards -->
            <div class="cards">
                <div class="card">
                    <h3>Total Users</h3>
                    <p>120</p>
                </div>
                <div class="card">
                    <h3>Active Instructors</h3>
                    <p>35</p>
                </div>
                <div class="card">
                    <h3>Pending Approvals</h3>
                    <p>8</p>
                </div>
                <div class="card">
                    <h3>System Logs</h3>
                    <p>452</p>
                </div>
            </div>

        </main>
    </div>
</body>

</html>