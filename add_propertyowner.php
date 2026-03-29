
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$user_id    = $_SESSION['user_id'];
$role_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE id='$user_id'"))['role'];
if (strtolower($role_check) !== 'admin') { header("Location: dashboard.php"); exit(); }
 
$error = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname'] ?? ''));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']    ?? ''));
    $phone    = mysqli_real_escape_string($conn, trim($_POST['phone']    ?? ''));
    $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
 
    if (!$fullname || !$email || !$_POST['password']) {
        $error = "Full name, email and password are required.";
    } else {
        // Check email not already taken
        $dup = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE email='$email' LIMIT 1"));
        if ($dup) {
            $error = "That email address is already registered.";
        } else {
            $q = mysqli_query($conn, "INSERT INTO users (fullname, email, phone, password, role, created_at)
                VALUES ('$fullname', '$email', '$phone', '$password', 'propertyowner', NOW())");
            if ($q) {
                $_SESSION['admin_success'] = "✅ Property owner <strong>" . htmlspecialchars($fullname) . "</strong> added successfully. You can now assign them a property.";
                header("Location: admin_dashboard.php?page=propertyowners"); exit();
            } else {
                $error = "Database error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add Property Owner | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25);--sw:260px}
html,body{height:100%;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden}
body::before{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
 
/* SIDEBAR */
.sidebar{position:fixed;left:0;top:0;width:var(--sw);height:100%;background:rgba(4,9,26,.98);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow-y:auto;z-index:1000}
.sidebar::-webkit-scrollbar{width:3px}.sidebar::-webkit-scrollbar-thumb{background:var(--gb)}
.sidebar h2{text-align:center;padding:24px 20px 20px;font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gold);border-bottom:1px solid var(--border)}
.sidebar a{color:var(--muted);padding:11px 22px;text-decoration:none;display:block;font-size:13px;font-weight:500;border-left:3px solid transparent;transition:all .2s}
.sidebar a:hover{color:var(--white);background:rgba(255,255,255,.04);border-left-color:var(--gb)}
.sidebar .sb-section{font-size:9px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.18);padding:14px 22px 4px;margin-top:6px}
 
/* HEADER */
.header{display:flex;justify-content:space-between;align-items:center;background:var(--gold);border-bottom:1px solid rgba(0,0,0,.1);color:var(--white);padding:16px 36px;position:sticky;top:0;z-index:100;margin-left:var(--sw);box-shadow:0 2px 20px rgba(0,0,0,.3)}
.header h1{font-family:"Cormorant Garamond",serif;font-size:22px;font-weight:700;color:var(--ink);letter-spacing:1px}
.back-btn{padding:9px 20px;background:rgba(4,9,26,.15);border:1px solid rgba(4,9,26,.2);border-radius:6px;color:var(--ink);font-size:12px;font-weight:700;text-decoration:none;letter-spacing:1px;transition:all .2s}
.back-btn:hover{background:rgba(4,9,26,.25)}
 
/* CONTENT */
.main{margin-left:var(--sw);padding:36px 40px;position:relative;z-index:10;min-height:100vh}
.wrap{max-width:560px}
 
/* CARD */
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:32px;margin-bottom:20px}
.card-title{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--white);margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid var(--border)}
 
/* FORM */
.fl{margin-bottom:18px}
.fl label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.fl input{width:100%;padding:11px 14px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .25s}
.fl input:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
.fl input::placeholder{color:rgba(255,255,255,.2)}
 
/* BUTTONS */
.btn-submit{padding:12px 32px;background:var(--gold);border:none;border-radius:7px;color:var(--ink);font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;transition:all .3s}
.btn-submit:hover{background:var(--gold-l);transform:translateY(-2px)}
.btn-cancel{padding:12px 24px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--muted);font-family:"Outfit",sans-serif;font-size:12px;font-weight:600;text-decoration:none;transition:all .2s}
.btn-cancel:hover{border-color:var(--gb);color:var(--white)}
 
/* ALERTS */
.alert{padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:20px;font-weight:500}
.alert.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
 
/* INFO BOX */
.info-box{background:rgba(200,164,60,.06);border:1px solid var(--gb);border-radius:10px;padding:14px 18px;font-size:13px;color:var(--muted);line-height:1.7;margin-bottom:20px}
.info-box strong{color:var(--gold)}
 
@media(max-width:900px){
  :root{--sw:0px}
  .sidebar{display:none}
  .main,.header{margin-left:0}
  .main{padding:20px 16px}
}
</style>
</head>
<body>
 
<!-- SIDEBAR -->
<div class="sidebar">
  <h2>ADMIN PANEL</h2>
  <div class="sb-section">Overview</div>
  <a href="admin_dashboard.php?page=dashboard">🏠 Home</a>
  <div class="sb-section">People</div>
  <a href="admin_dashboard.php?page=users">👤 Manage Users</a>
  <a href="admin_dashboard.php?page=tenants">🏘 Manage Tenants</a>
  <a href="admin_dashboard.php?page=brokers">🤝 Brokers / Agents</a>
  <a href="admin_dashboard.php?page=propertyowners" class="active" style="color:var(--gold);background:rgba(200,164,60,.08);border-left:3px solid var(--gold)">🏢 Property Owners</a>
  <div class="sb-section">Properties</div>
  <a href="admin_dashboard.php?page=properties">🏠 Manage Properties</a>
  <a href="add_property.php">➕ Add Property</a>
  <div class="sb-section">Finance</div>
  <a href="admin_dashboard.php?page=tenant_payments">💳 Tenant Payments</a>
  <a href="admin_dashboard.php?page=revenue_reports">📈 Revenue Reports</a>
  <div class="sb-section">Other</div>
  <a href="admin_dashboard.php?page=notifications">🔔 Notifications</a>
  <a href="logout.php" style="color:#fca5a5;margin-top:10px;border-top:1px solid var(--border)">🚪 Logout</a>
</div>
 
<!-- HEADER -->
<div class="header">
  <h1>Add New Property Owner</h1>
  <a href="admin_dashboard.php?page=propertyowners" class="back-btn">← Back to Property Owners</a>
</div>
 
<div class="main">
<div class="wrap">
 
  <?php if($error): ?>
    <div class="alert error">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
 
  <div class="info-box">
    <strong>ℹ️ Next Step After Adding:</strong> Once you add the owner here, go back to the <strong>Property Owners</strong> page and use the <strong>Assign Property</strong> dropdown to link a property to their account. Their dashboard activates the moment a property is assigned.
  </div>
 
  <div class="card">
    <div class="card-title">🏢 Owner Details</div>
    <form method="POST">
      <div class="fl">
        <label>Full Name *</label>
        <input type="text" name="fullname" placeholder="e.g. David Ssemakula" required value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
      </div>
      <div class="fl">
        <label>Email Address *</label>
        <input type="email" name="email" placeholder="owner@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="fl">
        <label>Phone Number</label>
        <input type="tel" name="phone" placeholder="+256 700 000 000" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
      </div>
      <div class="fl">
        <label>Password *</label>
        <input type="password" name="password" placeholder="Set a login password" required>
      </div>
      <div class="fl" style="margin-bottom:0">
        <label>Account Role</label>
        <input type="text" value="Property Owner" readonly style="opacity:.5;cursor:not-allowed">
      </div>
      <div style="display:flex;gap:12px;align-items:center;margin-top:24px">
        <button type="submit" class="btn-submit">🏢 Add Property Owner</button>
        <a href="admin_dashboard.php?page=propertyowners" class="btn-cancel">Cancel</a>
      </div>
    </form>
  </div>
 
</div>
</div>
</body>
</html>