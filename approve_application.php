
<?php
session_start();
include "db_connect.php";
require_once __DIR__ . "/send_mail.php";
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
 
// Fetch application
$app = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM job_applications WHERE id = $id LIMIT 1"));
 
if (!$app) {
    $_SESSION['admin_error'] = "Application not found.";
    header("Location: admin_dashboard.php?page=jobs"); exit();
}
 
// Update status to approved
mysqli_query($conn, "UPDATE job_applications SET status='approved' WHERE id=$id");
 
$name     = $app['full_name'] ?? 'New Staff';
$email    = $app['email']     ?? '';
$phone    = $app['phone']     ?? '';
$position = $app['position']  ?? 'Staff';
 
// ── Create a staff user account if one doesn't already exist ──
$new_user_id = 0;
if (!empty($email)) {
    // Check if user already exists
    $existing = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT id FROM users WHERE email = '" . mysqli_real_escape_string($conn, $email) . "' LIMIT 1"));
 
    if ($existing) {
        // User exists — just update their role to staff
        $new_user_id = (int)$existing['id'];
        mysqli_query($conn,
            "UPDATE users SET role='staff', fullname='" . mysqli_real_escape_string($conn, $name) . "'
             WHERE id = $new_user_id");
        $account_action = "existing account updated to staff role";
    } else {
        // Create a new staff account
        // Generate a temporary password: first name + last 4 digits of phone
        $fname    = explode(' ', $name)[0];
        $phone4   = strlen($phone) >= 4 ? substr($phone, -4) : '0000';
        $temp_pass = $fname . '@' . $phone4;
        $hashed   = password_hash($temp_pass, PASSWORD_DEFAULT);
        $safe_name  = mysqli_real_escape_string($conn, $name);
        $safe_email = mysqli_real_escape_string($conn, $email);
        $safe_phone = mysqli_real_escape_string($conn, $phone);
 
        mysqli_query($conn,
            "INSERT INTO users (fullname, email, phone, password, role, created_at)
             VALUES ('$safe_name', '$safe_email', '$safe_phone', '$hashed', 'staff', NOW())");
        $new_user_id  = (int)mysqli_insert_id($conn);
        $account_action = "new staff account created";
    }
}
 
// ── Send congratulations + login details email ──
if (!empty($email)) {
    $subject = "Congratulations! You Have Been Hired — HousingHub";
    $body  = "Dear " . $name . ",\n\n";
    $body .= "We are pleased to inform you that your application for\n";
    $body .= strtoupper($position) . " at HousingHub has been APPROVED.\n\n";
    $body .= "You have been added to our team as a Staff member.\n\n";
 
    if (!empty($temp_pass)) {
        $body .= "════════════════════════════════\n";
        $body .= "YOUR LOGIN CREDENTIALS\n";
        $body .= "════════════════════════════════\n";
        $body .= "Website : http://localhost/housinghub/index.php\n";
        $body .= "Email   : " . $email . "\n";
        $body .= "Password: " . $temp_pass . "\n";
        $body .= "════════════════════════════════\n\n";
        $body .= "⚠️ Please change your password after your first login.\n\n";
    }
 
    $body .= "Next steps:\n";
    $body .= "  • Keep your phone (" . $phone . ") reachable\n";
    $body .= "  • Check your email regularly for onboarding instructions\n";
    $body .= "  • Log in to the staff portal when directed\n\n";
    $body .= "Welcome to the HousingHub team!\n\n";
    $body .= "Warm regards,\n";
    $body .= "HousingHub HR Team\n";
    $body .= "careers@housinghuborg.ug";
 
    send_mail($email, $subject, $body);
 
    // Save notification if user account exists
    if ($new_user_id > 0) {
        $safe_pos = mysqli_real_escape_string($conn, $position);
        mysqli_query($conn,
            "INSERT INTO notifications (user_id, tenant_id, title, message, status, date)
             VALUES ($new_user_id, 0,
                     'Welcome to HousingHub Staff! 🎉',
                     'Your application for $safe_pos has been approved. Check your email for login details.',
                     'unread', NOW())");
    }
}
 
$_SESSION['admin_success'] = "✅ Application approved! "
    . ucfirst($account_action ?? 'done') . " for <strong>" . htmlspecialchars($name) . "</strong>."
    . (!empty($temp_pass) ? " Temporary password: <code>" . htmlspecialchars($temp_pass) . "</code>" : "")
    . " Confirmation email sent to " . htmlspecialchars($email) . ".";
 
header("Location: admin_dashboard.php?page=jobs");
exit();
?>