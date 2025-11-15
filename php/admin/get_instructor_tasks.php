<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('admin')) {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

if (!isset($_GET['instructor_id'])) {
    echo json_encode([]);
    exit;
}

$instructor_id = intval($_GET['instructor_id']);

// Fetch all tasks assigned to this instructor, including pending
$sql = "
    SELECT 
        t.id AS task_id,
        t.title AS task_title,
        t.description AS task_desc,
        t.academic_year,
        u.name AS instructor_name,
        uc.name AS coordinator_name,
        th.completed_at
    FROM tasks t
    LEFT JOIN task_history th ON t.id = th.task_id
    JOIN users u ON t.assigned_to = u.id
    JOIN users uc ON t.assigned_by = uc.id
    WHERE u.id = ?
    ORDER BY t.id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$instructor_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$formattedTasks = [];
foreach ($tasks as $task) {
    $status = ($task['completed_at'] !== null) ? 'completed' : 'pending';
    $formattedTasks[] = [
        'name' => $task['task_title'],
        'status' => $status,
        'description' => $task['task_desc']
    ];
}

echo json_encode($formattedTasks);
