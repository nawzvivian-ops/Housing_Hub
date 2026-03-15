
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
// ── No caching ──
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
 
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
 
$user_id = (int)$_SESSION['user_id'];
$user    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id' LIMIT 1"));
 
if (!$user || $user['role'] !== 'staff') {
    echo "<h2 style='color:red;text-align:center;font-family:sans-serif;padding:40px'>Access Denied!</h2>";
    exit();
}
 
// ── STAFF GATE: Must be approved via job application OR manually added by admin ──
// Check if this staff member has an approved application OR was directly created by admin
$is_approved = false;
 
// Check 1: Has an approved job application matching their email
$email_safe = mysqli_real_escape_string($conn, $user['email']);
$app_check  = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT id FROM job_applications WHERE email='$email_safe' AND status='approved' LIMIT 1"));
if ($app_check) $is_approved = true;
 
// Check 2: Admin manually set their status to 'active' or 'approved' in users table
// (if your users table has a status column)
$col_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'status'");
if ($col_check && mysqli_num_rows($col_check) > 0) {
    $status_val = strtolower(trim($user['status'] ?? ''));
    // Allow if status is active, approved, or empty/null (manually added by admin)
    if (in_array($status_val, ['active', 'approved', '']) || empty($status_val)) {
        $is_approved = true;
    }
} else {
    // No status column — trust admin created accounts
    $is_approved = true;
}
 
if (!$is_approved) {
    // Show a pending screen instead of dashboard
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pending Approval | HousingHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Outfit:wght@400;600&display=swap" rel="stylesheet">
    <style>
    *{box-sizing:border-box;margin:0;padding:0}
    :root{--ink:#04091a;--gold:#c8a43c;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25)}
    body{font-family:"Outfit",sans-serif;background:radial-gradient(ellipse 80% 60% at 70% 10%,rgba(14,90,200,.18),transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12),transparent 50%),var(--ink);color:var(--white);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
    .card{width:100%;max-width:460px;background:rgba(10,16,40,.95);border:1px solid var(--border);border-radius:16px;padding:48px 40px;text-align:center;box-shadow:0 40px 100px rgba(0,0,0,.6);animation:up .6s cubic-bezier(.23,1,.32,1)}
    @keyframes up{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:none}}
    .icon{font-size:52px;display:block;margin-bottom:20px;animation:fl 3s ease-in-out infinite}
    @keyframes fl{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
    h1{font-family:"Cormorant Garamond",serif;font-size:30px;color:var(--white);margin-bottom:10px}
    em{color:var(--gold);font-style:italic}
    p{font-size:13px;color:var(--muted);line-height:1.7;margin-bottom:22px}
    .steps{text-align:left;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:18px 22px;margin-bottom:26px}
    .step{display:flex;gap:12px;margin-bottom:12px;font-size:12px;color:var(--muted);line-height:1.5}
    .step:last-child{margin-bottom:0}
    .sn{width:20px;height:20px;border-radius:50%;background:rgba(200,164,60,.15);border:1px solid var(--gold);color:var(--gold);font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .btn{display:inline-block;padding:12px 28px;border:1px solid rgba(200,164,60,.3);color:var(--gold);font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:6px}
    .brand{font-family:"Cormorant Garamond",serif;font-size:12px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:rgba(200,164,60,.35);margin-bottom:26px}
    </style>
    </head>
    <body>
    <div class="card">
      <div class="brand">HOUSING HUB</div>
      <span class="icon">⏳</span>
      <h1>Account <em>Pending</em></h1>
      <p>Your staff account has not yet been fully activated by management. This may be because your application is still under review or your access has not been confirmed yet.</p>
      <div class="steps">
        <div class="step"><div class="sn">1</div><span>Your job application is reviewed by HR management.</span></div>
        <div class="step"><div class="sn">2</div><span>Once approved, your account will be activated automatically.</span></div>
        <div class="step"><div class="sn">3</div><span>You will receive an email with confirmation and next steps.</span></div>
      </div>
      <p style="font-size:11px;margin-bottom:22px">Questions? Email <span style="color:var(--gold)">careers@housinghuborg.ug</span></p>
      <a href="logout.php" class="btn">← Sign Out</a>
    </div>
    </body>
    </html>
    <?php
    exit();
}
 
$fullname = htmlspecialchars($user['fullname']);
$parts    = explode(' ', $fullname, 2);
$fname    = $parts[0];
$initials = strtoupper(substr($parts[0],0,1) . substr($parts[1]??'',0,1));
 
// ── Stats ──
$pending_inspections = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM inspections WHERE status='Pending'"))['c'] ?? 0;
$pending_maintenance = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM maintenance WHERE status='pending' OR status='in_progress'"))['c'] ?? 0;
$today               = date('Y-m-d');
$visitors_today      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM guests WHERE DATE(check_in)='$today'"))['c'] ?? 0;
$unread_notifs       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM notifications WHERE user_id='$user_id' AND is_read=0"))['c'] ?? 0;
$my_tasks_open       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM tasks WHERE assigned_to='$user_id' AND status!='Completed'"))['c'] ?? 0;
$my_tasks_done       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM tasks WHERE assigned_to='$user_id' AND status='Completed'"))['c'] ?? 0;
 
// ── Upcoming Events ──
$events = [];
$insp = mysqli_query($conn, "SELECT property_id, inspection_date, inspector_name FROM inspections WHERE inspection_date >= CURDATE() ORDER BY inspection_date ASC LIMIT 5");
while ($i = mysqli_fetch_assoc($insp)) {
    $events[] = ['type'=>'Inspection','icon'=>'🔍','date'=>$i['inspection_date'],'info'=>"Inspector: ".htmlspecialchars($i['inspector_name']),'color'=>'rgba(200,164,60,.2)','bc'=>'var(--gb)'];
}
$maint = mysqli_query($conn, "SELECT property_id, status, created_at FROM maintenance WHERE status='pending' OR status='in_progress' ORDER BY created_at ASC LIMIT 5");
while ($m = mysqli_fetch_assoc($maint)) {
    $events[] = ['type'=>'Maintenance','icon'=>'🔧','date'=>$m['created_at'],'info'=>"Status: ".htmlspecialchars($m['status']),'color'=>'rgba(239,68,68,.1)','bc'=>'rgba(239,68,68,.3)'];
}
$visits = mysqli_query($conn, "SELECT property_id, check_in FROM guests WHERE check_in >= NOW() ORDER BY check_in ASC LIMIT 5");
while ($v = mysqli_fetch_assoc($visits)) {
    $events[] = ['type'=>'Visitor','icon'=>'🪪','date'=>$v['check_in'],'info'=>"Property ID: ".$v['property_id'],'color'=>'rgba(14,90,200,.1)','bc'=>'rgba(14,90,200,.3)'];
}
usort($events, fn($a,$b) => strtotime($a['date']) - strtotime($b['date']));
 
// ── My Tasks ──
$my_tasks = [];
$tq = mysqli_query($conn, "SELECT title, status, priority, due_date FROM tasks WHERE assigned_to='$user_id' ORDER BY due_date ASC LIMIT 8");
while ($t = mysqli_fetch_assoc($tq)) $my_tasks[] = $t;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Dashboard | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25);--red:#ef4444;--green:#16a34a;--sw:260px}
html,body{height:100%;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden}
body::before{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
 
/* SIDEBAR */
.sb{position:fixed;left:0;top:0;width:var(--sw);height:100%;background:rgba(4,9,26,.98);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow-y:auto;z-index:500}
.sb::-webkit-scrollbar{width:3px}.sb::-webkit-scrollbar-thumb{background:var(--gb)}
.sb-head{padding:22px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.sb-logo{font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold)}
.sb-sub{font-size:9px;color:var(--muted);letter-spacing:1px}
.sb-staff{padding:16px 20px;border-bottom:1px solid var(--border)}
.sb-av{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.4),rgba(14,90,200,.4));border:2px solid var(--gb);display:flex;align-items:center;justify-content:center;font-family:"Cormorant Garamond",serif;font-size:17px;font-weight:700;color:var(--white);margin-bottom:8px}
.sb-name{font-size:13px;font-weight:600;color:var(--white);margin-bottom:2px}
.sb-role{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;background:rgba(14,90,200,.15);border:1px solid rgba(14,90,200,.3);border-radius:20px;font-size:9px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:#5b9cff}
.sb-nav{padding:12px 0;flex:1}
.nl{font-size:9px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.18);padding:0 20px;margin-bottom:4px;margin-top:14px}
.na{display:flex;align-items:center;gap:10px;padding:10px 20px;font-size:13px;font-weight:500;color:var(--muted);text-decoration:none;transition:all .2s;border-left:3px solid transparent}
.na:hover{color:var(--white);background:rgba(255,255,255,.04);border-left-color:var(--gb)}
.na.active{color:var(--gold);background:rgba(200,164,60,.08);border-left-color:var(--gold)}
.ni{font-size:15px;width:20px;text-align:center}
.nb{margin-left:auto;padding:2px 7px;background:var(--red);color:var(--white);border-radius:10px;font-size:9px;font-weight:700}
.sb-foot{padding:14px 20px;border-top:1px solid var(--border)}
.lo{width:100%;padding:10px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#fca5a5;font-family:"Outfit",sans-serif;font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;border-radius:6px;cursor:pointer;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .25s}
.lo:hover{background:rgba(239,68,68,.2)}
 
/* MAIN */
.mc{margin-left:var(--sw);position:relative;z-index:10;min-height:100vh}
.tb{display:flex;align-items:center;justify-content:space-between;padding:15px 32px;border-bottom:1px solid var(--border);background:rgba(4,9,26,.8);backdrop-filter:blur(20px);position:sticky;top:0;z-index:100}
.tb-title{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--white)}
.tb-sub{font-size:10px;color:var(--muted);letter-spacing:1px}
.content{padding:28px 32px}
 
/* CARDS */
.stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px}
.stat-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:18px;transition:all .3s}
.stat-card:hover{border-color:var(--gb);transform:translateY(-3px)}
.stat-icon{font-size:22px;margin-bottom:10px}
.stat-val{font-family:"Cormorant Garamond",serif;font-size:32px;font-weight:700;color:var(--gold);line-height:1}
.stat-lbl{font-size:11px;color:var(--muted);margin-top:4px;letter-spacing:.5px}
.ch2{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:20px}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:20px}
.card:hover{border-color:var(--gb)}
.card-title{font-family:"Cormorant Garamond",serif;font-size:17px;font-weight:700;color:var(--white);margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--border)}
 
/* EVENTS */
.ev{display:flex;gap:12px;margin-bottom:12px;padding:10px;border-radius:8px;align-items:flex-start}
.ev-icon{font-size:18px;flex-shrink:0;margin-top:1px}
.ev-type{font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--gold);margin-bottom:2px}
.ev-date{font-size:12px;color:var(--white);margin-bottom:2px}
.ev-info{font-size:11px;color:var(--muted)}
 
/* TASKS */
.task-item{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.task-item:last-child{border-bottom:none}
.task-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.task-title{font-size:13px;color:rgba(255,255,255,.85);flex:1}
.task-due{font-size:11px;color:var(--muted)}
.bx{display:inline-flex;align-items:center;padding:2px 8px;border-radius:20px;font-size:9px;font-weight:700;letter-spacing:.5px;text-transform:uppercase}
.bx.open{background:rgba(200,164,60,.1);border:1px solid var(--gb);color:var(--gold)}
.bx.progress{background:rgba(14,90,200,.1);border:1px solid rgba(14,90,200,.25);color:#5b9cff}
.bx.done{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.25);color:#86efac}
.bx.high{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#fca5a5}
 
/* QUICK LINKS */
.ql-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px}
.ql{display:flex;flex-direction:column;align-items:center;gap:8px;padding:18px 12px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;text-decoration:none;color:var(--white);transition:all .25s;text-align:center}
.ql:hover{border-color:var(--gb);background:rgba(200,164,60,.06);transform:translateY(-3px)}
.ql-icon{font-size:24px}
.ql-label{font-size:12px;font-weight:600;color:var(--muted)}
.ql:hover .ql-label{color:var(--gold)}
 
/* GUIDELINES */
.rule{display:flex;gap:10px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px;color:rgba(255,255,255,.7);line-height:1.5}
.rule:last-child{border-bottom:none}
.rule-dot{color:var(--gold);flex-shrink:0;margin-top:2px}
 
@media(max-width:900px){
  :root{--sw:0px}
  .sb{display:none}
  .mc{margin-left:0}
  .stats-grid{grid-template-columns:1fr 1fr}
  .ch2,.ql-grid{grid-template-columns:1fr}
  .content{padding:16px}
}
</style>
</head>
<body>
 
<!-- SIDEBAR -->
<aside class="sb">
  <div class="sb-head">
    <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--gb),rgba(14,90,200,.3));border:1.5px solid var(--gb);display:flex;align-items:center;justify-content:center;font-size:16px">🏠</div>
    <div><div class="sb-logo">Housing Hub</div><div class="sb-sub">Staff Portal</div></div>
  </div>
  <div class="sb-staff">
    <div class="sb-av"><?= $initials ?></div>
    <div class="sb-name"><?= $fullname ?></div>
    <span class="sb-role">● Staff Member</span>
  </div>
  <nav class="sb-nav">
    <div class="nl">Overview</div>
    <a href="staff_dashboard.php" class="na active"><span class="ni">🏠</span>Dashboard</a>
    <div class="nl">Work</div>
    <a href="staff_tasks.php" class="na"><span class="ni">✅</span>My Tasks<?php if($my_tasks_open>0):?><span class="nb"><?=$my_tasks_open?></span><?php endif;?></a>
    <a href="staff_inspections.php" class="na"><span class="ni">🔍</span>Inspections<?php if($pending_inspections>0):?><span class="nb"><?=$pending_inspections?></span><?php endif;?></a>
    <a href="staff_maintenance.php" class="na"><span class="ni">🔧</span>Maintenance<?php if($pending_maintenance>0):?><span class="nb"><?=$pending_maintenance?></span><?php endif;?></a>
    <a href="staff_guests.php" class="na"><span class="ni">🪪</span>Guest Approvals</a>
    <div class="nl">Tenants</div>
    <a href="staff_tenants.php" class="na"><span class="ni">👥</span>Tenant Profiles</a>
    <a href="staff_payments.php" class="na"><span class="ni">💳</span>Payments</a>
    <div class="nl">Other</div>
    <a href="staff_schedule.php" class="na"><span class="ni">📅</span>Schedule</a>
    <a href="staff_reports.php" class="na"><span class="ni">📊</span>Reports</a>
    <a href="staff_notifications.php" class="na"><span class="ni">🔔</span>Notifications<?php if($unread_notifs>0):?><span class="nb"><?=$unread_notifs?></span><?php endif;?></a>
  </nav>
  <div class="sb-foot"><a href="logout.php" class="lo">⬡ &nbsp;Sign Out</a></div>
</aside>
 
<!-- MAIN -->
<div class="mc">
  <div class="tb">
    <div>
      <div class="tb-title">Good day, <?= $fname ?>!</div>
      <div class="tb-sub">HousingHub · Staff Dashboard</div>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
      <a href="staff_notifications.php" style="width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,.04);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:14px;text-decoration:none;position:relative">
        🔔<?php if($unread_notifs>0):?><span style="position:absolute;top:5px;right:5px;width:7px;height:7px;background:var(--red);border-radius:50%;border:1.5px solid var(--ink)"></span><?php endif;?>
      </a>
      <a href="logout.php" style="padding:8px 16px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:6px;color:#fca5a5;font-size:11px;font-weight:600;text-decoration:none;letter-spacing:1px">Sign Out</a>
    </div>
  </div>
 
  <div class="content">
 
    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-val"><?= $my_tasks_open ?></div><div class="stat-lbl">My Open Tasks</div></div>
      <div class="stat-card"><div class="stat-icon">🔧</div><div class="stat-val" style="color:<?= $pending_maintenance>0?'#fca5a5':'var(--gold)' ?>"><?= $pending_maintenance ?></div><div class="stat-lbl">Pending Maintenance</div></div>
      <div class="stat-card"><div class="stat-icon">🔍</div><div class="stat-val"><?= $pending_inspections ?></div><div class="stat-lbl">Pending Inspections</div></div>
      <div class="stat-card"><div class="stat-icon">🪪</div><div class="stat-val" style="color:#86efac"><?= $visitors_today ?></div><div class="stat-lbl">Visitors Today</div></div>
      <div class="stat-card"><div class="stat-icon">🔔</div><div class="stat-val" style="color:<?= $unread_notifs>0?'var(--gold)':'var(--muted)' ?>"><?= $unread_notifs ?></div><div class="stat-lbl">Unread Notifications</div></div>
      <div class="stat-card"><div class="stat-icon">🏆</div><div class="stat-val" style="color:#86efac"><?= $my_tasks_done ?></div><div class="stat-lbl">Tasks Completed</div></div>
    </div>
 
    <!-- QUICK LINKS -->
    <div class="ql-grid">
      <a href="staff_tasks.php" class="ql"><span class="ql-icon">✅</span><span class="ql-label">My Tasks</span></a>
      <a href="staff_maintenance.php" class="ql"><span class="ql-icon">🔧</span><span class="ql-label">Maintenance</span></a>
      <a href="staff_inspections.php" class="ql"><span class="ql-icon">🔍</span><span class="ql-label">Inspections</span></a>
      <a href="staff_tenants.php" class="ql"><span class="ql-icon">👥</span><span class="ql-label">Tenants</span></a>
      <a href="staff_guests.php" class="ql"><span class="ql-icon">🪪</span><span class="ql-label">Guest Approvals</span></a>
      <a href="staff_payments.php" class="ql"><span class="ql-icon">💳</span><span class="ql-label">Payments</span></a>
    </div>
 
    <div class="ch2">
      <!-- UPCOMING EVENTS -->
      <div class="card">
        <div class="card-title">📅 Upcoming Events</div>
        <?php if(empty($events)): ?>
          <p style="font-size:13px;color:var(--muted)">No upcoming events.</p>
        <?php else: foreach(array_slice($events,0,6) as $e): ?>
          <div class="ev" style="background:<?= $e['color'] ?>;border-left:3px solid <?= $e['bc'] ?>">
            <span class="ev-icon"><?= $e['icon'] ?></span>
            <div>
              <div class="ev-type"><?= $e['type'] ?></div>
              <div class="ev-date"><?= date('d M Y, H:i', strtotime($e['date'])) ?></div>
              <div class="ev-info"><?= $e['info'] ?></div>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
 
      <!-- MY TASKS -->
      <div class="card">
        <div class="card-title">✅ My Tasks</div>
        <?php if(empty($my_tasks)): ?>
          <p style="font-size:13px;color:var(--muted)">No tasks assigned yet.</p>
        <?php else: foreach($my_tasks as $t):
          $st  = strtolower($t['status']);
          $dot = $st==='completed'?'#86efac':($st==='in progress'?'#5b9cff':'var(--gold)');
          $bc  = $st==='completed'?'done':($st==='in progress'?'progress':'open');
          $pri = strtolower($t['priority']??'');
        ?>
          <div class="task-item">
            <div class="task-dot" style="background:<?= $dot ?>"></div>
            <div class="task-title"><?= htmlspecialchars($t['title']) ?></div>
            <?php if($pri==='high'): ?><span class="bx high">High</span><?php endif; ?>
            <span class="bx <?= $bc ?>"><?= htmlspecialchars($t['status']) ?></span>
            <span class="task-due"><?= $t['due_date'] ? date('d M', strtotime($t['due_date'])) : '' ?></span>
          </div>
        <?php endforeach; endif; ?>
        <a href="staff_tasks.php" style="display:block;text-align:center;margin-top:14px;font-size:12px;color:var(--gold);text-decoration:none;letter-spacing:1px">View All Tasks →</a>
      </div>
    </div>
 
    <!-- GUIDELINES & PAYROLL -->
    <div class="ch2">
      <div class="card">
        <div class="card-title">📋 Staff Guidelines</div>
        <div class="rule"><span class="rule-dot">▸</span>Always update tenant and property information accurately.</div>
        <div class="rule"><span class="rule-dot">▸</span>Respond to maintenance requests within 24 hours.</div>
        <div class="rule"><span class="rule-dot">▸</span>Keep inspection records up-to-date at all times.</div>
        <div class="rule"><span class="rule-dot">▸</span>Notify management of any unusual activity on properties.</div>
        <div class="rule"><span class="rule-dot">▸</span>Respect tenant privacy and confidentiality always.</div>
        <div class="rule"><span class="rule-dot">▸</span>Log out after completing your tasks to secure your account.</div>
      </div>
      <div class="card">
        <div class="card-title">💰 Payroll Information</div>
        <div class="rule"><span class="rule-dot">▸</span>Staff salaries are processed on the last day of each month.</div>
        <div class="rule"><span class="rule-dot">▸</span>Submit all task updates and reports before payroll cut-off.</div>
        <div class="rule"><span class="rule-dot">▸</span>Overtime or bonuses will be reflected in your monthly pay where applicable.</div>
        <div class="rule"><span class="rule-dot">▸</span>For any payment issues, contact HR or management immediately.</div>
      </div>
    </div>
 
  </div>
</div>
 
</body>
</html>