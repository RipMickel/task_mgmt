<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

// Only coordinators allowed
if ($_SESSION['role'] !== 'coordinator') {
    echo "Access Denied";
    exit;
}

$user_id = $_SESSION['user_id'];

// Coordinator: CRUD access
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_schedule'])) {
        $stmt = $pdo->prepare("INSERT INTO class_schedule (course, section, instructor_id, day, time, room) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['course'], $_POST['section'], $_POST['instructor_id'], $_POST['day'], $_POST['time'], $_POST['room']]);
    }

    if (isset($_POST['update_schedule'])) {
        $stmt = $pdo->prepare("UPDATE class_schedule SET course=?, section=?, instructor_id=?, day=?, time=?, room=? WHERE id=?");
        $stmt->execute([$_POST['course'], $_POST['section'], $_POST['instructor_id'], $_POST['day'], $_POST['time'], $_POST['room'], $_POST['id']]);
    }

    if (isset($_POST['delete_schedule'])) {
        $stmt = $pdo->prepare("DELETE FROM class_schedule WHERE id=?");
        $stmt->execute([$_POST['id']]);
    }
}

// Fetch all schedules
$stmt = $pdo->query("SELECT cs.*, u.name as instructor_name 
                     FROM class_schedule cs
                     JOIN users u ON cs.instructor_id = u.id");
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Fetch all instructors for dropdown
$instructorStmt = $pdo->query("SELECT id, name FROM users WHERE role = 'instructor'");
$instructors = $instructorStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Class Schedule (Coordinator)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f4f6f9;
            color: #333;
        }

        .dashboard-container {
            display: flex;
            height: 100vh;
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
            overflow-y: auto;
        }

        h2 {
            margin-top: 0;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: #fff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table thead {
            background: #34495e;
            color: #fff;
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table tr:hover {
            background: #f9f9f9;
        }

        /* Forms */
        form {
            margin: 10px 0;
        }

        input,
        select,
        button {
            padding: 8px 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        input[type="text"],
        input[type="number"],
        select {
            width: 180px;
        }

        button {
            background: #1abc9c;
            color: white;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #16a085;
        }

        h3 {
            margin-top: 30px;
        }
    </style>

</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Coordinator Panel</h2>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php">Dashboard</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'assign_task.php' ? 'active' : '' ?>"><a href="assign_task.php">Assign Task</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'class_schedule.php' ? 'active' : '' ?>"><a href="class_schedule.php">Class Schedule</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : '' ?>"><a href="edit_profile.php">Edit Profile</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'user_logs.php' ? 'active' : '' ?>"><a href="user_logs.php">Recent Logins</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h2>📚 Class Schedule</h2>

            <?php if (count($schedules) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Instructor</th>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Room</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $sched): ?>
                            <tr>
                                <td><?= htmlspecialchars($sched['course']) ?></td>
                                <td><?= htmlspecialchars($sched['section']) ?></td>
                                <td><?= htmlspecialchars($sched['instructor_name']) ?></td>
                                <td><?= htmlspecialchars($sched['day']) ?></td>
                                <td><?= htmlspecialchars($sched['time']) ?></td>
                                <td><?= htmlspecialchars($sched['room']) ?></td>
                                <td>
                                    <!-- Edit -->
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $sched['id'] ?>">
                                        <input type="text" name="course" value="<?= htmlspecialchars($sched['course']) ?>" required>
                                        <input type="text" name="section" value="<?= htmlspecialchars($sched['section']) ?>" required>

                                        <!-- Dropdown instead of typing ID -->
                                        <select name="instructor_id" required>
                                            <option value="">-- Select Instructor --</option>
                                            <?php foreach ($instructors as $inst): ?>
                                                <option value="<?= $inst['id'] ?>" <?= $inst['id'] == $sched['instructor_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($inst['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <input type="text" name="day" value="<?= htmlspecialchars($sched['day']) ?>" required>
                                        <input type="text" name="time" value="<?= htmlspecialchars($sched['time']) ?>" required>
                                        <input type="text" name="room" value="<?= htmlspecialchars($sched['room']) ?>" required>
                                        <button type="submit" name="update_schedule">Update</button>
                                    </form>
                                    <!-- Delete -->
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $sched['id'] ?>">
                                        <button type="submit" name="delete_schedule" onclick="return confirm('Delete this schedule?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No schedules found.</p>
            <?php endif; ?>

            <h3>Add New Schedule</h3>
            <form method="post">
                <input type="text" name="course" placeholder="Course" required>
                <input type="text" name="section" placeholder="Section" required>

                <!-- Dropdown for instructor -->
                <select name="instructor_id" required>
                    <option value="">-- Select Instructor --</option>
                    <?php foreach ($instructors as $inst): ?>
                        <option value="<?= $inst['id'] ?>"><?= htmlspecialchars($inst['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="text" name="day" placeholder="Day" required>
                <input type="text" name="time" placeholder="Time" required>
                <input type="text" name="room" placeholder="Room" required>
                <button type="submit" name="add_schedule">Add Schedule</button>
            </form>
        </main>
    </div>
</body>

</html>