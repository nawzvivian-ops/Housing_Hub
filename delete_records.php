
<?php
session_start();
include "db_connect.php";
 
// ── No caching ──
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
 
// ── Must be logged in ──
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); exit();
}
 
// ── Must be admin ──
$session_uid = (int)$_SESSION['user_id'];
$admin = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT role FROM users WHERE id='$session_uid' LIMIT 1"));
if (!$admin || strtolower(trim($admin['role'])) !== 'admin') {
    header("Location: dashboard.php"); exit();
}
 
// ── Allowed tables and their admin page names ──
$allowed = [
    'properties'         => 'properties',
    'tenants'            => 'tenants',
    'payments'           => 'tenant_payments',
    'maintenance_requests'=> 'maintenance',
    'maintenance'        => 'maintenance',
    'job_applications'   => 'jobs',
    'guests'             => 'guests',
    'complaints'         => 'complaints',
    'users'              => 'users',
    'tasks'              => 'staff_tasks',
    'inspections'        => 'inspections',
    'notifications'      => 'notifications',
    'tenant_documents'   => 'tenant_documents',
    'visitors'           => 'guests',
];
 
// ── Read table and id ──
$table = $_GET['table'] ?? $_POST['table'] ?? '';
$id    = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
 
if (!array_key_exists($table, $allowed)) {
    $_SESSION['error'] = "Invalid table specified.";
    header("Location: admin_dashboard.php"); exit();
}
 
if ($id <= 0) {
    $_SESSION['error'] = "Invalid record ID.";
    header("Location: admin_dashboard.php?page=" . $allowed[$table]); exit();
}
 
// ── Fetch the record so we can show what's being deleted ──
$record = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM `$table` WHERE id = $id LIMIT 1"));
 
if (!$record) {
    $_SESSION['error'] = "Record not found.";
    header("Location: admin_dashboard.php?page=" . $allowed[$table]); exit();
}
 
// ── Work out a display name for the record ──
$display = $record['fullname']
        ?? $record['property_name']
        ?? $record['title']
        ?? $record['visitor_name']
        ?? $record['subject']
        ?? $record['issue']
        ?? $record['document_name']
        ?? "#$id";
 
// ── Show server-side confirmation page ──
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
.card{background:white;border-radius:14px;padding:40px;max-width:460px;width:100%;box-shadow:0 6px 24px rgba(0,0,0,.1);text-align:center}
.icon{font-size:52px;margin-bottom:16px}
h2{color:#dc2626;font-size:22px;margin-bottom:10px}
p{color:#555;font-size:14px;line-height:1.6;margin-bottom:6px}
.record-name{font-weight:700;color:#1a1a1a;font-size:16px;margin:8px 0}
.table-badge{display:inline-block;padding:3px 12px;border-radius:20px;background:#fee2e2;color:#dc2626;font-size:12px;font-weight:600;margin-bottom:20px;text-transform:uppercase;letter-spacing:.5px}
.warning{background:#fef9c3;border:1px solid #fde047;border-radius:8px;padding:12px 16px;font-size:13px;color:#854d0e;margin:16px 0;text-align:left}
.btn-row{display:flex;gap:12px;margin-top:24px}
.btn-cancel{flex:1;padding:12px;background:#f3f4f6;border:1px solid #ddd;border-radius:8px;color:#555;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;display:flex;align-items:center;justify-content:center;transition:.2s}
.btn-cancel:hover{background:#e5e7eb}
.btn-delete{flex:1;padding:12px;background:#dc2626;border:none;border-radius:8px;color:white;font-size:14px;font-weight:700;cursor:pointer;transition:.2s}
.btn-delete:hover{background:#b91c1c;transform:translateY(-1px)}
</style>
</head>
<body>
<div class="card">
  <div class="icon">🗑️</div>
  <h2>Delete Record?</h2>
  <div class="record-name"><?= htmlspecialchars($display) ?></div>
  <span class="table-badge"><?= htmlspecialchars($table) ?></span>
  <p>This action <strong>cannot be undone.</strong></p>
  <p>The record will be permanently removed from the database.</p>
 
  <?php if ($table === 'tenants'): ?>
  <div class="warning">⚠️ Deleting this tenant will also set their <strong>user account to unlinked</strong>. Their login account in the users table will remain but they will see the pending screen.</div>
  <?php elseif ($table === 'users'): ?>
  <div class="warning">⚠️ Deleting this user account will unlink them from the tenants table. Their tenant record will remain.</div>
  <?php elseif ($table === 'properties'): ?>
  <div class="warning">⚠️ Deleting this property may affect tenants and payments linked to it.</div>
  <?php endif; ?>
 
  <form method="POST">
    <input type="hidden" name="confirm_delete" value="1">
    <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
    <input type="hidden" name="id"    value="<?= $id ?>">
    <div class="btn-row">
      <a href="admin_dashboard.php?page=<?= $allowed[$table] ?>" class="btn-cancel">← Cancel</a>
      <button type="submit" class="btn-delete">Yes, Delete</button>
    </div>
  </form>
</div>
</body>
</html>
<?php exit(); }
 
// ── Confirmed — perform deletion ──
$confirmed_table = $_POST['table'] ?? '';
$confirmed_id    = (int)($_POST['id'] ?? 0);
 
// Re-validate after confirmation
if (!array_key_exists($confirmed_table, $allowed) || $confirmed_id <= 0) {
    $_SESSION['error'] = "Invalid delete request.";
    header("Location: admin_dashboard.php"); exit();
}
 
// Special handling before delete
if ($confirmed_table === 'tenants') {
    // Unlink user account — don't delete it, just remove the link
    mysqli_query($conn,
        "UPDATE users SET role='tenant'
         WHERE id = (SELECT user_id FROM tenants WHERE id=$confirmed_id)");
    mysqli_query($conn,
        "UPDATE tenants SET user_id = NULL WHERE id = $confirmed_id");
}
 
if ($confirmed_table === 'users') {
    // Unlink from tenants table before deleting user
    mysqli_query($conn,
        "UPDATE tenants SET user_id = NULL WHERE user_id = $confirmed_id");
}
 
if ($confirmed_table === 'properties') {
    // Unlink tenants from this property
    mysqli_query($conn,
        "UPDATE tenants SET property_id = NULL WHERE property_id = $confirmed_id");
}
 
// Perform the actual delete
$delete = mysqli_query($conn,
    "DELETE FROM `$confirmed_table` WHERE id = $confirmed_id");
 
if ($delete) {
    $_SESSION['success'] = ucwords(str_replace('_', ' ', $confirmed_table))
        . " record deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete record: " . mysqli_error($conn);
}
 
header("Location: admin_dashboard.php?page=" . $allowed[$confirmed_table]);
exit();
?>