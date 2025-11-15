<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$conversation_id = isset($data['conversation_id']) ? intval($data['conversation_id']) : 0;
$instructor_id = isset($data['instructor_id']) ? intval($data['instructor_id']) : 0;
$message = isset($data['message']) ? trim($data['message']) : '';
$coordinator_id = $_SESSION['user_id'] ?? 0;

if ($conversation_id > 0 && $message != '' && $instructor_id > 0 && $coordinator_id > 0) {
    try {
        $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, message, sent_at) VALUES (?,?,?,?,NOW())");
        $success = $stmt->execute([$conversation_id, $coordinator_id, $instructor_id, $message]);
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            $error = $stmt->errorInfo();
            echo json_encode(['success' => false, 'error' => $error[2]]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}
