<?php
session_start();
require_once "../inc/config.php";

// Restrict to coordinators only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinator') {
    echo "Access Denied";
    exit;
}

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE users SET status='active' WHERE id=? AND role='instructor'");
        $stmt->execute([$id]);
    } elseif ($action === 'deactivate') {
        $stmt = $pdo->prepare("UPDATE users SET status='inactive' WHERE id=? AND role='instructor'");
        $stmt->execute([$id]);
    } elseif ($action === 'activate') {
        $stmt = $pdo->prepare("UPDATE users SET status='active' WHERE id=? AND role='instructor'");
        $stmt->execute([$id]);
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id=? AND role='instructor'");
        $stmt->execute([$id]);
    }

    header("Location: manage_instructors.php");
    exit;
}

// Fetch instructors
$stmt = $pdo->query("SELECT * FROM users WHERE role='instructor' ORDER BY created_at DESC");
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Instructors</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
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

        /* Main Content */
        .main-content {
            flex-grow: 1;
            padding: 30px;
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        table th,
        table td {
            padding: 14px 16px;
            text-align: left;
        }

        table th {
            background: #2c3e50;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
        }

        table tr:nth-child(even) {
            background: #f9fbfc;
        }

        table tr:hover {
            background: #eaf6f6;
        }

        table td {
            color: #333;
            font-size: 15px;
        }

        /* Action links */
        a.action-btn {
            padding: 6px 12px;
            margin: 2px;
            border-radius: 4px;
            text-decoration: none;
            color: #fff;
            font-size: 13px;
        }

        a.approve {
            background: #27ae60;
        }

        a.deactivate {
            background: #e67e22;
        }

        a.activate {
            background: #3498db;
        }

        a.delete {
            background: #e74c3c;
        }

        a.action-btn:hover {
            opacity: 0.85;
        }

        @media (max-width: 768px) {

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            th {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            td {
                border: none;
                position: relative;
                padding-left: 50%;
            }

            td:before {
                position: absolute;
                top: 50%;
                left: 15px;
                transform: translateY(-50%);
                font-weight: bold;
                color: #555;
            }

            td:nth-of-type(1):before {
                content: "Name";
            }

            td:nth-of-type(2):before {
                content: "Email";
            }

            td:nth-of-type(3):before {
                content: "Status";
            }

            td:nth-of-type(4):before {
                content: "Actions";
            }
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
                <li class="active"><a href="manage_instructors.php">Manage Instructors</a></li>
                <li><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="user_logs.php">Recent Logins</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <div class="main-content">
            <h2>Manage Instructors</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($instructors as $inst): ?>
                        <tr>
                            <td><?= htmlspecialchars($inst['name']) ?></td>
                            <td><?= htmlspecialchars($inst['email']) ?></td>
                            <td><?= ucfirst($inst['status']) ?></td>
                            <td>
                                <?php if ($inst['status'] === 'pending'): ?>
                                    <a class="action-btn approve" href="?action=approve&id=<?= $inst['id'] ?>">Approve</a>
                                <?php elseif ($inst['status'] === 'active'): ?>
                                    <a class="action-btn deactivate" href="?action=deactivate&id=<?= $inst['id'] ?>">Deactivate</a>
                                <?php elseif ($inst['status'] === 'inactive'): ?>
                                    <a class="action-btn activate" href="?action=activate&id=<?= $inst['id'] ?>">Activate</a>
                                <?php endif; ?>
                                <a class="action-btn delete" href="?action=delete&id=<?= $inst['id'] ?>" onclick="return confirm('Delete this instructor?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>