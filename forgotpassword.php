<?php
session_start();
include "db_connect.php";
require_once __DIR__ . "/send_mail.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
header('Content-Type: application/json');
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}
 
$email = trim($_POST['email'] ?? '');
 
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit();
}
 
$email_esc = mysqli_real_escape_string($conn, $email);
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE email='$email_esc' LIMIT 1"));
 
// Always return success even if email not found (security — don't reveal accounts)
if (!$user) {
    echo json_encode(['success' => true, 'message' => 'If that email exists, a reset code has been sent.']);
    exit();
}
 
// Generate a 6-digit code + token
$code    = strtoupper(bin2hex(random_bytes(3))); // 6-char hex e.g. A3F92C
$expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));
 
// Create password_resets table if not exists
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
 
// Delete old tokens for this email
mysqli_query($conn, "DELETE FROM password_resets WHERE email='$email_esc'");
 
// Insert new token
$token_esc = mysqli_real_escape_string($conn, $code);
mysqli_query($conn, "INSERT INTO password_resets (email, token, expires_at) VALUES ('$email_esc', '$token_esc', '$expires')");
 
// Send email
$name = htmlspecialchars($user['fullname']);
$body = "Dear $name,\n\n"
      . "You requested a password reset for your HousingHub account.\n\n"
      . "Your reset code is:\n\n"
      . "    $code\n\n"
      . "Enter this code on the login page to set a new password.\n"
      . "This code expires in 30 minutes.\n\n"
      . "If you did not request this, you can safely ignore this email.\n\n"
      . "— The HousingHub Team\n"
      . "support@housinghub.ug";
 
$sent = send_mail($email, 'Your HousingHub Password Reset Code', $body);
 
if ($sent) {
    echo json_encode(['success' => true, 'message' => 'Reset code sent! Check your email inbox (and spam folder). Code expires in 30 minutes.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Could not send email. Please contact support@housinghub.ug']);
}
 