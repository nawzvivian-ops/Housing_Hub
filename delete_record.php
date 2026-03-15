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
    'properties'          => 'properties',
    'tenants'             => 'tenants',
    'payments'            => 'tenant_payments',
    'maintenance_requests'=> 'maintenance',
    'maintenance'         => 'maintenance',
    'job_applications'    => 'jobs',
    'guests'              => 'guests',
    'complaints'          => 'complaints',
    'users'               => 'users',
    'tasks'               => 'staff_tasks',
    'inspections'         => 'inspections',
    'notifications'       => 'notifications',
    'tenant_documents'    => 'tenant_documents',
    'visitors'            => 'guests',
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
 
// ── Fetch the record ──
$record = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM `$table` WHERE id = $id LIMIT 1"));
 
if (!$record) {
    $_SESSION['error'] = "Record not found.";
    header("Location: admin_dashboard.php?page=" . $allowed[$table]); exit();
}
 
// ── Display name ──
$display = $record['fullname']
        ?? $record['full_name']
        ?? $record['property_name']
        ?? $record['title']
        ?? $record['visitor_name']
        ?? $record['subject']
        ?? $record['issue']
        ?? $record['document_name']
        ?? "#$id";
 
// ── Show confirmation page ──
if (!isset($_POST['confirm_delete'])): ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Confirm Delete | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25);--red:#ef4444}
body{font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);min-height:100vh;
  display:flex;align-items:center;justify-content:center;padding:24px;
  background:radial-gradient(ellipse 80% 60% at 70% 10%,rgba(14,90,200,.18),transparent 55%),
             radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12),transparent 50%),var(--ink)}
/* grid overlay */
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;
  background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),
                   linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);
  background-size:72px 72px}
.card{position:relative;z-index:10;width:100%;max-width:460px;
  background:rgba(8,14,36,.96);border:1px solid var(--border);
  border-radius:16px;padding:44px 40px;text-align:center;
  box-shadow:0 40px 80px rgba(0,0,0,.6);
  animation:up .5s cubic-bezier(.23,1,.32,1) both}
@keyframes up{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:none}}
.brand{font-family:"Cormorant Garamond",serif;font-size:12px;font-weight:700;
  letter-spacing:3px;text-transform:uppercase;color:rgba(200,164,60,.4);margin-bottom:24px}
.icon{font-size:52px;margin-bottom:18px;display:block;
  animation:shake .5s ease .3s both}
@keyframes shake{0%,100%{transform:rotate(0)}20%{transform:rotate(-8deg)}40%{transform:rotate(8deg)}60%{transform:rotate(-4deg)}80%{transform:rotate(4deg)}}
h2{font-family:"Cormorant Garamond",serif;font-size:28px;font-weight:700;
  color:var(--white);margin-bottom:8px}
h2 em{color:#ff8f8a;font-style:italic}
.record-name{font-size:16px;font-weight:700;color:var(--gold);margin:12px 0 4px;
  padding:8px 16px;background:rgba(200,164,60,.08);border:1px solid var(--gb);
  border-radius:8px;display:inline-block;max-width:100%;word-break:break-word}
.table-badge{display:inline-block;padding:3px 12px;border-radius:20px;
  background:rgba(239,68,68,.1);color:#fca5a5;border:1px solid rgba(239,68,68,.25);
  font-size:10px;font-weight:700;margin:10px 0 16px;letter-spacing:1px;text-transform:uppercase}
p{font-size:13px;color:var(--muted);line-height:1.7;margin-bottom:6px}
.warning{background:rgba(200,164,60,.08);border:1px solid var(--gb);
  border-radius:8px;padding:12px 16px;font-size:12px;color:rgba(255,196,0,.8);
  margin:16px 0;text-align:left;line-height:1.6}
.warning strong{color:var(--gold)}
.btn-row{display:flex;gap:12px;margin-top:24px}
.btn-cancel{flex:1;padding:12px;background:rgba(255,255,255,.05);
  border:1px solid var(--border);border-radius:8px;color:var(--muted);
  font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;
  display:flex;align-items:center;justify-content:center;
  transition:all .25s;font-family:"Outfit",sans-serif}
.btn-cancel:hover{background:rgba(255,255,255,.1);color:var(--white)}
.btn-delete{flex:1;padding:12px;
  background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.35);
  border-radius:8px;color:#fca5a5;font-size:13px;font-weight:700;
  cursor:pointer;transition:all .25s;font-family:"Outfit",sans-serif;
  letter-spacing:.5px}
.btn-delete:hover{background:rgba(239,68,68,.3);color:#fff;transform:translateY(-2px);
  box-shadow:0 8px 20px rgba(239,68,68,.25)}
</style>
</head>
<body>
<div class="card">
  <div class="brand">HOUSING HUB · Admin</div>
  <span class="icon">🗑️</span>
  <h2>Delete <em>Record?</em></h2>
 
  <div class="record-name"><?= htmlspecialchars($display) ?></div>
  <div><span class="table-badge"><?= htmlspecialchars(str_replace('_',' ',$table)) ?></span></div>
 
  <p>This action <strong style="color:var(--white)">cannot be undone.</strong></p>
  <p>The record will be permanently removed from the database.</p>
 
  <?php if ($table === 'tenants'): ?>
  <div class="warning">⚠️ Deleting this tenant will <strong>unlink their user account</strong>. Their login will remain but they will see the pending screen on next login.</div>
  <?php elseif ($table === 'users'): ?>
  <div class="warning">⚠️ Deleting this user will <strong>unlink them from the tenants table</strong>. Their tenant record will remain intact.</div>
  <?php elseif ($table === 'properties'): ?>
  <div class="warning">⚠️ Deleting this property may <strong>affect tenants and payments</strong> linked to it.</div>
  <?php elseif ($table === 'payments'): ?>
  <div class="warning">⚠️ Deleting a payment record is <strong>permanent</strong>. Make sure this is intentional.</div>
  <?php endif; ?>
 
  <form method="POST">
    <input type="hidden" name="confirm_delete" value="1">
    <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
    <input type="hidden" name="id"    value="<?= $id ?>">
    <div class="btn-row">
      <a href="admin_dashboard.php?page=<?= $allowed[$table] ?>" class="btn-cancel">← Cancel</a>
      <button type="submit" class="btn-delete">🗑 Yes, Delete</button>
    </div>
  </form>
</div>
</body>
</html>
<?php exit();
endif;
 
// ── Confirmed — perform deletion ──
$confirmed_table = $_POST['table'] ?? '';
$confirmed_id    = (int)($_POST['id'] ?? 0);
 
if (!array_key_exists($confirmed_table, $allowed) || $confirmed_id <= 0) {
    $_SESSION['error'] = "Invalid delete request.";
    header("Location: admin_dashboard.php"); exit();
}
 
// Special pre-delete handling
if ($confirmed_table === 'tenants') {
    mysqli_query($conn,
        "UPDATE users SET role='tenant'
         WHERE id = (SELECT user_id FROM tenants WHERE id=$confirmed_id)");
    mysqli_query($conn,
        "UPDATE tenants SET user_id = NULL WHERE id = $confirmed_id");
}
 
if ($confirmed_table === 'users') {
    mysqli_query($conn,
        "UPDATE tenants SET user_id = NULL WHERE user_id = $confirmed_id");
}
 
if ($confirmed_table === 'properties') {
    mysqli_query($conn,
        "UPDATE tenants SET property_id = NULL WHERE property_id = $confirmed_id");
}
 
// Perform delete
$delete = mysqli_query($conn,
    "DELETE FROM `$confirmed_table` WHERE id = $confirmed_id");
 
if ($delete) {
    $_SESSION['success'] = ucwords(str_replace('_', ' ', $confirmed_table))
        . " record deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete: " . mysqli_error($conn);
}
 
header("Location: admin_dashboard.php?page=" . $allowed[$confirmed_table]);
exit();
?>