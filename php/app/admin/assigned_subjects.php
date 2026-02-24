<?php
session_start();
require_once "../../includes/config.php";
require_once "../../includes/functions.php";

redirect_if_not_logged_in();

// Allow instructor, coordinator, or admin
if (!check_role('instructor') && !check_role('coordinator') && !check_role('admin')) {
    echo "Access Denied";
    exit;
}

$user_id = $_SESSION['user_id'];
$assignedSubjects = [];

try {
    // Fetch subjects where current user is the one assigned (instructor_id)
    $sql = "
        SELECT s.subj_id,
               s.subj_code,
               s.subj_num,
               s.subj_description,
               s.subj_units,
               assigner.name AS assigned_by_name,
               sa.assigned_at
        FROM subject_assignments sa
        INNER JOIN subjects s ON sa.subject_id = s.subj_id
        INNER JOIN users assigner ON sa.coordinator_id = assigner.id
        WHERE sa.instructor_id = :user_id
        ORDER BY sa.assigned_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $assignedSubjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching assigned subjects: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Subjects</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
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

        <main class="main-content">
            <div class="page-header">
                <h1>My Subjects</h1>
            </div>

            <section class="assigned-subjects">
                <table id="subjectsTable" class="display">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Number</th>
                            <th>Description</th>
                            <th>Units</th>
                            <th>Assigned By</th>
                            <th>Assigned At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignedSubjects as $subj): ?>
                            <tr>
                                <td><?= htmlspecialchars($subj['subj_code']) ?></td>
                                <td><?= htmlspecialchars($subj['subj_num']) ?></td>
                                <td><?= htmlspecialchars($subj['subj_description']) ?></td>
                                <td><?= number_format((float)$subj['subj_units'], 2) ?></td>
                                <td><?= htmlspecialchars($subj['assigned_by_name']) ?></td>
                                <td><?= htmlspecialchars($subj['assigned_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        $(document).ready(function() {
            $('#subjectsTable').DataTable({
                "order": [
                    [5, "desc"]
                ],
                "pageLength": 10,
                dom: 'Bfrtip',
                buttons: [
                    'print',
                    {
                        extend: 'pdfHtml5',
                        title: 'My Subjects - <?= htmlspecialchars($_SESSION['name']) ?>',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    }
                ]
            });
        });
    </script>
</body>

</html>