<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('instructor')) {
    echo "Access Denied";
    exit;
}

// Fetch tasks assigned to this instructor
$stmt = $pdo->prepare("SELECT t.*, u.name as coordinator_name FROM tasks t 
                       JOIN users u ON t.assigned_by=u.id 
                       WHERE t.assigned_to=? ORDER BY t.deadline ASC");
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll();

// ... (the rest of your PHP logic: uploaded files, handle complete_task, upcomingTasks) ...
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Instructor Dashboard</title>
    <!-- DataTables + jQuery -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <link rel="stylesheet" href="../instructor/instructor.css">
    <style>
        body {
            font-family: Arial, sans‑serif;
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
            object‑fit: cover;
        }

        table.dataTable {
            width: 100% !important;
            border‑collapse: collapse;
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
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'task_history.php' ? 'active' : '' ?>"><a href="task_history.php">Task History of All Instructors</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : '' ?>"><a href="edit_profile.php">Edit Profile</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'logout.php' ? 'active' : '' ?>"><a href="../auth/logout.php">Logout</a></li>
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

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <section class="tasks" id="my-tasks">
                <h2>Your Tasks</h2>
                <?php if (count($tasks) > 0): ?>
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
                        <tbody>
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
                                            <form method="post" enctype="multipart/form-data">
                                                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                <input type="file" name="task_file">
                                                <div class="drive-link-input">
                                                    <label for="drive_link_<?= $task['id'] ?>">Or submit Google Drive link:</label><br>
                                                    <input type="url" name="drive_link" id="drive_link_<?= $task['id'] ?>" placeholder="https://drive.google.com/…">
                                                </div>
                                                <button type="submit" name="complete_task" class="btn">Mark as Completed</button>
                                            </form>
                                        <?php else: ?>
                                            Completed
                                            <?php if (!empty($task['file_path'])): ?>
                                                <br><a href="../uploads/<?= htmlspecialchars($task['file_path']) ?>" target="_blank">View File</a>
                                            <?php endif; ?>
                                            <?php if (!empty($task['drive_link'])): ?>
                                                <br><a href="<?= htmlspecialchars($task['drive_link']) ?>" target="_blank">View Drive Link</a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No tasks assigned yet.</p>
                <?php endif; ?>
            </section>

        </main>
    </div>

    <script>
        $(document).ready(function() {
            $('#tasksTable').DataTable({
                // you can add additional configuration here, for example:
                "order": [
                    [3, "asc"]
                ], // default sort by deadline (4th column 0‑based is index 3)
                "pageLength": 10, // show 10 rows per page
                "columnDefs": [{
                        "orderable": false,
                        "targets": 6
                    } // disable ordering on the “Action” column
                ]
            });
        });

        <?php if (!empty($upcomingTasks)): ?>
            let tasks = <?php echo json_encode($upcomingTasks); ?>;
            let message = "⚠️ Upcoming Deadlines:\n\n" + tasks.join("\n");
            alert(message);
        <?php endif; ?>
    </script>
</body>

</html>