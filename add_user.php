<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
$user_id = $_SESSION['user_id'] ?? 0;
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'"));
if (!$user || strtolower(trim($user['role'])) !== 'admin') {
    header("Location: dashboard.php"); exit();
}
 
$error   = '';
$success = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $role     = mysqli_real_escape_string($conn, $_POST['role']);
    $phone    = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
 
    // Check duplicate email
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "A user with this email already exists.";
    } else {
        mysqli_query($conn, "INSERT INTO users (fullname, email, role, phone, password) VALUES ('$fullname', '$email', '$role', '$phone', '$password')");
        header("Location: admin_dashboard.php?page=users"); exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add New User | HousingHub Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25)}
html,body{min-height:100vh;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);display:flex;align-items:center;justify-content:center;padding:32px 20px}
body{background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%),var(--ink)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
.wrap{position:relative;z-index:10;width:100%;max-width:480px}
.back{display:inline-flex;align-items:center;gap:8px;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);text-decoration:none;margin-bottom:24px;transition:color .2s}
.back:hover{color:var(--gold)}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:14px;padding:36px;box-shadow:0 20px 60px rgba(0,0,0,.4)}
.card-eyebrow{font-size:10px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gold);text-align:center;margin-bottom:8px}
.card-title{font-family:"Cormorant Garamond",serif;font-size:30px;font-weight:700;color:var(--white);text-align:center;margin-bottom:6px}
.card-title em{color:var(--gold);font-style:italic}
.card-sub{font-size:12px;color:var(--muted);text-align:center;margin-bottom:28px;letter-spacing:.5px}
.divider{height:1px;background:var(--border);margin-bottom:24px}
.fl{margin-bottom:16px}
.fl label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.fl input,.fl select{width:100%;padding:11px 13px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .25s}
.fl input:focus,.fl select:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
.fl input::placeholder{color:var(--muted)}
.fl select option{background:var(--ink);color:var(--white)}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.role-hint{font-size:11px;color:var(--muted);margin-top:-10px;margin-bottom:14px;padding-left:2px;line-height:1.5}
.alert{padding:12px 16px;border-radius:7px;font-size:13px;margin-bottom:18px}
.alert.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.btn{width:100%;padding:13px;background:var(--gold);border:none;color:var(--ink);font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;border-radius:7px;cursor:pointer;transition:all .3s;margin-top:4px}
.btn:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 8px 24px rgba(200,164,60,.3)}
@media(max-width:500px){.grid2{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="wrap">
  <a href="admin_dashboard.php?page=users" class="back">← Back to Users</a>
  <div class="card">
    <div class="card-eyebrow">HousingHub Admin</div>
    <div class="card-title">Add <em>New User</em></div>
    <div class="card-sub">Create a new user account and assign their role</div>
    <div class="divider"></div>
 
    <?php if($error): ?>
    <div class="alert error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
 
    <form method="POST">
      <div class="fl">
        <label>Full Name</label>
        <input type="text" name="fullname" placeholder="e.g. Nakato Sandra" required>
      </div>
      <div class="grid2">
        <div class="fl">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="user@email.com" required>
        </div>
        <div class="fl">
          <label>Phone Number</label>
          <input type="tel" name="phone" placeholder="+256 700 000000">
        </div>
      </div>
      <div class="fl">
        <label>User Role</label>
        <select name="role" required onchange="showHint(this.value)">
          <option value="">— Select Role —</option>
          <option value="tenant">Tenant</option>
          <option value="staff">Staff</option>
          <option value="propertyowner">Property Owner</option>
          <option value="broker">Broker</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div class="role-hint" id="role-hint"></div>
      <div class="fl">
        <label>Temporary Password</label>
        <input type="password" name="password" placeholder="Set a temporary password" required>
      </div>
      <button type="submit" class="btn">Create User Account →</button>
    </form>
  </div>
</div>
 
<script>
const hints = {
  tenant:        '🏘 Tenant — will access the tenant dashboard. Link them to a property in Manage Tenants.',
  staff:         '👷 Staff — will access the staff portal. Assign tasks and inspections from the admin panel.',
  propertyowner: '🏢 Property Owner — will see a pending screen until you assign a property to their account.',
  broker:        '🤝 Broker — will access the broker dashboard. Assign properties in Manage Properties.',
  admin:         '⚠️ Admin — full access to the entire admin panel. Only create if necessary.'
};
function showHint(role) {
  const el = document.getElementById('role-hint');
  el.textContent = hints[role] || '';
}
</script>
</body>
</html>