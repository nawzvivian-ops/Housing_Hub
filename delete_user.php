<?php
session_start();
require_once "db_connect.php";

// Ensure user is logged in
$session_uid = (int)($_SESSION['user_id'] ?? 0);
if ($session_uid <= 0) {
    header("Location: index.php");
    exit();
}

// Check if admin
$stmt = $conn->prepare("SELECT id, role FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $session_uid);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin || strtolower(trim($admin['role'])) !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Get user ID to delete
$del_id = (int)($_GET['id'] ?? 0);

if ($del_id <= 0) {
    header("Location: admin_dashboard.php?page=users");
    exit();
}

// Prevent self-delete
if ($del_id === $session_uid) {
    header("Location: admin_dashboard.php?page=users&error=cannot_delete_self");
    exit();
}

// Fetch target user
$stmt = $conn->prepare("SELECT id, role, fullname FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $del_id);
$stmt->execute();
$result = $stmt->get_result();
$target = $result->fetch_assoc();

if (!$target) {
    header("Location: admin_dashboard.php?page=users&error=not_found");
    exit();
}

// Prevent deleting another admin
if (strtolower(trim($target['role'])) === 'admin') {
    header("Location: admin_dashboard.php?page=users&error=cannot_delete_admin");
    exit();
}

/* =========================
   SHOW CONFIRMATION PAGE
========================= */
if (!isset($_POST['confirm_delete'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Confirm Delete | HousingHub</title>

<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">

<style>
/* (Your design preserved exactly) */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08)}
body{font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.card{width:100%;max-width:460px;background:rgba(4,9,26,.9);border:1px solid var(--border);border-radius:16px;padding:40px;text-align:center}
.btn-row{display:flex;gap:12px;margin-top:16px}
.btn-cancel,.btn-delete{flex:1;padding:12px;border-radius:8px;font-weight:600;text-decoration:none;text-align:center}
.btn-cancel{background:#222;color:#aaa}
.btn-delete{background:#ef4444;color:#fff;border:none;cursor:pointer}
</style>

</head>
<body>

<div class="card">
    <h2>⚠️ Delete User?</h2>

    <p><strong><?= htmlspecialchars($target['fullname']) ?></strong></p>
    <p><?= ucfirst(htmlspecialchars($target['role'])) ?></p>

    <p>This action cannot be undone.</p>

    <form method="POST">
        <input type="hidden" name="confirm_delete" value="1">
        <input type="hidden" name="del_id" value="<?= $del_id ?>">

        <div class="btn-row">
            <a href="admin_dashboard.php?page=users" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-delete">Delete</button>
        </div>
    </form>
</div>

</body>
</html>
<?php
exit();
}

/* =========================
   DELETE LOGIC
========================= */

// Validate POST ID
$confirmed_id = (int)($_POST['del_id'] ?? 0);
if ($confirmed_id !== $del_id) {
    header("Location: admin_dashboard.php?page=users&error=mismatch");
    exit();
}

// Remove link from tenants table safely
$stmt = $conn->prepare("UPDATE tenants SET user_id = NULL WHERE user_id = ?");
$stmt->bind_param("i", $del_id);
$stmt->execute();

// Delete user safely
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $del_id);
$stmt->execute();

// Redirect
header("Location: admin_dashboard.php?page=users&msg=deleted");
exit();