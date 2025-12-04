<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('coordinator')) {
    echo "Access Denied";
    exit;
}

$coordinator_id = $_SESSION['user_id'];

// Fetch user counts by role
$countStmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$userCounts = $countStmt->fetchAll(PDO::FETCH_ASSOC);

$roles = [];
foreach ($userCounts as $row) {
    $roles[$row['role']] = $row['count'];
}

// Fetch instructor task progress with deadlines
$sql = "
    SELECT 
        u.name AS instructor_name,
        t.title AS task_title,
        t.deadline,
        t.status,
        t.created_at,
        t.id AS task_id
    FROM users u
    LEFT JOIN tasks t ON u.id = t.assigned_to
    WHERE u.role = 'instructor'
    ORDER BY t.created_at DESC, u.name, t.deadline ASC
";
$stmt = $pdo->query($sql);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compute overall progress summary
$progressStmt = $pdo->query("
    SELECT 
        u.name AS instructor_name,
        COUNT(t.id) AS total_tasks,
        SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) AS completed_tasks
    FROM users u
    LEFT JOIN tasks t ON u.id = t.assigned_to
    WHERE u.role = 'instructor'
    GROUP BY u.id, u.name
");
$instructorProgress = $progressStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch initial total unread messages from instructors
$unreadStmt = $pdo->prepare("
    SELECT COUNT(*) AS unread_count
    FROM messages
    WHERE receiver_id = :coordinator_id
      AND is_read = 0
      AND sender_id IN (SELECT id FROM users WHERE role = 'instructor')
");
$unreadStmt->execute(['coordinator_id' => $coordinator_id]);
$unreadMessages = (int)$unreadStmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Coordinator Dashboard</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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
            position: relative;
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

        .main-content {
            flex-grow: 1;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 10px 30px;
            background: #fff;
            border-bottom: 1px solid #ddd;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .bell-icon {
            position: relative;
            font-size: 24px;
            cursor: pointer;
        }

        .bell-icon .notification-badge {
            position: absolute;
            top: -5px;
            right: -10px;
            background: #e74c3c;
            color: #fff;
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 50%;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background: #2c3e50;
            color: white;
        }

        tr.missed {
            background-color: #ffe6e6;
        }

        .missed-cell {
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Coordinator Panel</h2>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php">Dashboard</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'view_task.php' ? 'active' : '' ?>"><a href="view_task.php">My Task</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'assign_task.php' ? 'active' : '' ?>"><a href="assign_task.php">Assign Task</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'completed_task.php' ? 'active' : '' ?>"><a href="completed_task.php">Completed Task</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'manage_instructors.php' ? 'active' : '' ?>"><a href="manage_instructors.php">List of Instructors</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : '' ?>"><a href="edit_profile.php">Edit Profile</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'chat_list.php' ? 'active' : '' ?>"><a href="chat_list.php">Feedback</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">


            <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?> (Coordinator)</h1>

            <div class="table-container">
                <h2>Instructor Task Progress</h2>
                <table id="progressTable" class="display">
                    <thead>
                        <tr>
                            <th>Instructor</th>
                            <th>Total Tasks</th>
                            <th>Completed</th>
                            <th>Progress (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($instructorProgress as $row):
                            $progress = $row['total_tasks'] > 0 ? round(($row['completed_tasks'] / $row['total_tasks']) * 100, 1) : 0; ?>
                            <tr>
                                <td><?= htmlspecialchars($row['instructor_name']) ?></td>
                                <td><?= $row['total_tasks'] ?></td>
                                <td><?= $row['completed_tasks'] ?></td>
                                <td><?= $progress ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-container">
                <h2>Task Deadlines & Missed Submissions</h2>
                <table id="tasksTable" class="display">
                    <thead>
                        <tr>
                            <th>Instructor</th>
                            <th>Task Title</th>
                            <th>Deadline</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $t):
                            $isMissed = ($t['status'] != 'completed' && strtotime($t['deadline']) < time()); ?>
                            <tr class="<?= $isMissed ? 'missed' : '' ?>">
                                <td><?= htmlspecialchars($t['instructor_name']) ?></td>
                                <td><?= htmlspecialchars($t['task_title'] ?: '—') ?></td>
                                <td><?= htmlspecialchars($t['deadline'] ?: 'No deadline') ?></td>
                                <td class="<?= $isMissed ? 'missed-cell' : '' ?>"><?= $isMissed ? 'Missed' : ucfirst($t['status'] ?: 'Pending') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        $(document).ready(function() {
            $('#progressTable, #tasksTable').DataTable();

            let lastUnread = <?= $unreadMessages ?>;

            function notifyNewMessage(count) {
                alert("You have " + count + " new unread message(s) from instructors!");
            }

            // Check unread messages every 5 seconds
            setInterval(function() {
                $.ajax({
                    url: 'fetch_unread_messages.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        let count = parseInt(data.unread) || 0;
                        let badge = $('.bell-icon .notification-badge');

                        if (count > 0) {
                            if (badge.length) badge.text(count);
                            else $('<span class="notification-badge">' + count + '</span>').appendTo('.bell-icon');
                        } else {
                            badge.remove();
                        }

                        if (count > lastUnread) notifyNewMessage(count);
                        lastUnread = count;
                    },
                    error: function(err) {
                        console.error("Unread messages fetch failed:", err);
                    }
                });
            }, 5000);
        });
    </script>
</body>

</html>