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

// Use your SQL with filtering for the specific instructor
$sql = "
    SELECT 
        th.task_id,
        th.completed_at,
        th.file_path,
        th.drive_link,
        t.title AS task_title,
        t.description AS task_desc,
        t.academic_year,
        u.name AS instructor_name,
        uc.name AS coordinator_name,
        CASE 
            WHEN th.completed_at IS NOT NULL THEN 'completed'
            ELSE 'pending'
        END AS status
    FROM task_history th
    JOIN tasks t ON th.task_id = t.id
    JOIN users u ON t.assigned_to = u.id
    JOIN users uc ON t.assigned_by = uc.id
    WHERE u.id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$instructor_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return JSON in a simpler format for the modal
$formattedTasks = [];
foreach ($tasks as $task) {
    $formattedTasks[] = [
        'name' => $task['task_title'],
        'status' => $task['status'],
        'description' => $task['task_desc']
    ];
}

echo json_encode($formattedTasks);
