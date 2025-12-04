<?php
session_start();
require_once "../inc/config.php";   // PDO connection
require_once "../inc/functions.php"; // your auth / role / redirect helper

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
            font-family: Arial, sans-serif;
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

        table {
            background: white;
            border-radius: 10px !important;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            width: 100%;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: white;
        }

        .pending {
            background: #f0ad4e;
        }

        .completed {
            background: #5cb85c;
        }

        .btn {
            padding: 7px 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.25s;
        }

        .btn:hover {
            background: #0056b3;
        }

        .upload-box {
            background: #f8f9fc;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 5px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 99999;
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background: white;
            margin: auto;
            padding: 20px;
            width: 500px;
            border-radius: 10px;
            animation: fadeIn .2s ease-in-out;
            position: relative;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>My Panel</h2>
            <ul>
                <li class="active"><a href="<?= basename(__FILE__) ?>">My Tasks</a></li>
                <?php if (check_role('coordinator') || check_role('admin')): ?>
                    <li><a href="assign_task.php">Assign Task</a></li>
                <?php endif; ?>
                <li><a href="edit_profile.php">Edit Profile</a></li>
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