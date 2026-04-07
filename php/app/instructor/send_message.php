<?php
session_start();
require_once "../../includes/config.php";
require_once "../../includes/functions.php";
redirect_if_not_logged_in();

$conv_id = intval($_POST['conversation_id']);
$sender  = intval($_POST['sender_id']);
$message = trim($_POST['message']);

if ($message !== '') {
  $stmt = $pdo->prepare("
      INSERT INTO messages (conversation_id, sender_id, message)
      VALUES (?, ?, ?)
    ");
  $stmt->execute([$conv_id, $sender, $message]);
}
