<?php
require_once "inc/functions.php";
redirectIfNotLoggedIn();

if (!isRole('admin') && !isRole('coordinator')) {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $assigned_to = $_POST['assigned_to'];
    $academic_year = $_POST['academic_year'];
    $assign_date = $_POST['assign_date'];
    $deadline = $_POST['deadline'];

    $stmt = $pdo->prepare("INSERT INTO tasks (title, description, assigned_to, assigned_by, academic_year, assign_date, deadline) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$title, $desc, $assigned_to, $_SESSION['user_id'], $academic_year, $assign_date, $deadline]);

    echo "Task assigned!";
}

$instructors = getUsersByRole('instructor');
?>

<form method="post">
    Title: <input type="text" name="title" required><br>
    Description: <textarea name="description"></textarea><br>
    Assign to:
    <select name="assigned_to">
        <?php foreach ($instructors as $i): ?>
            <option value="<?= $i['id'] ?>"><?= $i['name'] ?></option>
        <?php endforeach; ?>
    </select><br>
    Academic Year: <input type="text" name="academic_year" required><br>
    Assign Date: <input type="date" name="assign_date" required><br>
    Deadline: <input type="date" name="deadline" required><br>
    <button type="submit">Assign Task</button>
</form>