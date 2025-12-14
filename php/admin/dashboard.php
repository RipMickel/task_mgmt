<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('admin')) {
    echo "Access Denied";
    exit;
}

// User counts by role
$countStmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$userCounts = $countStmt->fetchAll(PDO::FETCH_ASSOC);

$roles = [];
$counts = [];
foreach ($userCounts as $row) {
    $roles[] = ucfirst($row['role']);
    $counts[] = $row['count'];
}

// Instructor task progress
$sql = "
    SELECT 
        u.id AS instructor_id,
        u.name AS instructor_name,
        COUNT(t.id) AS total_tasks,
        SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) AS completed_tasks,
        SUM(CASE WHEN t.status = 'pending' THEN 1 ELSE 0 END) AS pending_tasks
    FROM users u
    LEFT JOIN tasks t ON u.id = t.assigned_to
    WHERE u.role = 'instructor'
    GROUP BY u.id, u.name
";
$stmt = $pdo->query($sql);
$instructorTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
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

        .welcome-container {
            display: flex;
            align-items: center;
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }

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

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        table.dataTable thead th {
            background: #2c3e50;
            color: white;
        }

        /* Print Button Styling */
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

        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 700px;
            border-radius: 8px;
        }

        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-modal:hover {
            color: black;
        }

        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                    <a href="dashboard.php">Dashboard</a>
                </li>
                <li><a href="view_task.php">My Task</a></li>
                <li><a href="assigned_subjects.php">My Subjects</a></li>
                <li><a href="completed_task.php">Completed Task</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="roles.php">Manage Roles</a></li>
                <li><a href="user_logs.php">Recent Logins</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="welcome-container">
                <?php
                $profilePic = !empty($_SESSION['profile_image'])
                    ? "../uploads/profiles/" . $_SESSION['profile_image']
                    : "../assets/images/default.png";
                ?>
                <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile" id="profile-pic" style="cursor:pointer;width:100px;height:100px;border-radius:50%;border:2px solid #ccc;object-fit:cover;">
                <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?> (Admin)</h1>
            </div>

            <!-- Cards -->
            <div class="cards">
                <?php foreach ($userCounts as $uc): ?>
                    <div class="card">
                        <h3><?= ucfirst($uc['role']) ?>s</h3>
                        <p><?= $uc['count'] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Instructor Task Progress -->
            <div class="table-container">
                <h2>Instructor Task Progress</h2>
                <table id="instructorTable" class="display">
                    <thead>
                        <tr>
                            <th>Instructor</th>
                            <th>Total Tasks</th>
                            <th>Completed Tasks</th>
                            <th>Pending Tasks</th>
                            <th>Progress (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($instructorTasks as $task):
                            $progress = $task['total_tasks'] > 0
                                ? round(($task['completed_tasks'] / $task['total_tasks']) * 100, 1)
                                : 0;
                        ?>
                            <tr>
                                <td class="instructor-name" data-id="<?= $task['instructor_id'] ?>" style="cursor:pointer;color:#2980b9;"><?= htmlspecialchars($task['instructor_name']) ?></td>
                                <td><?= $task['total_tasks'] ?></td>
                                <td><?= $task['completed_tasks'] ?></td>
                                <td><?= $task['pending_tasks'] ?></td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width:<?= $progress ?>%;"><?= $progress ?>%</div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Analytics Section with Chart -->
            <div class="analytics-container">
                <h2>Task Analytics</h2>
                <div class="chart-container">
                    <canvas id="taskAnalyticsChart"></canvas>
                </div>

                <p>Total Pending Tasks: <?= array_sum(array_column($instructorTasks, 'pending_tasks')) ?></p>
                <p>Total Completed Tasks: <?= array_sum(array_column($instructorTasks, 'completed_tasks')) ?></p>
                <p>Total Tasks: <?= array_sum(array_column($instructorTasks, 'total_tasks')) ?></p>

                <!-- Print Button -->
                <button class="print-btn" onclick="printAnalytics()">Print Analytics</button>
            </div>
        </main>
    </div>

    <!-- Modal -->
    <div id="taskModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Instructor Tasks</h2>
            <table id="taskList" style="width:100%;border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>Task Name</th>
                        <th>Status</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#instructorTable').DataTable({
                "pageLength": 10,
                "ordering": true,
                "lengthMenu": [5, 10, 25, 50],
                "language": {
                    "search": "Filter records:"
                }
            });

            // Set up Chart.js
            var ctx = document.getElementById('taskAnalyticsChart').getContext('2d');
            var taskAnalyticsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Total Tasks', 'Completed Tasks', 'Pending Tasks'],
                    datasets: [{
                        label: 'Task Analytics',
                        data: [
                            <?= array_sum(array_column($instructorTasks, 'total_tasks')) ?>,
                            <?= array_sum(array_column($instructorTasks, 'completed_tasks')) ?>,
                            <?= array_sum(array_column($instructorTasks, 'pending_tasks')) ?>
                        ],
                        backgroundColor: ['#3498db', '#2ecc71', '#e74c3c'],
                        borderColor: ['#2980b9', '#27ae60', '#c0392b'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    animation: {
                        duration: 1000, // Animation duration (1 second)
                        easing: 'easeInOutQuad'
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });

        // Print Analytics Section
        function printAnalytics() {
            var element = document.querySelector('.analytics-container');
            var opt = {
                margin: 1,
                filename: 'task_analytics.pdf',
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