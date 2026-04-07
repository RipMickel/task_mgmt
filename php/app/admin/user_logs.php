<?php
session_start();
require_once "../../includes/config.php";
require_once "../../includes/functions.php";
redirect_if_not_logged_in();

if (!check_role('admin')) {
    echo "Access Denied";
    exit;
}

// Fetch only recent login logs
$stmt = $pdo->prepare("SELECT l.*, u.name, u.role 
                       FROM user_logs l 
                       JOIN users u ON l.user_id = u.id 
                       WHERE l.action = 'User logged in'
                       ORDER BY l.created_at DESC");
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Recent Logins - Admin</title>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Buttons extension -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

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
        .sidebar .active a {
            background: #1abc9c;
            border-left: 5px solid #16a085;
            padding-left: 15px;
        }

        /* Main content */
        .main-content {
            flex-grow: 1;
            padding: 30px;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        /* DataTables styling */
        table.dataTable thead th {
            background: #1abc9c;
            color: white;
        }

        table.dataTable {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="view_task.php">My Task</a></li>
                <li><a href="assigned_subjects.php">My Subjects</a></li>

                <li><a href="completed_task.php">Completed Task</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="roles.php">Manage Roles</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'user_logs.php' ? 'active' : '' ?>">
                    <a href="user_logs.php">Recent Logins</a>
                </li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main content -->
        <main class="main-content">
            <h1>Recent Logins</h1>

            <?php if (count($logs) > 0): ?>
                <table id="logsTable" class="display">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['name']) ?></td>
                                <td><?= htmlspecialchars($log['role']) ?></td>
                                <td><?= htmlspecialchars($log['action']) ?></td>
                                <td><?= htmlspecialchars($log['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No login activity found.</p>
            <?php endif; ?>
        </main>
    </div>

    <script>
        $(document).ready(function() {
            $('#logsTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'print',
                    {
                        extend: 'pdfHtml5',
                        text: 'Export PDF',
                        title: 'Recent Logins'
                    }
                ],
                "order": [
                    [3, "desc"]
                ]
            });
        });
    </script>
</body>

</html>