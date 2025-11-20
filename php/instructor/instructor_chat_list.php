<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('instructor')) {
    echo "Access Denied";
    exit;
}

// Fetch all coordinators (or only those relevant)
$stmt = $pdo->prepare("SELECT id, name, profile_image FROM users WHERE role = 'coordinator'");
$stmt->execute();
$coordinators = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Coordinators</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background: #f0f2f5;
            color: #0c1b33;
            display: flex;
            height: 100vh;
        }

        h1 {
            text-align: center;
            margin: 30px 0;
            color: #0c1b33;
        }

        .dashboard-container {
            display: flex;
            width: 100%;
            padding: 20px;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #0c1b33;
            color: white;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            top: 0;
            left: 0;
            z-index: 100;
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

        /* Main Content Area */
        .main-content {
            flex-grow: 1;
            margin-left: 260px;
            padding: 30px;
        }

        .cards-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
        }

        .coordinator-card {
            background: #fff;
            width: 220px;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(12, 27, 51, 0.1);
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }

        .coordinator-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(12, 27, 51, 0.2);
        }

        .coordinator-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 12px;
            border: 2px solid #0c1b33;
        }

        .coordinator-card h2 {
            margin: 10px 0;
            font-size: 18px;
            color: #0c1b33;
        }

        .coordinator-card a {
            display: inline-block;
            margin-top: 12px;
            padding: 10px 15px;
            font-size: 15px;
            border-radius: 8px;
            background-color: #65aedd;
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .coordinator-card a:hover {
            background-color: #4a92c7;
        }

        /* Responsiveness */
        @media screen and (max-width: 768px) {
            .sidebar {
                width: 200px;
                padding: 20px;
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .coordinator-card {
                width: 180px;
            }
        }

        @media screen and (max-width: 480px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 10px;
            }

            .coordinator-card {
                width: 150px;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Instructor</h2>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php">Dashboard</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'task_history.php' ? 'active' : '' ?>"><a href="task_history.php">My Completed Tasks</a></li>
                                     <li><a href="assigned_subjects">Assigned Subjects</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : '' ?>"><a href="edit_profile.php">Edit Profile</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'instructor_chat_list.php' ? 'active' : '' ?>"><a href="instructor_chat_list.php">Feedback</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>
        <header>Coordinators</header>
        <div class="main-content">
            <h1>Chat with Coordinators</h1>

            <div class="cards-container">
                <?php foreach ($coordinators as $coord):
                    $profile_img = !empty($coord['profile_image']) ? '../uploads/profiles/' . $coord['profile_image'] : 'default_avatar.png';
                ?>
                    <div class="coordinator-card">
                        <img src="<?= htmlspecialchars($profile_img) ?>" alt="<?= htmlspecialchars($coord['name']) ?> Profile">
                        <h2><?= htmlspecialchars($coord['name']) ?></h2>
                        <a href="instructor_chat_view.php?coordinator_id=<?= htmlspecialchars($coord['id']) ?>">Open Chat</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>

</html>