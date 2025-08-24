<?php
require_once "inc/functions.php";
redirectIfNotLoggedIn();
?>

<h1>Welcome <?= $_SESSION['name'] ?> (<?= $_SESSION['role'] ?>)</h1>

<?php if (isRole('admin') || isRole('coordinator')): ?>
    <a href="assign_task.php">Assign Task</a><br>
<?php endif; ?>

<a href="view_tasks.php">View Tasks</a><br>
<a href="history.php">Task History</a><br>
<a href="auth/logout.php">Logout</a>