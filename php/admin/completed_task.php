<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('admin')) {
    echo "Access Denied";
    exit;
}

// Handle academic year search
$academicYear = isset($_GET['academic_year']) ? trim($_GET['academic_year']) : '';

// Explicitly select needed columns
$sql = "
    SELECT 
        th.task_id,
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
    $sql .= " WHERE t.academic_year = ? ORDER BY th.completed_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$academicYear]);
} else {
    $sql .= " ORDER BY th.completed_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}

$taskHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Analytics Data (This can be adjusted to match the specific analytics you want)
$taskCount = count($taskHistory);
$academicYears = array_column($taskHistory, 'academic_year');
$uniqueYears = array_unique($academicYears);
$tasksPerYear = array_count_values($academicYears);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Task History</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

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

        .main-content {
            flex: 1;
            padding: 30px;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            overflow-x: auto;
        }

        .search-form {
            margin-bottom: 15px;
        }

        table.dataTable thead th {
            background: #2c3e50;
            color: white;
        }

        table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        table tbody tr:hover {
            background: #f1f1f1;
        }

        .analytics-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .analytics-section h2 {
            margin-bottom: 15px;
        }

        .print-btn {
            background-color: #16a085;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            margin-top: 20px;
            border-radius: 5px;
        }

        .print-btn:hover {
            background-color: #1abc9c;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="view_task.php">My Task</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'completed_task.php' ? 'active' : '' ?>">

                    <a href="completed_task.php">Completed Task</a>
                </li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="roles.php">Manage Roles</a></li>
                <li><a href="user_logs.php">Recent Logins</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1>Completed Task</h1>

            <!-- Analytics Section -->
            <div class="analytics-section">
                <h2>Task Analytics</h2>
                <canvas id="taskChart" width="400" height="200"></canvas>
                <div>
                    <button class="print-btn" onclick="printToPDF()">Print to PDF</button>
                </div>
            </div>

            <!-- Task Table -->
            <?php if (count($taskHistory) > 0): ?>
                <div class="table-container">
                    <table id="taskTable" class="display">
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#taskTable').DataTable({
                "pageLength": 10,
                "ordering": true,
                "lengthMenu": [5, 10, 25, 50],
                "language": {
                    "search": "Filter records:"
                }
            });
        });

        // Generate the chart with task analytics
        var ctx = document.getElementById('taskChart').getContext('2d');
        var taskChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($tasksPerYear)) ?>,
                datasets: [{
                    label: 'Tasks per Academic Year',
                    data: <?= json_encode(array_values($tasksPerYear)) ?>,
                    backgroundColor: 'rgba(26, 188, 156, 0.6)',
                    borderColor: 'rgba(26, 188, 156, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Print to PDF
        function printToPDF() {
            var element = document.querySelector('.main-content');
            var opt = {
                margin: 1,
                filename: 'task-history.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'in',
                    format: 'letter',
                    orientation: 'portrait'
                }
            };
            html2pdf().from(element).set(opt).save();
        }
    </script>
</body>

</html>