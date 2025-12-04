<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('coordinator')) {
    echo "Access Denied";
    exit;
}

$coordinator_id = $_SESSION['user_id'];

// Fetch instructors, order with profile images first
$stmt = $pdo->prepare("
    SELECT id, name, profile_image 
    FROM users 
    WHERE role='instructor'
    ORDER BY 
        CASE WHEN profile_image IS NULL OR profile_image = '' THEN 1 ELSE 0 END,
        name ASC
");
$stmt->execute();
$instructors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Chat List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
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
            position: relative;
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

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .cards-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            padding-bottom: 50px;
        }

        .instructor-card {
            background-color: #fff;
            width: 250px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s, background-color 0.3s;
            margin-bottom: 15px;
            position: relative;
        }

        .instructor-card.unread {
            background-color: #fff7e6;
            /* Highlight unread messages */
        }

        .instructor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .instructor-card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 2px solid #007BFF;
        }

        .instructor-card h2 {
            margin: 10px 0;
            font-size: 18px;
            color: #333;
        }

        .instructor-card a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 15px;
            font-size: 16px;
            border-radius: 5px;
            background-color: #007BFF;
            color: #fff;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .instructor-card a:hover {
            background-color: #0056b3;
        }

        /* Unread count badge */
        .unread-count {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #e74c3c;
            color: #fff;
            font-size: 12px;
            font-weight: bold;
            padding: 3px 7px;
            border-radius: 50%;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Coordinator Panel</h2>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php">Dashboard</a></li>
                <li class="active"><a href="view_task.php">My Task</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'assign_task.php' ? 'active' : '' ?>"><a href="assign_task.php">Assign Task</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'completed_task.php' ? 'active' : '' ?>"><a href="completed_task.php">Completed Task</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'manage_instructors.php' ? 'active' : '' ?>"><a href="manage_instructors.php">List of Instructors</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : '' ?>"><a href="edit_profile.php">Edit Profile</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'chat_list.php' ? 'active' : '' ?>"><a href="chat_list.php">Feedback</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main content -->
        <main class="main-content">
            <h1>Chat with Instructors</h1>
            <div class="cards-container">
                <?php foreach ($instructors as $inst):
                    $profile_img = !empty($inst['profile_image']) ? '../uploads/profiles/' . $inst['profile_image'] : 'default_avatar.png';

                    // Count unread messages per instructor
                    $stmtUnread = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
                    $stmtUnread->execute([$inst['id'], $coordinator_id]);
                    $unreadCount = $stmtUnread->fetchColumn();
                    $unreadClass = ($unreadCount > 0) ? 'unread' : '';
                ?>
                    <div class="instructor-card <?= $unreadClass ?>">
                        <img src="<?= htmlspecialchars($profile_img) ?>" alt="<?= htmlspecialchars($inst['name']) ?> Profile">
                        <h2><?= htmlspecialchars($inst['name']) ?></h2>
                        <a href="chat_view.php?instructor_id=<?= $inst['id'] ?>">Open Chat</a>
                        <?php if ($unreadCount > 0): ?>
                            <div class="unread-count"><?= $unreadCount ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>

</html>