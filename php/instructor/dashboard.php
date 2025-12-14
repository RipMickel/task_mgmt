<?php
session_start();
require_once "../inc/config.php"; // Make sure $pdo is your PDO connection
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('instructor')) {
    echo "Access Denied";
    exit;
}

$instructor_id = $_SESSION['user_id'];

// Fetch tasks assigned to this instructor
$tasks = [];
$sql = "
    SELECT t.*, u.name as coordinator_name,
           th.file_path, th.drive_link
    FROM tasks t
    JOIN users u ON t.assigned_by = u.id
    LEFT JOIN task_history th ON th.task_id = t.id
    WHERE t.assigned_to = :instructor_id
    ORDER BY 
        CASE WHEN t.status = 'pending' THEN 0 ELSE 1 END,
        t.deadline DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['instructor_id' => $instructor_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count stats for tasks
$totalTasks     = count($tasks);
$pendingTasks   = count(array_filter($tasks, fn($t) => $t['status'] === 'pending'));
$completedTasks = count(array_filter($tasks, fn($t) => $t['status'] === 'completed'));

// Fetch count of assigned subjects
$sqlSubjects = "SELECT COUNT(*) as total FROM subject_assignments WHERE instructor_id = :instructor_id";
$stmtSubjects = $pdo->prepare($sqlSubjects);
$stmtSubjects->execute(['instructor_id' => $instructor_id]);
$assignedSubjectsCount = $stmtSubjects->fetchColumn() ?: 0;

// Handle marking task as completed
if (isset($_POST['complete_task'])) {
    $task_id    = $_POST['task_id'];
    $file_path  = null;
    $drive_link = null;

    if (isset($_FILES['task_file']) && $_FILES['task_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['task_file']['tmp_name'];
        $fileName    = $_FILES['task_file']['name'];

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

    if (isset($_POST['drive_link']) && !empty(trim($_POST['drive_link']))) {
        $link = trim($_POST['drive_link']);
        if (filter_var($link, FILTER_VALIDATE_URL) && (strpos($link, 'drive.google.com') !== false)) {
            $drive_link = $link;
        } else {
            $error = "Please enter a valid Google Drive link.";
        }
    }

    if ($file_path === null && $drive_link === null) {
        $error = "No file uploaded or Drive link provided.";
    }

    if (!isset($error)) {
        // Update task status
        $stmtUpdate = $pdo->prepare("UPDATE tasks SET status='completed' WHERE id=:task_id");
        $stmtUpdate->execute(['task_id' => $task_id]);

        // Insert into task history
        $stmtHistory = $pdo->prepare("INSERT INTO task_history (task_id, completed_at, file_path, drive_link) VALUES (:task_id, NOW(), :file_path, :drive_link)");
        $stmtHistory->execute([
            'task_id'   => $task_id,
            'file_path' => $file_path,
            'drive_link' => $drive_link
        ]);

        header("Location: dashboard.php");
        exit();
    }
}

// Check upcoming deadlines
$upcomingTasks = [];
$currentDate = new DateTime();
foreach ($tasks as $task) {
    if ($task['status'] === 'pending') {
        $deadline = new DateTime($task['deadline']);
        $interval = $currentDate->diff($deadline)->days;
        $isFuture = $deadline > $currentDate;
        if ($interval <= 2 && $isFuture) {
            $upcomingTasks[] = $task['title'] . " (Deadline: " . $task['deadline'] . ")";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Instructor Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        /* Keep your existing CSS intact */
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

        .stats-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }

        .card {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .card h3 {
            margin: 0;
            font-weight: 700;
            font-size: 22px;
        }

        .card p {
            margin: 5px 0 0;
            color: gray;
        }

        table {
            background: white;
            border-radius: 10px !important;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
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

        .alert-error {
            background-color: #ffdddd;
            padding: 12px;
            border-left: 5px solid red;
            margin-bottom: 20px;
            border-radius: 6px;
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
            <h2>Instructor</h2>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php">Dashboard</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'task_history.php' ? 'active' : '' ?>"><a href="task_history.php">Completed Tasks</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'assigned_subjects.php' ? 'active' : '' ?>"><a href="assigned_subjects.php">My Subjects</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : '' ?>"><a href="edit_profile.php">Edit Profile</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'instructor_chat_list.php
                .php' ? 'active' : '' ?>"><a href="instructor_chat_list.php">Feedback</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <div class="page-header">
                <?php
                $profilePic = !empty($_SESSION['profile_image']) ? "../uploads/profiles/" . $_SESSION['profile_image'] : "../assets/images/default.png";
                ?>
                <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile">
                <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>
            </div>
            <div class="stats-cards">
                <div class="card">
                    <h3><?= $totalTasks ?></h3>
                    <p>Total Tasks</p>
                </div>
                <div class="card">
                    <h3><?= $pendingTasks ?></h3>
                    <p>Pending</p>
                </div>
                <div class="card">
                    <h3><?= $completedTasks ?></h3>
                    <p>Completed</p>
                </div>
                <div class="card">
                    <h3><?= $assignedSubjectsCount ?></h3>
                    <p>Assigned Subjects</p>
                </div>
            </div>
            <?php if (isset($error)): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <section class="tasks">
                <h2>Your Tasks</h2>
                <table id="tasksTable" class="display">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Coordinator</th>
                            <th>Deadline</th>
                            <th>Academic Year</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?= htmlspecialchars($task['title']) ?></td>
                                <td><?= htmlspecialchars($task['description']) ?></td>
                                <td><?= htmlspecialchars($task['coordinator_name']) ?></td>
                                <td><?= htmlspecialchars($task['deadline']) ?></td>
                                <td><?= htmlspecialchars($task['academic_year']) ?></td>
                                <td><span class="status-badge <?= strtolower($task['status']) ?>"><?= htmlspecialchars($task['status']) ?></span></td>
                                <td>
                                    <button class="btn openModal" data-task='<?= json_encode($task) ?>'>View / Submit</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <div id="taskModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle"></h2>
            <p id="modalDescription"></p>
            <p><strong>Coordinator:</strong> <span id="modalCoordinator"></span></p>
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
                "order": [
                    [3, "asc"]
                ]
            });
            <?php if (!empty($upcomingTasks)): ?>
                let tasks = <?= json_encode($upcomingTasks); ?>;
                alert("⚠️ Upcoming Deadlines:\n\n" + tasks.join("\n"));
            <?php endif; ?>

            $(document).on("click", ".openModal", function() {
                let task = $(this).data("task");
                $("#modalTitle").text(task.title);
                $("#modalDescription").text(task.description);
                $("#modalCoordinator").text(task.coordinator_name);
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
            }
        });
    </script>
</body>

</html>