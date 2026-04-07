<?php
session_start();
require_once "../inc/config.php";

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Handle role update
if (isset($_POST['update_role'])) {
    $userId = $_POST['user_id'];
    $newRole = $_POST['role'];

    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$newRole, $userId]);

    $message = "User role updated successfully.";
}

// Fetch all users
$stmt = $pdo->query("SELECT id, name, email, role FROM users ORDER BY id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Roles</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: #ecf0f1;
            display: flex;
            flex-direction: column;
            padding: 20px 0;
        }

        .sidebar h2 {
            text-align: center;
            margin: 0 0 20px 0;
            font-size: 20px;
            font-weight: bold;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar li {
            margin-bottom: 10px;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: background 0.3s;
        }

        .sidebar a:hover,
        .sidebar .active a {
            background: #1abc9c;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
        }

        .alert {
            background: #dff0d8;
            color: #3c763d;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        /* Table Styling */
        .table-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 40px;
        }

        th,
        td {
            padding: 12px 9px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #2c3e50;
            color: #fff;
        }

        tr:hover {
            background: #f9f9f9;
        }

        select,
        button {
            padding: 6px 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        button {
            background: #1abc9c;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #16a085;
        }

        @media (max-width: 760px) {
            .dashboard-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }

            table {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="completed_task.php">Completed Task</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li class="active"><a href="roles.php">Manage Roles</a></li>
                <li><a href="user_logs.php">Recent Logins</a></li>

                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <div class="main-content">
            <h2>Manage Roles</h2>
            <?php if (isset($message)) echo "<div class='alert'>$message</div>"; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['id']) ?></td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <form method="post" style="display:flex; gap:5px; flex-wrap:wrap;">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <select name="role">
                                            <option value="instructor" <?= $user['role'] == 'instructor' ? 'selected' : '' ?>>Instructor</option>
                                            <option value="coordinator" <?= $user['role'] == 'coordinator' ? 'selected' : '' ?>>Coordinator</option>
                                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                        <button type="submit" name="update_role">Update</button>
                                    </form>
                                </td>
                                <td></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>