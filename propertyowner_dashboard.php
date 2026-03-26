
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
 
// ── VERIFICATION GATE ──
$has_property = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS c FROM properties WHERE owner_id=$user_id"))['c'] ?? 0;
 
if ($has_property == 0) { ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
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
</style></head><body>
<div class="card">
  <div class="brand">HOUSING HUB</div>
  <span class="icon">🏗️</span>
  <h1>Account <em>Pending</em></h1>
  <p>Your property owner account has been created but not yet activated. HousingHub needs to verify your details and link your properties before you can access your dashboard.</p>
  <div class="steps">
    <div class="step"><div class="sn">1</div><span>You registered as a property owner on HousingHub.</span></div>
    <div class="step"><div class="sn">2</div><span>Our team reviews your details and contacts you to confirm your property information.</span></div>
    <div class="step"><div class="sn">3</div><span>Once verified, your properties are added and your dashboard activates automatically.</span></div>
    <div class="step"><div class="sn">4</div><span>Log back in to access your full owner portal with live data.</span></div>
  </div>
  <div class="contact-box">
    <strong>📞 Want to speed things up?</strong>
    Contact our team directly to get verified faster.<br><br>
    📧 <span style="color:var(--gold)">owners@housinghuborg.ug</span><br>
    📱 <span style="color:var(--gold)">+256 700 000 000</span>
  </div>
  <a href="logout.php" class="btn">← Sign Out</a>
</div>
</body></html>
<?php exit(); }
 
$fullname = htmlspecialchars($user['fullname']);
$parts    = explode(' ', $fullname, 2);
$fname    = $parts[0];
$initials = strtoupper(substr($parts[0],0,1) . substr($parts[1]??'',0,1));
 
// ── Properties ──
$props_q = mysqli_query($conn, "SELECT * FROM properties WHERE owner_id=$user_id ORDER BY created_at DESC");
$my_properties = [];
while ($p = mysqli_fetch_assoc($props_q)) $my_properties[] = $p;
$total_props = count($my_properties);
 
$prop_ids     = array_column($my_properties, 'id');
$prop_ids_str = !empty($prop_ids) ? implode(',', $prop_ids) : '0';
 
// ── Stats ──
$total_tenants  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM tenants WHERE property_id IN ($prop_ids_str)"))['c'] ?? 0;
$total_revenue  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS c FROM payments WHERE property_id IN ($prop_ids_str) AND status='paid'"))['c'] ?? 0;
$pending_pay    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM payments WHERE property_id IN ($prop_ids_str) AND status='pending'"))['c'] ?? 0;
$pending_amount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS c FROM payments WHERE property_id IN ($prop_ids_str) AND status='pending'"))['c'] ?? 0;
$open_maint     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM maintenance WHERE property_id IN ($prop_ids_str) AND status!='completed'"))['c'] ?? 0;
$done_maint     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM maintenance WHERE property_id IN ($prop_ids_str) AND status='completed'"))['c'] ?? 0;
$total_units    = array_sum(array_column($my_properties, 'units'));
$occupied_units = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM tenants WHERE property_id IN ($prop_ids_str) AND status='Active'"))['c'] ?? 0;
$vacancy_rate   = $total_units > 0 ? round((($total_units - $occupied_units) / $total_units) * 100) : 0;
$occ_rate       = $total_units > 0 ? round(($occupied_units / $total_units) * 100) : 0;
$total_complaints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM complaints WHERE tenant_id IN (SELECT id FROM tenants WHERE property_id IN ($prop_ids_str)) AND status='pending'"))['c'] ?? 0;
 
// ── Payments this month ──
$month_start = date('Y-m-01');
$month_end   = date('Y-m-t');
$this_month_rev = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS c FROM payments WHERE property_id IN ($prop_ids_str) AND status='paid' AND date BETWEEN '$month_start' AND '$month_end'"))['c'] ?? 0;
 
// ── Recent payments ──
$recent_payments = [];
$rpq = mysqli_query($conn, "SELECT pay.*,t.fullname AS tenant_name,p.property_name FROM payments pay LEFT JOIN tenants t ON pay.tenant_id=t.id LEFT JOIN properties p ON pay.property_id=p.id WHERE pay.property_id IN ($prop_ids_str) ORDER BY pay.date DESC LIMIT 8");
if ($rpq) while ($r = mysqli_fetch_assoc($rpq)) $recent_payments[] = $r;
 
// ── Tenants list ──
$tenants_list = [];
$tq = mysqli_query($conn, "SELECT t.*,p.property_name,p.rent_amount FROM tenants t LEFT JOIN properties p ON t.property_id=p.id WHERE t.property_id IN ($prop_ids_str) ORDER BY t.fullname ASC");
if ($tq) while ($r = mysqli_fetch_assoc($tq)) $tenants_list[] = $r;
 
// ── Maintenance ──
$recent_maint = [];
$rmq = mysqli_query($conn, "SELECT m.*,p.property_name FROM maintenance m LEFT JOIN properties p ON m.property_id=p.id WHERE m.property_id IN ($prop_ids_str) ORDER BY m.created_at DESC LIMIT 8");
if ($rmq) while ($r = mysqli_fetch_assoc($rmq)) $recent_maint[] = $r;
 
// ── Monthly revenue chart (last 6 months) ──
$chart_data = [];
for ($i = 5; $i >= 0; $i--) {
    $ms = date('Y-m-01', strtotime("-$i months"));
    $me = date('Y-m-t',  strtotime("-$i months"));
    $ml = date('M Y',    strtotime("-$i months"));
    $rev = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS c FROM payments WHERE property_id IN ($prop_ids_str) AND status='paid' AND date BETWEEN '$ms' AND '$me'"))['c'] ?? 0;
    $cnt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM payments WHERE property_id IN ($prop_ids_str) AND status='paid' AND date BETWEEN '$ms' AND '$me'"))['c'] ?? 0;
    $chart_data[] = ['label' => date('M', strtotime("-$i months")), 'full' => $ml, 'value' => (float)$rev, 'count' => (int)$cnt];
}
$max_chart = max(array_column($chart_data, 'value') ?: [1]);
 
// ── Notifications unread count ──
$unread_notifs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM notifications WHERE user_id=$user_id AND is_read=0"))['c'] ?? 0;
 
// ── Leases for owner properties ──
$leases_list = [];
$lq = mysqli_query($conn, "SELECT t.*,p.property_name,p.rent_amount FROM tenants t LEFT JOIN properties p ON t.property_id=p.id WHERE t.property_id IN ($prop_ids_str) ORDER BY t.lease_end ASC");
if ($lq) while ($r = mysqli_fetch_assoc($lq)) $leases_list[] = $r;
 
// ── Complaints ──
$complaints_list = [];
$cq = mysqli_query($conn, "SELECT c.*,t.fullname AS tenant_name,p.property_name FROM complaints c LEFT JOIN tenants t ON c.tenant_id=t.id LEFT JOIN properties p ON t.property_id=p.id WHERE t.property_id IN ($prop_ids_str) ORDER BY c.created_at DESC");
if ($cq) while ($r = mysqli_fetch_assoc($cq)) $complaints_list[] = $r;
 
// ── Inspections ──
$inspections_list = [];
$iq = mysqli_query($conn, "SELECT i.*,p.property_name FROM inspections i LEFT JOIN properties p ON i.property_id=p.id WHERE i.property_id IN ($prop_ids_str) ORDER BY i.inspection_date DESC");
if ($iq) while ($r = mysqli_fetch_assoc($iq)) $inspections_list[] = $r;
 
// ── Visitors ──
$visitors_list = [];
$vq = mysqli_query($conn, "SELECT v.*,t.fullname AS tenant_name,p.property_name FROM visitors v LEFT JOIN tenants t ON v.tenant_id=t.id LEFT JOIN properties p ON v.property_id=p.id WHERE v.property_id IN ($prop_ids_str) ORDER BY v.visit_date DESC LIMIT 30");
if ($vq) while ($r = mysqli_fetch_assoc($vq)) $visitors_list[] = $r;
 
// ── Notifications list ──
$notifs_list = [];
$nq = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id=$user_id ORDER BY date DESC LIMIT 30");
if ($nq) while ($r = mysqli_fetch_assoc($nq)) $notifs_list[] = $r;
 
// ── Occupancy trend (12 months) ──
$occ_trend = [];
for ($i = 11; $i >= 0; $i--) {
    $ms = date('Y-m-01', strtotime("-$i months"));
    $me = date('Y-m-t',  strtotime("-$i months"));
    $ml = date('M', strtotime("-$i months"));
    $rev = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS c FROM payments WHERE property_id IN ($prop_ids_str) AND status='paid' AND date BETWEEN '$ms' AND '$me'"))['c'] ?? 0;
    $occ = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM tenants WHERE property_id IN ($prop_ids_str) AND status='Active' AND lease_start <= '$me'"))['c'] ?? 0;
    $occ_trend[] = ['label' => $ml, 'revenue' => (float)$rev, 'tenants' => (int)$occ];
}
 
// ── View handler ──
$view = $_GET['view'] ?? 'dashboard';
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
 
/* TOPBAR — fixed to top-right of content area */
.tb{position:fixed;top:0;left:var(--sw);right:0;z-index:200;display:flex;align-items:center;justify-content:space-between;padding:15px 32px;border-bottom:1px solid rgba(0,0,0,.15);background:var(--gold);box-shadow:0 2px 20px rgba(0,0,0,.3)}
.tb-title{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--ink)}
.tb-sub{font-size:10px;color:rgba(4,9,26,.6);letter-spacing:1px}
 
/* MAIN CONTENT */
.mc{margin-left:var(--sw);position:relative;z-index:10;min-height:100vh;padding-top:68px}
.content{padding:28px 32px}
 
/* SECTION TITLE */
.sec-title{font-family:"Cormorant Garamond",serif;font-size:22px;font-weight:700;color:var(--white);margin-bottom:18px;padding-bottom:12px;border-bottom:2px solid var(--gb);display:flex;justify-content:space-between;align-items:center}
.sec-link{font-size:12px;color:var(--gold);text-decoration:none;font-family:"Outfit",sans-serif;font-weight:600}
 
/* STAT CARDS */
.stats-grid{display:flex;flex-wrap:wrap;gap:16px;margin-bottom:20px;justify-content:center}
.stat-card{width:148px;height:148px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.12),rgba(14,90,200,.12));border:2px solid var(--gb);display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:12px;transition:all .35s;position:relative;flex-shrink:0;box-shadow:0 8px 28px rgba(0,0,0,.35)}
.stat-card:hover{transform:scale(1.08) translateY(-4px);border-color:var(--gold);box-shadow:0 16px 40px rgba(200,164,60,.25)}
.stat-icon{font-size:20px;margin-bottom:6px}
.stat-val{font-family:"Cormorant Garamond",serif;font-size:26px;font-weight:700;color:var(--gold);line-height:1}
.stat-lbl{font-size:10px;color:var(--muted);margin-top:4px;letter-spacing:.5px;padding:0 8px;line-height:1.3}
.stat-sub{font-size:10px;color:var(--muted);margin-top:4px;padding:0 6px;line-height:1.3}
 
/* CARDS */
.ch2{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:20px}
.ch3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px;margin-bottom:20px}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:20px;transition:border-color .3s}
.card:hover{border-color:var(--gb)}
.card-title{font-family:"Cormorant Garamond",serif;font-size:17px;font-weight:700;color:var(--white);margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
.card-link{font-size:11px;color:var(--gold);text-decoration:none;font-family:"Outfit",sans-serif;font-weight:600}
 
/* PROPERTY CARDS */
.prop-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:24px}
.prop-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:20px;transition:all .3s}
.prop-card:hover{border-color:var(--gb);transform:translateY(-3px)}
.prop-name{font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;color:var(--white);margin-bottom:4px}
.prop-addr{font-size:12px;color:var(--muted);margin-bottom:4px}
.prop-type{font-size:10px;color:var(--muted);letter-spacing:1px;text-transform:uppercase;margin-bottom:12px}
.prop-stats{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:12px}
.prop-stat{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:6px;padding:8px 10px;text-align:center}
.prop-stat-val{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--gold)}
.prop-stat-lbl{font-size:9px;color:var(--muted);letter-spacing:.5px;text-transform:uppercase}
.prop-footer{display:flex;justify-content:space-between;align-items:center;padding-top:10px;border-top:1px solid var(--border);font-size:12px;color:var(--muted)}
.prop-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase}
.badge-available{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.badge-occupied{background:rgba(200,164,60,.1);border:1px solid var(--gb);color:var(--gold)}
.badge-archived{background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--muted)}
 
/* TABLE ROWS */
.ptrow{display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.ptrow:last-child{border-bottom:none}
.trow{display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.trow:last-child{border-bottom:none}
.mrow{display:flex;align-items:flex-start;gap:12px;padding:11px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.mrow:last-child{border-bottom:none}
 
/* BADGES */
.bx{display:inline-flex;align-items:center;padding:2px 8px;border-radius:20px;font-size:9px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;white-space:nowrap}
.bx.paid{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.bx.pending{background:rgba(200,164,60,.1);border:1px solid var(--gb);color:var(--gold)}
.bx.failed{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.bx.active{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.bx.inactive{background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--muted)}
 
/* CHART */
.chart-wrap{display:flex;align-items:flex-end;gap:8px;height:130px;margin-top:10px}
.chart-bar-wrap{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;height:100%}
.chart-bar{width:100%;border-radius:4px 4px 0 0;background:linear-gradient(180deg,var(--gold),rgba(200,164,60,.3));transition:height 1.2s cubic-bezier(.23,1,.32,1);min-height:2px;cursor:pointer;position:relative}
.chart-bar:hover{background:linear-gradient(180deg,var(--gold-l),rgba(224,192,106,.5))}
.chart-tip{display:none;position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);background:rgba(4,9,26,.95);border:1px solid var(--gb);border-radius:6px;padding:6px 10px;font-size:11px;white-space:nowrap;color:var(--white);z-index:10}
.chart-bar:hover .chart-tip{display:block}
.chart-lbl{font-size:10px;color:var(--muted);letter-spacing:.5px}
 
/* VACANCY BAR */
.vac-bar{height:7px;background:rgba(255,255,255,.07);border-radius:3px;overflow:hidden;margin:7px 0}
.vac-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,var(--gold),var(--gold-l));transition:width 1.2s cubic-bezier(.23,1,.32,1)}
.vac-labels{display:flex;justify-content:space-between;font-size:11px;color:var(--muted)}
 
/* ALERT BOXES */
.alert-box{display:flex;align-items:flex-start;gap:12px;padding:12px 16px;border-radius:8px;margin-bottom:12px;font-size:13px;line-height:1.6}
.alert-warn{background:rgba(200,164,60,.06);border:1px solid var(--gb);color:var(--muted)}
.alert-warn strong{color:var(--gold)}
.alert-info{background:rgba(59,130,246,.06);border:1px solid rgba(59,130,246,.2);color:var(--muted)}
.alert-info strong{color:#5b9cff}
.alert-danger{background:rgba(239,68,68,.06);border:1px solid rgba(239,68,68,.2);color:var(--muted)}
.alert-danger strong{color:#fca5a5}
 
/* EMPTY */
.empty{text-align:center;padding:32px;color:var(--muted);font-size:14px}
.empty-icon{font-size:32px;margin-bottom:10px;display:block}
 
@media(max-width:900px){
  :root{--sw:0px}
  .sb{display:none}
  .mc{margin-left:0}
  .tb{left:0}
  .stats-grid{grid-template-columns:1fr 1fr}
  .ch2,.ch3{grid-template-columns:1fr}
  .content{padding:16px}
}
</style>
</head>
<body>
<div id="cur-dot"></div><div id="cur-ring"></div><div id="cur-trail"></div>
 
<!-- SIDEBAR -->
<aside class="sb">
  <div class="sb-head">
    <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--gb),rgba(14,90,200,.3));border:1.5px solid var(--gb);display:flex;align-items:center;justify-content:center;font-size:16px"><img src="image/hme.png" alt="Logo" height="34" width="34"></div>
    <div><div class="sb-logo">Housing Hub</div><div class="sb-sub">Owner Portal</div></div>
  </div>
  <div class="sb-user">
    <div class="sb-av"><?= $initials ?></div>
    <div class="sb-name"><?= $fullname ?></div>
    <span class="sb-role">● Property Owner</span>
  </div>
  <div class="sb-stats">
    <div class="sb-stat"><div class="sb-stat-val" style="color:<?= $pending_pay>0?'#fca5a5':'var(--gold)' ?>"><?= $pending_pay ?></div><div class="sb-stat-lbl">Pending</div></div>
    <div class="sb-stat"><div class="sb-stat-val" style="color:<?= $open_maint>0?'#fca5a5':'#86efac' ?>"><?= $open_maint ?></div><div class="sb-stat-lbl">Maint.</div></div>
    <div class="sb-stat"><div class="sb-stat-val" style="color:<?= $unread_notifs>0?'#fca5a5':'#86efac' ?>"><?= $unread_notifs ?></div><div class="sb-stat-lbl">Alerts</div></div>
  </div>
  <nav class="sb-nav">
    <div class="nl">Overview</div>
    <a href="propertyowner_dashboard.php" class="na <?= $view==='dashboard'?'active':'' ?>"><span class="ni"></span>Dashboard</a>
 
    <div class="nl">My Portfolio</div>
    <a href="propertyowner_dashboard.php?view=properties" class="na <?= $view==='properties'?'active':'' ?>"><span class="ni"></span>My Properties <span class="sb-badge"><?= $total_props ?></span></a>
    <a href="propertyowner_dashboard.php?view=tenants" class="na <?= $view==='tenants'?'active':'' ?>"><span class="ni"></span>My Tenants <span class="sb-badge"><?= $total_tenants ?></span></a>
    <a href="propertyowner_dashboard.php?view=payments" class="na <?= $view==='payments'?'active':'' ?>"><span class="ni"></span>Payments & Revenue <?php if($pending_pay>0):?><span class="sb-badge sb-badge-red"><?= $pending_pay ?></span><?php endif;?></a>
    <a href="propertyowner_dashboard.php?view=maintenance" class="na <?= $view==='maintenance'?'active':'' ?>"><span class="ni"></span>Maintenance <?php if($open_maint>0):?><span class="sb-badge sb-badge-red"><?= $open_maint ?></span><?php endif;?></a>
    <a href="propertyowner_dashboard.php?view=leases" class="na <?= $view==='leases'?'active':'' ?>"><span class="ni"></span>Lease Agreements</a>
    <a href="propertyowner_dashboard.php?view=visitors" class="na <?= $view==='visitors'?'active':'' ?>"><span class="ni"></span>Visitors & Guests</a>
 
    <div class="nl">Reports & Analytics</div>
    <a href="propertyowner_dashboard.php?view=reports" class="na <?= $view==='reports'?'active':'' ?>"><span class="ni"></span>Revenue Reports</a>
    <a href="propertyowner_dashboard.php?view=occupancy" class="na <?= $view==='occupancy'?'active':'' ?>"><span class="ni"></span>Occupancy Trends</a>
 
    <div class="nl">Tenant Relations</div>
    <a href="propertyowner_dashboard.php?view=complaints" class="na <?= $view==='complaints'?'active':'' ?>"><span class="ni"></span>Complaints <?php if($total_complaints>0):?><span class="sb-badge sb-badge-red"><?= $total_complaints ?></span><?php endif;?></a>
    <a href="propertyowner_dashboard.php?view=inspections" class="na <?= $view==='inspections'?'active':'' ?>"><span class="ni"></span>Inspections</a>
    <a href="propertyowner_dashboard.php?view=notifications" class="na <?= $view==='notifications'?'active':'' ?>"><span class="ni"></span>Notifications <?php if($unread_notifs>0):?><span class="sb-badge sb-badge-red"><?= $unread_notifs ?></span><?php endif;?></a>
 
    <div class="nl">Account</div>
    <a href="propertyowner_profile.php" class="na"><span class="ni"></span>My Profile</a>
    <a href="properties.php" class="na"><span class="ni"></span>Browse Properties</a>
    <a href="contact.php" class="na"><span class="ni"></span>Contact Support</a>
  </nav>
  <div class="sb-foot">
    <a href="propertyowner_dashboard.php?view=reports&download=1" class="lo" style="background:rgba(200,164,60,.1);border-color:var(--gb);color:var(--gold);margin-bottom:8px">📥 &nbsp;Download Report</a>
    <a href="logout.php" class="lo">⬡ &nbsp;Sign Out</a>
  </div>
</aside>
 
<!-- TOPBAR -->
<div class="tb">
  <div>
    <div class="tb-title">
      <?php if($view==='dashboard'): ?>Welcome, <?= $fname ?>!
      <?php elseif($view==='properties'): ?>My Properties
      <?php elseif($view==='tenants'): ?>My Tenants
      <?php elseif($view==='payments'): ?>Payments & Revenue
      <?php elseif($view==='maintenance'): ?>Maintenance Requests
      <?php endif; ?>
    </div>
    <div class="tb-sub">HousingHub · Owner Portal · <?= date('l, d F Y') ?></div>
  </div>
  <div style="display:flex;align-items:center;gap:10px">
    <div style="font-size:12px;color:rgba(4,9,26,.7)"><?= $total_props ?> propert<?= $total_props==1?'y':'ies' ?> · <?= $total_tenants ?> tenant<?= $total_tenants==1?'':'s' ?></div>
    <a href="propertyowner_profile.php" style="width:34px;height:34px;border-radius:8px;background:rgba(4,9,26,.15);border:1px solid rgba(4,9,26,.2);display:flex;align-items:center;justify-content:center;font-size:14px;text-decoration:none">👤</a>
    <a href="logout.php" style="padding:8px 16px;background:rgba(4,9,26,.15);border:1px solid rgba(4,9,26,.25);border-radius:6px;color:var(--ink);font-size:11px;font-weight:700;text-decoration:none;letter-spacing:1px">Sign Out</a>
  </div>
</div>
 
<div class="mc"><div class="content">
 
<?php if($view === 'dashboard'): ?>
<!-- ══════════ DASHBOARD ══════════ -->
 
<?php if($pending_pay > 0): ?>
<div class="alert-box alert-danger"><span style="font-size:18px"></span><div><strong><?= $pending_pay ?> pending payment<?= $pending_pay>1?'s':'' ?></strong> totalling UGX <?= number_format($pending_amount) ?> have not been collected yet.</div></div>
<?php endif; ?>
<?php if($open_maint > 0): ?>
<div class="alert-box alert-warn"><span style="font-size:18px"></span><div><strong><?= $open_maint ?> open maintenance request<?= $open_maint>1?'s':'' ?></strong> across your properties are awaiting resolution.</div></div>
<?php endif; ?>
<?php if($total_complaints > 0): ?>
<div class="alert-box alert-info"><span style="font-size:18px"></span><div><strong><?= $total_complaints ?> unresolved complaint<?= $total_complaints>1?'s':'' ?></strong> from your tenants need attention.</div></div>
<?php endif; ?>
 
<!-- STATS ROW 1 -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon"></div>
    <div class="stat-val"><?= $total_props ?></div>
    <div class="stat-lbl">My Properties</div>
    <div class="stat-sub"><?= $total_units ?> total units across all</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon"></div>
    <div class="stat-val"><?= $total_tenants ?></div>
    <div class="stat-lbl">Total Tenants</div>
    <div class="stat-sub"><?= $occupied_units ?> active · <?= $total_units - $occupied_units ?> vacant</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon"></div>
    <div class="stat-val" style="font-size:20px">UGX <?= number_format($total_revenue ?? 0) ?></div>
    <div class="stat-lbl">Total Revenue Collected</div>
    <div class="stat-sub" style="color:#86efac">UGX <?= number_format($this_month_rev) ?> this month</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon"></div>
    <div class="stat-val" style="color:<?= $pending_pay>0?'#fca5a5':'#86efac' ?>"><?= $pending_pay ?></div>
    <div class="stat-lbl">Pending Payments</div>
    <div class="stat-sub" style="color:<?= $pending_pay>0?'#fca5a5':'var(--muted)' ?>">UGX <?= number_format($pending_amount) ?> outstanding</div>
  </div>
</div>
 
<!-- STATS ROW 2 -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon"></div>
    <div class="stat-val" style="color:<?= $vacancy_rate>30?'#fca5a5':'var(--gold)' ?>"><?= $vacancy_rate ?>%</div>
    <div class="stat-lbl">Vacancy Rate</div>
    <div class="stat-sub"><?= $total_units - $occupied_units ?> unit<?= ($total_units-$occupied_units)==1?'':'s' ?> currently empty</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon"></div>
    <div class="stat-val" style="color:#86efac"><?= $occ_rate ?>%</div>
    <div class="stat-lbl">Occupancy Rate</div>
    <div class="stat-sub"><?= $occupied_units ?> of <?= $total_units ?> units filled</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon"></div>
    <div class="stat-val" style="color:<?= $open_maint>0?'#fca5a5':'#86efac' ?>"><?= $open_maint ?></div>
    <div class="stat-lbl">Open Maintenance</div>
    <div class="stat-sub"><?= $done_maint ?> completed total</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon"></div>
    <div class="stat-val" style="color:<?= $total_complaints>0?'#fca5a5':'#86efac' ?>"><?= $total_complaints ?></div>
    <div class="stat-lbl">Open Complaints</div>
    <div class="stat-sub">From your tenants</div>
  </div>
</div>
 
<div class="ch2">
  <!-- REVENUE CHART -->
  <div class="card">
    <div class="card-title"> Revenue — Last 6 Months <a href="propertyowner_dashboard.php?view=payments" class="card-link">Full Report →</a></div>
    <div class="chart-wrap" id="revenue-chart">
      <?php foreach($chart_data as $cd):
        $pct = $max_chart > 0 ? ($cd['value'] / $max_chart) * 100 : 0;
      ?>
      <div class="chart-bar-wrap">
        <div style="flex:1;display:flex;align-items:flex-end;width:100%">
          <div class="chart-bar" data-pct="<?= $pct ?>" style="height:0%;width:100%">
            <div class="chart-tip"><?= $cd['full'] ?><br>UGX <?= number_format($cd['value']) ?><br><?= $cd['count'] ?> payment<?= $cd['count']==1?'':'s' ?></div>
          </div>
        </div>
        <div class="chart-lbl"><?= $cd['label'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="margin-top:16px">
      <div style="font-size:12px;font-weight:600;color:var(--white);margin-bottom:4px">Occupancy Rate · <?= $occ_rate ?>%</div>
      <div class="vac-bar"><div class="vac-fill" id="occ-fill" style="width:0%"></div></div>
      <div class="vac-labels"><span>🟢 <?= $occupied_units ?> occupied</span><span>⚪ <?= $total_units - $occupied_units ?> vacant</span></div>
    </div>
  </div>
 
  <!-- RECENT PAYMENTS -->
  <div class="card">
    <div class="card-title"> Recent Payments <a href="propertyowner_dashboard.php?view=payments" class="card-link">View All →</a></div>
    <?php if(empty($recent_payments)): ?>
      <div class="empty">No payments recorded yet.</div>
    <?php else: foreach(array_slice($recent_payments,0,6) as $rp):
      $st = strtolower($rp['status']??'pending');
    ?>
      <div class="ptrow">
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;color:var(--white);font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($rp['tenant_name']??'—') ?></div>
          <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($rp['property_name']??'—') ?> · <?= htmlspecialchars($rp['payment_method']??'—') ?></div>
        </div>
        <div style="text-align:right;flex-shrink:0">
          <div style="font-size:13px;font-weight:700;color:#86efac">UGX <?= number_format($rp['amount']) ?></div>
          <div style="font-size:11px;color:var(--muted)"><?= $rp['date']?date('d M Y',strtotime($rp['date'])):'' ?></div>
        </div>
        <span class="bx <?= $st ?>"><?= ucfirst($st) ?></span>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>
 
<div class="ch2">
  <!-- TENANTS OVERVIEW -->
  <div class="card">
    <div class="card-title"> Tenants Overview <a href="propertyowner_dashboard.php?view=tenants" class="card-link">View All →</a></div>
    <?php if(empty($tenants_list)): ?>
      <div class="empty">No tenants assigned yet.</div>
    <?php else: foreach(array_slice($tenants_list,0,6) as $t):
      $ts = strtolower($t['status']??'active');
    ?>
      <div class="trow">
        <div style="width:34px;height:34px;border-radius:50%;background:rgba(200,164,60,.1);border:1px solid var(--gb);display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:14px;font-weight:700;color:var(--gold);flex-shrink:0"><?= strtoupper(substr($t['fullname'],0,1)) ?></div>
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;color:var(--white);font-weight:500"><?= htmlspecialchars($t['fullname']) ?></div>
          <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($t['property_name']??'—') ?> · UGX <?= number_format($t['rent_amount']??0) ?>/mo</div>
        </div>
        <div style="text-align:right;flex-shrink:0">
          <div style="font-size:11px;color:var(--muted)"><?= $t['lease_end']?'Ends '.date('d M Y',strtotime($t['lease_end'])):'' ?></div>
          <span class="bx <?= $ts==='active'?'active':'inactive' ?>"><?= ucfirst($ts) ?></span>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
 
  <!-- MAINTENANCE -->
  <div class="card">
    <div class="card-title"> Maintenance <a href="propertyowner_dashboard.php?view=maintenance" class="card-link">View All →</a></div>
    <?php if(empty($recent_maint)): ?>
      <div class="empty"><span class="empty-icon"></span>No maintenance requests. All clear!</div>
    <?php else: foreach(array_slice($recent_maint,0,5) as $m):
      $mst = strtolower($m['status']??'pending');
      $mc  = $mst==='completed'?'#86efac':($mst==='in_progress'?'#5b9cff':'#fca5a5');
    ?>
      <div class="mrow">
        <span style="font-size:18px;flex-shrink:0;margin-top:2px"></span>
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;color:var(--white)"><?= htmlspecialchars($m['issue']??'—') ?></div>
          <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($m['property_name']??'—') ?> · <?= $m['created_at']?date('d M Y',strtotime($m['created_at'])):'' ?></div>
        </div>
        <span class="bx" style="background:rgba(255,255,255,.05);border:1px solid var(--border);color:<?= $mc ?>"><?= ucfirst(str_replace('_',' ',$mst)) ?></span>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>
 
<!-- INFO -->
<div style="background:rgba(200,164,60,.05);border:1px solid var(--gb);border-radius:10px;padding:16px 20px;font-size:13px;color:var(--muted);line-height:1.8">
  <strong style="color:var(--gold)"> About Your Dashboard</strong><br>
  Your properties, tenants, payments, and maintenance are managed by the HousingHub team. To add a property, update details, or raise any concern — contact us at <span style="color:var(--gold)">owners@housinghuborg.ug</span> or call <span style="color:var(--gold)">+256 700 000 000</span>.
</div>
 
<?php elseif($view === 'properties'): ?>
<!-- ══════════ PROPERTIES ══════════ -->
<div class="sec-title">My Properties <span style="font-size:13px;font-family:'Outfit',sans-serif;font-weight:400;color:var(--muted)"><?= $total_props ?> propert<?= $total_props==1?'y':'ies' ?> · <?= $total_units ?> total units</span></div>
<?php if(empty($my_properties)): ?>
  <div class="empty"><span class="empty-icon"></span>No properties listed yet.<br><a href="contact.php" style="color:var(--gold)">Contact us to add your property →</a></div>
<?php else: ?>
  <div class="prop-grid">
    <?php foreach($my_properties as $p):
      $badge_class = strtolower($p['status']??'available')==='occupied'?'badge-occupied':(strtolower($p['status']??'available')==='archived'?'badge-archived':'badge-available');
      $pid = $p['id'];
      $p_tenants = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM tenants WHERE property_id=$pid"))['c']??0;
      $p_active  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM tenants WHERE property_id=$pid AND status='Active'"))['c']??0;
      $p_revenue = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) AS c FROM payments WHERE property_id=$pid AND status='paid'"))['c']??0;
      $p_pending = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) AS c FROM payments WHERE property_id=$pid AND status='pending'"))['c']??0;
      $p_maint   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM maintenance WHERE property_id=$pid AND status!='completed'"))['c']??0;
    ?>
    <div class="prop-card">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px">
        <div>
          <div class="prop-name"><?= htmlspecialchars($p['property_name']) ?></div>
          <div class="prop-addr">📍 <?= htmlspecialchars($p['address']??'—') ?></div>
          <div class="prop-type"><?= htmlspecialchars($p['property_type']??'Residential') ?></div>
        </div>
        <span class="prop-badge <?= $badge_class ?>"><?= ucfirst($p['status']??'Available') ?></span>
      </div>
      <div class="prop-stats">
        <div class="prop-stat"><div class="prop-stat-val"><?= (int)$p['units'] ?></div><div class="prop-stat-lbl">Units</div></div>
        <div class="prop-stat"><div class="prop-stat-val"><?= $p_active ?></div><div class="prop-stat-lbl">Active</div></div>
        <div class="prop-stat"><div class="prop-stat-val"><?= (int)$p['units'] - $p_active ?></div><div class="prop-stat-lbl">Vacant</div></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px">
        <div class="prop-stat"><div class="prop-stat-val" style="font-size:13px;color:#86efac">UGX <?= number_format($p_revenue) ?></div><div class="prop-stat-lbl">Collected</div></div>
        <div class="prop-stat"><div class="prop-stat-val" style="font-size:13px;color:<?= $p_pending>0?'#fca5a5':'var(--muted)' ?>">UGX <?= number_format($p_pending) ?></div><div class="prop-stat-lbl">Pending</div></div>
      </div>
      <div class="prop-footer">
        <span>Rent: <strong style="color:var(--gold)">UGX <?= number_format($p['rent_amount']??0) ?>/mo</strong></span>
        <?php if($p_maint > 0): ?><span style="color:#fca5a5">⚠️ <?= $p_maint ?> maintenance</span><?php endif; ?>
        <?php if($p['bedrooms']): ?><span><?= $p['bedrooms'] ?> bed<?= $p['bedrooms']>1?'s':'' ?></span><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
 
<?php elseif($view === 'tenants'): ?>
<!-- ══════════ TENANTS ══════════ -->
<div class="sec-title"> My Tenants <span style="font-size:13px;font-family:'Outfit',sans-serif;font-weight:400;color:var(--muted)"><?= $total_tenants ?> total · <?= $occupied_units ?> active</span></div>
 
<div class="stats-grid" style="margin-bottom:24px">
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val"><?= $total_tenants ?></div><div class="stat-lbl">Total Tenants</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val" style="color:#86efac"><?= $occupied_units ?></div><div class="stat-lbl">Active</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val" style="color:var(--gold)"><?= $total_units - $occupied_units ?></div><div class="stat-lbl">Vacant Units</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val"><?= $occ_rate ?>%</div><div class="stat-lbl">Occupancy Rate</div></div>
</div>
 
<div class="card">
  <div class="card-title">All Tenants</div>
  <?php if(empty($tenants_list)): ?>
    <div class="empty">No tenants found across your properties.</div>
  <?php else: foreach($tenants_list as $t):
    $ts = strtolower($t['status']??'active');
  ?>
    <div class="trow">
      <div style="width:38px;height:38px;border-radius:50%;background:rgba(200,164,60,.1);border:1px solid var(--gb);display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:16px;font-weight:700;color:var(--gold);flex-shrink:0"><?= strtoupper(substr($t['fullname'],0,1)) ?></div>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;color:var(--white);font-weight:600"><?= htmlspecialchars($t['fullname']) ?></div>
        <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($t['email']??'—') ?> · <?= htmlspecialchars($t['phone']??'—') ?></div>
        <div style="font-size:11px;color:var(--muted)"> <?= htmlspecialchars($t['property_name']??'—') ?></div>
      </div>
      <div style="text-align:right;flex-shrink:0;margin-right:12px">
        <div style="font-size:12px;color:var(--gold);font-weight:600">UGX <?= number_format($t['rent_amount']??0) ?>/mo</div>
        <div style="font-size:11px;color:var(--muted)"><?= $t['lease_start']?date('d M Y',strtotime($t['lease_start'])):'' ?> → <?= $t['lease_end']?date('d M Y',strtotime($t['lease_end'])):'' ?></div>
        <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($t['occupation']??'') ?></div>
      </div>
      <span class="bx <?= $ts==='active'?'active':'inactive' ?>"><?= ucfirst($ts) ?></span>
    </div>
  <?php endforeach; endif; ?>
</div>
 
<?php elseif($view === 'payments'): ?>
<!-- ══════════ PAYMENTS ══════════ -->
<div class="sec-title">Payments & Revenue</div>
 
<div class="stats-grid" style="margin-bottom:24px">
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val" style="font-size:18px">UGX <?= number_format($total_revenue) ?></div><div class="stat-lbl">Total Collected</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val" style="font-size:18px;color:#86efac">UGX <?= number_format($this_month_rev) ?></div><div class="stat-lbl">This Month</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val" style="color:<?= $pending_pay>0?'#fca5a5':'#86efac' ?>"><?= $pending_pay ?></div><div class="stat-lbl">Pending Payments</div><div class="stat-sub" style="color:#fca5a5">UGX <?= number_format($pending_amount) ?> owed</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val"><?= count($recent_payments) ?></div><div class="stat-lbl">Recent Transactions</div></div>
</div>
 
<!-- Revenue Chart -->
<div class="card" style="margin-bottom:20px">
  <div class="card-title">Monthly Revenue — Last 6 Months</div>
  <div class="chart-wrap" id="revenue-chart2">
    <?php foreach($chart_data as $cd):
      $pct = $max_chart > 0 ? ($cd['value'] / $max_chart) * 100 : 0;
    ?>
    <div class="chart-bar-wrap">
      <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;width:100%;gap:4px">
        <div style="font-size:10px;color:var(--muted)">UGX <?= number_format($cd['value']) ?></div>
        <div class="chart-bar" data-pct2="<?= $pct ?>" style="height:0%;width:100%">
          <div class="chart-tip"><?= $cd['full'] ?><br>UGX <?= number_format($cd['value']) ?><br><?= $cd['count'] ?> payment<?= $cd['count']==1?'':'s' ?></div>
        </div>
      </div>
      <div class="chart-lbl"><?= $cd['label'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
 
<div class="card">
  <div class="card-title">All Payment Records</div>
  <?php if(empty($recent_payments)): ?>
    <div class="empty">No payment records found.</div>
  <?php else: foreach($recent_payments as $rp):
    $st = strtolower($rp['status']??'pending');
  ?>
    <div class="ptrow">
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;color:var(--white);font-weight:500"><?= htmlspecialchars($rp['tenant_name']??'—') ?></div>
        <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($rp['property_name']??'—') ?> · <?= htmlspecialchars($rp['payment_method']??'—') ?> <?= $rp['transaction_ref']?'· Ref: '.htmlspecialchars($rp['transaction_ref']):'' ?></div>
      </div>
      <div style="text-align:right;flex-shrink:0;margin-right:10px">
        <div style="font-size:13px;font-weight:700;color:#86efac">UGX <?= number_format($rp['amount']) ?></div>
        <div style="font-size:11px;color:var(--muted)"><?= $rp['date']?date('d M Y',strtotime($rp['date'])):'' ?></div>
      </div>
      <span class="bx <?= $st ?>"><?= ucfirst($st) ?></span>
    </div>
  <?php endforeach; endif; ?>
</div>
 
<?php elseif($view === 'maintenance'): ?>
<!-- ══════════ MAINTENANCE ══════════ -->
<div class="sec-title"> Maintenance Requests</div>
 
<div class="stats-grid" style="margin-bottom:24px">
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val"><?= $open_maint + $done_maint ?></div><div class="stat-lbl">Total Requests</div></div>
  <div class="stat-card"><div class="stat-icon">🔴</div><div class="stat-val" style="color:<?= $open_maint>0?'#fca5a5':'#86efac' ?>"><?= $open_maint ?></div><div class="stat-lbl">Open / Pending</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val" style="color:#86efac"><?= $done_maint ?></div><div class="stat-lbl">Completed</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val"><?= $total_props ?></div><div class="stat-lbl">Properties Monitored</div></div>
</div>
 
<div class="card">
  <div class="card-title">All Maintenance Requests</div>
  <?php if(empty($recent_maint)): ?>
    <div class="empty"><span class="empty-icon">✅</span>No maintenance requests found. All properties are clear!</div>
  <?php else: foreach($recent_maint as $m):
    $mst = strtolower($m['status']??'pending');
    $mc  = $mst==='completed'?'#86efac':($mst==='in_progress'?'#5b9cff':'#fca5a5');
    $mbg = $mst==='completed'?'rgba(22,163,74,.1)':($mst==='in_progress'?'rgba(59,130,246,.1)':'rgba(239,68,68,.1)');
    $mbd = $mst==='completed'?'rgba(22,163,74,.3)':($mst==='in_progress'?'rgba(59,130,246,.3)':'rgba(239,68,68,.3)');
  ?>
    <div class="mrow">
      <span style="font-size:20px;flex-shrink:0;margin-top:2px">🔧</span>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;color:var(--white);font-weight:500"><?= htmlspecialchars($m['issue']??'—') ?></div>
        <div style="font-size:11px;color:var(--muted)"> <?= htmlspecialchars($m['property_name']??'—') ?></div>
        <div style="font-size:11px;color:var(--muted)">Reported: <?= $m['created_at']?date('d M Y, H:i',strtotime($m['created_at'])):'' ?></div>
      </div>
      <span class="bx" style="background:<?= $mbg ?>;border:1px solid <?= $mbd ?>;color:<?= $mc ?>"><?= ucfirst(str_replace('_',' ',$mst)) ?></span>
    </div>
  <?php endforeach; endif; ?>
</div>
 
<?php elseif($view === 'leases'): ?>
<!-- ══════════ LEASES ══════════ -->
<div class="sec-title">📄 Lease Agreements</div>
<div class="stats-grid" style="margin-bottom:24px">
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val"><?= count($leases_list) ?></div><div class="stat-lbl">Total Leases</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val" style="color:#86efac"><?= count(array_filter($leases_list, fn($l)=>strtolower($l['status']??'active')==='active')) ?></div><div class="stat-lbl">Active</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val" style="color:var(--gold)"><?= count(array_filter($leases_list, fn($l)=>$l['lease_end'] && strtotime($l['lease_end']) < strtotime('+30 days'))) ?></div><div class="stat-lbl">Expiring in 30 Days</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val" style="color:#fca5a5"><?= count(array_filter($leases_list, fn($l)=>$l['lease_end'] && strtotime($l['lease_end']) < time())) ?></div><div class="stat-lbl">Expired</div></div>
</div>
<div class="card">
  <div class="card-title">All Lease Agreements</div>
  <?php if(empty($leases_list)): ?>
    <div class="empty">No lease agreements found.</div>
  <?php else: foreach($leases_list as $l):
    $exp    = $l['lease_end'] ? strtotime($l['lease_end']) : null;
    $days   = $exp ? ceil(($exp - time()) / 86400) : null;
    $lc     = $days !== null && $days < 0 ? '#fca5a5' : ($days !== null && $days < 30 ? '#f97316' : '#86efac');
    $ltext  = $days !== null && $days < 0 ? 'Expired' : ($days !== null && $days < 30 ? "Expires in {$days}d" : ($l['lease_end'] ? date('d M Y', $exp) : 'No end date'));
  ?>
    <div class="trow">
      <div style="width:36px;height:36px;border-radius:50%;background:rgba(200,164,60,.1);border:1px solid var(--gb);display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:14px;font-weight:700;color:var(--gold);flex-shrink:0"><?= strtoupper(substr($l['fullname'],0,1)) ?></div>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;color:var(--white);font-weight:500"><?= htmlspecialchars($l['fullname']) ?></div>
        <div style="font-size:11px;color:var(--muted)">🏢 <?= htmlspecialchars($l['property_name']??'—') ?> · UGX <?= number_format($l['rent_amount']??0) ?>/mo</div>
        <div style="font-size:11px;color:var(--muted)">📅 <?= $l['lease_start']?date('d M Y',strtotime($l['lease_start'])):'' ?> → <?= $l['lease_end']?date('d M Y',strtotime($l['lease_end'])):'' ?></div>
      </div>
      <span class="bx" style="background:rgba(255,255,255,.05);border:1px solid var(--border);color:<?= $lc ?>"><?= $ltext ?></span>
    </div>
  <?php endforeach; endif; ?>
</div>
 
<?php elseif($view === 'complaints'): ?>
<!-- ══════════ COMPLAINTS ══════════ -->
<div class="sec-title">💬 Tenant Complaints</div>
<div class="stats-grid" style="margin-bottom:24px">
  <div class="stat-card"><div class="stat-icon">💬</div><div class="stat-val"><?= count($complaints_list) ?></div><div class="stat-lbl">Total Complaints</div></div>
  <div class="stat-card"><div class="stat-icon">🔴</div><div class="stat-val" style="color:#fca5a5"><?= count(array_filter($complaints_list,fn($x)=>strtolower($x['status']??'')==='pending')) ?></div><div class="stat-lbl">Pending</div></div>
  <div class="stat-card"><div class="stat-icon">🔵</div><div class="stat-val" style="color:#5b9cff"><?= count(array_filter($complaints_list,fn($x)=>strtolower($x['status']??'')==='in_progress')) ?></div><div class="stat-lbl">In Progress</div></div>
  <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-val" style="color:#86efac"><?= count(array_filter($complaints_list,fn($x)=>strtolower($x['status']??'')==='resolved')) ?></div><div class="stat-lbl">Resolved</div></div>
</div>
<div class="card">
  <div class="card-title">All Complaints</div>
  <?php if(empty($complaints_list)): ?>
    <div class="empty"><span class="empty-icon">✅</span>No complaints from your tenants. Great!</div>
  <?php else: foreach($complaints_list as $cmp):
    $cst = strtolower($cmp['status']??'pending');
    $cc  = $cst==='resolved'?'#86efac':($cst==='in_progress'?'#5b9cff':'#fca5a5');
    $cbg = $cst==='resolved'?'rgba(22,163,74,.1)':($cst==='in_progress'?'rgba(59,130,246,.1)':'rgba(239,68,68,.1)');
    $cbd = $cst==='resolved'?'rgba(22,163,74,.3)':($cst==='in_progress'?'rgba(59,130,246,.3)':'rgba(239,68,68,.3)');
  ?>
    <div class="mrow">
      <span style="font-size:18px;flex-shrink:0;margin-top:2px">💬</span>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;color:var(--white);font-weight:500"><?= htmlspecialchars($cmp['category']??'General') ?></div>
        <div style="font-size:12px;color:var(--muted);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars(substr($cmp['message']??'',0,80)) ?>...</div>
        <div style="font-size:11px;color:var(--muted)"> <?= htmlspecialchars($cmp['tenant_name']??'—') ?> · <?= htmlspecialchars($cmp['property_name']??'—') ?> · <?= $cmp['created_at']?date('d M Y',strtotime($cmp['created_at'])):'' ?></div>
      </div>
      <span class="bx" style="background:<?= $cbg ?>;border:1px solid <?= $cbd ?>;color:<?= $cc ?>"><?= ucfirst(str_replace('_',' ',$cst)) ?></span>
    </div>
  <?php endforeach; endif; ?>
</div>
 
<?php elseif($view === 'inspections'): ?>
<!-- ══════════ INSPECTIONS ══════════ -->
<div class="sec-title">🔍 Property Inspections</div>
<div class="stats-grid" style="margin-bottom:24px">
  <div class="stat-card"><div class="stat-icon">🔍</div><div class="stat-val"><?= count($inspections_list) ?></div><div class="stat-lbl">Total Inspections</div></div>
  <div class="stat-card"><div class="stat-icon">📅</div><div class="stat-val" style="color:var(--gold)"><?= count(array_filter($inspections_list,fn($x)=>strtolower($x['status']??'')==='scheduled')) ?></div><div class="stat-lbl">Scheduled</div></div>
  <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-val" style="color:#86efac"><?= count(array_filter($inspections_list,fn($x)=>strtolower($x['status']??'')==='completed')) ?></div><div class="stat-lbl">Completed</div></div>
  <div class="stat-card"><div class="stat-icon">🏢</div><div class="stat-val"><?= $total_props ?></div><div class="stat-lbl">Properties</div></div>
</div>
<div class="card">
  <div class="card-title">All Inspections</div>
  <?php if(empty($inspections_list)): ?>
    <div class="empty"><span class="empty-icon">🔍</span>No inspections scheduled yet.</div>
  <?php else: foreach($inspections_list as $ins):
    $ist = strtolower($ins['status']??'scheduled');
    $ic  = $ist==='completed'?'#86efac':($ist==='in_progress'?'#5b9cff':'var(--gold)');
    $ibg = $ist==='completed'?'rgba(22,163,74,.1)':($ist==='in_progress'?'rgba(59,130,246,.1)':'rgba(200,164,60,.1)');
    $ibd = $ist==='completed'?'rgba(22,163,74,.3)':($ist==='in_progress'?'rgba(59,130,246,.3)':'var(--gb)');
  ?>
    <div class="mrow">
      <span style="font-size:20px;flex-shrink:0;margin-top:2px">🔍</span>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;color:var(--white);font-weight:500">🏢 <?= htmlspecialchars($ins['property_name']??'—') ?></div>
        <div style="font-size:11px;color:var(--muted)">Inspector: <?= htmlspecialchars($ins['inspector_name']??'TBA') ?></div>
        <div style="font-size:11px;color:var(--muted)">📅 <?= $ins['inspection_date']?date('d M Y',strtotime($ins['inspection_date'])):'' ?> · Condition: <?= htmlspecialchars($ins['condition']??'N/A') ?></div>
      </div>
      <span class="bx" style="background:<?= $ibg ?>;border:1px solid <?= $ibd ?>;color:<?= $ic ?>"><?= ucfirst($ist) ?></span>
    </div>
  <?php endforeach; endif; ?>
</div>
 
<?php elseif($view === 'visitors'): ?>
<!-- ══════════ VISITORS ══════════ -->
<div class="sec-title"> Visitors & Guests</div>
<div class="stats-grid" style="margin-bottom:24px">
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val"><?= count($visitors_list) ?></div><div class="stat-lbl">Recent Visitors</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val" style="color:#86efac"><?= count(array_filter($visitors_list,fn($x)=>strtolower($x['status']??'')==='approved')) ?></div><div class="stat-lbl">Approved</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val" style="color:var(--gold)"><?= count(array_filter($visitors_list,fn($x)=>strtolower($x['status']??'')==='pending')) ?></div><div class="stat-lbl">Pending</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val"><?= $total_props ?></div><div class="stat-lbl">Properties</div></div>
</div>
<div class="card">
  <div class="card-title">Visitor Log</div>
  <?php if(empty($visitors_list)): ?>
    <div class="empty"><span class="empty-icon"></span>No visitor records found.</div>
  <?php else: foreach($visitors_list as $v):
    $vst = strtolower($v['status']??'pending');
    $vc  = $vst==='approved'?'#86efac':($vst==='rejected'?'#fca5a5':'var(--gold)');
  ?>
    <div class="trow">
      <span style="font-size:20px;flex-shrink:0"></span>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;color:var(--white);font-weight:500"><?= htmlspecialchars($v['visitor_name']??'—') ?></div>
        <div style="font-size:11px;color:var(--muted)">Visiting: <?= htmlspecialchars($v['tenant_name']??'—') ?> · <?= htmlspecialchars($v['property_name']??'—') ?></div>
        <div style="font-size:11px;color:var(--muted)">📅 <?= $v['visit_date']?date('d M Y',strtotime($v['visit_date'])):'' ?> · <?= htmlspecialchars($v['purpose']??'') ?></div>
      </div>
      <span class="bx" style="background:rgba(255,255,255,.05);border:1px solid var(--border);color:<?= $vc ?>"><?= ucfirst($vst) ?></span>
    </div>
  <?php endforeach; endif; ?>
</div>
 
<?php elseif($view === 'notifications'): ?>
<!-- ══════════ NOTIFICATIONS ══════════ -->
<div class="sec-title">🔔 Notifications
  <?php if($unread_notifs > 0): ?>
    <a href="?view=notifications&mark_all=1" style="font-size:12px;color:var(--gold);text-decoration:none;font-family:'Outfit',sans-serif;font-weight:600">Mark All Read</a>
  <?php endif; ?>
</div>
<?php
if (isset($_GET['mark_all'])) {
    mysqli_query($conn, "UPDATE notifications SET is_read=1, status='read' WHERE user_id=$user_id");
    $unread_notifs = 0;
    $notifs_list = [];
    $nq2 = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id=$user_id ORDER BY date DESC LIMIT 30");
    if ($nq2) while ($r = mysqli_fetch_assoc($nq2)) $notifs_list[] = $r;
}
?>
<div class="card">
  <div class="card-title">All Notifications <span style="font-size:12px;font-family:'Outfit',sans-serif;font-weight:400;color:var(--muted)"><?= $unread_notifs ?> unread</span></div>
  <?php if(empty($notifs_list)): ?>
    <div class="empty"><span class="empty-icon">🔔</span>No notifications yet.</div>
  <?php else: foreach($notifs_list as $n):
    $unread = !$n['is_read'];
  ?>
    <div class="mrow" style="<?= $unread?'background:rgba(200,164,60,.04);border-radius:8px;padding:10px 12px;margin:2px -12px;':'' ?>">
      <span style="font-size:18px;flex-shrink:0;margin-top:2px">🔔</span>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;color:var(--white);font-weight:<?= $unread?'600':'400' ?>"><?= htmlspecialchars($n['title']??'Notification') ?></div>
        <div style="font-size:12px;color:var(--muted);margin-top:2px"><?= htmlspecialchars($n['message']??'') ?></div>
        <div style="font-size:11px;color:var(--muted);margin-top:3px"><?= $n['date']?date('d M Y, H:i',strtotime($n['date'])):'' ?></div>
      </div>
      <?php if($unread): ?><span class="bx" style="background:rgba(200,164,60,.1);border:1px solid var(--gb);color:var(--gold)">New</span><?php endif; ?>
    </div>
  <?php endforeach; endif; ?>
</div>
 
<?php elseif($view === 'reports'): ?>
<!-- ══════════ REVENUE REPORTS ══════════ -->
<div class="sec-title">📊 Revenue Reports</div>
<div class="stats-grid" style="margin-bottom:24px">
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val" style="font-size:18px">UGX <?= number_format($total_revenue) ?></div><div class="stat-lbl">All-Time Revenue</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val" style="font-size:18px;color:#86efac">UGX <?= number_format($this_month_rev) ?></div><div class="stat-lbl">This Month</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val" style="color:#fca5a5">UGX <?= number_format($pending_amount) ?></div><div class="stat-lbl">Pending</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val"><?= $total_props ?></div><div class="stat-lbl">Properties</div></div>
</div>
<!-- 6-month chart -->
<div class="card" style="margin-bottom:20px">
  <div class="card-title">📈 Monthly Revenue — Last 6 Months</div>
  <div class="chart-wrap">
    <?php foreach($chart_data as $cd):
      $pct = $max_chart > 0 ? ($cd['value'] / $max_chart) * 100 : 0;
    ?>
    <div class="chart-bar-wrap">
      <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;width:100%;gap:4px">
        <div style="font-size:10px;color:var(--muted)">UGX <?= number_format($cd['value']) ?></div>
        <div class="chart-bar" data-pct="<?= $pct ?>" style="height:0%;width:100%">
          <div class="chart-tip"><?= $cd['full'] ?><br>UGX <?= number_format($cd['value']) ?><br><?= $cd['count'] ?> payment<?= $cd['count']==1?'':'s' ?></div>
        </div>
      </div>
      <div class="chart-lbl"><?= $cd['label'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<!-- Per-property breakdown -->
<div class="card">
  <div class="card-title">Revenue by Property</div>
  <?php foreach($my_properties as $p):
    $pid = $p['id'];
    $prev = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) AS c FROM payments WHERE property_id=$pid AND status='paid'"))['c']??0;
    $ppend = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) AS c FROM payments WHERE property_id=$pid AND status='pending'"))['c']??0;
    $pct2 = $total_revenue > 0 ? round(($prev / $total_revenue) * 100) : 0;
  ?>
    <div style="padding:12px 0;border-bottom:1px solid rgba(255,255,255,.04)">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
        <div>
          <div style="font-size:13px;color:var(--white);font-weight:500"><?= htmlspecialchars($p['property_name']) ?></div>
          <div style="font-size:11px;color:var(--muted)">📍 <?= htmlspecialchars($p['address']??'—') ?></div>
        </div>
        <div style="text-align:right">
          <div style="font-size:13px;color:#86efac;font-weight:600">UGX <?= number_format($prev) ?></div>
          <div style="font-size:11px;color:#fca5a5">UGX <?= number_format($ppend) ?> pending</div>
        </div>
      </div>
      <div style="height:5px;background:rgba(255,255,255,.07);border-radius:3px;overflow:hidden">
        <div style="height:100%;width:<?= $pct2 ?>%;background:linear-gradient(90deg,var(--gold),var(--gold-l));border-radius:3px;transition:width 1s"></div>
      </div>
      <div style="font-size:10px;color:var(--muted);margin-top:3px"><?= $pct2 ?>% of total revenue</div>
    </div>
  <?php endforeach; ?>
</div>
 
<?php elseif($view === 'occupancy'): ?>
<!-- ══════════ OCCUPANCY TRENDS ══════════ -->
<div class="sec-title"> Occupancy Trends</div>
<div class="stats-grid" style="margin-bottom:24px">
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val" style="color:#86efac"><?= $occ_rate ?>%</div><div class="stat-lbl">Current Occupancy</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val" style="color:<?= $vacancy_rate>30?'#fca5a5':'var(--gold)' ?>"><?= $vacancy_rate ?>%</div><div class="stat-lbl">Current Vacancy</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val"><?= $occupied_units ?> / <?= $total_units ?></div><div class="stat-lbl">Units Occupied</div></div>
  <div class="stat-card"><div class="stat-icon"></div><div class="stat-val"><?= $total_props ?></div><div class="stat-lbl">Properties</div></div>
</div>
<div class="card" style="margin-bottom:20px">
  <div class="card-title"> Occupancy & Revenue — Last 12 Months</div>
  <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;font-size:12px">
      <thead><tr>
        <th style="text-align:left;padding:8px 12px;color:var(--gold);font-weight:600;border-bottom:1px solid var(--border);letter-spacing:1px">Month</th>
        <th style="text-align:right;padding:8px 12px;color:var(--gold);font-weight:600;border-bottom:1px solid var(--border)">Active Tenants</th>
        <th style="text-align:right;padding:8px 12px;color:var(--gold);font-weight:600;border-bottom:1px solid var(--border)">Revenue Collected</th>
      </tr></thead>
      <tbody>
        <?php foreach($occ_trend as $ot): ?>
        <tr style="border-bottom:1px solid rgba(255,255,255,.04)">
          <td style="padding:9px 12px;color:var(--white)"><?= $ot['label'] ?></td>
          <td style="padding:9px 12px;text-align:right;color:#86efac"><?= $ot['tenants'] ?></td>
          <td style="padding:9px 12px;text-align:right;color:var(--gold)">UGX <?= number_format($ot['revenue']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<div class="card">
  <div class="card-title">Occupancy by Property</div>
  <?php foreach($my_properties as $p):
    $pid   = $p['id'];
    $pact  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM tenants WHERE property_id=$pid AND status='Active'"))['c']??0;
    $units = (int)$p['units'];
    $pocc  = $units > 0 ? round(($pact/$units)*100) : 0;
    $pocc_color = $pocc >= 80 ? '#86efac' : ($pocc >= 50 ? 'var(--gold)' : '#fca5a5');
  ?>
    <div style="padding:12px 0;border-bottom:1px solid rgba(255,255,255,.04)">
      <div style="display:flex;justify-content:space-between;margin-bottom:6px">
        <div style="font-size:13px;color:var(--white);font-weight:500"><?= htmlspecialchars($p['property_name']) ?></div>
        <div style="font-size:13px;font-weight:700;color:<?= $pocc_color ?>"><?= $pocc ?>% · <?= $pact ?>/<?= $units ?> units</div>
      </div>
      <div style="height:6px;background:rgba(255,255,255,.07);border-radius:3px;overflow:hidden">
        <div style="height:100%;width:<?= $pocc ?>%;background:<?= $pocc_color ?>;border-radius:3px;transition:width 1s"></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
 
<?php endif; ?>
 
</div></div>
 
<script>
// Animate chart bars on dashboard
window.addEventListener('load', () => {
  setTimeout(() => {
    document.querySelectorAll('.chart-bar[data-pct]').forEach(bar => {
      bar.style.height = bar.dataset.pct + '%';
    });
    document.querySelectorAll('.chart-bar[data-pct2]').forEach(bar => {
      bar.style.height = bar.dataset.pct2 + '%';
    });
    const occ = document.getElementById('occ-fill');
    const rate = <?= $total_units > 0 ? round(($occupied_units/$total_units)*100) : 0 ?>;
    if (occ) setTimeout(() => occ.style.width = rate + '%', 200);
  }, 300);
});
 
// Cursor
const dot=document.getElementById('cur-dot'),ring=document.getElementById('cur-ring'),trail=document.getElementById('cur-trail');
let mx=-200,my=-200,rx=-200,ry=-200,tx=-200,ty=-200;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;dot.style.left=mx+'px';dot.style.top=my+'px';});
(function anim(){rx+=(mx-rx)*.15;ry+=(my-ry)*.15;tx+=(mx-tx)*.06;ty+=(my-ty)*.06;ring.style.left=rx+'px';ring.style.top=ry+'px';trail.style.left=tx+'px';trail.style.top=ty+'px';requestAnimationFrame(anim);})();
document.querySelectorAll('a,button,.stat-card,.prop-card,.card,.na,.lo').forEach(el=>{
  el.addEventListener('mouseenter',()=>document.body.classList.add('cursor-hover'));
  el.addEventListener('mouseleave',()=>document.body.classList.remove('cursor-hover'));
});
document.addEventListener('mousedown',()=>document.body.classList.add('cursor-click'));
document.addEventListener('mouseup',()=>document.body.classList.remove('cursor-click'));
</script>
</body>
</html>