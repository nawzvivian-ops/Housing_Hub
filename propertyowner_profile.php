
<?php
session_start();
include "db_connect.php";
require_once __DIR__ . "/send_mail.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
 
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
 
$user_id = (int)$_SESSION['user_id'];
$user    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id' LIMIT 1"));
 
if (!$user || strtolower($user['role']) !== 'propertyowner') {
    header("Location: dashboard.php"); exit();
}
 
$success = $error = '';
 
// ── Handle profile update ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname'] ?? ''));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $phone    = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
 
    if (!$fullname || !$email) {
        $error = "Full name and email are required.";
    } else {
        // Check email not taken by another user
        $dup = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE email='$email' AND id!=$user_id LIMIT 1"));
        if ($dup) {
            $error = "That email address is already in use by another account.";
        } else {
            mysqli_query($conn, "UPDATE users SET fullname='$fullname', email='$email', phone='$phone' WHERE id=$user_id");
            $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id' LIMIT 1"));
            $success = "Profile updated successfully.";
        }
    }
}
 
// ── Handle password change ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current  = $_POST['current_password'] ?? '';
    $new      = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
 
    if (!password_verify($current, $user['password'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE id=$user_id");
        $success = "Password changed successfully.";
 
        // Email notification
        send_mail($user['email'],
            "Your HousingHub Password Was Changed",
            "Dear {$user['fullname']},\n\nYour HousingHub owner account password was just changed.\n\nIf you did not do this, contact us immediately at owners@housinghuborg.ug\n\nHousingHub Team"
        );
    }
}
 
// ── Data ──
$fullname = htmlspecialchars($user['fullname']);
$parts    = explode(' ', $fullname, 2);
$fname    = $parts[0];
$initials = strtoupper(substr($parts[0],0,1) . substr($parts[1]??'',0,1));
 
$prop_ids_res = mysqli_query($conn, "SELECT id FROM properties WHERE owner_id=$user_id");
$prop_ids = [];
while ($r = mysqli_fetch_assoc($prop_ids_res)) $prop_ids[] = $r['id'];
$prop_ids_str = !empty($prop_ids) ? implode(',', $prop_ids) : '0';
$total_props   = count($prop_ids);
$total_tenants = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM tenants WHERE property_id IN ($prop_ids_str)"))['c'] ?? 0;
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS c FROM payments WHERE property_id IN ($prop_ids_str) AND status='paid'"))['c'] ?? 0;
$member_since  = $user['created_at'] ? date('d M Y', strtotime($user['created_at'])) : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Profile | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25);--sw:260px}
html,body{height:100%;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden;cursor:none}
body::before{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
 
/* CURSOR */
#cur-dot{width:8px;height:8px;background:var(--gold);border-radius:50%;position:fixed;z-index:99999;pointer-events:none;transform:translate(-50%,-50%);mix-blend-mode:difference}
#cur-ring{width:20px;height:20px;border:1.5px solid rgba(200,164,60,.7);border-radius:50%;position:fixed;z-index:99998;pointer-events:none;transform:translate(-50%,-50%);transition:width .45s cubic-bezier(.23,1,.32,1),height .45s}
#cur-trail{width:30px;height:30px;border:1px solid rgba(200,164,60,.15);border-radius:50%;position:fixed;z-index:99997;pointer-events:none;transform:translate(-50%,-50%);transition:width .7s,height .7s}
body.cursor-hover #cur-dot{background:#fff}
body.cursor-hover #cur-ring{border-color:var(--gold);background:rgba(200,164,60,.06)}
body.cursor-click #cur-dot{width:5px;height:5px}
body.cursor-click #cur-ring{width:28px;height:28px}
@media(max-width:900px){body{cursor:auto!important}#cur-dot,#cur-ring,#cur-trail{display:none}}
 
/* SIDEBAR */
.sb{position:fixed;left:0;top:0;width:var(--sw);height:100%;background:rgba(4,9,26,.98);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow-y:auto;z-index:500}
.sb::-webkit-scrollbar{width:3px}.sb::-webkit-scrollbar-thumb{background:var(--gb)}
.sb-head{padding:22px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.sb-logo{font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold)}
.sb-sub{font-size:9px;color:var(--muted);letter-spacing:1px}
.sb-user{padding:16px 20px;border-bottom:1px solid var(--border)}
.sb-av{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.4),rgba(14,90,200,.4));border:2px solid var(--gb);display:flex;align-items:center;justify-content:center;font-family:"Cormorant Garamond",serif;font-size:17px;font-weight:700;color:var(--white);margin-bottom:8px}
.sb-name{font-size:13px;font-weight:600;color:var(--white);margin-bottom:3px}
.sb-role{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;background:rgba(200,164,60,.12);border:1px solid var(--gb);border-radius:20px;font-size:9px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--gold)}
.sb-nav{padding:12px 0;flex:1}
.nl{font-size:9px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.18);padding:0 20px;margin-bottom:4px;margin-top:14px}
.na{display:flex;align-items:center;gap:10px;padding:10px 20px;font-size:13px;font-weight:500;color:var(--muted);text-decoration:none;transition:all .2s;border-left:3px solid transparent}
.na:hover{color:var(--white);background:rgba(255,255,255,.04);border-left-color:var(--gb)}
.na.active{color:var(--gold);background:rgba(200,164,60,.08);border-left-color:var(--gold)}
.ni{font-size:15px;width:20px;text-align:center}
.sb-foot{padding:14px 20px;border-top:1px solid var(--border)}
.lo{width:100%;padding:10px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#fca5a5;font-family:"Outfit",sans-serif;font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;border-radius:6px;cursor:pointer;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .25s}
.lo:hover{background:rgba(239,68,68,.2)}
.sb-badge{margin-left:auto;padding:1px 7px;border-radius:10px;font-size:10px;font-weight:700;background:rgba(200,164,60,.15);color:var(--gold);flex-shrink:0}
.sb-badge-red{background:rgba(239,68,68,.2);color:#fca5a5}
.sb-stats{display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;padding:12px 16px;border-bottom:1px solid var(--border)}
.sb-stat{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:6px;padding:7px 6px;text-align:center}
.sb-stat-val{font-family:"Cormorant Garamond",serif;font-size:16px;font-weight:700;color:var(--gold);line-height:1}
.sb-stat-lbl{font-size:8px;color:var(--muted);letter-spacing:.3px;margin-top:2px}
 
/* TOPBAR — gold, fixed */
.tb{position:fixed;top:0;left:var(--sw);right:0;z-index:200;display:flex;align-items:center;justify-content:space-between;padding:15px 32px;background:var(--gold);border-bottom:1px solid rgba(0,0,0,.15);box-shadow:0 2px 20px rgba(0,0,0,.3)}
.tb-title{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--ink)}
.tb-sub{font-size:10px;color:rgba(4,9,26,.6);letter-spacing:1px}
 
/* MAIN */
.mc{margin-left:var(--sw);position:relative;z-index:10;min-height:100vh;padding-top:68px}
.content{padding:28px 32px;max-width:860px}
 
/* PROFILE HERO */
.profile-hero{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:14px;padding:28px 32px;margin-bottom:24px;display:flex;align-items:center;gap:24px;position:relative;overflow:hidden}
.profile-hero::before{content:"";position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--gold),transparent)}
.profile-av-lg{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.4),rgba(14,90,200,.4));border:3px solid var(--gb);display:flex;align-items:center;justify-content:center;font-family:"Cormorant Garamond",serif;font-size:32px;font-weight:700;color:var(--white);flex-shrink:0}
.profile-name{font-family:"Cormorant Garamond",serif;font-size:28px;font-weight:700;color:var(--white);margin-bottom:4px}
.profile-email{font-size:13px;color:var(--muted);margin-bottom:8px}
.profile-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:rgba(200,164,60,.12);border:1px solid var(--gb);border-radius:20px;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--gold)}
.profile-stats{display:flex;gap:32px;margin-left:auto;text-align:center;flex-shrink:0}
.ps-val{font-family:"Cormorant Garamond",serif;font-size:28px;font-weight:700;color:var(--gold);line-height:1}
.ps-lbl{font-size:10px;color:var(--muted);margin-top:4px;letter-spacing:.5px}
 
/* CARDS */
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:20px;transition:border-color .3s}
.card:hover{border-color:var(--gb)}
.card-title{font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;color:var(--white);margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--border)}
 
/* FORM */
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.fl{margin-bottom:16px}
.fl label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.fl input,.fl select{width:100%;padding:11px 14px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .25s}
.fl input:focus,.fl select:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
.fl input::placeholder{color:var(--muted)}
.fl input[readonly]{opacity:.5;cursor:not-allowed}
 
/* BUTTONS */
.btn-gold{padding:11px 28px;background:var(--gold);border:none;color:var(--ink);font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;border-radius:7px;cursor:pointer;transition:all .3s}
.btn-gold:hover{background:var(--gold-l);transform:translateY(-2px)}
.btn-outline{padding:11px 28px;background:none;border:1px solid var(--gb);color:var(--gold);font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;border-radius:7px;cursor:pointer;transition:all .3s}
.btn-outline:hover{background:rgba(200,164,60,.1)}
 
/* ALERTS */
.alert{padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:18px;font-weight:500}
.alert.success{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.alert.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
 
/* INFO ROWS */
.info-row{display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.info-row:last-child{border-bottom:none}
.info-icon{font-size:16px;width:28px;text-align:center;flex-shrink:0}
.info-label{font-size:11px;color:var(--muted);letter-spacing:.5px;width:140px;flex-shrink:0}
.info-value{font-size:13px;color:var(--white);flex:1}
 
/* PASSWORD STRENGTH */
.pw-strength{height:3px;border-radius:2px;margin-top:6px;transition:all .3s;background:var(--border)}
.pw-hint{font-size:11px;color:var(--muted);margin-top:4px}
 
@media(max-width:900px){
  :root{--sw:0px}
  .sb{display:none}.mc{margin-left:0}.tb{left:0}
  .grid2{grid-template-columns:1fr}
  .profile-hero{flex-wrap:wrap}
  .profile-stats{margin-left:0;gap:16px}
  .content{padding:16px}
}
</style>
</head>
<body>
<div id="cur-dot"></div><div id="cur-ring"></div><div id="cur-trail"></div>
 
<!-- SIDEBAR -->
<aside class="sb">
  <div class="sb-head">
    <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--gb),rgba(14,90,200,.3));border:1.5px solid var(--gb);display:flex;align-items:center;justify-content:center;font-size:16px">🏠</div>
    <div><div class="sb-logo">Housing Hub</div><div class="sb-sub">Owner Portal</div></div>
  </div>
  <div class="sb-user">
    <div class="sb-av"><?= $initials ?></div>
    <div class="sb-name"><?= $fullname ?></div>
    <span class="sb-role">● Property Owner</span>
  </div>
  <div class="sb-stats">
    <div class="sb-stat"><div class="sb-stat-val"><?= $total_props ?></div><div class="sb-stat-lbl">Props</div></div>
    <div class="sb-stat"><div class="sb-stat-val"><?= $total_tenants ?></div><div class="sb-stat-lbl">Tenants</div></div>
    <div class="sb-stat"><div class="sb-stat-val" style="font-size:11px">UGX<br><?= $total_revenue>0?round($total_revenue/1000).'K':0 ?></div><div class="sb-stat-lbl">Revenue</div></div>
  </div>
  <nav class="sb-nav">
    <div class="nl">Overview</div>
    <a href="propertyowner_dashboard.php" class="na"><span class="ni">🏠</span>Dashboard</a>
 
    <div class="nl">My Portfolio</div>
    <a href="propertyowner_dashboard.php?view=properties" class="na"><span class="ni">🏢</span>My Properties <span class="sb-badge"><?= $total_props ?></span></a>
    <a href="propertyowner_dashboard.php?view=tenants" class="na"><span class="ni">👥</span>My Tenants <span class="sb-badge"><?= $total_tenants ?></span></a>
    <a href="propertyowner_dashboard.php?view=payments" class="na"><span class="ni">💳</span>Payments & Revenue</a>
    <a href="propertyowner_dashboard.php?view=maintenance" class="na"><span class="ni">🔧</span>Maintenance</a>
    <a href="propertyowner_dashboard.php?view=leases" class="na"><span class="ni">📄</span>Lease Agreements</a>
    <a href="propertyowner_dashboard.php?view=visitors" class="na"><span class="ni">👥</span>Visitors & Guests</a>
 
    <div class="nl">Reports & Analytics</div>
    <a href="propertyowner_dashboard.php?view=reports" class="na"><span class="ni">📊</span>Revenue Reports</a>
    <a href="propertyowner_dashboard.php?view=occupancy" class="na"><span class="ni">📉</span>Occupancy Trends</a>
 
    <div class="nl">Tenant Relations</div>
    <a href="propertyowner_dashboard.php?view=complaints" class="na"><span class="ni">💬</span>Complaints</a>
    <a href="propertyowner_dashboard.php?view=inspections" class="na"><span class="ni">🔍</span>Inspections</a>
    <a href="propertyowner_dashboard.php?view=notifications" class="na"><span class="ni">🔔</span>Notifications</a>
 
    <div class="nl">Account</div>
    <a href="propertyowner_profile.php" class="na active"><span class="ni">👤</span>My Profile</a>
    <a href="properties.php" class="na"><span class="ni">🌐</span>Browse Properties</a>
    <a href="contact.php" class="na"><span class="ni">📞</span>Contact Support</a>
  </nav>
  <div class="sb-foot">
    <a href="propertyowner_dashboard.php?view=reports" class="lo" style="background:rgba(200,164,60,.1);border-color:var(--gb);color:var(--gold);margin-bottom:8px">📥 &nbsp;Download Report</a>
    <a href="logout.php" class="lo">⬡ &nbsp;Sign Out</a>
  </div>
</aside>
 
<!-- TOPBAR -->
<div class="tb">
  <div>
    <div class="tb-title">My Profile</div>
    <div class="tb-sub">HousingHub · Owner Portal · <?= date('l, d F Y') ?></div>
  </div>
  <div style="display:flex;align-items:center;gap:10px">
    <a href="propertyowner_dashboard.php" style="padding:8px 16px;background:rgba(4,9,26,.15);border:1px solid rgba(4,9,26,.2);border-radius:6px;color:var(--ink);font-size:11px;font-weight:700;text-decoration:none;letter-spacing:1px">← Dashboard</a>
    <a href="logout.php" style="padding:8px 16px;background:rgba(4,9,26,.15);border:1px solid rgba(4,9,26,.25);border-radius:6px;color:var(--ink);font-size:11px;font-weight:700;text-decoration:none;letter-spacing:1px">Sign Out</a>
  </div>
</div>
 
<div class="mc"><div class="content">
 
<?php if($success): ?><div class="alert success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if($error):   ?><div class="alert error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
 
<!-- PROFILE HERO -->
<div class="profile-hero">
  <div class="profile-av-lg"><?= $initials ?></div>
  <div>
    <div class="profile-name"><?= $fullname ?></div>
    <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
    <div class="profile-badge">🏢 Property Owner · Member since <?= $member_since ?></div>
  </div>
  <div class="profile-stats">
    <div><div class="ps-val"><?= $total_props ?></div><div class="ps-lbl">Properties</div></div>
    <div><div class="ps-val"><?= $total_tenants ?></div><div class="ps-lbl">Tenants</div></div>
    <div><div class="ps-val" style="font-size:18px">UGX <?= number_format($total_revenue) ?></div><div class="ps-lbl">Revenue</div></div>
  </div>
</div>
 
<!-- ACCOUNT INFO READ-ONLY -->
<div class="card">
  <div class="card-title">📋 Account Information</div>
  <div class="info-row"><div class="info-icon">👤</div><div class="info-label">Full Name</div><div class="info-value"><?= $fullname ?></div></div>
  <div class="info-row"><div class="info-icon">✉️</div><div class="info-label">Email Address</div><div class="info-value"><?= htmlspecialchars($user['email']) ?></div></div>
  <div class="info-row"><div class="info-icon">📱</div><div class="info-label">Phone Number</div><div class="info-value"><?= htmlspecialchars($user['phone'] ?? 'Not set') ?></div></div>
  <div class="info-row"><div class="info-icon">🏷️</div><div class="info-label">Account Role</div><div class="info-value"><span style="color:var(--gold)">Property Owner</span></div></div>
  <div class="info-row"><div class="info-icon">📅</div><div class="info-label">Member Since</div><div class="info-value"><?= $member_since ?></div></div>
  <div class="info-row"><div class="info-icon">🏢</div><div class="info-label">Properties</div><div class="info-value"><?= $total_props ?> propert<?= $total_props==1?'y':'ies' ?> · <?= $total_tenants ?> tenant<?= $total_tenants==1?'':'s' ?></div></div>
  <div class="info-row"><div class="info-icon">💰</div><div class="info-label">Total Revenue</div><div class="info-value" style="color:#86efac">UGX <?= number_format($total_revenue) ?> collected</div></div>
</div>
 
<!-- EDIT PROFILE FORM -->
<div class="card">
  <div class="card-title">✏️ Edit Profile</div>
  <form method="POST">
    <div class="grid2">
      <div class="fl">
        <label>Full Name *</label>
        <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required placeholder="Your full name">
      </div>
      <div class="fl">
        <label>Phone Number</label>
        <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+256 700 000 000">
      </div>
    </div>
    <div class="fl">
      <label>Email Address *</label>
      <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required placeholder="your@email.com">
    </div>
    <div class="fl">
      <label>Account Role</label>
      <input type="text" value="Property Owner" readonly>
    </div>
    <div style="display:flex;gap:12px;margin-top:6px">
      <button type="submit" name="update_profile" class="btn-gold">💾 Save Changes</button>
      <a href="propertyowner_dashboard.php" class="btn-outline">Cancel</a>
    </div>
  </form>
</div>
 
<!-- CHANGE PASSWORD -->
<div class="card">
  <div class="card-title">🔒 Change Password</div>
  <form method="POST">
    <div class="fl">
      <label>Current Password *</label>
      <input type="password" name="current_password" required placeholder="Enter your current password">
    </div>
    <div class="grid2">
      <div class="fl">
        <label>New Password *</label>
        <input type="password" name="new_password" id="newpw" required placeholder="At least 6 characters" oninput="checkStrength(this.value)">
        <div class="pw-strength" id="pw-bar"></div>
        <div class="pw-hint" id="pw-hint">Enter a new password</div>
      </div>
      <div class="fl">
        <label>Confirm New Password *</label>
        <input type="password" name="confirm_password" required placeholder="Repeat new password">
      </div>
    </div>
    <button type="submit" name="change_password" class="btn-gold">🔒 Update Password</button>
  </form>
</div>
 
<!-- CONTACT SUPPORT -->
<div style="background:rgba(200,164,60,.05);border:1px solid var(--gb);border-radius:12px;padding:20px 24px;margin-bottom:24px">
  <div style="font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:700;color:var(--white);margin-bottom:8px">📞 Need Help?</div>
  <p style="font-size:13px;color:var(--muted);line-height:1.7;margin-bottom:16px">To add a new property, update property details, or resolve account issues — contact your dedicated HousingHub account manager.</p>
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
    <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:14px;text-align:center">
      <div style="font-size:20px;margin-bottom:6px">✉️</div>
      <div style="font-size:11px;color:var(--muted);margin-bottom:3px">Email</div>
      <div style="font-size:12px;color:var(--gold);font-weight:600">owners@housinghuborg.ug</div>
    </div>
    <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:14px;text-align:center">
      <div style="font-size:20px;margin-bottom:6px">📱</div>
      <div style="font-size:11px;color:var(--muted);margin-bottom:3px">Phone / WhatsApp</div>
      <div style="font-size:12px;color:var(--gold);font-weight:600">+256 700 000 000</div>
    </div>
    <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:14px;text-align:center">
      <div style="font-size:20px;margin-bottom:6px">🕐</div>
      <div style="font-size:11px;color:var(--muted);margin-bottom:3px">Working Hours</div>
      <div style="font-size:12px;color:var(--gold);font-weight:600">Mon–Sat · 8am–6pm</div>
    </div>
  </div>
</div>
 
<!-- SIGN OUT -->
<div style="text-align:center;padding-bottom:20px">
  <a href="logout.php" style="display:inline-flex;align-items:center;gap:8px;padding:12px 32px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;border-radius:7px;text-decoration:none;transition:all .25s">🚪 Sign Out of HousingHub</a>
</div>
 
</div></div>
 
<script>
function checkStrength(pw) {
  const bar  = document.getElementById('pw-bar');
  const hint = document.getElementById('pw-hint');
  if (!pw) { bar.style.background='var(--border)'; bar.style.width='0'; hint.textContent='Enter a new password'; return; }
  let score = 0;
  if (pw.length >= 6)  score++;
  if (pw.length >= 10) score++;
  if (/[A-Z]/.test(pw)) score++;
  if (/[0-9]/.test(pw)) score++;
  if (/[^A-Za-z0-9]/.test(pw)) score++;
  const levels = [
    {w:'20%', c:'#ef4444', t:'Too weak'},
    {w:'40%', c:'#f97316', t:'Weak'},
    {w:'60%', c:'#eab308', t:'Fair'},
    {w:'80%', c:'#84cc16', t:'Strong'},
    {w:'100%',c:'#22c55e', t:'Very strong'},
  ];
  const l = levels[Math.min(score-1, 4)] || levels[0];
  bar.style.width = l.w;
  bar.style.background = l.c;
  hint.textContent = l.t;
  hint.style.color = l.c;
}
 
// Cursor
const dot=document.getElementById('cur-dot'),ring=document.getElementById('cur-ring'),trail=document.getElementById('cur-trail');
let mx=-200,my=-200,rx=-200,ry=-200,tx=-200,ty=-200;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;dot.style.left=mx+'px';dot.style.top=my+'px';});
(function anim(){rx+=(mx-rx)*.15;ry+=(my-ry)*.15;tx+=(mx-tx)*.06;ty+=(my-ty)*.06;ring.style.left=rx+'px';ring.style.top=ry+'px';trail.style.left=tx+'px';trail.style.top=ty+'px';requestAnimationFrame(anim);})();
document.querySelectorAll('a,button,.card,.na,.lo,.info-row').forEach(el=>{
  el.addEventListener('mouseenter',()=>document.body.classList.add('cursor-hover'));
  el.addEventListener('mouseleave',()=>document.body.classList.remove('cursor-hover'));
});
document.addEventListener('mousedown',()=>document.body.classList.add('cursor-click'));
document.addEventListener('mouseup',()=>document.body.classList.remove('cursor-click'));
</script>
</body>
</html>