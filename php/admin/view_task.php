<?php
session_start();
require_once "../inc/config.php";   // PDO connection
require_once "../inc/functions.php";

redirect_if_not_logged_in();

// Identify logged-in user
$user_id = $_SESSION['user_id'];

// Handle submission of a task (file upload or drive link)
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
        $maxSize      = 10 * 1024 * 1024; // 10 MB

        if (function_exists('finfo_open')) {
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fileTmpPath);
            finfo_close($finfo);
        } else {
            $mimeType = mime_content_type($fileTmpPath);
        }

        if (!in_array($mimeType, $allowedMimes)) {
            $error = "Invalid file type. Only PDF or Word documents allowed.";
        } elseif ($fileSize > $maxSize) {
            $error = "File is too large. Maximum allowed size is 10 MB.";
        } else {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $newFileName = time() . '_' . basename($fileName);
            $dest_path   = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $file_path = $newFileName;
            } else {
                $error = "There was an error moving the uploaded file.";
            }
        }
    }

    if (empty($file_path)) {
        if (isset($_POST['drive_link']) && !empty(trim($_POST['drive_link']))) {
            $link = trim($_POST['drive_link']);
            if (filter_var($link, FILTER_VALIDATE_URL) && (strpos($link, 'drive.google.com') !== false)) {
                $drive_link = $link;
            } else {
                $error = "Please enter a valid Google Drive link.";
            }
        }
    }

    if (empty($file_path) && empty($drive_link)) {
        $error = $error ?? "No file uploaded or Drive link provided.";
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

        $_SESSION['success'] = "Task submitted/completed successfully!";
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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
            <h2>Coordinator Panel</h2>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php">Dashboard</a></li>
                <li><a href="view_task.php">My Task</a></li>
                <li><a href="completed_task.php">Completed Task</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="roles.php">Manage Roles</a></li>
                <li><a href="user_logs.php">Recent Logins</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert-success"><?= htmlspecialchars($_SESSION['success']);
                                            unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert-error"><?= htmlspecialchars($_SESSION['error']);
                                            unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <h2>Assigned Tasks</h2>
            <table id="tasksTable" class="display">
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
                        <tr data-task='<?= htmlspecialchars(json_encode($task), ENT_QUOTES, 'UTF-8') ?>'>
                            <td><?= htmlspecialchars($task['title']) ?></td>
                            <td><?= htmlspecialchars($task['description']) ?></td>
                            <td><?= htmlspecialchars($task['assigned_by_name']) . " (" . htmlspecialchars($task['assigned_by_role']) . ")" ?></td>
                            <td><?= htmlspecialchars($task['deadline']) ?></td>
                            <td><?= htmlspecialchars($task['academic_year']) ?></td>
                            <td><span class="status-badge <?= strtolower($task['status'] ?? '') ?>"><?= htmlspecialchars($task['status'] ?? '') ?></span></td>
                            <td><button class="btn openModal">View / Submit</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>

    <div id="taskModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle"></h2>
            <p id="modalDescription"></p>
            <p><strong>Assigned By:</strong> <span id="modalBy"></span></p>
            <p><strong>Deadline:</strong> <span id="modalDeadline"></span></p>
            <p><strong>Status:</strong> <span id="modalStatus"></span></p>
            <p><strong>Academic Year:</strong> <span id="modalYear"></span></p>
            <div id="modalSubmitSection"></div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#tasksTable').DataTable({
                "pageLength": 10,
                // Order by Deadline column (index 3), descending
                "order": [
                    [3, "desc"]
                ]
            });

            $(document).on("click", ".openModal", function() {
                let tr = $(this).closest('tr');
                let task = JSON.parse(tr.attr('data-task'));
                $("#modalTitle").text(task.title);
                $("#modalDescription").text(task.description);
                $("#modalBy").text(task.assigned_by_name + " (" + task.assigned_by_role + ")");
                $("#modalDeadline").text(task.deadline);
                $("#modalStatus").text(task.status);
                $("#modalYear").text(task.academic_year);

                let submitHTML = "";
                if (task.status === "pending") {
                    submitHTML = `<form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="task_id" value="${task.id}">
                    <div class="upload-box"><strong>Upload File:</strong><input type="file" name="task_file"></div>
                    <div class="upload-box"><strong>Or Google Drive Link:</strong><input type="url" name="drive_link" placeholder="https://drive.google.com/..."></div>
                    <button type="submit" name="complete_task" class="btn">Submit</button>
                </form>`;
                } else {
                    submitHTML = `<p><strong>Completed Task:</strong></p>`;
                    if (task.file_path) submitHTML += `<a href="../uploads/${task.file_path}" target="_blank">View File</a><br>`;
                    if (task.drive_link) submitHTML += `<a href="${task.drive_link}" target="_blank">View Drive Link</a>`;
                }
                $("#modalSubmitSection").html(submitHTML);
                $("#taskModal").fadeIn();
            });

            $(".close").click(function() {
                $("#taskModal").fadeOut();
            });
            window.onclick = function(event) {
                if (event.target == document.getElementById("taskModal")) $("#taskModal").fadeOut();
            };
        });
    </script>
</body>

</html>