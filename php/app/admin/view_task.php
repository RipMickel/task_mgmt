<?php
session_start();
require_once "../../includes/config.php";   // PDO connection
require_once "../../includes/functions.php";

redirect_if_not_logged_in();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_task'])) {
    $task_id    = $_POST['task_id'];
    $file_path  = null;
    $drive_link = null;
    $error      = null;

    if (isset($_FILES['task_file']) && $_FILES['task_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['task_file']['tmp_name'];
        $fileName    = $_FILES['task_file']['name'];
        $fileSize    = $_FILES['task_file']['size'];

        $allowedMimes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize      = 10 * 1024 * 1024;

        if (function_exists('finfo_open')) {
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fileTmpPath);
            finfo_close($finfo);
        } else {
            $mimeType = mime_content_type($fileTmpPath);
        }

        if (!in_array($mimeType, $allowedMimes)) {
            $error = "Invalid file type. Only PDF or Word docs allowed.";
        } elseif ($fileSize > $maxSize) {
            $error = "File too large (max 10MB).";
        } else {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $newFileName = time() . '_' . basename($fileName);
            $dest_path   = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $file_path = $newFileName;
            } else {
                $error = "There was an error uploading your file.";
            }
        }
    }

    if (empty($file_path) && !empty(trim($_POST['drive_link']))) {
        $link = trim($_POST['drive_link']);
        if (filter_var($link, FILTER_VALIDATE_URL) && strpos($link, 'drive.google.com') !== false) {
            $drive_link = $link;
        } else {
            $error = "Please enter a valid Google Drive link.";
        }
    }

    if (empty($file_path) && empty($drive_link)) {
        $error = $error ?? "No file uploaded or link provided.";
    }

    if (!isset($error)) {
        $stmtUpdate = $pdo->prepare("UPDATE tasks SET status = 'completed' WHERE id = :tid AND assigned_to = :uid");
        $stmtUpdate->execute([':tid' => $task_id, ':uid' => $user_id]);

        $stmtHist = $pdo->prepare("INSERT INTO task_history (task_id, completed_at, file_path, drive_link) VALUES (:tid, NOW(), :fp, :dl)");
        $stmtHist->execute([
            ':tid' => $task_id,
            ':fp'  => $file_path,
            ':dl'  => $drive_link
        ]);

        $_SESSION['success'] = "Task completed successfully!";
        header("Location: " . basename(__FILE__));
        exit;
    } else {
        $_SESSION['error'] = $error;
    }
}

$sql = "
  SELECT t.*, u_assigned_by.name AS assigned_by_name, u_assigned_by.role AS assigned_by_role,
         th.file_path, th.drive_link
  FROM tasks t
  JOIN users u_assigned_by ON t.assigned_by = u_assigned_by.id
  LEFT JOIN task_history th ON th.task_id = t.id
  WHERE t.assigned_to = :uid
  ORDER BY 
     CASE WHEN t.status = 'pending' THEN 0 ELSE 1 END,
     t.deadline DESC, t.date_assigned DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $user_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Tasks</title>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
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
            padding: 20px 0;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .sidebar a {
            display: block;
            color: #ecf0f1;
            padding: 12px 22px;
            text-decoration: none;
            transition: .3s;
        }

        .sidebar a:hover,
        .sidebar .active a {
            background: #1abc9c;
            padding-left: 25px;
        }

        .main-content {
            flex: 1;
            padding: 30px;
        }

        .alert-success,
        .alert-error {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }

        table.dataTable thead th {
            background: #2c3e50;
            color: #fff;
        }

        .btn {
            background: #1abc9c;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 5px;
            cursor: pointer;
            transition: .2s;
        }

        .btn:hover {
            background: #16a085;
        }

        .status-badge {
            padding: 5px 8px;
            border-radius: 5px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }

        .status-badge.pending {
            background: #f39c12;
            color: #fff;
        }

        .status-badge.completed {
            background: #27ae60;
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <a href="dashboard.php">Dashboard</a>
            <a href="view_task.php">My Task</a>
            <a href="assigned_subjects.php">My Subjects</a>
            <a href="completed_task.php">Completed Task</a>
            <a href="manage_users.php">Manage Users</a>
            <a href="roles.php">Manage Roles</a>
            <a href="user_logs.php">Recent Logins</a>
            <a href="../auth/logout.php">Logout</a>
        </aside>

        <div class="main-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert-success"><?= $_SESSION['success'];
                                            unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert-error"><?= $_SESSION['error'];
                                            unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <h2>Assigned Tasks</h2>
            <table id="tasksTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Assigned By</th>
                        <th>Deadline</th>
                        <th>Academic Year</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr data-task='<?= htmlspecialchars(json_encode($task), ENT_QUOTES, "UTF-8") ?>'>
                            <td><?= htmlspecialchars($task['title']) ?></td>
                            <td><?= htmlspecialchars($task['description']) ?></td>
                            <td><?= htmlspecialchars($task['assigned_by_name']) ?></td>
                            <td><?= htmlspecialchars($task['deadline']) ?></td>
                            <td><?= htmlspecialchars($task['academic_year']) ?></td>
                            <td><span class="status-badge <?= strtolower($task['status']) ?>"><?= htmlspecialchars($task['status']) ?></span></td>
                            <td><button class="btn openModal">View / Submit</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#tasksTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'print',
                    {
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        title: 'Tasks Report'
                    }
                ],
                "pageLength": 10,
                "order": [
                    [3, "desc"]
                ]
            });
        });
    </script>
</body>

</html>