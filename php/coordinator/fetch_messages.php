<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

header('Content-Type: application/json');

$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;
$coordinator_id = $_SESSION['user_id'] ?? 0;

if ($conversation_id <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT sender_id, message FROM messages WHERE conversation_id=? ORDER BY sent_at ASC");
$stmt->execute([$conversation_id]);
$msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$messages = array_map(function ($m) use ($coordinator_id) {
    return [
        'sender' => $m['sender_id'] == $coordinator_id ? 'coordinator' : 'instructor',
        'message' => $m['message']
    ];
}, $msgs);

echo json_encode($messages);
