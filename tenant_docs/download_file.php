<?php
session_start();
include "db_connect.php";

// Ensure tenant is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied");
}

$user_id = $_SESSION['user_id'];

// Get tenant info
$tenant_result = mysqli_query($conn, "SELECT * FROM tenants WHERE user_id='$user_id'");
$tenant = mysqli_fetch_assoc($tenant_result);

if (!$tenant) {
    die("Tenant information not found.");
}

// Get file type from query string
$file_type = $_GET['type'] ?? '';

$allowed_types = ['lease', 'rent', 'purchase'];
if (!in_array($file_type, $allowed_types)) {
    die("Invalid file type.");
}

// Map file type to actual file
$filename = "tenant_docs/{$file_type}_{$tenant['id']}.pdf";

// Check if file exists
if (!file_exists($filename)) {
    die("File not found.");
}

// Serve the file for download
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.basename($filename).'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filename));
readfile($filename);
exit;
?>