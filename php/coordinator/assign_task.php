<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
require_once "send_email.php";

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
    $assigned_to = $_POST['user_id'];
    $deadline = $_POST['deadline'];
    $academic_year = $_POST['academic_year'];
    $assigned_by = $_SESSION['user_id'];

    if (!empty($title) && !empty($description) && !empty($assigned_to) && !empty($deadline) && !empty($academic_year)) {
        // Insert task
        $stmt = $pdo->prepare("INSERT INTO tasks (title, description, assigned_to, assigned_by, deadline, academic_year, date_assigned) 
                               VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$title, $description, $assigned_to, $assigned_by, $deadline, $academic_year]);

        // Insert into user_logs
        $log_stmt = $pdo->prepare("INSERT INTO user_logs (user_id, action) VALUES (?, ?)");
        $log_stmt->execute([$assigned_by, "Assigned task '$title' to user ID $assigned_to"]);

        // Send email notification to assigned user
        $user_stmt = $pdo->prepare("SELECT email, name FROM users WHERE id = ?");
        $user_stmt->execute([$assigned_to]);
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && !empty($user['email'])) {
            $to = $user['email'];
            $subject = "New Task Assigned: $title";

            $message = "
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>New Task Assigned</title>
  <style>
    body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background-color: #f4f7fc; }
    .email-container { width: 100%; max-width: 600px; margin: 0 auto; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    h1 { color: #2e6c8b; font-size: 24px; margin-bottom: 10px; }
    p { font-size: 14px; line-height: 1.6; }
    ul { padding-left: 20px; margin-bottom: 20px; }
    li { font-size: 14px; margin-bottom: 10px; }
    .footer { font-size: 12px; color: #888; text-align: center; margin-top: 30px; }
    .task-info { background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-top: 20px; }
    .task-info strong { color: #2e6c8b; }
  </style>
</head>
<body>
  <div class='email-container'>
    <h1>Hello " . htmlspecialchars($user['name']) . ",</h1>
    <p>A new task has been assigned to you by " . htmlspecialchars($_SESSION['username']) . ".</p>
    <div class='task-info'>
      <ul>
        <li><strong>Task Title:</strong> $title</li>
        <li><strong>Description:</strong> $description</li>
        <li><strong>Deadline:</strong> $deadline</li>
        <li><strong>Academic Year:</strong> $academic_year</li>
      </ul>
    </div>
    <p>Please check your dashboard for more details.</p>
    <p>Regards,<br>Team</p>
    <div class='footer'>
      <p>This is an automated message, please do not reply to this email.</p>
    </div>
  </div>
</body>
</html>
";

            $sent = sendEmailNotification($to, $subject, $message);
            if (!$sent) {
                error_log("Failed to send email to $to");
            }
        }

        $_SESSION['success'] = "Task assigned successfully!";
        header("Location: assign_task.php");
        exit;
    } else {
        $_SESSION['error'] = "All fields are required!";
    }
}

// Fetch all users (including admin, coordinator, instructor, etc.)
$user_stmt = $pdo->query("SELECT id, name, role FROM users");
$users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Assign Task - Coordinator/Admin</title>
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
        <aside class="sidebar">
            <h2>Coordinator / Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li class="active"><a href="view_task.php">My Task</a></li>
                <li class="active"><a href="assign_task.php">Assign Task</a></li>
                <li><a href="view_tasks.php">View Assigned Tasks</a></li>
                <li><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

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
                        <select name="title" required>
                            <option value="Syllabus">Syllabus</option>
                            <!-- you can add more titles if needed -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Task Description</label>
                        <textarea name="description" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="user_id">Assign to User</label>
                        <select name="user_id" required>
                            <option value="">-- Select User --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?php echo $u['id']; ?>">
                                    <?php echo htmlspecialchars($u['name'] . " (" . $u['role'] . ")"); ?>
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
                        <select name="academic_year" required>
                            <option value="2024-2025">2024-2025</option>
                            <option value="2025-2026">2025-2026</option>
                            <option value="2026-2027">2026-2027</option>
                        </select>
                    </div>

                    <button type="submit">Assign Task</button>
                </form>
            </div>
        </main>
    </div>
</body>

</html>