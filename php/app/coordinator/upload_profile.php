<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../includes/config.php";
require_once "../../includes/functions.php";
redirect_if_not_logged_in();

$user_id = $_SESSION['user_id'];

// Check if file is uploaded
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
    $file = $_FILES['profile_image'];
    $fileName = basename($file['name']);
    $fileTmp = $file['tmp_name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileExt, $allowed)) {
        // Create upload folder if it doesn't exist
        $uploadDir = "../uploads/profiles/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Rename file to prevent conflict (e.g., userID_timestamp)
        $newFileName = "profile_" . $user_id . "_" . time() . "." . $fileExt;
        $uploadPath = $uploadDir . $newFileName;

        // Move file
        if (move_uploaded_file($fileTmp, $uploadPath)) {
            // Update database record
            $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->execute([$newFileName, $user_id]);

            // Update session so the new image displays immediately
            $_SESSION['profile_image'] = $newFileName;

            // ✅ Redirect back with success
            header("Location: dashboard.php?upload=success");
            exit;
        } else {
            echo "Error: Failed to move uploaded file.";
        }
    } else {
        echo "Error: Only JPG, JPEG, PNG, and GIF files are allowed.";
    }
} else {
    echo "Error: No file uploaded or upload error.";
}
