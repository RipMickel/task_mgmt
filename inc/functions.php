<?php
// ✅ Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

/**
 * Check user role
 * - Supports single role (string) or multiple roles (array)
 */
function check_role($roles)
{
    if (!isset($_SESSION['role'])) {
        return false;
    }

    if (is_array($roles)) {
        return in_array($_SESSION['role'], $roles);
    }

    return $_SESSION['role'] === $roles;
}

/**
 * Redirect if user is not logged in
 */
function redirect_if_not_logged_in()
{
    if (!is_logged_in()) {
        // ✅ Dynamically get the project root
        $redirectPath = dirname($_SERVER['PHP_SELF']) . "/index.php";
        header("Location: " . $redirectPath);
        exit();
    }
}

/**
 * Log user actions (login, logout, task, etc.)
 */
function log_action($pdo, $user_id, $action)
{
    try {
        $stmt = $pdo->prepare("INSERT INTO user_logs (user_id, action) VALUES (?, ?)");
        $stmt->execute([$user_id, $action]);
    } catch (PDOException $e) {
        error_log("Log Action Error: " . $e->getMessage());
    }
}
