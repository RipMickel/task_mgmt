<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

if (!check_role('coordinator')) {
    echo "Access Denied";
    exit;
}

// Fetch instructors
$instructors = $pdo->query("SELECT * FROM users WHERE role='instructor'")->fetchAll();

// Assign task
if (isset($_POST['assign_task'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $assigned_to = $_POST['assigned_to'];
    $date_assigned = date('Y-m-d');
    $deadline = $_POST['deadline'];
    $academic_year = $_POST['academic_year'];
    $assigned_by = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO tasks (title,description,assigned_to,assigned_by,date_assigned,deadline,academic_year) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$title, $desc, $assigned_to, $assigned_by, $date_assigned, $deadline, $academic_year]);
}

// Fetch tasks assigned by this coordinator
$tasks = $pdo->prepare("SELECT t.*, u.name as instructor_name FROM tasks t JOIN users u ON t.assigned_to=u.id WHERE t.assigned_by=?");
$tasks->execute([$_SESSION['user_id']]);
$assigned_tasks = $tasks->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Coordinator Dashboard</title>
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

        .welcome-container {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .welcome-container img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            border: 2px solid #ddd;
        }

        .welcome-container h1 {
            font-size: 22px;
            margin: 0;
        }

        .form-section {
            background: #fff;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .form-section h3 {
            margin-bottom: 15px;
        }

        form input,
        form textarea,
        form select {
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        table th,
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        table th {
            background: #f5f5f5;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Coordinator Panel</h2>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php">Dashboard</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'class_schedule.php' ? 'active' : '' ?>"><a href="class_schedule.php">Class Schedule</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="welcome-container">
                <?php
                $profilePic = !empty($_SESSION['profile_image'])
                    ? "../uploads/profiles/" . $_SESSION['profile_image']
                    : "../assets/images/default.png";
                ?>
                <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile">
                <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>
            </div>

            <!-- Assign Task -->
            <section class="form-section">
                <h3>📌 Assign New Task</h3>
                <form method="post">
                    <input type="text" name="title" placeholder="Task Title" required>
                    <textarea name="description" placeholder="Task Description" required></textarea>
                    <select name="assigned_to" required>
                        <option value="">-- Select Instructor --</option>
                        <?php foreach ($instructors as $ins): ?>
                            <option value="<?= $ins['id'] ?>"><?= htmlspecialchars($ins['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" name="deadline" required>
                    <input type="text" name="academic_year" placeholder="Academic Year (e.g. 2025-2026)" required>
                    <button type="submit" name="assign_task">Assign Task</button>
                </form>
            </section>

            <!-- Assigned Tasks -->
            <section>
                <h3>📋 Assigned Tasks</h3>
                <?php if (count($assigned_tasks) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Instructor</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Academic Year</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assigned_tasks as $task): ?>
                                <tr>
                                    <td><?= htmlspecialchars($task['title']) ?></td>
                                    <td><?= htmlspecialchars($task['instructor_name']) ?></td>
                                    <td><?= htmlspecialchars($task['deadline']) ?></td>
                                    <td><?= htmlspecialchars($task['status']) ?></td>
                                    <td><?= htmlspecialchars($task['academic_year']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No tasks assigned yet.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>

</html>