
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
// Admin only
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$admin = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT role FROM users WHERE id='{$_SESSION['user_id']}' LIMIT 1"));
if (!$admin || strtolower($admin['role']) !== 'admin') {
    header("Location: dashboard.php"); exit();
}
 
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: admin_dashboard.php?page=jobs"); exit(); }
 
// ── Fetch application using only columns that exist ──
// Table: job_applications(id, full_name, email, phone, position, resume, status, created_at)
$app = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM job_applications WHERE id = $id LIMIT 1"));
 
if (!$app) {
    $_SESSION['error'] = "Application not found.";
    header("Location: admin_dashboard.php?page=jobs"); exit();
}
 
// Update status to rejected
mysqli_query($conn, "UPDATE job_applications SET status='rejected' WHERE id=$id");
 
// ── Send rejection email ──
$to       = $app['email'] ?? '';
$name     = $app['full_name'] ?? 'Applicant';
$position = $app['position'] ?? 'the position';
 
if (!empty($to)) {
    $subject = "Your Application Update — HousingHub";
    $body  = "Dear " . $name . ",\n\n";
    $body .= "Thank you for your interest in the position of " . $position . " at HousingHub\n";
    $body .= "and for taking the time to submit your application.\n\n";
    $body .= "After careful consideration, we regret to inform you that we will not be\n";
    $body .= "moving forward with your application at this time. This was a difficult\n";
    $body .= "decision as we received many strong applications.\n\n";
    $body .= "We encourage you to:\n";
    $body .= "  • Apply for other open positions at HousingHub in the future\n";
    $body .= "  • Visit our careers page regularly for new opportunities\n";
    $body .= "  • Contact us at careers@housinghuborg.ug\n\n";
    $body .= "We wish you the very best in your job search.\n\n";
    $body .= "Kind regards,\n";
    $body .= "HousingHub HR Team\n";
    $body .= "careers@housinghuborg.ug";
 
    $headers = "From: HousingHub HR <careers@housinghuborg.ug>\r\n"
             . "Reply-To: careers@housinghuborg.ug\r\n"
             . "X-Mailer: PHP/" . phpversion();
 
    mail($to, $subject, $body, $headers);
    $_SESSION['admin_success'] = "Application rejected. Notification email sent to {$to}.";
} else {
    $_SESSION['admin_success'] = "Application rejected. (No email address on file.)";
}
 
header("Location: admin_dashboard.php?page=jobs");
exit();
?>