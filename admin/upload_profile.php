<?php
session_start();
require_once "../inc/config.php";

// Handle file upload
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = "../uploads/profiles/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES['profile_image']['name']);
    $targetFile = $uploadDir . $fileName;

    // Validate image
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $validTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($fileType, $validTypes)) {
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
            // Update session and database
            $_SESSION['profile_image'] = $fileName;

            $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->execute([$fileName, $_SESSION['user_id']]);

            header("Location: dashboard.php");
            exit;
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "Invalid file type.";
    }
} else {
    echo "No file uploaded or an error occurred.";
}
