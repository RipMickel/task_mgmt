<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Update profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    if ($password) {
        $update = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
        $update->execute([$name, $email, $password, $user_id]);
    } else {
        $update = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $update->execute([$name, $email, $user_id]);
    }

    log_action($pdo, $user_id, "Updated profile");
    $_SESSION['success'] = "Profile updated successfully!";
    header("Location: edit_profile.php");
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background: #2c3e50;
            color: #fff;
            padding: 20px 0;
            flex-shrink: 0;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 20px;
            letter-spacing: 1px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 10px 0;
        }

        .sidebar ul li a {
            display: block;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: background 0.3s;
        }

        .sidebar ul li a:hover,
        .sidebar ul li.active a {
            background: #34495e;
            border-left: 4px solid #1abc9c;
        }

        /* Main content */
        .main-content {
            flex-grow: 1;
            padding: 30px;
        }

        .form-section {
            background: #fff;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            max-width: 500px;
        }

        .form-section h3 {
            margin-bottom: 15px;
        }

        form input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        form button {
            background: #1abc9c;
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        form button:hover {
            background: #16a085;
        }

        .success-message {
            color: green;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Coordinator Panel</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="assign_task.php">Assign Task</a></li>
                <li><a href="completed_task.php">Completed Task</a></li>
                <li><a href="manage_instructors.php">List of Instructors</a></li>
                <li class="active"><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="chat_list.php">Feedback</a></li>

                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main content -->
        <main class="main-content">
            <section class="form-section">
                <h3>👤 Edit Profile</h3>
                <?php if (!empty($_SESSION['success'])): ?>
                    <p class="success-message"><?= $_SESSION['success'];
                                                unset($_SESSION['success']); ?></p>
                <?php endif; ?>

                <form method="post">
                    <label>Name:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

                    <label>Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

                    <label>New Password (leave blank if unchanged):</label>
                    <input type="password" name="password">

                    <button type="submit">Update Profile</button>
                </form>
            </section>
        </main>
    </div>
</body>

</html>