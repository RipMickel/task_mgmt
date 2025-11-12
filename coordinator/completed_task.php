<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('coordinator')) {
    echo "Access Denied";
    exit;
}

// Handle academic year search
$academicYear = isset($_GET['academic_year']) ? $_GET['academic_year'] : '';

$sql = "
  SELECT th.task_id,
         th.completed_at,
         th.file_path,
         th.drive_link,
         t.title AS task_title,
         t.description AS task_desc,
         t.academic_year,
         u.name AS instructor_name,
         uc.name AS coordinator_name
  FROM task_history th
  JOIN tasks t ON th.task_id = t.id
  JOIN users u ON t.assigned_to = u.id
  JOIN users uc ON t.assigned_by = uc.id
";


if ($academicYear) {
    $sql .= " WHERE t.academic_year = :academic_year";
    $sql .= " ORDER BY th.completed_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':academic_year', $academicYear, PDO::PARAM_STR);
    $stmt->execute();
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
    <title>Task History</title>
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

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
            width: 220px;
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

        .table-container {
            margin-top: 40px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            word-wrap: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 250px;
        }

        table a {
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        table th {
            background: #2c3e50;
            color: white;
        }

        .search-form {
            margin-bottom: 20px;
        }

        .search-form input {
            padding: 8px;
            font-size: 14px;
            margin-right: 10px;
            width: 100%;
            max-width: 300px;
            box-sizing: border-box;

        }


        .search-form button {
            padding: 8px 16px;
            font-size: 14px;
            background: #1abc9c;
            border: none;
            color: white;
            cursor: pointer;
        }

        .search-form .btn {
            color: #e74c3c;
            text-decoration: none;
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Coordinator Panel</h2>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php">Dashboard</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'assign_task.php' ? 'active' : '' ?>"><a href="assign_task.php">Assign Task</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'completed_task.php' ? 'active' : '' ?>"><a href="completed_task.php">Completed Task</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'manage_instructors.php' ? 'active' : '' ?>"><a href="manage_instructors.php">List of Instructors</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : '' ?>"><a href="edit_profile.php">Edit Profile</a></li>

                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1>Completed Task</h1>



            <?php if (count($taskHistory) > 0): ?>
                <!-- Table Container -->
                <div class="table-container">
                    <table id="taskHistoryTable">
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
                                        <?php
                                        if (!empty($task['file_path'])) {
                                            echo '<a href="../uploads/' . htmlspecialchars($task['file_path']) . '" target="_blank">View Uploaded File</a>';
                                        } elseif (!empty($task['drive_link'])) {
                                            echo '<a href="' . htmlspecialchars($task['drive_link']) . '" target="_blank">View Google Drive File</a>';
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No task history available.</p>
            <?php endif; ?>
        </main>
    </div>

    <!-- Initialize DataTables -->
    <script>
        $(document).ready(function() {
            $('#taskHistoryTable').DataTable({
                "pageLength": 10, // Number of rows per page
                "searching": true, // Enable search functionality
                "ordering": true, // Enable column sorting
                "info": true, // Show info like "Showing 1 to 10 of 50 entries"
                "lengthChange": false // Disable the page length dropdown
            });
        });
    </script>
</body>

</html>