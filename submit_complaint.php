<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$category = trim($_POST['category'] ?? 'Other');
$message = trim($_POST['message'] ?? '');

if ($message === '') {
    $_SESSION['error'] = "Please enter your complaint message.";
    header("Location: dashboard.php#pg-complaints");
    exit();
}

$tenant_id = $user_id;
$lookup = $conn->prepare("SELECT id FROM tenants WHERE user_id = ? LIMIT 1");
if ($lookup) {
    $lookup->bind_param("i", $user_id);
    $lookup->execute();
    $row = $lookup->get_result()->fetch_assoc();
    if ($row) {
        $tenant_id = (int)$row['id'];
    }
}

$stmt = $conn->prepare("INSERT INTO complaints (tenant_id, category, message, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
if (!$stmt) {
    $_SESSION['error'] = "Could not prepare complaint.";
    header("Location: dashboard.php#pg-complaints");
    exit();
}

$stmt->bind_param("iss", $tenant_id, $category, $message);
$ok = $stmt->execute();
$_SESSION[$ok ? 'success' : 'error'] = $ok ? "Complaint submitted." : "Could not submit complaint.";

header("Location: dashboard.php#pg-complaints");
exit();
?>
