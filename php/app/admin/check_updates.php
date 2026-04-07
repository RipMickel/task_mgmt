<?php
require_once "../../includes/config.php";

// Count total tasks 
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM tasks");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo $row['total'];
