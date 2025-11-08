<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $profile_image = $_SESSION['profile_image'] ?? null;

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['profile_image']['name']);
        $uploadDir = '../uploads/profiles/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $dest_path = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $profile_image = $fileName;
        }
    }

    if ($password) {
        $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, password=?, profile_image=? WHERE id=?");
        $stmt->execute([$name, $email, $password, $profile_image, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, profile_image=? WHERE id=?");
        $stmt->execute([$name, $email, $profile_image, $user_id]);
    }

    $_SESSION['name'] = $name;
    $_SESSION['profile_image'] = $profile_image;

    header("Location: dashboard.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../instructor/instructor.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }

        .dashboard-container {
            display: flex;
        }


        .sidebar {
            background: #1a1a2e;
            color: white;
            padding: 20px;
            width: 220px;
        }


        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 15px 0;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
        }

        .sidebar ul li.active a {
            font-weight: bold;
            color: #ffd700;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            background-color: #ecf0f1;
        }

        h1 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f4f4f4;
            color: black;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        a.btn,
        a {
            color: blue;
            text-decoration: none;
        }



        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .alert-error {
            background-color: #e74c3c;
            color: white;
        }

        .alert-success {
            color: white;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Instructor Panel</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="task_history.php">Task History </a></li>
                <li class="active"><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1>Edit Profile</h1>
            <form method="post" enctype="multipart/form-data">
                <label>Name:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br>
                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>
                <label>Password (leave blank to keep current):</label>
                <input type="password" name="password"><br>
                <label>Profile Image:</label>
                <input type="file" name="profile_image"><br>
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="../uploads/profiles/<?= htmlspecialchars($user['profile_image']) ?>" width="100" height="100" style="border-radius:50%;">
                <?php endif; ?>
                <button type="submit" class="btn">Update Profile</button>
            </form>
        </main>
    </div>
</body>

</html>