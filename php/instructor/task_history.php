<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('instructor')) {
    echo "Access Denied";
    exit;
}

$user_id = $_SESSION['user_id']; // Get current instructor ID

// Handle general search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base SQL query filtered by current instructor
$sql = "SELECT th.*, 
               t.title as task_title, 
               t.description as task_desc, 
               t.academic_year, 
               u.name as instructor_name, 
               uc.name as coordinator_name
        FROM task_history th
        JOIN tasks t ON th.task_id = t.id
        JOIN users u ON t.assigned_to = u.id
        JOIN users uc ON t.assigned_by = uc.id
        WHERE u.id = ?";

// Apply search filter if provided
if ($search) {
    $sql .= " AND (
                t.title LIKE ? OR 
                t.description LIKE ? OR 
                t.academic_year LIKE ? OR 
                uc.name LIKE ?
              )";
    $sql .= " ORDER BY th.completed_at DESC";
    $stmt = $pdo->prepare($sql);
    $searchTerm = "%$search%";
    $stmt->execute([$user_id, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
} else {
    $sql .= " ORDER BY th.completed_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
}

$taskHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Completed Tasks</title>
    <!-- ✅ DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
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
            padding: 30px;
            background-color: #ecf0f1;
        }

        h1 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f4f4f4;
            color: black;
        }

        a.btn,
        a {
            color: blue;
            text-decoration: none;
        }

        .search-form {
            margin-bottom: 20px;
        }

        .search-form input {
            padding: 8px;
            width: 250px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .search-form button {
            padding: 8px 15px;
            background-color: #1a1a2e;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .search-form .btn {
            margin-left: 10px;
            color: #1a1a2e;
            text-decoration: underline;
        }

        /* Modal Styles */
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
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
            position: relative;
        }

        .modal-close {
            color: #aaa;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .modal-close:hover {
            color: black;
        }

        .modal iframe {
            width: 100%;
            height: 500px;
            border: none;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Instructor Panel</h2>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php">Dashboard</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'task_history.php' ? 'active' : '' ?>"><a href="task_history.php">My Completed Tasks</a></li>
                                     <li><a href="assigned_subjects">Assigned Subjects</a></li>

                <li class="<?= basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : '' ?>"><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="instructor_chat_list.php">Feedback</a></li>

                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1>My Completed Tasks</h1>

            <?php if (count($taskHistory) > 0): ?>
                <table id="tasksTable" class="display responsive nowrap">
                    <thead>
                        <tr>
                            <th>Task Title</th>
                            <th>Description</th>
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
                                <td><?= htmlspecialchars($task['coordinator_name']) ?></td>
                                <td><?= htmlspecialchars($task['academic_year']) ?></td>
                                <td><?= htmlspecialchars($task['completed_at']) ?></td>
                                <td>
                                    <?php if (!empty($task['file_path'])): ?>
                                        <a href="#" class="view-modal" data-type="file" data-src="../uploads/<?= htmlspecialchars($task['file_path']) ?>">View File</a>
                                    <?php elseif (!empty($task['drive_link'])): ?>
                                        <a href="#" class="view-modal" data-type="drive" data-src="<?= htmlspecialchars($task['drive_link']) ?>">View Drive File</a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No completed tasks found.</p>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal -->
    <div id="fileModal" class="modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <iframe src="" id="modalIframe"></iframe>
        </div>
    </div>

    <!-- ✅ jQuery & DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tasksTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthChange: true,
                order: [
                    [4, "desc"]
                ],
                language: {
                    search: "Filter Records:"
                }
            });

            // Modal Logic
            const modal = $('#fileModal');
            const iframe = $('#modalIframe');

            $('.view-modal').click(function(e) {
                e.preventDefault();
                const type = $(this).data('type');
                let src = $(this).data('src');

                if (type === 'drive') {
                    // Convert Google Drive link to embed if necessary
                    if (src.includes("drive.google.com")) {
                        src = src.replace("/view", "/preview");
                    }
                }

                iframe.attr('src', src);
                modal.show();
            });

            $('.modal-close, #fileModal').click(function(e) {
                if (e.target !== this) return; // only close if click outside iframe or on X
                iframe.attr('src', '');
                modal.hide();
            });
        });
    </script>
</body>

</html>