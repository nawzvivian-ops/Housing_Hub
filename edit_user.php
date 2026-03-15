
<?php
session_start();
include "db_connect.php";
 
// ── Check admin ──
$session_id = (int)($_SESSION['user_id'] ?? 0);
$admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE id='$session_id' LIMIT 1"));
if (!$admin || strtolower(trim($admin['role'])) !== 'admin') {
    header("Location: dashboard.php"); exit();
}
 
// ── Get the user ID — GET on load, POST on submit ──
$edit_id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($edit_id <= 0) { echo "No user ID provided."; exit(); }
 
// ── Fetch user ──
$edit_user = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM users WHERE id='$edit_id' LIMIT 1"));
if (!$edit_user) { echo "User not found (ID: $edit_id)."; exit(); }
 
// ── Handle form submission ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullname'])) {
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $role     = mysqli_real_escape_string($conn, trim($_POST['role']));
    $phone    = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
 
    mysqli_query($conn,
        "UPDATE users SET fullname='$fullname', email='$email', role='$role', phone='$phone'
         WHERE id='$edit_id'");
 
    if ($role === 'tenant') {
        mysqli_query($conn,
            "UPDATE tenants SET fullname='$fullname', email='$email', phone='$phone'
             WHERE user_id='$edit_id'");
    }
 
    header("Location: admin_dashboard.php?page=users&msg=updated");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User | HousingHub Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25)}
body{
  font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);
  min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
  background:radial-gradient(ellipse 80% 60% at 70% 10%,rgba(14,90,200,.18),transparent 55%),
             radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12),transparent 50%),var(--ink)
}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;
  background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),
                   linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);
  background-size:72px 72px}
.card{position:relative;z-index:10;width:100%;max-width:480px;
  background:rgba(8,14,36,.96);border:1px solid var(--border);border-radius:16px;
  padding:44px 40px;box-shadow:0 40px 80px rgba(0,0,0,.6);
  animation:up .5s cubic-bezier(.23,1,.32,1) both}
@keyframes up{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}
.brand{font-family:"Cormorant Garamond",serif;font-size:11px;font-weight:700;
  letter-spacing:3px;text-transform:uppercase;color:rgba(200,164,60,.4);
  text-align:center;margin-bottom:20px}
h2{font-family:"Cormorant Garamond",serif;font-size:28px;font-weight:700;
  color:var(--white);text-align:center;margin-bottom:6px}
h2 em{color:var(--gold);font-style:italic}
.sub{text-align:center;font-size:12px;color:var(--muted);margin-bottom:28px}
.user-info{background:rgba(200,164,60,.07);border:1px solid var(--gb);border-radius:8px;
  padding:12px 16px;margin-bottom:24px;font-size:12px;color:rgba(255,255,255,.65);line-height:1.7}
.user-info strong{color:var(--gold)}
label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;
  text-transform:uppercase;color:var(--gold);margin-bottom:7px}
input,select{width:100%;padding:12px 14px;margin-bottom:18px;
  background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:8px;
  font-size:14px;color:var(--white);font-family:"Outfit",sans-serif;
  outline:none;transition:border-color .25s,background .25s}
input:focus,select:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
input::placeholder{color:var(--muted)}
select option{background:#04091a;color:var(--white)}
.btn{width:100%;padding:13px;background:var(--gold);border:none;border-radius:8px;
  font-size:13px;font-weight:700;color:var(--ink);font-family:"Outfit",sans-serif;
  letter-spacing:1px;text-transform:uppercase;cursor:pointer;transition:all .3s;margin-top:4px}
.btn:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 8px 22px rgba(200,164,60,.3)}
.back{display:block;text-align:center;margin-top:18px;text-decoration:none;
  color:var(--muted);font-size:12px;font-weight:600;letter-spacing:.5px;transition:color .2s}
.back:hover{color:var(--white)}
</style>
</head>
<body>
<div class="card">
  <div class="brand">Housing Hub · Admin Panel</div>
  <h2>Edit <em>User</em></h2>
  <div class="sub">Update account details below</div>
 
  <div class="user-info">
    Editing: <strong><?= htmlspecialchars($edit_user['fullname']) ?></strong>
    &nbsp;·&nbsp; ID: <strong>#<?= $edit_id ?></strong>
    &nbsp;·&nbsp; Role: <strong><?= htmlspecialchars(ucfirst($edit_user['role'])) ?></strong>
  </div>
 
  <form method="POST">
    <input type="hidden" name="id" value="<?= $edit_id ?>">
 
    <label>Full Name</label>
    <input type="text" name="fullname"
           value="<?= htmlspecialchars($edit_user['fullname']) ?>" required>
 
    <label>Email Address</label>
    <input type="email" name="email"
           value="<?= htmlspecialchars($edit_user['email'] ?? '') ?>" required>
 
    <label>Phone Number</label>
    <input type="tel" name="phone"
           value="<?= htmlspecialchars($edit_user['phone'] ?? '') ?>"
           placeholder="+256 700 000000">
 
    <label>Role</label>
    <select name="role" required>
      <option value="admin"         <?= $edit_user['role']==='admin'         ?'selected':'' ?>>Admin</option>
      <option value="staff"         <?= $edit_user['role']==='staff'         ?'selected':'' ?>>Staff</option>
      <option value="tenant"        <?= $edit_user['role']==='tenant'        ?'selected':'' ?>>Tenant</option>
      <option value="broker"        <?= $edit_user['role']==='broker'        ?'selected':'' ?>>Broker</option>
      <option value="propertyowner" <?= $edit_user['role']==='propertyowner' ?'selected':'' ?>>Property Owner</option>
      <option value="guest"         <?= $edit_user['role']==='guest'         ?'selected':'' ?>>Guest</option>
    </select>
 
    <button type="submit" class="btn">💾 Save Changes</button>
  </form>
 
  <a href="admin_dashboard.php?page=users" class="back">⬅ Back to Users</a>
</div>
</body>
</html>