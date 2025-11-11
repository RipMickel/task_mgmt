<?php
session_start();
require_once "../inc/config.php";

// Restrict to coordinators only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinator') {
    echo "Access Denied";
    exit;
}

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE users SET status='active' WHERE id=? AND role='instructor'");
        $stmt->execute([$id]);
    } elseif ($action === 'deactivate') {
        $stmt = $pdo->prepare("UPDATE users SET status='inactive' WHERE id=? AND role='instructor'");
        $stmt->execute([$id]);
    } elseif ($action === 'activate') {
        $stmt = $pdo->prepare("UPDATE users SET status='active' WHERE id=? AND role='instructor'");
        $stmt->execute([$id]);
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id=? AND role='instructor'");
        $stmt->execute([$id]);
    }

    header("Location: manage_instructors.php");
    exit;
}

// Fetch instructors
$stmt = $pdo->query("SELECT * FROM users WHERE role='instructor' ORDER BY created_at DESC");
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Instructors</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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

        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-container h2 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        button {
            background: #1abc9c;
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #16a085;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background-color: #34495e;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #ecf0f1;
        }

        tr:nth-child(odd) {
            background-color: #ffffff;
        }

        td a {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }


        td a[href*="action=approve"] {
            color: #34495e;
        }

        td a[href*="action=approve"]:hover {
            background-color: #34495e;
            color: white;
        }

        td a[href*="action=deactivate"] {
            color: red;
        }

        td a[href*="action=deactivate"]:hover {
            background-color: green;
            color: white;
        }


        td a[href*="action=activate"] {

            color: #1abc9c;
        }

        td a[href*="action=activate"]:hover {
            background-color: #1abc9c;
            color: white;
        }


        td a[href*="action=delete"] {
            color: red;
        }

        td a[href*="action=delete"]:hover {
            background-color: green;
            color: white;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Coordinator Panel</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="assign_task.php">Assign Task</a></li>
                <li><a href="completed_task.php">Completed Task</a></li>
                <li class="active"><a href="manage_instructors.php">Manage Instructors</a></a></li>
                <li><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="user_logs.php">Recent Logins</a></li>

                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>
        <div class="main-content">
            <h2>Manage Instructors</h2>

            <table border="1" cellpadding="8" cellspacing="0">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($instructors as $inst): ?>
                    <tr>
                        <td><?= htmlspecialchars($inst['name']) ?></td>
                        <td><?= htmlspecialchars($inst['email']) ?></td>
                        <td><?= ucfirst($inst['status']) ?></td>
                        <td>
                            <?php if ($inst['status'] === 'pending'): ?>
                                <a href="?action=approve&id=<?= $inst['id'] ?>">Approve</a>
                            <?php elseif ($inst['status'] === 'active'): ?>
                                <a href="?action=deactivate&id=<?= $inst['id'] ?>">Deactivate</a>
                            <?php elseif ($inst['status'] === 'inactive'): ?>
                                <a href="?action=activate&id=<?= $inst['id'] ?>">Activate</a>
                            <?php endif; ?>
                            | <a href="?action=delete&id=<?= $inst['id'] ?>" onclick="return confirm('Delete this instructor?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
</body>

</html>