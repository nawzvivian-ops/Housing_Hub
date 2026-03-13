<?php
session_start();
include "db_connect.php";

// Only admins can delete
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user role
$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT role FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($result);
$role = strtolower(trim($user['role']));

if ($role !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Validate inputs
$table = $_GET['table'] ?? '';
$id = intval($_GET['id'] ?? 0);

// Define allowed tables (to prevent SQL injection)
$allowed_tables = [
    'properties',
    'tenants',
    'payments',
    'maintenance_requests',
    'job_applications',
    'guests',
    'complaints'
];

if (!in_array($table, $allowed_tables)) {
    $_SESSION['error'] = "Invalid table specified.";
    header("Location: admin_dashboard.php");
    exit();
}

if ($id <= 0) {
    $_SESSION['error'] = "Invalid record ID.";
    header("Location: admin_dashboard.php?page=$table");
    exit();
}

// Perform deletion
$delete = mysqli_query($conn, "DELETE FROM `$table` WHERE id = $id");

if ($delete) {
    $_SESSION['success'] = ucfirst($table) . " record deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete record. Try again.";
}

// Redirect back to the table page
header("Location: admin_dashboard.php?page=$table");
exit();
?>

