<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('coordinator')) {
    echo "Access Denied";
    exit;
}

$coordinator_id = $_SESSION['user_id'];
$instructor_id = isset($_GET['instructor_id']) ? intval($_GET['instructor_id']) : 0;

if ($instructor_id <= 0) {
    die("Invalid instructor");
}

// Fetch coordinator name
$stmt = $pdo->prepare("SELECT name FROM users WHERE id=?");
$stmt->execute([$coordinator_id]);
$coordinator = $stmt->fetch(PDO::FETCH_ASSOC);
$coordinator_name = $coordinator['name'] ?? "Coordinator";

// Fetch instructor name
$stmt = $pdo->prepare("SELECT name FROM users WHERE id=?");
$stmt->execute([$instructor_id]);
$instructor = $stmt->fetch(PDO::FETCH_ASSOC);
$instructor_name = $instructor['name'] ?? "Instructor";

// Ensure conversation exists
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

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if ($message) {
        $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, message, sent_at) VALUES (?,?,?,?,NOW())");
        $stmt->execute([$conversation_id, $coordinator_id, $instructor_id, $message]);
        header("Location: chat_view.php?instructor_id=$instructor_id");
        exit;
    }
}

// Fetch messages
$stmt = $pdo->prepare("SELECT sender_id, receiver_id, message, sent_at FROM messages WHERE conversation_id=? ORDER BY sent_at ASC");
$stmt->execute([$conversation_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Chat with <?= htmlspecialchars($instructor_name) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        .chat-container {
            width: 600px;
            max-width: 95%;
            margin: 30px auto;
            border: 1px solid #ccc;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            background-color: #fff;
            height: 80vh;
        }

        .chat-header {
            padding: 15px;
            background-color: #007BFF;
            color: #fff;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            font-weight: bold;
        }

        .chat-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background-color: #e5ddd5;
        }

        .message {
            max-width: 70%;
            margin-bottom: 10px;
            padding: 10px 15px;
            border-radius: 20px;
            clear: both;
        }

        .message .sender {
            font-weight: bold;
            margin-bottom: 3px;
        }

        .message .time {
            display: block;
            font-size: 0.75em;
            color: #555;
            margin-top: 5px;
        }

        .message.coordinator {
            background-color: #DCF8C6;
            float: right;
            text-align: right;
        }

        .message.instructor {
            background-color: #fff;
            float: left;
            text-align: left;
        }

        .chat-input {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ccc;
            background-color: #f9f9f9;
        }

        .chat-input input[type="text"] {
            flex: 1;
            padding: 10px;
            border-radius: 20px;
            border: 1px solid #ccc;
            outline: none;
        }

        .chat-input button {
            margin-left: 10px;
            padding: 10px 20px;
            border-radius: 20px;
            border: none;
            background-color: #007BFF;
            color: #fff;
            cursor: pointer;
        }

        .chat-input button:hover {
            background-color: #0056b3;
        }

        .back-link {
            display: block;
            margin: 10px 15px;
            text-decoration: none;
            color: #007BFF;
        }
    </style>
</head>

<body>

    <a href="chat_list.php" class="back-link">&larr; Back to list</a>

    <div class="chat-container">
        <div class="chat-header">
            Chat with <?= htmlspecialchars($instructor_name) ?>
        </div>
        <div class="chat-messages">
            <?php foreach ($messages as $m):
                $sender_name = ($m['sender_id'] == $coordinator_id) ? $coordinator_name : $instructor_name;
                $class = ($m['sender_id'] == $coordinator_id) ? 'coordinator' : 'instructor';
            ?>
                <div class="message <?= $class ?>">
                    <div class="sender"><?= htmlspecialchars($sender_name) ?></div>
                    <div class="text"><?= htmlspecialchars($m['message']) ?></div>
                    <div class="time"><?= $m['sent_at'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <form method="post" class="chat-input">
            <input type="text" name="message" placeholder="Type a message..." required>
            <button type="submit">Send</button>
        </form>
    </div>

</body>

</html>