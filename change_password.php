<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Fetch current password hash
    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (password_verify($current, $user['password'])) {
        if ($new === $confirm) {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param("si", $hash, $user_id);
            $stmt->execute();
            $message = "Password updated successfully!";
        } else {
            $message = "New passwords do not match.";
        }
    } else {
        $message = "Current password is incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Change Password</title>
<style>
body { font-family:'Segoe UI', sans-serif; background:lightblue; padding:50px; }
.container { max-width:400px; margin: auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 6px 15px rgba(0,0,0,0.1); }
input { width:100%; padding:10px; margin:8px 0; border-radius:6px; border:1px solid #ccc; }
button { padding:10px 15px; background:#4f46e5; color:#fff; border:none; border-radius:6px; cursor:pointer; width:100%; }
button:hover { background:#3b36e0; }
p.message { padding:10px; background:#fef3c7; border:1px solid #facc15; border-radius:6px; color:#92400e; }
a { display:inline-block; margin-top:15px; text-decoration:none; color:#4f46e5; }
</style>
</head>
<body>
<div class="container"style="border:3px solid #105ceb;">
<h2>CHANGE PASSWORD</h2>
<?php if($message) echo "<p class='message'>$message</p>"; ?>
<form method="POST">
    <label>Current Password:</label>
    <input type="password" name="current_password" required>

    <label>New Password:</label>
    <input type="password" name="new_password" required>

    <label>Confirm New Password:</label>
    <input type="password" name="confirm_password" required>

    <button type="submit">Update Password</button>
</form>
<a href="tenants.php">← Back to Dashboard</a>
</div>
</body>
</html>