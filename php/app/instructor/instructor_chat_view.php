<?php
session_start();
require_once "../../includes/config.php";
require_once "../../includes/functions.php";
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Chat with Coordinator</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background: #f0f2f5;
        }

        .chat-container {
            display: flex;
            flex-direction: column;
            max-width: 700px;
            margin: 50px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            height: 80vh;
        }

        .chat-header {
            padding: 15px 20px;
            background: #0c1b33;
            color: #fff;
            font-weight: 600;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            font-size: 18px;
        }

        #messages {
            flex-grow: 1;
            padding: 15px;
            overflow-y: auto;
            background: #e5ddd5;
        }

        .message {
            max-width: 70%;
            margin-bottom: 10px;
            padding: 10px 15px;
            border-radius: 20px;
            clear: both;
            word-wrap: break-word;
        }

        .sent {
            background-color: #65aedd;
            color: #fff;
            margin-left: auto;
            border-bottom-right-radius: 0;
        }

        .received {
            background-color: #fff;
            color: #0c1b33;
            margin-right: auto;
            border-bottom-left-radius: 0;
        }

        .chat-input {
            display: flex;
            padding: 10px 15px;
            background: #f0f2f5;
            border-top: 1px solid #ddd;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        .chat-input input {
            flex-grow: 1;
            padding: 10px 15px;
            border-radius: 20px;
            border: 1px solid #ccc;
            outline: none;
            font-size: 15px;
        }

        .chat-input button {
            padding: 10px 20px;
            margin-left: 10px;
            background: #65aedd;
            color: #fff;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .chat-input button:hover {
            background: #4a92c7;
        }

        /* Scrollbar style */
        #messages::-webkit-scrollbar {
            width: 6px;
        }

        #messages::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }

        #messages::-webkit-scrollbar-track {
            background: transparent;
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
    <a href="instructor_chat_list.php" class="back-link">&larr; Back to list</a>

    <div class="chat-container">
        <div class="chat-header">Chat with Coordinator</div>
        <div id="messages"></div>
        <form id="sendForm" class="chat-input">
            <input type="text" id="msg" name="message" placeholder="Type a message…">
            <button type="submit">Send</button>
        </form>
    </div>
</body>

</html>