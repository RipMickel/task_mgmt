<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Coordinator: CRUD access
if ($role === 'coordinator' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_schedule'])) {
        $stmt = $pdo->prepare("INSERT INTO class_schedule (course, section, instructor_id, day, time, room) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['course'], $_POST['section'], $_POST['instructor_id'], $_POST['day'], $_POST['time'], $_POST['room']]);
    }

    if (isset($_POST['update_schedule'])) {
        $stmt = $pdo->prepare("UPDATE class_schedule SET course=?, section=?, instructor_id=?, day=?, time=?, room=? WHERE id=?");
        $stmt->execute([$_POST['course'], $_POST['section'], $_POST['instructor_id'], $_POST['day'], $_POST['time'], $_POST['room'], $_POST['id']]);
    }

    if (isset($_POST['delete_schedule'])) {
        $stmt = $pdo->prepare("DELETE FROM class_schedule WHERE id=?");
        $stmt->execute([$_POST['id']]);
    }
}

// Fetch schedules
if ($role === 'instructor') {
    $stmt = $pdo->prepare("SELECT cs.*, u.name as instructor_name 
                           FROM class_schedule cs
                           JOIN users u ON cs.instructor_id = u.id
                           WHERE cs.instructor_id = ?");
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->query("SELECT cs.*, u.name as instructor_name 
                         FROM class_schedule cs
                         JOIN users u ON cs.instructor_id = u.id");
}

$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
