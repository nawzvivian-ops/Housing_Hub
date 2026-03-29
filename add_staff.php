
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$me = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE id='{$_SESSION['user_id']}' LIMIT 1"));
if (!$me || strtolower($me['role']) !== 'admin') { header("Location: dashboard.php"); exit(); }
 
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone    = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $salary   = (float)($_POST['salary'] ?? 0);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) { $error = "Email already registered."; }
    else {
        mysqli_query($conn, "INSERT INTO users (fullname,email,phone,password,salary,role,created_at) VALUES ('$fullname','$email','$phone','$password',$salary,'staff',NOW())");
        $_SESSION['admin_success'] = "Staff member <strong>" . htmlspecialchars($fullname) . "</strong> added.";
        header("Location: admin_dashboard.php?page=staff_roles"); exit();
    }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add Staff | HousingHub Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25);--sw:260px}
body{font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);min-height:100vh;padding:36px 40px;margin-left:var(--sw)}
body::before{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.12),transparent 55%)}
.wrap{position:relative;z-index:1;max-width:580px}
h2{font-family:"Cormorant Garamond",serif;font-size:28px;font-weight:700;color:var(--gold);margin-bottom:24px;padding-bottom:12px;border-bottom:2px solid var(--gb)}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:32px;position:relative;overflow:hidden}
.card::before{content:"";position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,var(--gold),transparent)}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.fl{margin-bottom:16px}
label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
input{width:100%;padding:11px 14px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .25s}
input:focus{border-color:var(--gb)}
input::placeholder{color:rgba(255,255,255,.2)}
.alert{padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:20px}
.alert.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.btn-submit{padding:13px 32px;background:var(--gold);border:none;border-radius:7px;color:var(--ink);font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;transition:all .3s}
.btn-submit:hover{background:var(--gold-l);transform:translateY(-2px)}
.btn-back{padding:13px 24px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--muted);font-family:"Outfit",sans-serif;font-size:12px;font-weight:600;text-decoration:none;transition:all .3s}
.btn-back:hover{border-color:var(--gb);color:var(--white)}
</style>
</head>
<body><div class="wrap">
<h2>👥 Add New Staff Member</h2>
<?php if($error): ?><div class="alert error">⚠️ <?= $error ?></div><?php endif; ?>
<div class="card">
  <form method="POST">
    <div class="fl"><label>Full Name *</label><input type="text" name="fullname" placeholder="e.g. Moses Kato" required></div>
    <div class="grid2">
      <div class="fl"><label>Email *</label><input type="email" name="email" placeholder="staff@email.com" required></div>
      <div class="fl"><label>Phone</label><input type="text" name="phone" placeholder="+256 700 000 000"></div>
    </div>
    <div class="fl"><label>Monthly Salary (UGX)</label><input type="number" name="salary" min="0" placeholder="e.g. 800000"></div>
    <div class="fl"><label>Password *</label><input type="password" name="password" placeholder="Set login password" required></div>
    <div style="display:flex;gap:12px;margin-top:8px">
      <button type="submit" class="btn-submit">+ Add Staff</button>
      <a href="admin_dashboard.php?page=staff_roles" class="btn-back">← Back</a>
    </div>
  </form>
</div>
</div></body></html>