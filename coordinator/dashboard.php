<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('coordinator')) {
    echo "Access Denied";
    exit;
}

// Fetch user counts by role
$countStmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$userCounts = $countStmt->fetchAll(PDO::FETCH_ASSOC);

$roles = [];
$counts = [];
foreach ($userCounts as $row) {
    $roles[] = ucfirst($row['role']);
    $counts[] = $row['count'];
}

// Fetch instructor task progress with deadlines
$sql = "
    SELECT 
        u.name AS instructor_name,
        t.title AS task_title,
        t.deadline,
        t.status,
        t.id AS task_id
    FROM users u
    LEFT JOIN tasks t ON u.id = t.assigned_to
    WHERE u.role = 'instructor'
    ORDER BY u.name, t.deadline ASC
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Coordinator Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .welcome-container {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .welcome-container img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            border: 2px solid #ddd;
        }

        .welcome-container h1 {
            font-size: 22px;
            margin: 0;
        }

        .cards {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .card h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }

        .card p {
            font-size: 22px;
            font-weight: bold;
            color: #2c3e50;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'assign_task.php' ? 'active' : '' ?>"><a href="assign_task.php">Assign Task</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'manage_instructors.php' ? 'active' : '' ?>"><a href="manage_instructors.php">Manage Instructors</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : '' ?>"><a href="edit_profile.php">Edit Profile</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'user_logs.php' ? 'active' : '' ?>"><a href="user_logs.php">Recent Logins</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'completed_task.php' ? 'active' : '' ?>"><a href="completed_task.php">Completed Task</a></li>
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

            <!-- Summary Cards -->
            <div class="cards">
                <?php foreach ($userCounts as $uc): ?>
                    <div class="card">
                        <h3><?= ucfirst($uc['role']) ?>s</h3>
                        <p><?= $uc['count'] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Instructor Progress -->
            <div class="table-container">
                <h2>Instructor Task Progress</h2>
                <table>
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
                            $progress = $row['total_tasks'] > 0
                                ? round(($row['completed_tasks'] / $row['total_tasks']) * 100, 1)
                                : 0;
                        ?>
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

            <!-- Task Deadlines and Missed Submissions -->
            <div class="table-container">
                <h2>Task Deadlines & Missed Submissions</h2>
                <table>
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
                            $isMissed = ($t['status'] != 'completed' && strtotime($t['deadline']) < time());
                        ?>
                            <tr class="<?= $isMissed ? 'missed' : '' ?>">
                                <td><?= htmlspecialchars($t['instructor_name']) ?></td>
                                <td><?= htmlspecialchars($t['task_title']) ?: '—' ?></td>
                                <td><?= htmlspecialchars($t['deadline']) ?: 'No deadline' ?></td>
                                <td class="<?= $isMissed ? 'missed-cell' : '' ?>">
                                    <?= $isMissed ? 'Missed' : ucfirst($t['status'] ?: 'Pending') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Chart -->
            <div class="table-container" style="max-width: 600px;">
                <canvas id="userChart"></canvas>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('userChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($roles) ?>,
                datasets: [{
                    label: 'User Count',
                    data: <?= json_encode($counts) ?>,
                    backgroundColor: ['#27ae60', '#3498db', '#e74c3c']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>