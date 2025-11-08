<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

// Only coordinator can access
if (!check_role('coordinator')) {
    echo "Access Denied";
    exit;
}

// Fetch all instructors and their tasks with deadlines
$sql = "
    SELECT 
        u.id AS instructor_id,
        u.name AS instructor_name,
        t.id AS task_id,
        t.title AS task_title,
        t.deadline,
        t.status
    FROM users u
    LEFT JOIN tasks t ON u.id = t.assigned_to
    WHERE u.role = 'instructor'
    ORDER BY u.name ASC, t.deadline ASC
";
$stmt = $pdo->query($sql);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize tasks by instructor
$instructorTasks = [];
foreach ($tasks as $task) {
    $instructorTasks[$task['instructor_name']][] = $task;
}

$currentDate = new DateTime();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Instructor Progress</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            background: #fff;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        th {
            background: #2c3e50;
            color: #fff;
        }

        tr.missed {
            background-color: #f8d7da;
        }

        /* Red for missed */
    </style>
</head>

<body>
    <h1>Instructor Task Progress</h1>
    <table id="progressTable">
        <thead>
            <tr>
                <th>Instructor</th>
                <th>Task Title</th>
                <th>Deadline</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($instructorTasks as $instructor => $tasksArr): ?>
                <?php foreach ($tasksArr as $task):
                    $deadline = new DateTime($task['deadline']);
                    $missed = ($task['status'] !== 'completed' && $deadline < $currentDate);
                ?>
                    <tr class="<?= $missed ? 'missed' : '' ?>">
                        <td><?= htmlspecialchars($instructor) ?></td>
                        <td><?= htmlspecialchars($task['task_title'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($task['deadline'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($task['status'] ?? 'pending') ?><?= $missed ? ' (Missed)' : '' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            const table = $('#progressTable').DataTable({
                "order": [
                    [1, "asc"]
                ],
                "pageLength": 25
            });

            // Auto-refresh every 5 seconds
            setInterval(function() {
                $.ajax({
                    url: 'fetch_instructor_progress.php', // separate file to return table rows only
                    type: 'GET',
                    success: function(data) {
                        table.clear().rows.add($(data)).draw();
                    }
                });
            }, 5000);
        });
    </script>
</body>

</html>