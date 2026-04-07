<?php
session_start();
require_once "../../includes/config.php";
require_once "../../includes/functions.php";
require_once "send_email.php";

// Restrict access to coordinators only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinator') {
    echo "Access Denied";
    exit;
}

// Handle actions (approve / activate / deactivate / delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id     = intval($_GET['id']);
    $action = $_GET['action'];
    $newStatus = null;

    if ($action === 'approve' || $action === 'activate') {
        $stmt = $pdo->prepare("UPDATE users SET status='active' WHERE id=? AND role='instructor'");
        $stmt->execute([$id]);
        $newStatus = 'Active';
    } elseif ($action === 'deactivate') {
        $stmt = $pdo->prepare("UPDATE users SET status='inactive' WHERE id=? AND role='instructor'");
        $stmt->execute([$id]);
        $newStatus = 'Inactive';
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id=? AND role='instructor'");
        $stmt->execute([$id]);
        $newStatus = null;
    }

    // If status changed (approve / activate / deactivate), send email notification
    if ($newStatus !== null) {
        // Fetch user email + name
        $u = $pdo->prepare("SELECT email, name FROM users WHERE id = ?");
        $u->execute([$id]);
        $user = $u->fetch(PDO::FETCH_ASSOC);

        if ($user && !empty($user['email'])) {
            $to      = $user['email'];
            $subject = "Account Status Changed";

            $message = "
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>Account Status Notification</title>
  <style>
    body { font-family: Arial, sans-serif; color: #333; margin:0; padding:0; background-color: #f4f7fc; }
    .email-container { width:100%; max-width:600px; margin:0 auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); }
    h1 { color:#2e6c8b; font-size:24px; margin-bottom:10px; }
    p { font-size:14px; line-height:1.6; }
    .footer { font-size:12px; color:#888; text-align:center; margin-top:30px; }
  </style>
</head>
<body>
  <div class='email-container'>
    <h1>Hello " . htmlspecialchars($user['name']) . ",</h1>
    <p>Your account status has been changed by the coordinator/admin.</p>
    <p><strong>New Status:</strong> $newStatus</p>
    <p>If you have any questions or concerns, please contact the administrator.</p>
    <div class='footer'>
      <p>This is an automated message — please do not reply.</p>
    </div>
  </div>
</body>
</html>
";

            // send notification
            $sent = sendEmailNotification($to, $subject, $message);
            if (!$sent) {
                error_log("Failed to send account status email to $to");
            }
        }
    }

    // redirect to avoid resubmit on refresh
    header("Location: manage_instructors.php");
    exit;
}

// Fetch instructor list
$stmt = $pdo->query("SELECT * FROM users WHERE role='instructor' ORDER BY created_at DESC");
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Instructors</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #eef1f5;
        }

        .dashboard-container {
            display: flex;
            height: 100vh;
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
            transition: background-color 0.25s, color 0.25s;
        }

        .sidebar a:hover,
        .sidebar .active a {
            background: #1e2a47;
            color: #fff;
        }

        .main-content {
            flex: 1;
            padding: 20px 30px;
            overflow-y: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background-color: #34495e;
            color: white;
        }

        tr:nth-child(even) {
            background: #ecf0f1;
        }

        tr:nth-child(odd) {
            background: #fff;
        }

        td a {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }

        /* Action links styling */
        td a[href*="action=approve"] {
            color: #34495e;
        }

        td a[href*="action=approve"]:hover {
            background: #34495e;
            color: white;
        }

        td a[href*="action=deactivate"] {
            color: red;
        }

        td a[href*="action=deactivate"]:hover {
            background: green;
            color: white;
        }

        td a[href*="action=activate"] {
            color: #1abc9c;
        }

        td a[href*="action=activate"]:hover {
            background: #1abc9c;
            color: white;
        }

        td a[href*="action=delete"] {
            color: red;
        }

        td a[href*="action=delete"]:hover {
            background: green;
            color: white;
        }
    </style>

</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Coordinator Panel</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="view_task.php">My Task</a></li>
                <li><a href="assign_task.php">Assign Task</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'assign_subject.php' ? 'active' : '' ?>"><a href="assign_subject.php">Assign Subjects</a></li>

                <li class="<?= basename($_SERVER['PHP_SELF']) == 'assigned_subjects.php' ? 'active' : '' ?>"><a href="assigned_subjects.php">My Subjects</a></li>

                <li><a href="completed_task.php">Completed Task</a></li>
                <li class="active"><a href="manage_instructors.php">List of Instructors</a></li>
                <li><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="chat_list.php">Feedback</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>
        <div class="main-content">
            <h2>List of Instructors</h2>
            <table border="1" cellpadding="8" cellspacing="0">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($instructors as $inst): ?>
                    <tr>
                        <td><?= htmlspecialchars($inst['name']) ?></td>
                        <td><?= htmlspecialchars($inst['email']) ?></td>
                        <td><?= ucfirst($inst['status']) ?></td>
                        <td>
                            <?php if ($inst['status'] === 'pending'): ?>
                                <a href="?action=approve&id=<?= $inst['id'] ?>">Approve</a>
                            <?php elseif ($inst['status'] === 'active'): ?>
                                <a href="?action=deactivate&id=<?= $inst['id'] ?>">Deactivate</a>
                            <?php elseif ($inst['status'] === 'inactive'): ?>
                                <a href="?action=activate&id=<?= $inst['id'] ?>">Activate</a>
                            <?php endif; ?>
                            | <a href="?action=delete&id=<?= $inst['id'] ?>" onclick="return confirm('Delete this instructor?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>

</html>