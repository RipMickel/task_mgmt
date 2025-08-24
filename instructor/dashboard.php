<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('instructor')) {
    echo "Access Denied";
    exit;
}

// Fetch tasks assigned to this instructor
$stmt = $pdo->prepare("SELECT t.*, u.name as coordinator_name FROM tasks t 
                       JOIN users u ON t.assigned_by=u.id 
                       WHERE t.assigned_to=? ORDER BY t.deadline ASC");
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll();

// Fetch all uploaded files by other instructors
$filesStmt = $pdo->prepare("SELECT th.*, t.title as task_title, t.academic_year, u.name as instructor_name 
                            FROM task_history th
                            JOIN tasks t ON th.task_id = t.id
                            JOIN users u ON t.assigned_to = u.id
                            WHERE u.id != ? AND th.file_path IS NOT NULL
                            ORDER BY th.completed_at DESC");
$filesStmt->execute([$_SESSION['user_id']]);
$uploadedFiles = $filesStmt->fetchAll();

// Handle marking task as completed with PDF upload
if (isset($_POST['complete_task'])) {
    $task_id = $_POST['task_id'];
    $file_path = null;

    if (isset($_FILES['task_file']) && $_FILES['task_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['task_file']['tmp_name'];
        $fileName = $_FILES['task_file']['name'];
        $fileType = $_FILES['task_file']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Only allow PDF files
        if ($fileExtension === 'pdf' && $fileType === 'application/pdf') {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $newFileName = time() . '_' . basename($fileName);
            $dest_path = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $file_path = $newFileName;
            } else {
                $error = "There was an error moving the uploaded file.";
            }
        } else {
            $error = "Only PDF files are allowed.";
        }
    } else {
        $error = "No file uploaded.";
    }

    if (!isset($error)) {
        $pdo->prepare("UPDATE tasks SET status='completed' WHERE id=?")->execute([$task_id]);
        $pdo->prepare("INSERT INTO task_history (task_id, completed_at, file_path) VALUES (?,NOW(),?)")->execute([$task_id, $file_path]);
        header("Location: dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Instructor Dashboard</title>
    <link rel="stylesheet" href="../instructor/instructor.css">
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Instructor Panel</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="task_history.php">Task History of All Instructors</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <section class="tasks" id="my-tasks">
                <h2>Your Tasks</h2>
                <?php if (count($tasks) > 0): ?>
                    <table>
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
                                    <td><?= htmlspecialchars($task['status']) ?></td>
                                    <td>
                                        <?php if ($task['status'] === 'pending'): ?>
                                            <form method="post" enctype="multipart/form-data">
                                                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                <input type="file" name="task_file" required>
                                                <button type="submit" name="complete_task" class="btn">Mark as Completed</button>
                                            </form>
                                        <?php else: ?>
                                            Completed
                                            <?php if (!empty($task['file_path'])): ?>
                                                <br><a href="../uploads/<?= htmlspecialchars($task['file_path']) ?>" target="_blank">View File</a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No tasks assigned yet.</p>
                <?php endif; ?>
            </section>

            <section class="uploaded-files">
                <h2>Other Instructors' Uploaded Files</h2>
                <?php if (count($uploadedFiles) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Instructor</th>
                                <th>Academic Year</th>
                                <th>Uploaded File</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($uploadedFiles as $file): ?>
                                <tr>
                                    <td><?= htmlspecialchars($file['task_title']) ?></td>
                                    <td><?= htmlspecialchars($file['instructor_name']) ?></td>
                                    <td><?= htmlspecialchars($file['academic_year']) ?></td>
                                    <td><a href="../uploads/<?= htmlspecialchars($file['file_path']) ?>" target="_blank"><?= htmlspecialchars($file['file_path']) ?></a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No uploaded files from other instructors.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>

</html>