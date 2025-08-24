<?php
require_once "inc/functions.php";
redirectIfNotLoggedIn();

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE status='completed'");
$stmt->execute();
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Task History</h2>
<table border="1">
    <tr>
        <th>Title</th>
        <th>Assigned To</th>
        <th>Completed At</th>
    </tr>
    <?php foreach ($history as $h): ?>
        <tr>
            <td><?= $h['title'] ?></td>
            <td>
                <?php
                $stmt2 = $pdo->prepare("SELECT name FROM users WHERE id=?");
                $stmt2->execute([$h['assigned_to']]);
                echo $stmt2->fetchColumn();
                ?>
            </td>
            <td><?= $h['updated_at'] ?? $h['created_at'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>