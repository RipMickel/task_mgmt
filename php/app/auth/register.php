<?php
require_once "../inc/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Allow only nbsc.edu.ph emails
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@nbsc\.edu\.ph$/", $email)) {
        header("Location: ../index.php?message=invalid_email");
        exit;
    }

    // Validate role
    $allowed_roles = ['admin', 'coordinator', 'instructor'];
    if (!in_array($role, $allowed_roles)) {
        header("Location: ../index.php?message=invalid_role");
        exit;
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        header("Location: ../index.php?message=already_registered");
        exit;
    }

    // Default status = pending for instructors
    $status = ($role === "instructor") ? "pending" : "active";

    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (name,email,password,role,status) VALUES (?,?,?,?,?)");
    $stmt->execute([$name, $email, $password, $role, $status]);

    if ($role === "instructor") {
        header("Location: ../index.php?message=pending_approval");
    } else {
        header("Location: ../index.php?message=registered_success");
    }
    exit;
}
