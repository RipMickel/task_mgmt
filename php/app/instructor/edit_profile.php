<?php
session_start();
require_once "../../includes/config.php";
require_once "../../includes/functions.php";
redirect_if_not_logged_in();

$user_id = $_SESSION['user_id'];

$feedback = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $profile_image = $_SESSION['profile_image'] ?? null;

    // Validate name / email (basic)
    if (empty($name) || empty($email)) {
        $error = "Name and Email are required.";
    } else {
        // Handle image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_image']['tmp_name'];
            $fileName = time() . '_' . basename($_FILES['profile_image']['name']);
            $uploadDir = '../uploads/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $dest_path = $uploadDir . $fileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $profile_image = $fileName;
            } else {
                $error = "There was an error uploading the image.";
            }
        }

        if (empty($error)) {
            if ($password) {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, profile_image = ? WHERE id = ?");
                $stmt->execute([$name, $email, $password, $profile_image, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, profile_image = ? WHERE id = ?");
                $stmt->execute([$name, $email, $profile_image, $user_id]);
            }

            // Update session variables
            $_SESSION['name'] = $name;
            $_SESSION['profile_image'] = $profile_image;

            $feedback = "Profile updated successfully.";
        }
    }
}

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: #0c1b33;
            color: white;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            text-align: center;
            font-weight: 700;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar a {
            display: block;
            padding: 12px 15px;
            text-decoration: none;
            color: #ddd;
            border-radius: 6px;
            margin-bottom: 10px;
            transition: 0.25s;
        }

        .sidebar a:hover,
        .sidebar .active a {
            background: #1e2a47;
            color: #fff;
        }


        .main-content {
            flex: 1;
            padding: 30px;
            background-color: #ecf0f1;
        }

        .main-content h1 {
            margin-bottom: 25px;
            font-size: 2em;
        }

        form.edit-profile {
            background: #fff;
            padding: 25px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        form.edit-profile label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        form.edit-profile input[type="text"],
        form.edit-profile input[type="email"],
        form.edit-profile input[type="password"],
        form.edit-profile input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        form.edit-profile img.profile-preview {
            display: block;
            margin-bottom: 18px;
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ddd;
        }

        form.edit-profile button.btn-submit {
            background-color: #1a1a2e;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            font-size: 1em;
            cursor: pointer;
        }

        form.edit-profile button.btn-submit:hover {
            background-color: #333344;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .alert-success {
            background-color: #2ecc71;
            color: #fff;
        }

        .alert-error {
            background-color: #e74c3c;
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Instructor</h2>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php">Dashboard</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'task_history.php' ? 'active' : '' ?>"><a href="task_history.php">Completed Tasks</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'assigned_subjects.php' ? 'active' : '' ?>"><a href="assigned_subjects.php">My Subjects</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : '' ?>"><a href="edit_profile.php">Edit Profile</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'instructor_chat_list.php' ? 'active' : '' ?>"><a href="instructor_chat_list.php">Feedback</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1>Edit Profile</h1>

            <?php if (!empty($feedback)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($feedback) ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form class="edit-profile" method="post" enctype="multipart/form-data">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>

                <label for="password">Password (leave blank to keep current):</label>
                <input type="password" id="password" name="password">

                <label for="profile_image">Profile Image:</label>
                <input type="file" id="profile_image" name="profile_image" accept="image/*">

                <?php if (!empty($user['profile_image'])): ?>
                    <img class="profile-preview" src="../uploads/profiles/<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile preview">
                <?php endif; ?>

                <button type="submit" class="btn-submit">Update Profile</button>
            </form>
        </main>
    </div>
</body>

</html>