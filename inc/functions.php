<?php
// inc/functions.php

function is_logged_in()
{
    return isset($_SESSION['user']);
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: /auth/login.php');
        exit;
    }
}

function current_user()
{
    return $_SESSION['user'] ?? null;
}

function require_role($roles = [])
{
    $user = current_user();
    if (!$user || !in_array($user['role'], (array)$roles)) {
        http_response_code(403);
        echo "Forbidden - insufficient privileges.";
        exit;
    }
}

function flash_set($key, $msg)
{
    $_SESSION['flash'][$key] = $msg;
}
function flash_get($key)
{
    if (isset($_SESSION['flash'][$key])) {
        $m = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $m;
    }
    return null;
}

// ensure email domain is allowed
function check_allowed_domain($email)
{
    $domain = substr(strrchr($email, "@"), 1);
    return strtolower($domain) === strtolower(ALLOWED_EMAIL_DOMAIN);
}

// fetch user by email
function get_user_by_email($pdo, $email)
{
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}
