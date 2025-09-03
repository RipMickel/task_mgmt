<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

// Only coordinators or admins can assign tasks
if (!check_role('coordinator') && !check_role('admin')) {
    echo "Access Denied";
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $assigned_to = $_POST['instructor_id'];
    $deadline = $_POST['deadline'];
    $academic_year = $_POST['academic_year'];
    $assigned_by = $_SESSION['user_id'];

    if (!empty($title) && !empty($description) && !empty($assigned_to) && !empty($deadline) && !empty($academic_year)) {
        $stmt = $pdo->prepare("INSERT INTO tasks (title, description, assigned_to, assigned_by, deadline, academic_year, date_assigned) 
                               VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$title, $description, $assigned_to, $assigned_by, $deadline, $academic_year]);

        // Insert into user_logs
        $log_stmt = $pdo->prepare("INSERT INTO user_logs (user_id, action) VALUES (?, ?)");
        $log_stmt->execute([$assigned_by, "Assigned task '$title' to instructor ID $assigned_to"]);

        $_SESSION['success'] = "Task assigned successfully!";
        header("Location: assign_task.php");
        exit;
    } else {
        $_SESSION['error'] = "All fields are required!";
    }
}

// Fetch instructors
$stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'instructor'");
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Assign Task - Coordinator</title>
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

        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-container h2 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        button {
            background: #1abc9c;
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #16a085;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
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
                <li class="active"><a href="assign_task.php">Assign Task</a></li>
                <li><a href="class_schedule.php">Class Schedule</a></li>
                <li><a href="manage_instructors.php">Manage Instructors</a></li>
                <li><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="user_logs.php">Recent Logins</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="form-container">
                <h2>Assign Task</h2>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success'];
                                                        unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error"><?php echo $_SESSION['error'];
                                                    unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="title">Task Title</label>
                        <input type="text" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Task Description</label>
                        <textarea name="description" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="instructor_id">Assign to Instructor</label>
                        <select name="instructor_id" required>
                            <option value="">-- Select Instructor --</option>
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?php echo $instructor['id']; ?>">
                                    <?php echo htmlspecialchars($instructor['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="deadline">Deadline</label>
                        <input type="date" name="deadline" required>
                    </div>

                    <div class="form-group">
                        <label for="academic_year">Academic Year</label>
                        <input type="text" name="academic_year" placeholder="e.g. 2025-2026" required>
                    </div>

                    <button type="submit">Assign Task</button>
                </form>
            </div>
        </main>
    </div>
</body>

</html>