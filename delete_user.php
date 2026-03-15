<?php
session_start();
include "db_connect.php";
 
// ── Must be logged in as admin ──
$session_uid = (int)($_SESSION['user_id'] ?? 0);
$admin = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT id, role FROM users WHERE id='$session_uid' LIMIT 1"));
 
if (!$admin || strtolower(trim($admin['role'])) !== 'admin') {
    header("Location: dashboard.php"); exit();
}
 
$del_id = (int)($_GET['id'] ?? 0);
 
if ($del_id <= 0) {
    header("Location: admin_dashboard.php?page=users"); exit();
}
 
// ── Prevent deleting yourself ──
if ($del_id === $session_uid) {
    header("Location: admin_dashboard.php?page=users&error=cannot_delete_self"); exit();
}
 
// ── Fetch the user being deleted ──
$target = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT id, role, fullname FROM users WHERE id='$del_id' LIMIT 1"));
 
if (!$target) {
    header("Location: admin_dashboard.php?page=users&error=not_found"); exit();
}
 
// ── Prevent deleting other admins ──
if (strtolower(trim($target['role'])) === 'admin') {
    header("Location: admin_dashboard.php?page=users&error=cannot_delete_admin"); exit();
}
 
// ── Show confirmation page (server-side confirm) ──
if (!isset($_POST['confirm_delete'])) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Confirm Delete | HousingHub</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:"Segoe UI",sans-serif;background:#f0f4f8;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}
.card{background:white;border-radius:14px;padding:40px;max-width:440px;width:100%;box-shadow:0 6px 24px rgba(0,0,0,.1);text-align:center}
.icon{font-size:52px;margin-bottom:16px}
h2{color:#dc2626;font-size:22px;margin-bottom:10px}
p{color:#555;font-size:14px;line-height:1.6;margin-bottom:8px}
.name{font-weight:700;color:#1a1a1a;font-size:16px}
.role{display:inline-block;padding:3px 12px;border-radius:20px;background:#fee2e2;color:#dc2626;font-size:12px;font-weight:600;margin-bottom:24px}
.btn-row{display:flex;gap:12px;margin-top:24px}
.btn-cancel{flex:1;padding:12px;background:#f3f4f6;border:1px solid #ddd;border-radius:8px;color:#555;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;display:block;transition:.2s}
.btn-cancel:hover{background:#e5e7eb}
.btn-delete{flex:1;padding:12px;background:#dc2626;border:none;border-radius:8px;color:white;font-size:14px;font-weight:700;cursor:pointer;transition:.2s}
.btn-delete:hover{background:#b91c1c;transform:translateY(-1px)}
.warning{background:#fef9c3;border:1px solid #fde047;border-radius:8px;padding:12px 16px;font-size:13px;color:#854d0e;margin-top:16px;text-align:left}
</style>
</head>
<body>
<div class="card">
  <div class="icon">⚠️</div>
  <h2>Delete User?</h2>
  <p class="name"><?= htmlspecialchars($target['fullname']) ?></p>
  <span class="role"><?= htmlspecialchars(ucfirst($target['role'])) ?></span>
  <p>This action <strong>cannot be undone</strong>. The user will permanently lose access to HousingHub.</p>
 
  <?php if (strtolower($target['role']) === 'tenant'): ?>
  <div class="warning">
    ⚠️ This user is a <strong>tenant</strong>. Their account will be deleted but their record in the <strong>tenants table will remain</strong> unless you also delete it from Manage Tenants.
  </div>
  <?php endif; ?>
 
  <form method="POST">
    <input type="hidden" name="confirm_delete" value="1">
    <input type="hidden" name="del_id" value="<?= $del_id ?>">
    <div class="btn-row">
      <a href="admin_dashboard.php?page=users" class="btn-cancel">Cancel</a>
      <button type="submit" class="btn-delete">Yes, Delete</button>
    </div>
  </form>
</div>
</body>
</html>
<?php exit(); }
 
// ── Confirmed — do the delete ──
$confirmed_id = (int)($_POST['del_id'] ?? 0);
 
// Double-check the ID matches and isn't admin
if ($confirmed_id !== $del_id) {
    header("Location: admin_dashboard.php?page=users&error=mismatch"); exit();
}
 
// Unlink from tenants table first (set user_id to NULL) so tenant record stays
mysqli_query($conn, "UPDATE tenants SET user_id = NULL WHERE user_id = '$del_id'");
 
// Now delete the user
mysqli_query($conn, "DELETE FROM users WHERE id = '$del_id'");
 
header("Location: admin_dashboard.php?page=users&msg=deleted");
exit();
?>