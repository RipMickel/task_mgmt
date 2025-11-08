<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";

if (!check_role('instructor')) {
    exit;
}

$stmt = $pdo->prepare("SELECT t.*, u.name as coordinator_name FROM tasks t 
                       JOIN users u ON t.assigned_by=u.id 
                       WHERE t.assigned_to=? ORDER BY t.deadline ASC");
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll();

foreach ($tasks as $task): ?>
    <tr>
        <td><?= htmlspecialchars($task['title']) ?></td>
        <td><?= htmlspecialchars($task['description']) ?></td>
        <td><?= htmlspecialchars($task['coordinator_name']) ?></td>
        <td><?= htmlspecialchars($task['deadline']) ?></td>
        <td><?= htmlspecialchars($task['academic_year']) ?></td>
        <td><?= htmlspecialchars($task['status']) ?></td>
        <td>
            <?php if ($task['status'] === 'pending'): ?>
                <form method="post" class="complete-form">
                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                    <input type="url" name="drive_link" placeholder="Google Drive link" required>
                    <button type="submit" class="btn">Mark Completed</button>
                </form>
            <?php else: ?>
                Completed
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>