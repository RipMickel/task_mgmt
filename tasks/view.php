<?php
require_once "inc/functions.php";
redirectIfNotLoggedIn();

if (isRole('instructor')) {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE assigned_to=?");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM tasks");
    $stmt->execute();
}

$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Tasks</h2>
<table border="1">
    <tr>
        <th>Title</th>
        <th>Description</th>
        <th>Assigned To</th>
        <th>Status</th>
        <th>Deadline</th>
    </tr>
    <?php foreach ($tasks as $t): ?>
        <tr>
            <td><?= $t['title'] ?></td>
            <td><?= $t['description'] ?></td>
            <td>
                <?php
                $stmt2 = $pdo->prepare("SELECT name FROM users WHERE id=?");
                $stmt2->execute([$t['assigned_to']]);
                echo $stmt2->fetchColumn();
                ?>
            </td>
            <td><?= $t['status'] ?></td>
            <td><?= $t['deadline'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>