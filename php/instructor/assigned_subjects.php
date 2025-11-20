<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";

redirect_if_not_logged_in();

if (!check_role('instructor')) {
    echo "Access Denied";
    exit;
}

// Fetch assigned subjects for this instructor
$stmt = $pdo->prepare("
    SELECT s.subj_id, s.subj_code, s.subj_num, s.subj_description, s.subj_units,
           u.name AS coordinator_name, sa.assigned_at
    FROM subject_assignments sa
    JOIN subjects s ON sa.subject_id = s.subj_id
    JOIN users u ON sa.coordinator_id = u.id
    WHERE sa.instructor_id = ?
    ORDER BY sa.assigned_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$assignedSubjects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Assigned Subjects - Instructor</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #eef1f5;
        }

        .dashboard-container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: #0c1b33;
            color: white;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            text-align: center;
            font-weight: 700;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar a {
            display: block;
            padding: 12px 15px;
            text-decoration: none;
            color: #ddd;
            border-radius: 6px;
            margin-bottom: 10px;
            transition: 0.25s;
        }

        .sidebar a:hover,
        .sidebar .active a {
            background: #1e2a47;
            color: #fff;
        }

        .main-content {
            flex: 1;
            padding: 20px 30px;
            overflow-y: auto;
        }

        .page-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .page-header img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }

        table {
            background: white;
            border-radius: 10px !important;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background: #0c1b33;
            color: white;
        }

        .muted {
            color: #666;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">

        <aside class="sidebar">
            <h2>Instructor</h2>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php">Dashboard</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'assigned_subjects.php' ? 'active' : '' ?>"><a href="assigned_subjects.php">Assigned Subjects</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'task_history.php' ? 'active' : '' ?>"><a href="task_history.php">Completed Tasks</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : '' ?>"><a href="edit_profile.php">Edit Profile</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'instructor_chat_list.php' ? 'active' : '' ?>"><a href="instructor_chat_list.php">Feedback</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">

            <div class="page-header">
                <?php
                $profilePic = !empty($_SESSION['profile_image'])
                    ? "../uploads/profiles/" . $_SESSION['profile_image']
                    : "../assets/images/default.png";
                ?>
                <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile">
                <h1>Assigned Subjects for <?= htmlspecialchars($_SESSION['name']) ?></h1>
            </div>

            <section class="assigned-subjects">
                <table id="subjectsTable" class="display">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Number</th>
                            <th>Description</th>
                            <th>Units</th>
                            <th>Coordinator</th>
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
                                <td><?= htmlspecialchars($subj['coordinator_name']) ?></td>
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
                "pageLength": 10
            });
        });
    </script>
</body>

</html>