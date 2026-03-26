
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
header('Content-Type: application/json');
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}
 
$token    = strtoupper(trim($_POST['token'] ?? ''));
$password = $_POST['password'] ?? '';
 
// Validate inputs
if (!$token) {
    echo json_encode(['success' => false, 'message' => 'Please enter the reset code from your email.']);
    exit();
}
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters.']);
    exit();
}
 
// Make sure table exists (in case forgot_password.php was never called first)
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
 
$token_esc = mysqli_real_escape_string($conn, $token);
$now       = date('Y-m-d H:i:s');
 
// Look up token
$row = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM password_resets
     WHERE token = '$token_esc'
     LIMIT 1"
));
 
if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Reset code not found. Please request a new one.']);
    exit();
}
 
if ($row['used']) {
    echo json_encode(['success' => false, 'message' => 'This reset code has already been used. Please request a new one.']);
    exit();
}
 
if ($row['expires_at'] < $now) {
    echo json_encode(['success' => false, 'message' => 'This reset code has expired (30 min limit). Please request a new one.']);
    exit();
}
 
// All good — update password
$email   = $row['email'];
$hashed  = password_hash($password, PASSWORD_DEFAULT);
$email_e = mysqli_real_escape_string($conn, $email);
 
$update = mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE email='$email_e'");
 
if (!$update || mysqli_affected_rows($conn) === 0) {
    echo json_encode(['success' => false, 'message' => 'Could not update password. Account may not exist. Contact support.']);
    exit();
}
 
// Mark token used
mysqli_query($conn, "UPDATE password_resets SET used=1 WHERE token='$token_esc'");
 
// Set session success so it shows on login page
$_SESSION['success'] = 'Password updated successfully. Please log in with your new password.';
 
echo json_encode(['success' => true, 'message' => 'Password updated successfully! Redirecting to login...']);