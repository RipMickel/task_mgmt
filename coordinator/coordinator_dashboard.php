<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('coordinator')) {
    echo "Access Denied";
    exit;
}

// Fetch instructors
$instructors = $pdo->query("SELECT * FROM users WHERE role='instructor'")->fetchAll();

// Assign task
if (isset($_POST['assign_task'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $assigned_to = $_POST['assigned_to'];
    $date_assigned = date('Y-m-d');
    $deadline = $_POST['deadline'];
    $academic_year = $_POST['academic_year'];
    $assigned_by = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO tasks (title,description,assigned_to,assigned_by,date_assigned,deadline,academic_year) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$title, $desc, $assigned_to, $assigned_by, $date_assigned, $deadline, $academic_year]);
}

// Fetch tasks assigned by this coordinator
$tasks = $pdo->prepare("SELECT t.*, u.name as instructor_name FROM tasks t JOIN users u ON t.assigned_to=u.id WHERE t.assigned_by=?");
$tasks->execute([$_SESSION['user_id']]);
$assigned_tasks = $tasks->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Coordinator Dashboard</title>
</head>

<body>
    <h2>Welcome Coordinator, <?= $_SESSION['name'] ?></h2>

    <h3>Assign New Task</h3>
    <form method="post">
        <input type="text" name="title" placeholder="Task Title" required><br>
        <textarea name="description" placeholder="Task Description" required></textarea><br>
        <select name="assigned_to" required>
            <?php foreach ($instructors as $ins): ?>
                <option value="<?= $ins['id'] ?>"><?= $ins['name'] ?></option>
            <?php endforeach; ?>
        </select><br>
        <input type="date" name="deadline" required><br>
        <input type="text" name="academic_year" placeholder="Academic Year (e.g. 2025-2026)" required><br>
        <button type="submit" name="assign_task">Assign Task</button>
    </form>

    <h3>Assigned Tasks</h3>
    <table border="1">
        <tr>
            <th>Title</th>
            <th>Instructor</th>
            <th>Deadline</th>
            <th>Status</th>
            <th>Academic Year</th>
        </tr>
        <?php foreach ($assigned_tasks as $task): ?>
            <tr>
                <td><?= $task['title'] ?></td>
                <td><?= $task['instructor_name'] ?></td>
                <td><?= $task['deadline'] ?></td>
                <td><?= $task['status'] ?></td>
                <td><?= $task['academic_year'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <a href="../auth/logout.php">Logout</a>
</body>

</html>