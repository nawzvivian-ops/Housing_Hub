
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = (int)$_SESSION['user_id'];
$user    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id' LIMIT 1"));
if (!$user || strtolower($user['role']) !== 'staff') {
    echo "<h2 style='color:red;text-align:center;font-family:sans-serif;padding:40px'>Access Denied!</h2>"; exit();
}
 
$success = '';
$error   = '';
 
// Update profile
if (isset($_POST['update_profile'])) {
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $phone    = mysqli_real_escape_string($conn, trim($_POST['phone']));
    mysqli_query($conn, "UPDATE users SET fullname='$fullname', phone='$phone' WHERE id=$user_id");
    $_SESSION['fullname'] = $fullname;
    $success = "Profile updated successfully.";
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id' LIMIT 1"));
}
 
// Change password
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
 
    if (!password_verify($current, $user['password'])) {
        $error = "Current password is incorrect.";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    } elseif (strlen($new) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE id=$user_id");
        $success = "Password changed successfully.";
    }
}
 
$fullname = htmlspecialchars($user['fullname']);
$parts    = explode(' ', $fullname, 2);
$initials = strtoupper(substr($parts[0],0,1) . substr($parts[1]??'',0,1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Profile | HousingHub Staff</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25)}
html,body{min-height:100vh;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white)}
body{background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%),var(--ink)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
.wrap{position:relative;z-index:10;max-width:680px;margin:0 auto;padding:32px 24px}
.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px}
.back{display:inline-flex;align-items:center;gap:8px;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);text-decoration:none;transition:color .2s}
.back:hover{color:var(--gold)}
.page-title{font-family:"Cormorant Garamond",serif;font-size:32px;font-weight:700;color:var(--white)}
.page-title em{color:var(--gold);font-style:italic}
.avatar{width:68px;height:68px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.4),rgba(14,90,200,.4));border:2px solid var(--gb);display:flex;align-items:center;justify-content:center;font-family:"Cormorant Garamond",serif;font-size:26px;font-weight:700;color:var(--white);margin:0 auto 14px}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:20px}
.card-title{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--white);margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--border)}
.fl{margin-bottom:14px}
.fl label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.fl input{width:100%;padding:11px 13px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:6px;color:var(--white);font-family:"Outfit",sans-serif;font-size:14px;outline:none;transition:border-color .25s}
.fl input:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
.fl input::placeholder{color:var(--muted)}
.fl input[disabled]{opacity:.5;cursor:not-allowed}
.btn{width:100%;padding:12px;background:var(--gold);border:none;border-radius:6px;font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;color:var(--ink);letter-spacing:1px;text-transform:uppercase;cursor:pointer;transition:all .3s;margin-top:4px}
.btn:hover{background:var(--gold-l);transform:translateY(-1px)}
.alert{padding:12px 16px;border-radius:7px;font-size:13px;margin-bottom:16px}
.alert.success{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.alert.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
</style>
</head>
<body>
<div class="wrap">
 
  <div class="topbar">
    <a href="staff_dashboard.php" class="back">← Dashboard</a>
    <div style="text-align:right">
      <div class="page-title">My <em>Profile</em></div>
    </div>
  </div>
 
  <?php if($success): ?><div class="alert success">✓ <?= htmlspecialchars($success) ?></div><?php endif; ?>
  <?php if($error):   ?><div class="alert error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
 
  <!-- AVATAR -->
  <div style="text-align:center;margin-bottom:24px">
    <div class="avatar"><?= $initials ?></div>
    <div style="font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;color:var(--white)"><?= $fullname ?></div>
    <div style="font-size:12px;color:var(--muted);margin-top:4px"><?= htmlspecialchars($user['email']??'') ?></div>
    <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:rgba(14,90,200,.15);border:1px solid rgba(14,90,200,.3);border-radius:20px;font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:#5b9cff;margin-top:8px">● Staff Member</span>
  </div>
 
  <!-- EDIT PROFILE -->
  <div class="card">
    <div class="card-title">✏️ Edit Profile</div>
    <form method="POST">
      <div class="fl"><label>Full Name</label><input type="text" name="fullname" value="<?= $fullname ?>" required></div>
      <div class="fl"><label>Email Address</label><input type="email" value="<?= htmlspecialchars($user['email']??'') ?>" disabled></div>
      <div class="fl"><label>Phone Number</label><input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']??'') ?>" placeholder="+256 700 000000"></div>
      <button type="submit" name="update_profile" class="btn">Save Changes →</button>
    </form>
  </div>
 
  <!-- CHANGE PASSWORD -->
  <div class="card">
    <div class="card-title">🔒 Change Password</div>
    <form method="POST">
      <div class="fl"><label>Current Password</label><input type="password" name="current_password" placeholder="Your current password" required></div>
      <div class="fl"><label>New Password</label><input type="password" name="new_password" placeholder="New password (min 6 chars)" required></div>
      <div class="fl"><label>Confirm New Password</label><input type="password" name="confirm_password" placeholder="Repeat new password" required></div>
      <button type="submit" name="change_password" class="btn">Update Password →</button>
    </form>
  </div>
 
</div>
</body>
</html>