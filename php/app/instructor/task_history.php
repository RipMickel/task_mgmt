<?php
session_start();
require_once "../../includes/config.php";
require_once "../../includes/functions.php";
redirect_if_not_logged_in();

if (!check_role('instructor')) {
    echo "Access Denied";
    exit;
}

// Handle general search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base SQL query — show all completed tasks (all users)
$sql = "SELECT th.*, 
               t.title as task_title, 
               t.description as task_desc, 
               t.academic_year, 
               u.name as instructor_name, 
               uc.name as coordinator_name
        FROM task_history th
        JOIN tasks t ON th.task_id = t.id
        JOIN users u ON t.assigned_to = u.id
        JOIN users uc ON t.assigned_by = uc.id";

// Apply search filter if provided
if ($search) {
    $sql .= " WHERE (
                t.title LIKE ? OR 
                t.description LIKE ? OR 
                t.academic_year LIKE ? OR 
                u.name LIKE ? OR
                uc.name LIKE ?
              )";
    $sql .= " ORDER BY th.completed_at DESC";
    $stmt = $pdo->prepare($sql);
    $searchTerm = "%$search%";
    $stmt->execute([
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm
    ]);
} else {
    $sql .= " ORDER BY th.completed_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}

$taskHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>All Completed Tasks</title>

    <!-- DataTables CSS -->
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
            width: 260px;
            background: #0c1b33;
            color: white;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            text-align: center;
            font-weight: 700;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar a {
            display: block;
            padding: 12px 15px;
            text-decoration: none;
            color: #ddd;
            border-radius: 6px;
            margin-bottom: 10px;
            transition: 0.25s;
        }

        .sidebar a:hover,
        .sidebar .active a {
            background: #1e2a47;
            color: #fff;
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
            table-layout: auto;
            /* allows cells to auto‑adjust */
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            word-wrap: break‑word;
            white-space: normal;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f4f4f4;
            color: black;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Instructor</h2>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php">Dashboard</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'task_history.php' ? 'active' : '' ?>"><a href="task_history.php">Completed Tasks</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'assigned_subjects.php' ? 'active' : '' ?>"><a href="assigned_subjects.php">My Subjects</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : '' ?>"><a href="edit_profile.php">Edit Profile</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'instructor_chat_list.php' ? 'active' : '' ?>"><a href="instructor_chat_list.php">Feedback</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1>All Completed Tasks</h1>

            <?php if (count($taskHistory) > 0): ?>
                <table id="tasksTable" class="display responsive">
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
                                        <a href="#" class="view-modal" data-type="file" data-src="../uploads/<?= htmlspecialchars($task['file_path']) ?>">View File</a>
                                    <?php elseif (!empty($task['drive_link'])): ?>
                                        <a href="#" class="view-modal" data-type="drive" data-src="<?= htmlspecialchars($task['drive_link']) ?>">View Drive File</a>
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

    <!-- Modal -->
    <div id="fileModal" class="modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <iframe src="" id="modalIframe"></iframe>
        </div>
    </div>

    <!-- jQuery & DataTables JS -->
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
                    [5, "desc"]
                ],
                language: {
                    search: "Filter Records:"
                }
            });
        });
    </script>
</body>

</html>