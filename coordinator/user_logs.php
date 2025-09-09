<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('coordinator')) {
    echo "Access Denied";
    exit;
}

// Fetch only recent login logs
$stmt = $pdo->prepare("SELECT l.*, u.name, u.role 
                       FROM user_logs l 
                       JOIN users u ON l.user_id = u.id 
                       WHERE l.action = 'User logged in'
                       ORDER BY l.created_at DESC");
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Recent Logins - Coordinator</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background: #2c3e50;
            color: #fff;
            padding: 20px 0;
            flex-shrink: 0;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 20px;
            letter-spacing: 1px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 10px 0;
        }

        .sidebar ul li a {
            display: block;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: background 0.3s;
        }

        .sidebar ul li a:hover,
        .sidebar ul li.active a {
            background: #34495e;
            border-left: 4px solid #1abc9c;
        }

        /* Main content */
        .main-content {
            flex-grow: 1;
            padding: 30px;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        table th,
        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background: #1abc9c;
            color: white;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .chart-container {
            margin-top: 40px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Coordinator Panel</h2>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="assign_task.php">Assign Task</a></li>
                <li><a href="manage_instructors.php">Manage Instructors</a></li>
                <li><a href="edit_profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a></li>
                <li class="active"><a href="user_logs.php"><i class="fas fa-list"></i>Recent Logins</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main content -->
        <main class="main-content">
            <h1>Recent Logins</h1>
            <?php if (count($logs) > 0): ?>
                <table>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Date & Time</th>
                    </tr>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['name']) ?></td>
                            <td><?= htmlspecialchars($log['role']) ?></td>
                            <td><?= htmlspecialchars($log['action']) ?></td>
                            <td><?= htmlspecialchars($log['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No login activity found.</p>
            <?php endif; ?>



</body>

</html>