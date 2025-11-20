<?php
session_start();
require_once "../inc/config.php"; // Make sure $pdo is defined in config.php as a PDO instance
require_once "../inc/functions.php";

redirect_if_not_logged_in();
if (!check_role('coordinator')) {
    echo "Access Denied";
    exit;
}

$coordinator_id = $_SESSION['user_id'];
$errors = [];
$success = "";

/* Handle POST actions */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create_subject') {
        $subj_code = trim($_POST['subj_code'] ?? '');
        $subj_num = trim($_POST['subj_num'] ?? '');
        $subj_description = trim($_POST['subj_description'] ?? '');
        $subj_units_raw = trim($_POST['subj_units'] ?? '');
        if ($subj_code === '' || $subj_num === '') $errors[] = "Subject code and subject number are required.";
        if ($subj_units_raw === '') $errors[] = "Subject units is required.";
        elseif (!is_numeric($subj_units_raw) || floatval($subj_units_raw) < 0) $errors[] = "Subject units must be a non-negative number.";
        else $subj_units = number_format((float)$subj_units_raw, 2, '.', '');

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO subjects (subj_code, subj_num, subj_description, subj_units, created_by) VALUES (:code, :num, :desc, :units, :created_by)");
                $stmt->execute([
                    ':code' => $subj_code,
                    ':num' => $subj_num,
                    ':desc' => $subj_description,
                    ':units' => $subj_units,
                    ':created_by' => $coordinator_id
                ]);
                $success = "Subject created successfully.";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) $errors[] = "Subject with that code/number already exists.";
                else $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }

    if ($action === 'assign_subject') {
        $subject_id = intval($_POST['subject_id'] ?? 0);
        $instructor_id = intval($_POST['instructor_id'] ?? 0);
        if ($subject_id <= 0 || $instructor_id <= 0) $errors[] = "Please select both a subject and an instructor.";
        else {
            try {
                $check = $pdo->prepare("SELECT id FROM subject_assignments WHERE subject_id = :sid AND instructor_id = :iid");
                $check->execute([':sid' => $subject_id, ':iid' => $instructor_id]);
                if ($check->rowCount() > 0) $errors[] = "This subject is already assigned to the selected instructor.";
                else {
                    $ins = $pdo->prepare("INSERT INTO subject_assignments (subject_id, instructor_id, coordinator_id) VALUES (:sid, :iid, :cid)");
                    $ins->execute([':sid' => $subject_id, ':iid' => $instructor_id, ':cid' => $coordinator_id]);
                    $success = "Subject assigned successfully.";
                }
            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }

    if ($action === 'remove_assignment' && isset($_POST['assignment_id'])) {
        $assignment_id = intval($_POST['assignment_id']);
        if ($assignment_id > 0) {
            try {
                $del = $pdo->prepare("DELETE FROM subject_assignments WHERE id = :aid");
                $del->execute([':aid' => $assignment_id]);
                $success = "Assignment removed.";
            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
            }
        } else $errors[] = "Invalid assignment id.";
    }
}

/* Fetch instructors */
$instructors = [];
try {
    $insRes = $pdo->query("SELECT id, name, email FROM users WHERE role = 'instructor' ORDER BY name");
    $instructors = $insRes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

/* Fetch subjects */
$subjects = [];
try {
    $subRes = $pdo->query("SELECT subj_id, subj_code, subj_num, subj_description, subj_units FROM subjects ORDER BY subj_code, subj_num");
    $subjects = $subRes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

/* Fetch assignments */
$assignments = [];
$sql = "SELECT sa.id AS assignment_id, s.subj_id, s.subj_code, s.subj_num, s.subj_description, s.subj_units,
        u.id AS instructor_id, u.name AS instructor_name, sa.assigned_at
        FROM subject_assignments sa
        JOIN subjects s ON sa.subject_id = s.subj_id
        JOIN users u ON sa.instructor_id = u.id
        ORDER BY sa.assigned_at DESC";
try {
    $res = $pdo->query($sql);
    $assignments = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

/* Fetch unread messages count */
$unreadMessages = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS unread_count FROM messages WHERE receiver_id = :rid AND is_read = 0 AND sender_id IN (SELECT id FROM users WHERE role = 'instructor')");
    $stmt->execute([':rid' => $coordinator_id]);
    $unreadMessages = $stmt->fetchColumn();
} catch (PDOException $e) {}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Assign Subject - Coordinator</title>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<style>
/* Keep all your previous CSS intact */
body { margin: 0; font-family: Arial, sans-serif; background: #f4f6f9; }
.dashboard-container { display: flex; min-height: 100vh; }
.sidebar { width: 240px; background: #2c3e50; color: #fff; padding: 20px 0; flex-shrink: 0; }
.sidebar h2 { text-align: center; margin-bottom: 30px; font-size: 20px; letter-spacing: 1px; }
.sidebar ul { list-style: none; padding: 0; }
.sidebar ul li { margin: 10px 0; position: relative; }
.sidebar ul li a { display: block; padding: 12px 20px; color: #ecf0f1; text-decoration: none; transition: background 0.3s; }
.sidebar ul li a:hover, .sidebar ul li.active a { background: #34495e; border-left: 4px solid #1abc9c; }
.main-content { flex-grow: 1; padding: 30px; }
.header { display: flex; justify-content: flex-end; align-items: center; padding: 10px 30px; background: #fff; border-bottom: 1px solid #ddd; position: sticky; top: 0; z-index: 100; }
.bell-icon { position: relative; font-size: 24px; cursor: pointer; margin-right: 20px; }
.bell-icon .notification-badge { position: absolute; top: -5px; right: -10px; background: #e74c3c; color: #fff; font-size: 12px; padding: 2px 6px; border-radius: 50%; }

.container { padding: 0 20px; max-width: 1200px; margin: 0 auto; }
.card { background: #fff; padding: 18px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); margin-bottom: 18px; }
h1,h2 { margin: 0 0 12px 0; }
.form-row { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:10px; }
.form-row label { min-width: 110px; }
input[type="text"], textarea, select { padding:8px; border:1px solid #ccc; border-radius:4px; min-width:220px; }
button { padding:8px 12px; border:none; border-radius:4px; cursor:pointer; background:#1abc9c; color:#fff; }
.small-btn { padding:6px 8px; font-size:13px; background:#e74c3c; color:#fff; border:none; border-radius:4px; cursor:pointer; }
.notice { padding:10px; border-radius:6px; margin-bottom:10px; }
.notice.success { background:#e8f8f3; color:#0a6b50; }
.notice.error { background:#ffe8e8; color:#8b1a1a; }
table.dataTable thead th { background:#2c3e50; color:#fff; }
.muted { color:#666; font-size:13px; }
.small { font-size:13px; color:#555; }

@media (max-width: 900px) { .sidebar { display: none; } .main-content { padding: 15px; } }
</style>
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <h2>Coordinator Panel</h2>
        <ul>
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php">Dashboard</a></li>
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'assign_task.php' ? 'active' : '' ?>"><a href="assign_task.php">Assign Task</a></li>
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'completed_task.php' ? 'active' : '' ?>"><a href="completed_task.php">Completed Task</a></li>
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'manage_instructors.php' ? 'active' : '' ?>"><a href="manage_instructors.php">List of Instructors</a></li>
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'assign_subject.php' ? 'active' : '' ?>"><a href="assign_subject.php">Assign Subjects</a></li>
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : '' ?>"><a href="edit_profile.php">Edit Profile</a></li>
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'chat_list.php' ? 'active' : '' ?>"><a href="chat_list.php">Feedback</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="container">

            <?php if (!empty($success)): ?>
                <div class="notice success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="notice error"><ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul></div>
            <?php endif; ?>

            <div class="card">
                <h2>Create New Subject</h2>
                <form method="post" action="">
                    <input type="hidden" name="action" value="create_subject">
                    <div class="form-row">
                        <label>Subject Code</label>
                        <input type="text" name="subj_code" required placeholder="e.g. ICS 84">
                        <label>Subject No.</label>
                        <input type="text" name="subj_num" required placeholder="e.g. 101">
                    </div>
                    <div class="form-row">
                        <label>Course Description</label>
                        <textarea name="subj_description" rows="2" placeholder="e.g. Capstone Project 2"></textarea>
                    </div>
                    <div class="form-row">
                        <label>Units</label>
                        <input type="text" name="subj_units" required placeholder="e.g. 3 or 3.00">
                        <div class="small muted">Enter numeric units. Example: 3 or 4.00</div>
                    </div>
                    <div class="form-row">
                        <button type="submit">Create Subject</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2>Assign Subject to Instructor</h2>
                <form method="post" action="">
                    <input type="hidden" name="action" value="assign_subject">
                    <div class="form-row">
                        <label>Select Subject</label>
                        <select name="subject_id" required>
                            <option value="">-- Select subject --</option>
                            <?php foreach ($subjects as $s):
                                $label = htmlspecialchars($s['subj_code'] . ' ' . $s['subj_num'] . ' — ' . $s['subj_description'] . ' (' . number_format((float)$s['subj_units'],2) . ' units)');
                            ?>
                                <option value="<?= (int)$s['subj_id'] ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label>Instructor</label>
                        <select name="instructor_id" required>
                            <option value="">-- Select instructor --</option>
                            <?php foreach ($instructors as $i): ?>
                                <option value="<?= (int)$i['id'] ?>"><?= htmlspecialchars($i['name'] . ' (' . $i['email'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit">Assign</button>
                    </div>
                    <div class="muted">If instructor not listed, create user with role = 'instructor'.</div>
                </form>
            </div>

            <!-- DATA TABLE BELOW FIRST TWO CONTAINERS -->
            <div class="card">
                <h2>Active Assigned Subject to Instructors</h2>
                <div class="table-container">
                    <table id="assignmentsTable" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th>Subj ID</th>
                                <th>Code</th>
                                <th>Number</th>
                                <th>Description</th>
                                <th>Units</th>
                                <th>Instructor</th>
                                <th>Assigned At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $a): ?>
                                <tr>
                                    <td><?= (int)$a['subj_id'] ?></td>
                                    <td><?= htmlspecialchars($a['subj_code']) ?></td>
                                    <td><?= htmlspecialchars($a['subj_num']) ?></td>
                                    <td><?= htmlspecialchars($a['subj_description']) ?></td>
                                    <td><?= htmlspecialchars(number_format((float)$a['subj_units'],2)) ?></td>
                                    <td><?= htmlspecialchars($a['instructor_name']) ?></td>
                                    <td><?= htmlspecialchars($a['assigned_at']) ?></td>
                                    <td>
                                        <form method="post" style="display:inline" onsubmit="return confirm('Remove this assignment?');">
                                            <input type="hidden" name="action" value="remove_assignment">
                                            <input type="hidden" name="assignment_id" value="<?= (int)$a['assignment_id'] ?>">
                                            <button type="submit" class="small-btn">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div> <!-- /container -->
    </main>
</div>

<script>
$(document).ready(function() {
    $('#assignmentsTable').DataTable({
        "order": [[ 6, "desc" ]],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'print',
                text: 'Print Table',
                title: 'Assigned Subjects',
                exportOptions: { columns: ':visible:not(:last-child)' },
                autoPrint: true
            },
            {
                extend: 'pdfHtml5',
                text: 'Export PDF',
                title: 'Assigned Subjects',
                exportOptions: { columns: ':visible:not(:last-child)' },
                orientation: 'landscape',
                pageSize: 'A4'
            }
        ]
    });

    var lastUnread = <?= (int)$unreadMessages ?>;
    setInterval(function() {
        $.ajax({
            url: 'fetch_unread_messages.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                var count = parseInt(data.unread) || 0;
                if (count > 0) {
                    if ($('.bell-icon .notification-badge').length) $('.bell-icon .notification-badge').text(count);
                    else $('.bell-icon').append('<span class="notification-badge">' + count + '</span>');
                } else $('.bell-icon .notification-badge').remove();
                lastUnread = count;
            }
        });
    }, 5000);
});
</script>
</body>
</html>
