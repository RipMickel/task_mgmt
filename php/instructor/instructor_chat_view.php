<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];

if ($role !== 'instructor') {
    echo "Access Denied";
    exit;
}

$instructor_id = $user_id;
$coordinator_id = intval($_GET['coordinator_id'] ?? 0);

// Get or create conversation
$stmt = $pdo->prepare("
  SELECT id FROM conversations
  WHERE coordinator_id = ? AND instructor_id = ?
");
$stmt->execute([$coordinator_id, $instructor_id]);
$conv = $stmt->fetch();

if (!$conv) {
    $stmt = $pdo->prepare("
      INSERT INTO conversations (coordinator_id, instructor_id)
      VALUES (?, ?)
    ");
    $stmt->execute([$coordinator_id, $instructor_id]);
    $conversation_id = $pdo->lastInsertId();
} else {
    $conversation_id = $conv['id'];
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Chat with Coordinator</title>
    <style>
        #messages {
            border: 1px solid #ccc;
            height: 300px;
            overflow-y: scroll;
            padding: 10px;
        }

        .message {
            margin-bottom: 10px;
        }

        .sent {
            text-align: right;
            color: blue;
        }

        .received {
            text-align: left;
            color: green;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        var conversationId = <?= json_encode($conversation_id) ?>;
        var userId = <?= json_encode($user_id) ?>;

        function fetchMessages() {
            $.get('fetch_messages.php', {
                conversation_id: conversationId
            }, function(data) {
                $('#messages').html(data);
                $('#messages').scrollTop($('#messages')[0].scrollHeight);
            });
        }

        $(document).ready(function() {
            fetchMessages();
            setInterval(fetchMessages, 3000);

            $('#sendForm').submit(function(e) {
                e.preventDefault();
                var msg = $('#msg').val().trim();
                if (!msg) return;
                $.post('send_message.php', {
                    conversation_id: conversationId,
                    sender_id: userId,
                    message: msg
                }, function() {
                    $('#msg').val('');
                    fetchMessages();
                });
            });
        });
    </script>
</head>

<body>
    <h1>Chat with Coordinator</h1>
    <div id="messages"></div>
    <form id="sendForm">
        <input type="text" id="msg" name="message" placeholder="Type your message…" style="width:80%">
        <button type="submit">Send</button>
    </form>
</body>

</html>