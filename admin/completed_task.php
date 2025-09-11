<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('admin')) {
    echo "Access Denied";
    exit;
}

// Fetch all task history with file uploads and academic year
$stmt = $pdo->prepare("SELECT th.*, t.title as task_title, t.description as task_desc, t.academic_year, u.name as instructor_name, uc.name as coordinator_name
                       FROM task_history th
                       JOIN tasks t ON th.task_id = t.id
                       JOIN users u ON t.assigned_to = u.id
                       JOIN users uc ON t.assigned_by = uc.id
                       ORDER BY th.completed_at DESC");
$stmt->execute();

// Handle academic year search
$academicYear = isset($_GET['academic_year']) ? $_GET['academic_year'] : '';

// Fetch all task history with optional academic year filter
$sql = "SELECT th.*, t.title as task_title, t.description as task_desc, t.academic_year, u.name as instructor_name, uc.name as coordinator_name
        FROM task_history th
        JOIN tasks t ON th.task_id = t.id
        JOIN users u ON t.assigned_to = u.id
        JOIN users uc ON t.assigned_by = uc.id";

if ($academicYear) {
    $sql .= " WHERE t.academic_year = ?";
    $sql .= " ORDER BY th.completed_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$academicYear]);
} else {
    $sql .= " ORDER BY th.completed_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}
$taskHistory = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Task History</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        /* Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: #ecf0f1;
            display: flex;
            flex-direction: column;
            padding: 20px 0;
        }

        .sidebar h2 {
            text-align: center;
            margin: 0 0 20px 0;
            font-size: 20px;
            font-weight: bold;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar li {
            margin-bottom: 10px;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: background 0.3s;
        }

        .sidebar a:hover,
        .sidebar .active a {
            background: #1abc9c;
            border-left: 5px solid #16a085;
            padding-left: 15px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 30px;
        }

        /* Welcome Section */
        .welcome-container {
            display: flex;
            align-items: center;
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }

        .welcome-container img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            border: 2px solid #3498db;
        }

        /* Cards */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .card h3 {
            margin-bottom: 10px;
            font-size: 18px;
            color: #2c3e50;
        }

        .card p {
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
        }

        /* Table Styling */
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-container th,
        .table-container td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .table-container th {
            background: #2c3e50;
            color: white;
        }

        /* Progress Bar */
        .progress-bar {
            background: #ecf0f1;
            border-radius: 6px;
            overflow: hidden;
            height: 20px;
            width: 100%;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #27ae60, #2ecc71);
            text-align: center;
            color: white;
            font-size: 12px;
            font-weight: bold;
            line-height: 20px;
            transition: width 0.6s ease;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>

                <li class="<?= basename($_SERVER['PHP_SELF']) == 'completed_task.php' ? 'active' : '' ?>">
                    <a href="completed_task.php">Completed Task</a>
                </li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="roles.php">Manage Roles</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>


        <main class="main-content">
            <h1>Completed Task</h1>
            <form method="GET" class="search-form">
                <label for="academic_year">Search by Academic Year:</label>
                <input type="text" name="academic_year" id="academic_year" value="<?= htmlspecialchars($academicYear) ?>" placeholder="e.g., 2025-2026">
                <button type="submit">Search</button>
                <a href="task_history.php" class="btn">Reset</a>
            </form>

            <?php if (count($taskHistory) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Task Title</th>
                            <th>Description</th>
                            <th>Instructor</th>
                            <th>Coordinator</th>
                            <th>Academic Year</th>
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
                                <td><?= htmlspecialchars($task['academic_year']) ?></td>
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