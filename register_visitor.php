
<?php
session_start();
mysqli_report(MYSQLI_REPORT_OFF);
include "db_connect.php";
 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: login.php");
    exit();
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenant_id     = (int)$_SESSION['user_id'];
    $property_id   = (int)($_POST['property_id'] ?? 0) ?: null;
    $visitor_name  = mysqli_real_escape_string($conn, trim($_POST['visitor_name']  ?? ''));
    $relationship  = mysqli_real_escape_string($conn, trim($_POST['relationship']  ?? ''));
    $visitor_phone = mysqli_real_escape_string($conn, trim($_POST['visitor_phone'] ?? ''));
    $visitor_id    = mysqli_real_escape_string($conn, trim($_POST['visitor_id']    ?? ''));
    $visit_date    = mysqli_real_escape_string($conn, trim($_POST['visit_date']    ?? ''));
    $duration      = mysqli_real_escape_string($conn, trim($_POST['duration']      ?? ''));
    $purpose       = mysqli_real_escape_string($conn, trim($_POST['purpose']       ?? ''));
 
    if (empty($visitor_name) || empty($visit_date)) {
        $_SESSION['error'] = "Visitor name and visit date are required.";
        header("Location: dashboard.php");
        exit();
    }
 
    $stmt = mysqli_prepare($conn,
        "INSERT INTO visitors
            (tenant_id, property_id, visitor_name, relationship, visitor_phone,
             visitor_id, visit_date, duration, purpose, status, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved', NOW())"
    );
 
    // i = int, s = string — 9 placeholders: i, i, s, s, s, s, s, s, s
    mysqli_stmt_bind_param($stmt, "iisssssss",
        $tenant_id,
        $property_id,
        $visitor_name,
        $relationship,
        $visitor_phone,
        $visitor_id,
        $visit_date,
        $duration,
        $purpose
    );
 
    if (mysqli_stmt_execute($stmt)) {
        $friendly_date = date('d M Y', strtotime($visit_date));
        $_SESSION['success'] = "Visitor registered! {$visitor_name}'s pass is approved for {$friendly_date}.";
    } else {
        $_SESSION['error'] = "Failed to register visitor: " . mysqli_error($conn);
    }
}
 
header("Location: dashboard.php");
exit();
?>