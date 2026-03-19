
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
 
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
 
$user_id = (int)$_SESSION['user_id'];
$user    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id' LIMIT 1"));
 
if (!$user || strtolower($user['role']) !== 'propertyowner') {
    echo "<h2 style='color:red;text-align:center;font-family:sans-serif;padding:40px'>Access Denied!</h2>"; exit();
}
 
// ── VERIFICATION GATE: Must have at least 1 property linked by admin ──
$has_property = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS c FROM properties WHERE owner_id=$user_id"))['c'] ?? 0;
 
if ($has_property == 0) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pending Verification | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Outfit:wght@400;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25)}
body{font-family:"Outfit",sans-serif;background:radial-gradient(ellipse 80% 60% at 70% 10%,rgba(14,90,200,.18),transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12),transparent 50%),var(--ink);color:var(--white);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.card{width:100%;max-width:480px;background:rgba(10,16,40,.95);border:1px solid var(--border);border-radius:16px;padding:48px 40px;text-align:center;box-shadow:0 40px 100px rgba(0,0,0,.6);animation:up .6s cubic-bezier(.23,1,.32,1)}
@keyframes up{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:none}}
.icon{font-size:56px;display:block;margin-bottom:20px;animation:fl 3s ease-in-out infinite}
@keyframes fl{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
h1{font-family:"Cormorant Garamond",serif;font-size:32px;color:var(--white);margin-bottom:10px}
em{color:var(--gold);font-style:italic}
p{font-size:13px;color:var(--muted);line-height:1.7;margin-bottom:22px}
.steps{text-align:left;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:18px 22px;margin-bottom:26px}
.step{display:flex;gap:12px;margin-bottom:12px;font-size:12px;color:var(--muted);line-height:1.5;align-items:flex-start}
.step:last-child{margin-bottom:0}
.sn{width:22px;height:22px;border-radius:50%;background:rgba(200,164,60,.15);border:1px solid var(--gold);color:var(--gold);font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.contact-box{background:rgba(200,164,60,.06);border:1px solid var(--gb);border-radius:8px;padding:14px 18px;margin-bottom:24px;font-size:13px;color:var(--muted)}
.contact-box strong{color:var(--gold);display:block;margin-bottom:6px}
.btn{display:inline-block;padding:12px 28px;border:1px solid rgba(200,164,60,.3);color:var(--gold);font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:6px;transition:all .2s}
.btn:hover{background:rgba(200,164,60,.1)}
.brand{font-family:"Cormorant Garamond",serif;font-size:12px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:rgba(200,164,60,.35);margin-bottom:26px}
</style>
</head>
<body>
<div class="card">
  <div class="brand">HOUSING HUB</div>
  <span class="icon">🏗️</span>
  <h1>Account <em>Pending</em></h1>
  <p>Your property owner account has been created but has not been fully activated yet. HousingHub needs to verify your details and link your properties before you can access your dashboard.</p>
  <div class="steps">
    <div class="step"><div class="sn">1</div><span>You registered as a property owner on HousingHub.</span></div>
    <div class="step"><div class="sn">2</div><span>Our team reviews your details and contacts you to confirm your property information.</span></div>
    <div class="step"><div class="sn">3</div><span>Once verified, your properties are added to the system and your dashboard activates automatically.</span></div>
    <div class="step"><div class="sn">4</div><span>Log back in to access your full owner portal with live data from your properties.</span></div>
  </div>
  <div class="contact-box">
    <strong>📞 Want to speed things up?</strong>
    Contact our team directly to get verified faster.<br><br>
    📧 <span style="color:var(--gold)">owners@housinghuborg.ug</span><br>
    📱 <span style="color:var(--gold)">+256 700 000 000</span>
  </div>
  <a href="logout.php" class="btn">← Sign Out</a>
</div>
</body>
</html>
<?php exit(); }
 
$fullname = htmlspecialchars($user['fullname']);
$parts    = explode(' ', $fullname, 2);
$fname    = $parts[0];
$initials = strtoupper(substr($parts[0],0,1) . substr($parts[1]??'',0,1));
 
// ── Properties owned ──
$props_q = mysqli_query($conn, "SELECT * FROM properties WHERE owner_id=$user_id ORDER BY created_at DESC");
$my_properties = [];
while ($p = mysqli_fetch_assoc($props_q)) $my_properties[] = $p;
$total_props = count($my_properties);
 
// ── Stats ──
$prop_ids = array_column($my_properties, 'id');
$prop_ids_str = !empty($prop_ids) ? implode(',', $prop_ids) : '0';
 
$total_tenants  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM tenants WHERE property_id IN ($prop_ids_str)"))['c'] ?? 0;
$total_revenue  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS c FROM payments WHERE property_id IN ($prop_ids_str) AND status='paid'"))['c'] ?? 0;
$pending_pay    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM payments WHERE property_id IN ($prop_ids_str) AND status='pending'"))['c'] ?? 0;
$open_maint     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM maintenance WHERE property_id IN ($prop_ids_str) AND status!='completed'"))['c'] ?? 0;
$total_units    = array_sum(array_column($my_properties, 'units'));
$occupied_units = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM tenants WHERE property_id IN ($prop_ids_str) AND status='Active'"))['c'] ?? 0;
$vacancy_rate   = $total_units > 0 ? round((($total_units - $occupied_units) / $total_units) * 100) : 0;
 
// ── Recent payments ──
$recent_payments = [];
$rpq = mysqli_query($conn, "SELECT pay.*,t.fullname AS tenant_name,p.property_name FROM payments pay LEFT JOIN tenants t ON pay.tenant_id=t.id LEFT JOIN properties p ON pay.property_id=p.id WHERE pay.property_id IN ($prop_ids_str) ORDER BY pay.date DESC LIMIT 6");
if ($rpq) while ($r = mysqli_fetch_assoc($rpq)) $recent_payments[] = $r;
 
// ── Recent maintenance ──
$recent_maint = [];
$rmq = mysqli_query($conn, "SELECT m.*,p.property_name FROM maintenance m LEFT JOIN properties p ON m.property_id=p.id WHERE m.property_id IN ($prop_ids_str) ORDER BY m.created_at DESC LIMIT 5");
if ($rmq) while ($r = mysqli_fetch_assoc($rmq)) $recent_maint[] = $r;
 
// ── Monthly revenue chart data (last 6 months) ──
$chart_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month_start = date('Y-m-01', strtotime("-$i months"));
    $month_end   = date('Y-m-t', strtotime("-$i months"));
    $month_label = date('M', strtotime("-$i months"));
    $rev = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS c FROM payments WHERE property_id IN ($prop_ids_str) AND status='paid' AND date BETWEEN '$month_start' AND '$month_end'"))['c'] ?? 0;
    $chart_data[] = ['label' => $month_label, 'value' => (float)$rev];
}
$max_chart = max(array_column($chart_data, 'value') ?: [1]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Owner Dashboard | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25);--red:#ef4444;--sw:260px}
html,body{height:100%;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden}
body::before{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
 
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
 
/* MAIN */
.mc{margin-left:var(--sw);position:relative;z-index:10;min-height:100vh}
.tb{display:flex;align-items:center;justify-content:space-between;padding:15px 32px;border-bottom:1px solid var(--border);background:rgba(4,9,26,.8);backdrop-filter:blur(20px);position:sticky;top:0;z-index:100}
.tb-title{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--white)}
.tb-sub{font-size:10px;color:var(--muted);letter-spacing:1px}
.content{padding:28px 32px}
 
/* STAT CARDS */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.stat-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:18px;transition:all .3s}
.stat-card:hover{border-color:var(--gb);transform:translateY(-3px)}
.stat-icon{font-size:22px;margin-bottom:10px}
.stat-val{font-family:"Cormorant Garamond",serif;font-size:32px;font-weight:700;color:var(--gold);line-height:1}
.stat-lbl{font-size:11px;color:var(--muted);margin-top:4px;letter-spacing:.5px}
 
/* CARDS */
.ch2{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:20px}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:20px;transition:border-color .3s}
.card:hover{border-color:var(--gb)}
.card-title{font-family:"Cormorant Garamond",serif;font-size:17px;font-weight:700;color:var(--white);margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
.card-link{font-size:11px;color:var(--gold);text-decoration:none;font-family:"Outfit",sans-serif;font-weight:600;letter-spacing:.5px}
 
/* PROPERTY CARDS */
.prop-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:24px}
.prop-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:20px;transition:all .3s}
.prop-card:hover{border-color:var(--gb);transform:translateY(-3px)}
.prop-name{font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;color:var(--white);margin-bottom:6px}
.prop-addr{font-size:12px;color:var(--muted);margin-bottom:12px}
.prop-stats{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.prop-stat{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:6px;padding:8px 10px;text-align:center}
.prop-stat-val{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--gold)}
.prop-stat-lbl{font-size:10px;color:var(--muted);letter-spacing:.5px}
.prop-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;margin-bottom:12px}
.badge-available{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.badge-occupied{background:rgba(200,164,60,.1);border:1px solid var(--gb);color:var(--gold)}
.badge-archived{background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--muted)}
 
/* PAYMENTS TABLE */
.ptrow{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.ptrow:last-child{border-bottom:none}
.pt-name{font-size:13px;color:var(--white);font-weight:500;flex:1}
.pt-prop{font-size:11px;color:var(--muted)}
.pt-amt{font-size:13px;font-weight:700;color:#86efac;white-space:nowrap}
.pt-date{font-size:11px;color:var(--muted);white-space:nowrap}
.bx{display:inline-flex;align-items:center;padding:2px 8px;border-radius:20px;font-size:9px;font-weight:700;letter-spacing:.5px;text-transform:uppercase}
.bx.paid{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.bx.pending{background:rgba(200,164,60,.1);border:1px solid var(--gb);color:var(--gold)}
.bx.failed{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
 
/* MAINTENANCE */
.mrow{display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.mrow:last-child{border-bottom:none}
.mrow-icon{font-size:18px;flex-shrink:0;margin-top:1px}
.mrow-title{font-size:13px;color:var(--white);margin-bottom:3px}
.mrow-prop{font-size:11px;color:var(--muted)}
 
/* REVENUE CHART */
.chart-wrap{display:flex;align-items:flex-end;gap:10px;height:120px;margin-top:10px}
.chart-bar-wrap{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;height:100%}
.chart-bar{width:100%;border-radius:4px 4px 0 0;background:linear-gradient(180deg,var(--gold),rgba(200,164,60,.4));transition:height 1s cubic-bezier(.23,1,.32,1);min-height:3px}
.chart-lbl{font-size:10px;color:var(--muted);letter-spacing:.5px}
 
/* EMPTY STATE */
.empty{text-align:center;padding:32px;color:var(--muted);font-size:14px}
.empty-icon{font-size:32px;margin-bottom:10px;display:block}
 
/* VACANCY BAR */
.vac-wrap{margin-top:8px}
.vac-bar{height:6px;background:rgba(255,255,255,.07);border-radius:3px;overflow:hidden;margin:6px 0}
.vac-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,var(--gold),var(--gold-l))}
.vac-labels{display:flex;justify-content:space-between;font-size:11px;color:var(--muted)}
 
@media(max-width:900px){
  :root{--sw:0px}
  .sb{display:none}
  .mc{margin-left:0}
  .stats-grid{grid-template-columns:1fr 1fr}
  .ch2{grid-template-columns:1fr}
  .content{padding:16px}
}
</style>
</head>
<body>
 
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
  <nav class="sb-nav">
    <div class="nl">Overview</div>
    <a href="propertyowner_dashboard.php" class="na active"><span class="ni">🏠</span>Dashboard</a>
    <div class="nl">My Portfolio</div>
    <a href="propertyowner_dashboard.php?view=properties" class="na"><span class="ni">🏢</span>My Properties</a>
    <a href="propertyowner_dashboard.php?view=tenants" class="na"><span class="ni">👥</span>My Tenants</a>
    <a href="propertyowner_dashboard.php?view=payments" class="na"><span class="ni">💳</span>Payments & Revenue</a>
    <a href="propertyowner_dashboard.php?view=maintenance" class="na"><span class="ni">🔧</span>Maintenance</a>
    <div class="nl">Account</div>
    <a href="propertyowner_profile.php" class="na"><span class="ni">👤</span>My Profile</a>
  </nav>
  <div class="sb-foot"><a href="logout.php" class="lo">⬡ &nbsp;Sign Out</a></div>
</aside>
 
<!-- MAIN -->
<div class="mc">
  <div class="tb">
    <div>
      <div class="tb-title">Welcome, <?= $fname ?>! &nbsp;<span style="font-size:13px;color:var(--muted);font-family:'Outfit',sans-serif;font-weight:400"><?= date('l, d F Y') ?></span></div>
      <div class="tb-sub">HousingHub · Property Owner Dashboard</div>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
      <a href="propertyowner_profile.php" style="width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,.04);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:14px;text-decoration:none">👤</a>
      <a href="logout.php" style="padding:8px 16px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:6px;color:#fca5a5;font-size:11px;font-weight:600;text-decoration:none;letter-spacing:1px">Sign Out</a>
    </div>
  </div>
 
  <div class="content">
 
    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon">🏢</div><div class="stat-val"><?= $total_props ?></div><div class="stat-lbl">My Properties</div></div>
      <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-val"><?= $total_tenants ?></div><div class="stat-lbl">Active Tenants</div></div>
      <div class="stat-card"><div class="stat-icon">💰</div><div class="stat-val" style="font-size:20px">UGX <?= number_format($total_revenue ?? 0) ?></div><div class="stat-lbl">Total Revenue Collected</div></div>
      <div class="stat-card"><div class="stat-icon">⏳</div><div class="stat-val" style="color:<?= $pending_pay>0?'#fca5a5':'var(--gold)' ?>"><?= $pending_pay ?></div><div class="stat-lbl">Pending Payments</div></div>
    </div>
 
    <!-- SECOND STATS ROW -->
    <div class="stats-grid" style="margin-bottom:24px">
      <div class="stat-card"><div class="stat-icon">🏘</div><div class="stat-val"><?= $total_units ?></div><div class="stat-lbl">Total Units</div></div>
      <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-val" style="color:#86efac"><?= $occupied_units ?></div><div class="stat-lbl">Occupied Units</div></div>
      <div class="stat-card"><div class="stat-icon">📭</div><div class="stat-val" style="color:<?= $vacancy_rate>30?'#fca5a5':'var(--gold)' ?>"><?= $vacancy_rate ?>%</div><div class="stat-lbl">Vacancy Rate</div></div>
      <div class="stat-card"><div class="stat-icon">🔧</div><div class="stat-val" style="color:<?= $open_maint>0?'#fca5a5':'var(--gold)' ?>"><?= $open_maint ?></div><div class="stat-lbl">Open Maintenance</div></div>
    </div>
 
    <!-- MY PROPERTIES -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-title">
        🏢 My Properties
        <a href="properties.php" class="card-link">Browse All →</a>
      </div>
      <?php if(empty($my_properties)): ?>
        <div class="empty"><span class="empty-icon">🏗</span>No properties listed yet.<br><a href="contact.php" style="color:var(--gold)">Contact us to add your property</a></div>
      <?php else: ?>
        <div class="prop-grid">
          <?php foreach($my_properties as $p):
            $badge_class = strtolower($p['status']??'available') === 'occupied' ? 'badge-occupied' : (strtolower($p['status']??'available') === 'archived' ? 'badge-archived' : 'badge-available');
            $pid = $p['id'];
            $p_tenants = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM tenants WHERE property_id=$pid AND status='Active'"))['c']??0;
            $p_revenue = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) AS c FROM payments WHERE property_id=$pid AND status='paid'"))['c']??0;
          ?>
          <div class="prop-card">
            <div class="prop-name"><?= htmlspecialchars($p['property_name']) ?></div>
            <div class="prop-addr">📍 <?= htmlspecialchars($p['address']??'—') ?></div>
            <span class="prop-badge <?= $badge_class ?>"><?= ucfirst($p['status']??'Available') ?></span>
            <div class="prop-stats">
              <div class="prop-stat"><div class="prop-stat-val"><?= (int)$p['units'] ?></div><div class="prop-stat-lbl">Units</div></div>
              <div class="prop-stat"><div class="prop-stat-val"><?= $p_tenants ?></div><div class="prop-stat-lbl">Tenants</div></div>
              <div class="prop-stat"><div class="prop-stat-val" style="font-size:14px">UGX <?= number_format($p['rent_amount']??0) ?></div><div class="prop-stat-lbl">Rent/Unit</div></div>
              <div class="prop-stat"><div class="prop-stat-val" style="font-size:14px;color:#86efac">UGX <?= number_format($p_revenue) ?></div><div class="prop-stat-lbl">Collected</div></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
 
    <div class="ch2">
      <!-- REVENUE CHART -->
      <div class="card">
        <div class="card-title">📈 Revenue — Last 6 Months</div>
        <div class="chart-wrap" id="revenue-chart">
          <?php foreach($chart_data as $cd):
            $pct = $max_chart > 0 ? ($cd['value'] / $max_chart) * 100 : 0;
          ?>
          <div class="chart-bar-wrap">
            <div style="flex:1;display:flex;align-items:flex-end;width:100%">
              <div class="chart-bar" data-pct="<?= $pct ?>" style="height:0%;width:100%" title="UGX <?= number_format($cd['value']) ?>"></div>
            </div>
            <div class="chart-lbl"><?= $cd['label'] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <!-- VACANCY BAR -->
        <div class="vac-wrap" style="margin-top:20px">
          <div style="font-size:12px;font-weight:600;color:var(--white);margin-bottom:4px">Occupancy Rate</div>
          <div class="vac-bar"><div class="vac-fill" id="occ-fill" style="width:0%"></div></div>
          <div class="vac-labels">
            <span><?= $occupied_units ?> occupied</span>
            <span><?= $total_units - $occupied_units ?> vacant</span>
          </div>
        </div>
      </div>
 
      <!-- RECENT PAYMENTS -->
      <div class="card">
        <div class="card-title">💳 Recent Payments</div>
        <?php if(empty($recent_payments)): ?>
          <div class="empty">No payments recorded yet.</div>
        <?php else: foreach($recent_payments as $rp):
          $st = strtolower($rp['status']??'pending');
        ?>
          <div class="ptrow">
            <div style="flex:1">
              <div class="pt-name"><?= htmlspecialchars($rp['tenant_name']??'—') ?></div>
              <div class="pt-prop"><?= htmlspecialchars($rp['property_name']??'—') ?></div>
            </div>
            <div style="text-align:right">
              <div class="pt-amt">UGX <?= number_format($rp['amount']) ?></div>
              <div class="pt-date"><?= $rp['date']?date('d M',strtotime($rp['date'])):'' ?></div>
            </div>
            <span class="bx <?= $st ?>"><?= ucfirst($st) ?></span>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
 
    <!-- MAINTENANCE REQUESTS -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-title">🔧 Open Maintenance Requests</div>
      <?php if(empty($recent_maint)): ?>
        <div class="empty"><span class="empty-icon">✅</span>No open maintenance requests. All clear!</div>
      <?php else: foreach($recent_maint as $m):
        $mst = strtolower($m['status']??'pending');
        $mc  = $mst==='completed'?'#86efac':($mst==='in_progress'?'#5b9cff':'#fca5a5');
      ?>
        <div class="mrow">
          <span class="mrow-icon">🔧</span>
          <div style="flex:1">
            <div class="mrow-title"><?= htmlspecialchars($m['issue']??'—') ?></div>
            <div class="mrow-prop"><?= htmlspecialchars($m['property_name']??'—') ?></div>
          </div>
          <span class="bx" style="background:rgba(255,255,255,.05);border:1px solid var(--border);color:<?= $mc ?>"><?= ucfirst(str_replace('_',' ',$mst)) ?></span>
        </div>
      <?php endforeach; endif; ?>
    </div>
 
    <!-- INFO FOOTER -->
    <div style="background:rgba(200,164,60,.06);border:1px solid var(--gb);border-radius:10px;padding:16px 20px;font-size:13px;color:var(--muted);line-height:1.8">
      <strong style="color:var(--gold)">ℹ️ About Your Dashboard</strong><br>
      Your properties, tenants, payments, and maintenance are all managed by the HousingHub team on your behalf.
      If you need to add a property, update your details, or have any concerns — contact us at
      <span style="color:var(--gold)">owners@housinghuborg.ug</span> or call your dedicated account manager.
    </div>
 
  </div>
</div>
 
<script>
// Animate chart bars
window.addEventListener('load', () => {
  setTimeout(() => {
    document.querySelectorAll('.chart-bar').forEach(bar => {
      bar.style.height = bar.dataset.pct + '%';
    });
    const occ = document.getElementById('occ-fill');
    const rate = <?= $total_units > 0 ? round(($occupied_units/$total_units)*100) : 0 ?>;
    if (occ) setTimeout(() => occ.style.width = rate + '%', 200);
  }, 300);
});
</script>
</body>
</html>