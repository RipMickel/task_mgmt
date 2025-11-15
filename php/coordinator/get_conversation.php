<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

header('Content-Type: application/json');

$instructor_id = isset($_GET['instructor_id']) ? intval($_GET['instructor_id']) : 0;
$coordinator_id = $_SESSION['user_id'] ?? 0;

if ($instructor_id <= 0 || $coordinator_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid instructor or coordinator']);
    exit;
}

// Check if conversation exists
$stmt = $pdo->prepare("SELECT id FROM conversations WHERE coordinator_id=? AND instructor_id=? LIMIT 1");
$stmt->execute([$coordinator_id, $instructor_id]);
$conv = $stmt->fetch(PDO::FETCH_ASSOC);

if ($conv) {
    $conversation_id = $conv['id'];
} else {
    $stmt = $pdo->prepare("INSERT INTO conversations (coordinator_id,instructor_id,created_at) VALUES (?,?,NOW())");
    $stmt->execute([$coordinator_id, $instructor_id]);
    $conversation_id = $pdo->lastInsertId();
}

echo json_encode(['success' => true, 'conversation_id' => $conversation_id]);
