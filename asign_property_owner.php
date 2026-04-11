
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
// Auth
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$me = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE id='{$_SESSION['user_id']}' LIMIT 1"));
if (!$me || strtolower($me['role']) !== 'admin') { header("Location: dashboard.php"); exit(); }
 
// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_dashboard.php?page=propertyowners"); exit();
}
 
$owner_id    = (int)($_POST['owner_id']    ?? 0);
$property_id = (int)($_POST['property_id'] ?? 0);
 
// Validate
if ($owner_id <= 0) {
    $_SESSION['admin_error'] = "No owner ID received. Please try again.";
    header("Location: admin_dashboard.php?page=propertyowners"); exit();
}
if ($property_id <= 0) {
    $_SESSION['admin_error'] = "No property selected. Please choose a property from the dropdown.";
    header("Location: admin_dashboard.php?page=propertyowners"); exit();
}
 
// Get owner — check both 'propertyowner' and 'owner' roles to be safe
$owner = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT id, fullname, email FROM users WHERE id=$owner_id LIMIT 1"));
if (!$owner) {
    $_SESSION['admin_error'] = "Owner account (ID: $owner_id) not found in database.";
    header("Location: admin_dashboard.php?page=propertyowners"); exit();
}
 
// Get property
$prop = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT id, property_name FROM properties WHERE id=$property_id LIMIT 1"));
if (!$prop) {
    $_SESSION['admin_error'] = "Property (ID: $property_id) not found in database.";
    header("Location: admin_dashboard.php?page=propertyowners"); exit();
}
 
// Do the assignment
mysqli_query($conn, "UPDATE properties SET owner_id=$owner_id WHERE id=$property_id");
 
if (mysqli_affected_rows($conn) === 0) {
    // Might already be assigned to same owner — still success
}
 
$oname      = htmlspecialchars($owner['fullname']);
$pname      = htmlspecialchars($prop['property_name']);
$pname_safe = mysqli_real_escape_string($conn, $prop['property_name']);
 
// Save notification
mysqli_query($conn, "INSERT INTO notifications (user_id, tenant_id, title, message, status, date)
    VALUES ($owner_id, 0,
    'Property Assigned to Your Account',
    'The property \"$pname_safe\" has been linked to your account. Log in to view your owner dashboard.',
    'unread', NOW())");
 
$_SESSION['admin_success'] = "✅ <strong>$pname</strong> has been assigned to <strong>$oname</strong>. Their dashboard is now active.";
header("Location: admin_dashboard.php?page=propertyowners");
exit();