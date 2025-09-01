<?php
session_start();
require_once "../inc/config.php";
require_once "../inc/functions.php";
redirect_if_not_logged_in();

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Coordinator: CRUD access
if ($role === 'coordinator' && $_SERVER['REQUEST_METHOD'] === 'POST') {
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

// Fetch schedules
if ($role === 'instructor') {
    $stmt = $pdo->prepare("SELECT cs.*, u.name as instructor_name 
                           FROM class_schedule cs
                           JOIN users u ON cs.instructor_id = u.id
                           WHERE cs.instructor_id = ?");
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->query("SELECT cs.*, u.name as instructor_name 
                         FROM class_schedule cs
                         JOIN users u ON cs.instructor_id = u.id");
}

$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Class Schedule</title>
    <link rel="stylesheet" href="../instructor/instructor.css">
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2><?= ucfirst($role) ?> Panel</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="task_history.php">Task History</a></li>
                <li class="active"><a href="class_schedule.php">Class Schedule</a></li>
                <li><a href="edit_profile.php">Edit Profile</a></li>
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
                            <?php if ($role === 'coordinator'): ?>
                                <th>Actions</th>
                            <?php endif; ?>
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
                                <?php if ($role === 'coordinator'): ?>
                                    <td>
                                        <!-- Edit -->
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $sched['id'] ?>">
                                            <input type="text" name="course" value="<?= htmlspecialchars($sched['course']) ?>" required>
                                            <input type="text" name="section" value="<?= htmlspecialchars($sched['section']) ?>" required>
                                            <input type="number" name="instructor_id" value="<?= htmlspecialchars($sched['instructor_id']) ?>" required>
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
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No schedules found.</p>
            <?php endif; ?>

            <?php if ($role === 'coordinator'): ?>
                <h3>Add New Schedule</h3>
                <form method="post">
                    <input type="text" name="course" placeholder="Course" required>
                    <input type="text" name="section" placeholder="Section" required>
                    <input type="number" name="instructor_id" placeholder="Instructor ID" required>
                    <input type="text" name="day" placeholder="Day" required>
                    <input type="text" name="time" placeholder="Time" required>
                    <input type="text" name="room" placeholder="Room" required>
                    <button type="submit" name="add_schedule">Add Schedule</button>
                </form>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>