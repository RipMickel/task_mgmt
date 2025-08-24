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
$stmt = $pdo->prepare("SELECT t.*, u.name as coordinator_name FROM tasks t JOIN users u ON t.assigned_by=u.id WHERE t.assigned_to=?");
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll();

// Mark task as completed
if (isset($_GET['complete'])) {
    $task_id = $_GET['complete'];
    $pdo->prepare("UPDATE tasks SET status='completed' WHERE id=?")->execute([$task_id]);
    $pdo->prepare("INSERT INTO task_history (task_id, completed_at) VALUES (?,NOW())")->execute([$task_id]);
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Instructor Dashboard</title>
</head>

<body>
    <h2>Welcome Instructor, <?= $_SESSION['name'] ?></h2>

    <h3>Your Tasks</h3>
    <table border="1">
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Coordinator</th>
            <th>Deadline</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($tasks as $task): ?>
            <tr>
                <td><?= $task['title'] ?></td>
                <td><?= $task['description'] ?></td>
                <td><?= $task['coordinator_name'] ?></td>
                <td><?= $task['deadline'] ?></td>
                <td><?= $task['status'] ?></td>
                <td>
                    <?php if ($task['status'] == 'pending'): ?>
                        <a href="?complete=<?= $task['id'] ?>">Mark as Completed</a>
                    <?php else: ?>
                        Completed
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <a href="../auth/logout.php">Logout</a>
</body>

</html>