
<?php
session_start();
include "db_connect.php";
require_once "send_mail.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
// Admin only
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE id='{$_SESSION['user_id']}' LIMIT 1"));
if (!$admin || strtolower($admin['role']) !== 'admin') { header("Location: dashboard.php"); exit(); }
 
$staff_id = (int)($_GET['id'] ?? 0);
if ($staff_id <= 0) { header("Location: admin_dashboard.php?page=staff_roles"); exit(); }
 
$staff = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$staff_id AND role='staff' LIMIT 1"));
if (!$staff) {
    $_SESSION['admin_error'] = "Staff member not found.";
    header("Location: admin_dashboard.php?page=staff_roles"); exit();
}
 
$name   = $staff['fullname'];
$email  = $staff['email'];
$salary = number_format($staff['salary'] ?? 0);
$month  = date('F Y'); // e.g. "March 2026"
$day    = date('d M Y');
 
// ── Task performance this month ──
$tasks_done = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS c FROM tasks WHERE assigned_to=$staff_id AND status='Completed'
     AND due_date >= DATE_FORMAT(NOW(),'%Y-%m-01')"))['c'] ?? 0;
$tasks_total = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS c FROM tasks WHERE assigned_to=$staff_id
     AND due_date >= DATE_FORMAT(NOW(),'%Y-%m-01')"))['c'] ?? 0;
 
// ── Build email ──
$subject = "Your HousingHub Payslip — $month";
 
$body  = "Dear $name,\n\n";
$body .= "Please find your payslip summary for $month below.\n\n";
$body .= "════════════════════════════════\n";
$body .= "        HOUSINGhub PAYSLIP\n";
$body .= "════════════════════════════════\n";
$body .= "Staff Name   : $name\n";
$body .= "Email        : $email\n";
$body .= "Pay Period   : $month\n";
$body .= "Payment Date : Last day of $month\n";
$body .= "────────────────────────────────\n";
$body .= "Basic Salary : UGX $salary\n";
$body .= "Deductions   : UGX 0\n";
$body .= "Bonuses      : UGX 0\n";
$body .= "────────────────────────────────\n";
$body .= "NET PAY      : UGX $salary\n";
$body .= "════════════════════════════════\n\n";
$body .= "PERFORMANCE SUMMARY ($month)\n";
$body .= "────────────────────────────────\n";
$body .= "Tasks Completed : $tasks_done\n";
$body .= "Total Tasks     : $tasks_total\n";
$body .= "────────────────────────────────\n\n";
$body .= "Your salary will be processed on the last day of $month.\n";
$body .= "For any queries regarding your pay, contact HR immediately.\n\n";
$body .= "Thank you for your continued dedication to HousingHub.\n\n";
$body .= "Warm regards,\n";
$body .= "HousingHub HR & Payroll\n";
$body .= "hr@housinghuborg.ug\n";
$body .= "Generated on: $day";
 
$sent = send_mail($email, $subject, $body);
 
// Save notification for the staff member
$safe_month = mysqli_real_escape_string($conn, $month);
$safe_name  = mysqli_real_escape_string($conn, $name);
mysqli_query($conn,
    "INSERT INTO notifications (user_id, tenant_id, title, message, status, date)
     VALUES ($staff_id, 0,
             'Your Payslip for $safe_month 📄',
             'Your payslip for $safe_month has been sent to your email address. Net pay: UGX $salary.',
             'unread', NOW())");
 
if ($sent) {
    $_SESSION['admin_success'] = "✅ Payslip for <strong>$name</strong> sent to <strong>$email</strong> for $month.";
} else {
    $_SESSION['admin_error'] = "⚠️ Could not send email to $email. Check your send_mail.php credentials and ensure Composer is installed.";
}
 
header("Location: admin_dashboard.php?page=staff_roles");
exit();
?>