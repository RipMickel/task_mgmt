<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('instructor')) {
    echo "Access Denied";
    exit;
}

$user_id = $_SESSION['user_id']; // Get current instructor ID

// Handle general search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base SQL query filtered by current instructor
$sql = "SELECT th.*, 
               t.title as task_title, 
               t.description as task_desc, 
               t.academic_year, 
               u.name as instructor_name, 
               uc.name as coordinator_name
        FROM task_history th
        JOIN tasks t ON th.task_id = t.id
        JOIN users u ON t.assigned_to = u.id
        JOIN users uc ON t.assigned_by = uc.id
        WHERE u.id = ?";

// Apply search filter if provided
if ($search) {
    $sql .= " AND (
                t.title LIKE ? OR 
                t.description LIKE ? OR 
                t.academic_year LIKE ? OR 
                uc.name LIKE ?
              )";
    $sql .= " ORDER BY th.completed_at DESC";
    $stmt = $pdo->prepare($sql);
    $searchTerm = "%$search%";
    $stmt->execute([$user_id, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
} else {
    $sql .= " ORDER BY th.completed_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
}

$taskHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Completed Tasks</title>
    <link rel="stylesheet" href="../instructor/instructor.css">
    <!-- ✅ DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            background: #1a1a2e;
            color: white;
            padding: 20px;
            width: 220px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 15px 0;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
        }

        .sidebar ul li.active a {
            font-weight: bold;
            color: #ffd700;
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
            background-color: #f4f4f4;
            color: black;
        }

        a.btn,
        a {
            color: blue;
            text-decoration: none;
        }

        .search-form {
            margin-bottom: 20px;
        }

        .search-form input {
            padding: 8px;
            width: 250px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .search-form button {
            padding: 8px 15px;
            background-color: #1a1a2e;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .search-form .btn {
            margin-left: 10px;
            color: #1a1a2e;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Instructor Panel</h2>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php">Dashboard</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'task_history.php' ? 'active' : '' ?>"><a href="task_history.php">My Completed Tasks</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : '' ?>"><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1>My Completed Tasks</h1>

            <?php if (count($taskHistory) > 0): ?>
                <table id="tasksTable" class="display responsive nowrap">
                    <thead>
                        <tr>
                            <th>Task Title</th>
                            <th>Description</th>
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
                                <td><?= htmlspecialchars($task['coordinator_name']) ?></td>
                                <td><?= htmlspecialchars($task['academic_year']) ?></td>
                                <td><?= htmlspecialchars($task['completed_at']) ?></td>
                                <td>
                                    <?php if (!empty($task['file_path'])): ?>
                                        <a href="../uploads/<?= htmlspecialchars($task['file_path']) ?>" target="_blank">View File</a>
                                    <?php elseif (!empty($task['drive_link'])): ?>
                                        <a href="<?= htmlspecialchars($task['drive_link']) ?>" target="_blank">View Drive File</a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No completed tasks found.</p>
            <?php endif; ?>
        </main>
    </div>

    <!-- ✅ jQuery & DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tasksTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthChange: true,
                order: [
                    [4, "desc"]
                ],
                language: {
                    search: "Filter Records:"
                }
            });
        });
    </script>
</body>

</html>