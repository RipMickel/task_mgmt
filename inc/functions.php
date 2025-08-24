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
