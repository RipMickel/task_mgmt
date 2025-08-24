<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('instructor')) {
    echo "Access Denied";
    exit;
}

// Fetch all task history with file uploads
$stmt = $pdo->prepare("SELECT th.*, t.title as task_title, t.description as task_desc, u.name as instructor_name, uc.name as coordinator_name
                       FROM task_history th
                       JOIN tasks t ON th.task_id = t.id
                       JOIN users u ON t.assigned_to = u.id
                       JOIN users uc ON t.assigned_by = uc.id
                       ORDER BY th.completed_at DESC");
$stmt->execute();
$taskHistory = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Task History</title>
    <link rel="stylesheet" href="../instructor/instructor.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }

        .dashboard-container {
            display: flex;
        }

        .sidebar {
            width: 220px;
            background-color: #2c3e50;
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 15px;
        }

        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 8px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .sidebar ul li a:hover {
            background-color: #34495e;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            background-color: #ecf0f1;
        }

        h1 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #2980b9;
            color: #fff;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        a.btn,
        a {
            color: #2980b9;
            text-decoration: none;
        }

        a.btn:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .alert-error {
            background-color: #e74c3c;
            color: white;
        }

        .alert-success {
            background-color: #2ecc71;
            color: white;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Instructor Panel</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="task_history.php">Task History of All Instructors</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1>Task History of All Instructors</h1>

            <?php if (count($taskHistory) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Task Title</th>
                            <th>Description</th>
                            <th>Instructor</th>
                            <th>Coordinator</th>
                            <th>Completed At</th>
                            <th>File</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($taskHistory as $task): ?>
                            <tr>
                                <td><?= htmlspecialchars($task['task_title']) ?></td>
                                <td><?= htmlspecialchars($task['task_desc']) ?></td>
                                <td><?= htmlspecialchars($task['instructor_name']) ?></td>
                                <td><?= htmlspecialchars($task['coordinator_name']) ?></td>
                                <td><?= htmlspecialchars($task['completed_at']) ?></td>
                                <td>
                                    <?php if (!empty($task['file_path'])): ?>
                                        <a href="../uploads/<?= htmlspecialchars($task['file_path']) ?>" target="_blank"><?= htmlspecialchars($task['file_path']) ?></a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No task history available.</p>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>