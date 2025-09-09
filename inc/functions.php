<?php
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function check_role($role)
{
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function redirect_if_not_logged_in()
{
    if (!is_logged_in()) {
        header("Location: /index.php");
        exit();
    }
}
function log_action($pdo, $user_id, $action)
{
    $stmt = $pdo->prepare("INSERT INTO user_logs (user_id, action) VALUES (?, ?)");
    $stmt->execute([$user_id, $action]);
}
