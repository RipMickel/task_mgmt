<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('instructor')) {
    echo "Access Denied";
    exit;
}

// Fetch all coordinators (or only those relevant)
$stmt = $pdo->prepare("SELECT id, name FROM users WHERE role = 'coordinator'");
$stmt->execute();
$coordinators = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>My Coordinators</title>
</head>

<body>
    <h1>Chat with Coordinators</h1>
    <ul>
        <?php foreach ($coordinators as $coord): ?>
            <li>
                <a href="instructor_chat_view.php?coordinator_id=<?= htmlspecialchars($coord['id']) ?>">
                    <?= htmlspecialchars($coord['name']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>

</html>