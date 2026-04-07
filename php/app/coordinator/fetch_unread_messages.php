<?php
session_start();
require_once "../../includes/config.php";
require_once "../../includes/functions.php";
redirect_if_not_logged_in();

if (!check_role('coordinator')) {
    echo json_encode(['unread' => 0]);
    exit;
}

$coordinator_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT COUNT(*) AS unread
    FROM messages
    WHERE receiver_id = :coordinator_id
      AND is_read = 0
      AND sender_id IN (SELECT id FROM users WHERE role = 'instructor')
");
$stmt->execute(['coordinator_id' => $coordinator_id]);
$unread = (int)$stmt->fetchColumn();

echo json_encode(['unread' => $unread]);
