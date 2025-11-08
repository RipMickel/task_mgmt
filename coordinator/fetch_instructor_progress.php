<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

// Only coordinator can access
if (!check_role('coordinator')) {
    echo "Access Denied";
    exit;
}

// Fetch all instructors and their tasks with deadlines
$sql = "
    SELECT 
        u.id AS instructor_id,
        u.name AS instructor_name,
        t.id AS task_id,
        t.title AS task_title,
        t.deadline,
        t.status
    FROM users u
    LEFT JOIN tasks t ON u.id = t.assigned_to
    WHERE u.role = 'instructor'
    ORDER BY u.name ASC, t.deadline ASC
";
$stmt = $pdo->query($sql);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize tasks by instructor
$instructorTasks = [];
foreach ($tasks as $task) {
    $instructorTasks[$task['instructor_name']][] = $task;
}

$currentDate = new DateTime();

// Generate table rows
foreach ($instructorTasks as $instructor => $tasksArr) {
    foreach ($tasksArr as $task) {
        $deadline = new DateTime($task['deadline']);
        $missed = ($task['status'] !== 'completed' && $deadline < $currentDate);
?>
        <tr class="<?= $missed ? 'missed' : '' ?>">
            <td><?= htmlspecialchars($instructor) ?></td>
            <td><?= htmlspecialchars($task['task_title'] ?? '-') ?></td>
            <td><?= htmlspecialchars($task['deadline'] ?? '-') ?></td>
            <td><?= htmlspecialchars($task['status'] ?? 'pending') ?><?= $missed ? ' (Missed)' : '' ?></td>
        </tr>
<?php
    }
}
