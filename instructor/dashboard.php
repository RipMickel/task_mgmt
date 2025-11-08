<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('instructor')) {
    echo "Access Denied";
    exit;
}

// Fetch tasks assigned to this instructor (for initial load)
$stmt = $pdo->prepare("SELECT t.*, u.name as coordinator_name FROM tasks t 
                       JOIN users u ON t.assigned_by=u.id 
                       WHERE t.assigned_to=? ORDER BY t.deadline ASC");
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll();

// Handle marking task as completed using Google Drive link
if (isset($_POST['complete_task'])) {
    $task_id = $_POST['task_id'];
    $file_path = trim($_POST['drive_link']);

    $deadlineStmt = $pdo->prepare("SELECT deadline FROM tasks WHERE id = ?");
    $deadlineStmt->execute([$task_id]);
    $taskDeadline = $deadlineStmt->fetchColumn();

    if ($taskDeadline && new DateTime() > new DateTime($taskDeadline)) {
        $error = "You can no longer submit this task. The deadline has passed.";
    } elseif (empty($file_path)) {
        $error = "Please provide a valid Google Drive link.";
    } elseif (strpos($file_path, 'drive.google.com') === false) {
        $error = "Invalid link. Please submit a Google Drive URL.";
    } else {
        $pdo->prepare("UPDATE tasks SET status='completed' WHERE id=?")->execute([$task_id]);
        $pdo->prepare("INSERT INTO task_history (task_id, completed_at, file_path) VALUES (?,NOW(),?)")
            ->execute([$task_id, $file_path]);
        exit(json_encode(["success" => true]));
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Instructor Dashboard</title>

    <!-- DataTables + jQuery -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.2/css/dataTables.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.2/js/dataTables.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f4f6f9;
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
            padding: 20px;
            background: white;
        }

        .welcome-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .welcome-container img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }

        table.dataTable {
            width: 100% !important;
            border-collapse: collapse;
        }

        .btn {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .alert-error {
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                text-align: center;
            }

            .main-content {
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Instructor Panel</h2>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php">Dashboard</a></li>
                <li><a href="task_history.php">Task History</a></li>
                <li><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="welcome-container">
                <?php
                $profilePic = !empty($_SESSION['profile_image'])
                    ? "../uploads/profiles/" . $_SESSION['profile_image']
                    : "../assets/images/default.png";
                ?>
                <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile">
                <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>
            </div>

            <section class="tasks" id="my-tasks">
                <h2>Your Tasks</h2>
                <div class="table-responsive">
                    <table id="tasksTable" class="display">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Coordinator</th>
                                <th>Deadline</th>
                                <th>Academic Year</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tasks-body">
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td><?= htmlspecialchars($task['title']) ?></td>
                                    <td><?= htmlspecialchars($task['description']) ?></td>
                                    <td><?= htmlspecialchars($task['coordinator_name']) ?></td>
                                    <td><?= htmlspecialchars($task['deadline']) ?></td>
                                    <td><?= htmlspecialchars($task['academic_year']) ?></td>
                                    <td><?= htmlspecialchars($task['status']) ?></td>
                                    <td>
                                        <?php if ($task['status'] === 'pending'): ?>
                                            <form method="post" class="complete-form">
                                                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                <input type="url" name="drive_link" placeholder="Google Drive link" required>
                                                <button type="submit" class="btn">Mark Completed</button>
                                            </form>
                                        <?php else: ?>
                                            Completed
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        let table;

        $(document).ready(function() {
            // Initialize DataTable
            table = $('#tasksTable').DataTable({
                responsive: true,
                pageLength: 5
            });

            // Handle marking task as completed via AJAX
            $(document).on('submit', '.complete-form', function(e) {
                e.preventDefault();
                $.post('dashboard.php', $(this).serialize(), function() {
                    refreshTasks();
                });
            });

            // Auto-refresh every 5 seconds
            setInterval(refreshTasks, 5000);
        });

        // Refresh the task table via AJAX
        function refreshTasks() {
            $.ajax({
                url: 'fetch_tasks.php',
                method: 'GET',
                success: function(data) {
                    table.clear().rows.add($(data)).draw();
                }
            });
        }
    </script>
</body>

</html>