
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$admin = mysqli_fetch_assoc(mysqli_query($conn,"SELECT role FROM users WHERE id='{$_SESSION['user_id']}' LIMIT 1"));
if (!$admin || strtolower($admin['role']) !== 'admin') { header("Location: dashboard.php"); exit(); }
 
$owner_id    = (int)($_POST['owner_id']    ?? 0);
$property_id = (int)($_POST['property_id'] ?? 0);
 
if ($owner_id <= 0 || $property_id <= 0) {
    $_SESSION['admin_error'] = "Invalid owner or property selected.";
    header("Location: admin_dashboard.php?page=propertyowners"); exit();
}
 
// Check owner exists and is propertyowner role
$owner = mysqli_fetch_assoc(mysqli_query($conn,"SELECT fullname FROM users WHERE id=$owner_id AND role='propertyowner' LIMIT 1"));
if (!$owner) {
    $_SESSION['admin_error'] = "Owner account not found.";
    header("Location: admin_dashboard.php?page=propertyowners"); exit();
}
 
// Check property exists
$prop = mysqli_fetch_assoc(mysqli_query($conn,"SELECT property_name FROM properties WHERE id=$property_id LIMIT 1"));
if (!$prop) {
    $_SESSION['admin_error'] = "Property not found.";
    header("Location: admin_dashboard.php?page=propertyowners"); exit();
}
 
// Assign
mysqli_query($conn,"UPDATE properties SET owner_id=$owner_id WHERE id=$property_id");
 
$oname = htmlspecialchars($owner['fullname']);
$pname = htmlspecialchars($prop['property_name']);
 
// Notify the owner
mysqli_query($conn,"INSERT INTO notifications (user_id, tenant_id, title, message, status, date)
    VALUES ($owner_id, 0,
    'Property Assigned to Your Account 🏢',
    'The property \"$pname\" has been linked to your account. Log in to view your owner dashboard.',
    'unread', NOW())");
 
$_SESSION['admin_success'] = "✅ <strong>$pname</strong> has been assigned to <strong>$oname</strong>. Their dashboard is now active.";
header("Location: admin_dashboard.php?page=propertyowners");
exit();
?>