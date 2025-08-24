<?php
require_once "config.php";

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isRole($role)
{
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function redirectIfNotLoggedIn()
{
    if (!isLoggedIn()) {
        header("Location: auth/login.php");
        exit;
    }
}

function getUsersByRole($role)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = ?");
    $stmt->execute([$role]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
