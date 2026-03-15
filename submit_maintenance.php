<?php
session_start();
mysqli_report(MYSQLI_REPORT_OFF);
include "db_connect.php";
 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: login.php");
    exit();
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenant_id   = (int)$_SESSION['user_id'];
    $category    = trim($_POST['category']    ?? '');
    $issue_title = trim($_POST['issue_title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $issue       = mysqli_real_escape_string($conn, "[{$category}] {$issue_title} — {$description}");
 
    if (empty($issue_title)) {
        $_SESSION['error'] = "Please enter an issue title.";
        header("Location: dashboard.php");
        exit();
    }
 
    // Get property_id from your tenants table
    $property_id = null;
    try {
        $pq = mysqli_prepare($conn, "SELECT property_id FROM tenants WHERE user_id = ? LIMIT 1");
        if ($pq) {
            mysqli_stmt_bind_param($pq, "i", $tenant_id);
            mysqli_stmt_execute($pq);
            $pr = mysqli_stmt_get_result($pq);
            if ($row = mysqli_fetch_assoc($pr)) {
                $property_id = $row['property_id'] ? (int)$row['property_id'] : null;
            }
        }
    } catch (Exception $e) {
        $property_id = null;
    }
 
    // Insert into maintenance table
    $stmt = mysqli_prepare($conn,
        "INSERT INTO maintenance (property_id, tenant_id, issue, status, created_at)
         VALUES (?, ?, ?, 'open', NOW())"
    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iis", $property_id, $tenant_id, $issue);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Maintenance request submitted! Your property manager has been notified.";
        } else {
            $_SESSION['error'] = "Failed to submit: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Database error: " . mysqli_error($conn);
    }
}
 
header("Location: dashboard.php");
exit();
?>
 