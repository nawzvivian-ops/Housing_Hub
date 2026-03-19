
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
 
$user    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE id='{$_SESSION['user_id']}' LIMIT 1"));
$role    = strtolower($user['role'] ?? '');
$id      = (int)($_GET['id'] ?? 0);
 
// Determine redirect based on role
$redirect = match($role) {
    'admin'         => 'admin_dashboard.php?page=notifications',
    'staff'         => 'staff_notifications.php',
    'propertyowner' => 'propertyowner_dashboard.php',
    default         => 'dashboard.php'
};
 
if ($id > 0) {
    // Mark as read — update both is_read and status fields to cover all variations
    mysqli_query($conn, "UPDATE notifications SET is_read=1, status='read' WHERE id=$id");
    $_SESSION['success'] = "Notification marked as read.";
}
 
// Mark all — if ?all=1 is passed
if (isset($_GET['all'])) {
    $uid = (int)$_SESSION['user_id'];
    mysqli_query($conn, "UPDATE notifications SET is_read=1, status='read' WHERE user_id=$uid");
    $_SESSION['success'] = "All notifications marked as read.";
}
 
header("Location: $redirect");
exit();
?>