<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("UPDATE maintenance_requests SET status='completed', completed_at=CURDATE() WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: admin_dashboard.php?page=maintenance");
exit();
?>
