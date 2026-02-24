<?php
session_start();
require_once "../../includes/config.php";
require_once "../../includes/functions.php";
redirect_if_not_logged_in();

$conv_id = intval($_GET['conversation_id']);

// Fetch messages
$stmt = $pdo->prepare("
  SELECT m.*, u.name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
   WHERE m.conversation_id = ?
   ORDER BY m.sent_at ASC
");
$stmt->execute([$conv_id]);
$messages = $stmt->fetchAll();

foreach ($messages as $m) {
  $class = ($m['sender_id'] == $_SESSION['user_id']) ? 'sent' : 'received';
  echo '<div class="message ' . $class . '">'
    . '<strong>' . htmlspecialchars($m['name']) . ':</strong> '
    . htmlspecialchars($m['message'])
    . '<br><small>' . htmlspecialchars($m['sent_at']) . '</small>'
    . '</div>';
}
