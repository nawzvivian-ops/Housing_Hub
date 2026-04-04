<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'broker') {
    header("Location: index.php"); exit();
}

$user_id   = (int)$_SESSION['user_id'];
$user_name = $_SESSION['fullname'] ?? 'Broker';
$page      = $_GET['view'] ?? 'overview';

$u_q = mysqli_prepare($conn, "SELECT * FROM users WHERE id=? LIMIT 1");
mysqli_stmt_bind_param($u_q, "i", $user_id);
mysqli_stmt_execute($u_q);
$user     = mysqli_fetch_assoc(mysqli_stmt_get_result($u_q));
$fullname = htmlspecialchars($user['fullname'] ?? $user_name);
$email    = htmlspecialchars($user['email']    ?? '');
$phone    = htmlspecialchars($user['phone']    ?? 'Not set');
$parts    = explode(' ', $fullname, 2);
$initials = strtoupper(substr($parts[0],0,1).substr($parts[1]??'',0,1));
$commission_rate = (float)($user['commission_rate'] ?? 10);

// ── Stats ──
$my_props_count = 0;
$r = mysqli_query($conn, "SHOW COLUMNS FROM properties LIKE 'broker_id'");
if ($r && mysqli_num_rows($r) > 0) {
    $my_props_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM properties WHERE broker_id=$user_id"))['c'] ?? 0;
}

$total_commission = 0; $deals_closed = 0;
$r2 = mysqli_query($conn, "SHOW COLUMNS FROM properties LIKE 'broker_id'");
if ($r2 && mysqli_num_rows($r2) > 0) {
    $cm = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT SUM(pay.amount * $commission_rate / 100) AS total, COUNT(*) AS deals
         FROM payments pay JOIN properties pr ON pay.property_id=pr.id
         WHERE pr.broker_id=$user_id AND pay.status IN ('paid','completed')"));
    $total_commission = (float)($cm['total'] ?? 0);
    $deals_closed     = (int)($cm['deals']   ?? 0);
}

$pending_viewings = 0;
$r3 = mysqli_query($conn, "SHOW TABLES LIKE 'property_viewing_requests'");
if ($r3 && mysqli_num_rows($r3) > 0) {
    $pending_viewings = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) AS c FROM property_viewing_requests WHERE status='pending'"))['c'] ?? 0;
}

$pending_apps = 0;
$r4 = mysqli_query($conn, "SHOW TABLES LIKE 'tenant_applications'");
if ($r4 && mysqli_num_rows($r4) > 0 && $my_props_count > 0) {
    $pending_apps = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) AS c FROM tenant_applications ta
         JOIN properties pr ON ta.property_id=pr.id
         WHERE pr.broker_id=$user_id AND ta.status='pending'"))['c'] ?? 0;
}

$unread_notifs = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS c FROM notifications WHERE user_id=$user_id AND (is_read=0 OR status='unread')"))['c'] ?? 0;

// Monthly earnings for chart (last 6 months)
$monthly_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month_start = date('Y-m-01', strtotime("-$i months"));
    $month_end   = date('Y-m-t', strtotime("-$i months"));
    $label       = date('M', strtotime("-$i months"));
    $val = 0;
    $r_bi = mysqli_query($conn,"SHOW COLUMNS FROM properties LIKE 'broker_id'");
    if ($r_bi && mysqli_num_rows($r_bi) > 0) {
        $res = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT SUM(pay.amount * $commission_rate / 100) AS total
             FROM payments pay JOIN properties pr ON pay.property_id=pr.id
             WHERE pr.broker_id=$user_id AND pay.status IN ('paid','completed')
             AND pay.date BETWEEN '$month_start' AND '$month_end'"));
        $val = (float)($res['total'] ?? 0);
    }
    $monthly_data[] = ['label' => $label, 'value' => $val];
}

// Broker notes (create table if not exists)
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS broker_client_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    broker_id INT NOT NULL,
    client_name VARCHAR(200) NOT NULL,
    note TEXT NOT NULL,
    created_at DATETIME DEFAULT NOW()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Handle note save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_note'])) {
    $cname = mysqli_real_escape_string($conn, trim($_POST['client_name'] ?? ''));
    $cnote = mysqli_real_escape_string($conn, trim($_POST['note'] ?? ''));
    if ($cname && $cnote) {
        mysqli_query($conn, "INSERT INTO broker_client_notes (broker_id, client_name, note) VALUES ($user_id, '$cname', '$cnote')");
        $_SESSION['broker_success'] = "Note saved.";
    }
    header("Location: broker_dashboard.php?view=notes"); exit();
}
if (isset($_GET['delete_note'])) {
    $nid = (int)$_GET['delete_note'];
    mysqli_query($conn, "DELETE FROM broker_client_notes WHERE id=$nid AND broker_id=$user_id");
    header("Location: broker_dashboard.php?view=notes"); exit();
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $cur = $_POST['current_password'] ?? '';
    $new = $_POST['new_password']     ?? '';
    $con = $_POST['confirm_password'] ?? '';
    if (!password_verify($cur, $user['password'])) {
        $_SESSION['broker_error'] = "Current password is incorrect.";
    } elseif (strlen($new) < 6) {
        $_SESSION['broker_error'] = "New password must be at least 6 characters.";
    } elseif ($new !== $con) {
        $_SESSION['broker_error'] = "Passwords do not match.";
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hash' WHERE id=$user_id");
        $_SESSION['broker_success'] = "Password updated successfully.";
    }
    header("Location: broker_dashboard.php?view=profile"); exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Broker Dashboard | HousingHub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25);--red:#ef4444;--green:#16a34a;--sw:265px}
html,body{height:100%;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden}
body::before{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(200,164,60,.1),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(14,90,200,.1),transparent 50%)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
.sb{width:var(--sw);background:rgba(4,9,26,.98);border-right:1px solid var(--border);position:fixed;top:0;left:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column;z-index:500}
.sb::-webkit-scrollbar{width:3px}.sb::-webkit-scrollbar-thumb{background:var(--gb);border-radius:2px}
.sb-head{padding:24px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.sb-logo{font-family:"Cormorant Garamond",serif;font-size:17px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--white)}
.sb-logo-sub{font-size:9px;color:var(--muted);letter-spacing:1px}
.sb-logo-icon{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.3),rgba(14,90,200,.3));border:1.5px solid var(--gb);display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}
.sb-user{padding:16px 20px;border-bottom:1px solid var(--border)}
.sb-av{width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.4),rgba(14,90,200,.3));border:2px solid var(--gb);display:flex;align-items:center;justify-content:center;font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;color:var(--white);margin-bottom:10px}
.sb-name{font-size:13px;font-weight:600;color:var(--white);margin-bottom:2px}
.sb-role{font-size:11px;color:var(--muted)}
.sb-verified{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.25);border-radius:20px;font-size:9px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#86efac;margin-top:7px}
.sb-nav{padding:14px 0;flex:1}
.nl{font-size:9px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.18);padding:0 20px;margin-bottom:5px;margin-top:16px}
.nl:first-child{margin-top:0}
.na{display:flex;align-items:center;gap:10px;padding:10px 20px;font-size:13px;font-weight:500;color:var(--muted);border:none;background:none;width:100%;text-align:left;cursor:pointer;transition:all .2s;position:relative;text-decoration:none}
.na:hover{color:var(--white);background:rgba(255,255,255,.04)}
.na.active{color:var(--gold);background:rgba(200,164,60,.08)}
.na.active::before{content:"";position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:60%;background:var(--gold);border-radius:0 2px 2px 0}
.ni{font-size:16px;width:22px;text-align:center;flex-shrink:0}
.nbadge{margin-left:auto;padding:2px 7px;background:var(--red);color:var(--white);border-radius:10px;font-size:9px;font-weight:700}
.nbadge.gold{background:var(--gold);color:var(--ink)}
.sb-foot{padding:16px 20px;border-top:1px solid var(--border)}
.lo{width:100%;padding:10px;background:rgba(255,95,87,.07);border:1px solid rgba(255,95,87,.2);color:#ff8f8a;font-family:"Outfit",sans-serif;font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;border-radius:6px;cursor:pointer;transition:all .25s;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none}
.lo:hover{background:rgba(255,95,87,.14)}
.mc{margin-left:var(--sw);display:flex;flex-direction:column;min-height:100vh;position:relative;z-index:10}
.tb{display:flex;align-items:center;justify-content:space-between;padding:15px 36px;background:var(--gold);border-bottom:1px solid rgba(0,0,0,.12);position:sticky;top:0;z-index:100;box-shadow:0 2px 20px rgba(0,0,0,.25)}
.tb-left{display:flex;align-items:center;gap:14px}
.tb-title{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--ink)}
.tb-sub{font-size:10px;color:rgba(4,9,26,.55);letter-spacing:1px}
.tb-right{display:flex;align-items:center;gap:10px}
.tb-btn{width:34px;height:34px;border-radius:8px;background:rgba(4,9,26,.15);border:1px solid rgba(4,9,26,.2);display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;position:relative;color:var(--ink);text-decoration:none;transition:all .2s}
.tb-btn:hover{background:rgba(4,9,26,.25)}
.nd{position:absolute;top:5px;right:5px;width:7px;height:7px;background:var(--red);border-radius:50%;border:1.5px solid var(--gold)}
.tb-logout{padding:8px 16px;background:rgba(4,9,26,.15);border:1px solid rgba(4,9,26,.25);color:var(--ink);font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;border-radius:6px;text-decoration:none;transition:all .2s}
.tb-logout:hover{background:rgba(4,9,26,.25)}
.pg{display:none;padding:28px 36px;flex:1;animation:fu .4s ease both}
.pg.active{display:block}
@keyframes fu{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
.ey{font-size:10px;font-weight:600;letter-spacing:3.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px;display:flex;align-items:center;gap:10px}
.ey::before{content:"";width:18px;height:1px;background:var(--gold)}
.sh{font-family:"Cormorant Garamond",serif;font-size:clamp(22px,2.8vw,32px);font-weight:700;color:var(--white);margin-bottom:6px;line-height:1.1}
.sh em{color:var(--gold);font-style:italic}
.sp{font-size:13px;color:var(--muted);margin-bottom:24px;line-height:1.65}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:22px;transition:border-color .3s}
.card:hover{border-color:var(--gb)}
.ch2{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
.ch3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px}
.ch4{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
.ct{font-family:"Cormorant Garamond",serif;font-size:17px;font-weight:700;color:var(--white)}
.chead{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.ca{font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--gold);cursor:pointer;border:none;background:none;text-decoration:none}
.mc-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:20px;transition:all .35s}
.mc-card:hover{border-color:var(--gb);background:rgba(200,164,60,.04);transform:translateY(-3px)}
.mc-icon{font-size:24px;margin-bottom:10px}
.mc-val{font-family:"Cormorant Garamond",serif;font-size:30px;font-weight:700;color:var(--white);line-height:1}
.mc-val.g{color:var(--gold)}.mc-val.gr{color:#86efac}.mc-val.r{color:#fca5a5}
.mc-lbl{font-size:11px;color:var(--muted);letter-spacing:1px;margin-top:4px}
.mc-sub{font-size:11px;color:#86efac;margin-top:5px}
.mc-sub.n{color:#fca5a5}
.wb{background:linear-gradient(135deg,rgba(200,164,60,.1),rgba(14,90,200,.08));border:1px solid var(--border);border-radius:14px;padding:24px 28px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:20px}
.wb h2{font-family:"Cormorant Garamond",serif;font-size:24px;font-weight:700;color:var(--white);margin-bottom:5px}
.wb h2 em{color:var(--gold);font-style:italic}
.wb p{font-size:13px;color:var(--muted);line-height:1.6}
.ai{display:flex;gap:12px;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--border)}
.ai:last-child{border-bottom:none;margin-bottom:0;padding-bottom:0}
.ad{width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,.05);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0}
.at{font-size:13px;color:rgba(255,255,255,.8);margin-bottom:2px}
.at strong{color:var(--white)}
.atm{font-size:11px;color:var(--muted)}
.dt{width:100%;border-collapse:collapse}
.dt th{font-size:9px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--muted);padding:8px 12px;border-bottom:1px solid var(--border);text-align:left;white-space:nowrap}
.dt td{font-size:12px;color:rgba(255,255,255,.8);padding:13px 12px;border-bottom:1px solid rgba(255,255,255,.04)}
.dt tr:last-child td{border-bottom:none}
.dt tr:hover td{background:rgba(255,255,255,.02)}
.bx{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:9px;font-weight:700;letter-spacing:.5px;text-transform:uppercase}
.bx.green{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.25);color:#86efac}
.bx.gold{background:rgba(200,164,60,.1);border:1px solid var(--gb);color:var(--gold)}
.bx.blue{background:rgba(14,90,200,.1);border:1px solid rgba(14,90,200,.25);color:#5b9cff}
.bx.red{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#fca5a5}
.prop-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px}
.prop-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;overflow:hidden;transition:all .35s}
.prop-card:hover{border-color:var(--gb);transform:translateY(-4px)}
.prop-img{height:140px;background:linear-gradient(135deg,rgba(200,164,60,.15),rgba(14,90,200,.15));display:flex;align-items:center;justify-content:center;font-size:44px;border-bottom:1px solid var(--border)}
.prop-body{padding:16px}
.prop-name{font-family:"Cormorant Garamond",serif;font-size:17px;font-weight:700;color:var(--white);margin-bottom:4px}
.prop-addr{font-size:12px;color:var(--muted);margin-bottom:10px}
.prop-meta{display:flex;justify-content:space-between;align-items:center}
.prop-rent{font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;color:var(--gold)}
.prop-type{font-size:10px;color:var(--muted);letter-spacing:1px;text-transform:uppercase}
.prop-action{display:block;width:100%;padding:9px;background:rgba(200,164,60,.1);border:1px solid var(--gb);border-radius:6px;color:var(--gold);font-size:11px;font-weight:700;text-align:center;cursor:pointer;transition:all .25s;margin-top:12px;text-decoration:none}
.prop-action:hover{background:rgba(200,164,60,.2)}
.fl{margin-bottom:14px}
.fl label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.fl input,.fl select,.fl textarea{width:100%;padding:11px 13px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:6px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .25s}
.fl input:focus,.fl select:focus,.fl textarea:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
.fl input::placeholder,.fl textarea::placeholder{color:var(--muted)}
.fl select option{background:var(--ink)}
.fl textarea{resize:vertical;min-height:85px}
.sbtn{width:100%;padding:12px;background:var(--gold);color:var(--ink);font-family:"Outfit",sans-serif;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;border:none;border-radius:6px;cursor:pointer;transition:all .3s;margin-top:4px}
.sbtn:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 8px 22px rgba(200,164,60,.3)}
.ph{display:flex;align-items:center;gap:20px;padding:24px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:14px;margin-bottom:18px}
.pav{width:68px;height:68px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.5),rgba(14,90,200,.4));border:2px solid var(--gb);display:flex;align-items:center;justify-content:center;font-family:"Cormorant Garamond",serif;font-size:26px;font-weight:700;color:var(--white);flex-shrink:0}
.pgrid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px}
.pf{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:13px}
.pfl{font-size:9px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:5px}
.pfv{font-size:13px;color:var(--white)}
.earn-big{font-family:"Cormorant Garamond",serif;font-size:44px;font-weight:700;color:var(--gold);line-height:1}
.earn-sub{font-size:12px;color:var(--muted);margin:4px 0 14px}
.earn-tier{display:inline-flex;align-items:center;gap:8px;padding:5px 14px;background:rgba(200,164,60,.1);border:1px solid var(--gb);border-radius:20px;font-size:11px;font-weight:600;color:var(--gold)}
.alert{padding:12px 18px;border-radius:8px;margin-bottom:18px;font-size:13px;font-weight:500}
.alert.s{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.alert.e{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.ni-item{display:flex;gap:12px;padding:13px;border-radius:8px;transition:background .2s;border-bottom:1px solid rgba(255,255,255,.04)}
.ni-item:last-child{border-bottom:none}
.ni-item.unread{background:rgba(200,164,60,.04)}
.ni-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;background:rgba(255,255,255,.04);border:1px solid var(--border)}
.ni-t{font-size:13px;font-weight:600;color:var(--white);margin-bottom:3px}
.ni-item.unread .ni-t::after{content:"●";font-size:6px;color:var(--gold);margin-left:6px;vertical-align:middle}
.ni-m{font-size:12px;color:var(--muted);line-height:1.6;margin-bottom:3px}
.ni-d{font-size:10px;color:rgba(255,255,255,.2)}
.pipeline{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px}
.pipe-col{background:rgba(255,255,255,.02);border:1px solid var(--border);border-radius:10px;padding:14px}
.pipe-col-h{font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
.pipe-item{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:12px;margin-bottom:10px;transition:border-color .25s}
.pipe-item:hover{border-color:var(--gb)}
.pipe-item:last-child{margin-bottom:0}
.pipe-name{font-size:13px;font-weight:600;color:var(--white);margin-bottom:3px}
.pipe-prop{font-size:11px;color:var(--muted)}
.pipe-date{font-size:10px;color:rgba(255,255,255,.2);margin-top:5px}
.qa-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.qa-btn{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:18px;text-align:center;cursor:pointer;transition:all .3s;text-decoration:none;display:block}
.qa-btn:hover{border-color:var(--gb);background:rgba(200,164,60,.06);transform:translateY(-3px)}
.qa-icon{font-size:26px;margin-bottom:8px}
.qa-label{font-size:11px;font-weight:600;color:var(--muted);letter-spacing:.5px}
.qa-btn:hover .qa-label{color:var(--white)}
/* AI ASSISTANT */
.ai-box{background:rgba(200,164,60,.04);border:1px solid var(--gb);border-radius:14px;padding:20px;margin-bottom:20px}
.ai-msgs{max-height:260px;overflow-y:auto;margin-bottom:12px;display:flex;flex-direction:column;gap:8px}
.ai-msgs::-webkit-scrollbar{width:3px}.ai-msgs::-webkit-scrollbar-thumb{background:var(--gb);border-radius:2px}
.msg-user{align-self:flex-end;background:var(--gold);color:var(--ink);padding:9px 14px;border-radius:12px 12px 2px 12px;font-size:13px;max-width:80%}
.msg-ai{align-self:flex-start;background:rgba(255,255,255,.06);border:1px solid var(--border);padding:9px 14px;border-radius:2px 12px 12px 12px;font-size:13px;color:rgba(255,255,255,.85);max-width:85%;line-height:1.55}
.ai-input-row{display:flex;gap:8px}
.ai-input{flex:1;padding:10px 13px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:8px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none}
.ai-input:focus{border-color:var(--gb)}
.ai-send{padding:10px 18px;background:var(--gold);color:var(--ink);border:none;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;transition:background .2s}
.ai-send:hover{background:var(--gold-l)}
/* NOTE CARDS */
.note-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:12px;transition:border-color .25s}
.note-card:hover{border-color:var(--gb)}
.note-client{font-size:13px;font-weight:600;color:var(--white);margin-bottom:5px}
.note-text{font-size:12px;color:var(--muted);line-height:1.6;margin-bottom:8px}
.note-date{font-size:10px;color:rgba(255,255,255,.2)}
/* SUSPENSION NOTICE */
.suspended-banner{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:10px;padding:14px 18px;margin-bottom:20px;font-size:13px;color:#fca5a5;display:flex;align-items:center;gap:10px}
@media(max-width:960px){
  :root{--sw:0px}
  .sb{display:none}
  .mc{margin-left:0}
  .ch4,.ch3,.ch2,.pipeline,.pgrid,.qa-grid{grid-template-columns:1fr}
  .pg{padding:16px}
  .tb{padding:12px 16px}
  .prop-grid{grid-template-columns:1fr}
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sb">
  <div class="sb-head">
    <div class="sb-logo-icon">💼</div>
    <div><div class="sb-logo">HOUSING HUB</div><div class="sb-logo-sub">Broker Portal</div></div>
  </div>
  <div class="sb-user">
    <div class="sb-av"><?= $initials ?></div>
    <div class="sb-name"><?= $fullname ?></div>
    <div class="sb-role"><?= $email ?></div>
    <div class="sb-verified">✓ Verified Broker</div>
  </div>
  <nav class="sb-nav">
    <div class="nl">Main</div>
    <button class="na <?= $page==='overview'?'active':'' ?>" onclick="show('overview',this)"><span class="ni"></span>Overview</button>
    <button class="na <?= $page==='properties'?'active':'' ?>" onclick="show('properties',this)"><span class="ni"></span>Available Properties</button>
    <button class="na <?= $page==='my_listings'?'active':'' ?>" onclick="show('my_listings',this)"><span class="ni"></span>My Listings</button>
    <div class="nl">Business</div>
    <button class="na <?= $page==='commissions'?'active':'' ?>" onclick="show('commissions',this)"><span class="ni"></span>Commissions & Earnings</button>
    <button class="na <?= $page==='pipeline'?'active':'' ?>" onclick="show('pipeline',this)"><span class="ni"></span>Client Pipeline<?php if($pending_apps>0): ?><span class="nbadge"><?=$pending_apps?></span><?php endif; ?></button>
    <button class="na <?= $page==='viewings'?'active':'' ?>" onclick="show('viewings',this)"><span class="ni"></span>Viewing Requests<?php if($pending_viewings>0): ?><span class="nbadge"><?=$pending_viewings?></span><?php endif; ?></button>
    <button class="na <?= $page==='notes'?'active':'' ?>" onclick="show('notes',this)"><span class="ni"></span>Client Notes</button>
    <div class="nl">Tools</div>
    <button class="na <?= $page==='insights'?'active':'' ?>" onclick="show('insights',this)"><span class="ni"></span>Market Insights</button>
    <div class="nl">Account</div>
    <button class="na <?= $page==='notifications'?'active':'' ?>" onclick="show('notifications',this)"><span class="ni"></span>Notifications<?php if($unread_notifs>0): ?><span class="nbadge"><?=$unread_notifs?></span><?php endif; ?></button>
    <button class="na <?= $page==='profile'?'active':'' ?>" onclick="show('profile',this)"><span class="ni"></span>My Profile</button>
  </nav>
  <div class="sb-foot"><a href="logout.php" class="lo">Sign Out</a></div>
</aside>

<!-- MAIN -->
<div class="mc">
  <div class="tb">
    <div class="tb-left">
      <div>
        <div class="tb-title" id="tb-title">Overview</div>
        <div class="tb-sub">HousingHub · Broker Dashboard</div>
      </div>
    </div>
    <div class="tb-right">
      <div class="tb-btn" onclick="show('notifications',null)">🔔<?php if($unread_notifs>0): ?><span class="nd"></span><?php endif; ?></div>
      <div class="tb-btn" onclick="show('profile',null)">👤</div>
      <a href="logout.php" class="tb-logout">Sign Out</a>
    </div>
  </div>

  <!-- ══ OVERVIEW ══ -->
  <div class="pg <?= $page==='overview'?'active':'' ?>" id="pg-overview">
    <?php if(isset($_SESSION['broker_success'])): ?>
      <div class="alert s">✅ <?= htmlspecialchars($_SESSION['broker_success']) ?></div>
      <?php unset($_SESSION['broker_success']); ?>
    <?php endif; ?>
    <?php if(isset($_SESSION['broker_error'])): ?>
      <div class="alert e">⚠️ <?= htmlspecialchars($_SESSION['broker_error']) ?></div>
      <?php unset($_SESSION['broker_error']); ?>
    <?php endif; ?>

    <?php if(!empty($user['status']) && $user['status'] === 'suspended'): ?>
    <div class="suspended-banner">🚫 Your account is currently suspended. Contact the admin for assistance.</div>
    <?php endif; ?>

    <div class="wb">
      <div>
        <h2>Good day, <em><?= explode(' ', $fullname)[0] ?></em>!</h2>
        <p>Your broker account is active. Here's your current business snapshot.</p>
      </div>
      <div style="font-size:50px;opacity:.8;flex-shrink:0">💼</div>
    </div>

    <div class="ch4">
      <div class="mc-card">
        <div class="mc-icon"></div>
        <div class="mc-val g"><?= $my_props_count ?></div>
        <div class="mc-lbl">My Listed Properties</div>
        <div class="mc-sub">Active inventory</div>
      </div>
      <div class="mc-card">
        <div class="mc-icon"></div>
        <div class="mc-val gr">UGX <?= number_format($total_commission) ?></div>
        <div class="mc-lbl">Total Commissions</div>
        <div class="mc-sub">Lifetime earnings</div>
      </div>
      <div class="mc-card">
        <div class="mc-icon"></div>
        <div class="mc-val"><?= $deals_closed ?></div>
        <div class="mc-lbl">Deals Closed</div>
        <div class="mc-sub"><?= $commission_rate ?>% commission rate</div>
      </div>
      <div class="mc-card">
        <div class="mc-icon"></div>
        <div class="mc-val <?= $pending_viewings>0?'g':'' ?>"><?= $pending_viewings ?></div>
        <div class="mc-lbl">Pending Viewings</div>
        <div class="mc-sub <?= $pending_viewings>0?'n':'' ?>"><?= $pending_viewings>0?'Needs attention':'All clear' ?></div>
      </div>
    </div>

    <div class="qa-grid">
      <a class="qa-btn" onclick="show('properties',null)"><div class="qa-icon"></div><div class="qa-label">Browse Properties</div></a>
      <a class="qa-btn" onclick="show('pipeline',null)"><div class="qa-icon"></div><div class="qa-label">View Clients</div></a>
      <a class="qa-btn" onclick="show('commissions',null)"><div class="qa-icon"></div><div class="qa-label">My Earnings</div></a>
    </div>

    <!-- Earnings Chart + Activity -->
    <div class="ch2">
      <div class="card">
        <div class="chead"><div class="ct">6-Month Earnings</div><span style="font-size:11px;color:var(--muted)">Commission trend</span></div>
        <canvas id="earningsChart" height="160"></canvas>
      </div>
      <div class="card">
        <div class="chead"><div class="ct">Recent Activity</div></div>
        <?php
        $notifs_q = mysqli_query($conn,"SELECT title, message, date FROM notifications WHERE user_id=$user_id ORDER BY date DESC LIMIT 6");
        $has_notif = false;
        if ($notifs_q) {
          while ($n = mysqli_fetch_assoc($notifs_q)) {
            $has_notif = true;
            echo '<div class="ai"><div class="ad">🔔</div><div><div class="at"><strong>'.htmlspecialchars($n['title']).'</strong></div><div class="at" style="font-weight:400;font-size:12px">'.htmlspecialchars(substr($n['message']??'',0,80)).'</div><div class="atm">'.($n['date']?date('d M Y, H:i',strtotime($n['date'])):'—').'</div></div></div>';
          }
        }
        if (!$has_notif):
        ?>
        <div class="ai"><div class="ad">👋</div><div><div class="at"><strong>Welcome to your Broker Panel!</strong></div><div class="atm">Your activity will appear here as you work deals.</div></div></div>
        <div class="ai"><div class="ad">✅</div><div><div class="at"><strong>Account Verified</strong></div><div class="atm">You have full broker access.</div></div></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Commission Summary -->
    <div class="ch2">
      <div class="card">
        <div class="chead"><div class="ct">Commission Summary</div></div>
        <div class="earn-big">UGX <?= number_format($total_commission) ?></div>
        <div class="earn-sub">Total earned · <?= $commission_rate ?>% rate</div>
        <div class="earn-tier">⭐ <?= $deals_closed < 5 ? 'New Broker' : ($deals_closed < 20 ? 'Silver Tier' : 'Gold Tier') ?></div>
        <div style="margin-top:20px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div style="text-align:center;padding:12px;background:rgba(22,163,74,.06);border:1px solid rgba(22,163,74,.15);border-radius:8px">
            <div style="font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:700;color:#86efac"><?= $deals_closed ?></div>
            <div style="font-size:10px;color:var(--muted);margin-top:3px">Deals Closed</div>
          </div>
          <div style="text-align:center;padding:12px;background:rgba(200,164,60,.06);border:1px solid var(--gb);border-radius:8px">
            <div style="font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:700;color:var(--gold)"><?= $commission_rate ?>%</div>
            <div style="font-size:10px;color:var(--muted);margin-top:3px">Commission Rate</div>
          </div>
        </div>
        <div style="margin-top:14px;padding:10px;background:rgba(14,90,200,.06);border:1px solid rgba(14,90,200,.2);border-radius:8px;font-size:12px;color:var(--muted)">
          🚀 Close <?= max(0, 5 - $deals_closed) ?> more deals to reach <?= $deals_closed < 5 ? 'Silver' : ($deals_closed < 20 ? 'Gold' : 'Platinum') ?> tier.
        </div>
      </div>
      <div class="card">
        <div class="chead"><div class="ct">Available Properties Preview</div><button class="ca" onclick="show('properties',null)">View All →</button></div>
        <?php
        $prev_props = mysqli_query($conn,"SELECT property_name, property_type, address, rent_amount, status FROM properties ORDER BY created_at DESC LIMIT 4");
        if ($prev_props && mysqli_num_rows($prev_props) > 0): ?>
        <table class="dt">
          <thead><tr><th>Property</th><th>Rent</th><th>Status</th></tr></thead>
          <tbody>
          <?php while($pr = mysqli_fetch_assoc($prev_props)): ?>
          <tr>
            <td><div style="font-weight:600"><?= htmlspecialchars($pr['property_name']) ?></div><div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($pr['address']??'—') ?></div></td>
            <td style="color:var(--gold)"><?= number_format($pr['rent_amount']??0) ?></td>
            <td><span class="bx <?= strtolower($pr['status']??'')=='available'?'green':'gold' ?>"><?= htmlspecialchars($pr['status']??'Unknown') ?></span></td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
        <?php else: ?>
        <div style="text-align:center;padding:30px;color:var(--muted);font-size:13px">No properties yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ══ AVAILABLE PROPERTIES ══ -->
  <div class="pg <?= $page==='properties'?'active':'' ?>" id="pg-properties">
    <div class="card" style="margin-bottom:20px; padding:20px; background:rgba(255,255,255,.05); border:1px solid var(--border); border-radius:12px;">
  <h2 style="margin-bottom:10px;">Add New Property</h2>
  <form action="submit_property.php" method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:10px;">
    <div class="fl">
      <label>Property Name</label>
      <input type="text" name="property_name" placeholder="e.g. Sunny Villa" required>
    </div>
    <div class="fl">
      <label>Property Type</label>
      <input type="text" name="property_type" placeholder="e.g. Residential" required>
    </div>
    <div class="fl">
      <label>Address</label>
      <input type="text" name="address" required>
    </div>
    <div class="fl">
      <label>Units</label>
      <input type="number" name="units" required>
    </div>
    <div class="fl">
      <label>Rent Amount (UGX)</label>
      <input type="number" step="0.01" name="rent_amount" required>
    </div>
    <div class="fl">
      <label>Transaction Type</label>
      <select name="purpose" required>
        <option value="">Select Type</option>
        <option value="rent">For Rent</option>
        <option value="buy">For Buy</option>
        <option value="lease">For Lease</option>
      </select>
    </div>
    <div class="fl">
      <label>Bedrooms</label>
      <input type="number" name="bedrooms" required>
    </div>
    <div class="fl">
      <label>Size (sqft)</label>
      <input type="number" name="size_sqft" required>
    </div>
    <div class="fl">
      <label>Amenities</label>
      <textarea name="amenities" placeholder="e.g. Swimming pool, Gym"></textarea>
    </div>
    <div class="fl">
      <label>Latitude</label>
      <input type="text" name="latitude" required>
    </div>
    <div class="fl">
      <label>Longitude</label>
      <input type="text" name="longitude" required>
    </div>
    <div class="fl">
      <label>Description</label>
      <textarea name="description" placeholder="Add property details"></textarea>
    </div>
    <div class="fl">
      <label>Commission Rate (%)</label>
      <input type="number" step="0.01" name="commission_rate" required>
    </div>
    <div class="fl">
      <label>Commission Percentage (%)</label>
      <input type="number" step="0.01" name="commission_percentage" required>
    </div>
    <div class="fl">
      <label>Property Image</label>
      <input type="file" name="property_image" accept="image/*" required>
    </div>
    <button type="submit" class="sbtn">Submit Property for Approval</button>
  </form>
</div>
    <div class="ey">Listings</div>
    <h2 class="sh">Available <em>Properties</em></h2>
    <p class="sp">Browse the full HousingHub property catalogue. All listings are owner-verified.</p>
    <?php
    $type_filter = $_GET['type'] ?? '';
    $where_clause = $type_filter ? "WHERE property_type='".mysqli_real_escape_string($conn,$type_filter)."'" : '';
    $all_props = mysqli_query($conn,"SELECT p.*,u.fullname AS owner_name FROM properties p LEFT JOIN users u ON p.owner_id=u.id $where_clause ORDER BY p.created_at DESC");
    $prop_count = $all_props ? mysqli_num_rows($all_props) : 0;
    $types_q = mysqli_query($conn,"SELECT property_type, COUNT(*) AS c FROM properties WHERE property_type IS NOT NULL AND property_type!='' GROUP BY property_type");
    $types = [];
    if ($types_q) while ($t = mysqli_fetch_assoc($types_q)) $types[$t['property_type']] = $t['c'];
    ?>
    <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
      <a href="broker_dashboard.php?view=properties" style="padding:7px 14px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;background:<?=!$type_filter?'rgba(200,164,60,.2)':'rgba(255,255,255,.04)'?>;border:1px solid <?=!$type_filter?'var(--gb)':'var(--border)'?>;color:<?=!$type_filter?'var(--gold)':'var(--muted)'?>">All <span style="background:rgba(255,255,255,.1);border-radius:10px;padding:1px 6px;font-size:10px;margin-left:4px"><?= $prop_count ?></span></a>
      <?php foreach($types as $t_name => $t_cnt): ?>
      <a href="broker_dashboard.php?view=properties&type=<?=urlencode($t_name)?>" style="padding:7px 14px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;background:<?=$type_filter===$t_name?'rgba(200,164,60,.2)':'rgba(255,255,255,.04)'?>;border:1px solid <?=$type_filter===$t_name?'var(--gb)':'var(--border)'?>;color:<?=$type_filter===$t_name?'var(--gold)':'var(--muted)'?>"><?=$t_name?> <span style="background:rgba(255,255,255,.1);border-radius:10px;padding:1px 6px;font-size:10px;margin-left:4px"><?=$t_cnt?></span></a>
      <?php endforeach; ?>
    </div>
    <?php if ($prop_count === 0): ?>
    <div style="text-align:center;padding:60px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px">
      <div style="font-size:48px;margin-bottom:16px">🏘️</div>
      <div style="font-size:16px;font-weight:600;color:var(--white);margin-bottom:8px">No properties found</div>
    </div>
    <?php else: ?>
    <div class="prop-grid">
    <?php while($pr = mysqli_fetch_assoc($all_props)):
      $icon = ['Residential'=>'🏠','Commercial'=>'🏢','Industrial'=>'🏭','Agricultural'=>'🌾','Land'=>'🗺️'][$pr['property_type']??''] ?? '🏗️';
      $st = strtolower($pr['status']??'');
    ?>
    <div class="prop-card">
      <div class="prop-img"><?= $icon ?></div>
      <div class="prop-body">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px">
          <div class="prop-name"><?= htmlspecialchars($pr['property_name']) ?></div>
          <span class="bx <?= $st==='available'?'green':($st==='occupied'?'red':'gold') ?>"><?= htmlspecialchars(ucfirst($pr['status']??'Unknown')) ?></span>
        </div>
        <div class="prop-addr">📍 <?= htmlspecialchars($pr['address']??'Location not listed') ?></div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:12px">
          <div style="text-align:center;padding:7px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:6px">
            <div style="font-size:13px;font-weight:700;color:var(--gold)"><?= (int)($pr['bedrooms']??0) ?></div>
            <div style="font-size:9px;color:var(--muted);margin-top:2px">Beds</div>
          </div>
          <div style="text-align:center;padding:7px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:6px">
            <div style="font-size:13px;font-weight:700;color:var(--gold)"><?= (int)($pr['units']??1) ?></div>
            <div style="font-size:9px;color:var(--muted);margin-top:2px">Units</div>
          </div>
          <div style="text-align:center;padding:7px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:6px">
            <div style="font-size:10px;font-weight:600;color:var(--muted)"><?= htmlspecialchars($pr['property_type']??'N/A') ?></div>
            <div style="font-size:9px;color:var(--muted);margin-top:2px">Type</div>
          </div>
        </div>
        <div class="prop-meta">
          <div>
            <div class="prop-rent">UGX <?= number_format($pr['rent_amount']??0) ?></div>
            <div class="prop-type">per month</div>
          </div>
          <div style="font-size:11px;color:var(--muted)">Owner: <?= htmlspecialchars($pr['owner_name']??'HousingHub') ?></div>
        </div>
        <a class="prop-action" href="mailto:nawzvivian@gmail.com?subject=Broker Enquiry: <?= urlencode($pr['property_name']) ?>">📧 Enquire About This Property</a>
      </div>
    </div>
    <?php endwhile; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- ══ MY LISTINGS ══ -->
  <div class="pg <?= $page==='my_listings'?'active':'' ?>" id="pg-my_listings">
    <div class="ey">My Portfolio</div>
    <h2 class="sh">My <em>Listings</em></h2>
    <p class="sp">Properties assigned to you. These generate commissions when deals close.</p>
    <?php
    $mine_q = mysqli_query($conn,"SELECT p.*,u.fullname AS owner_name FROM properties p LEFT JOIN users u ON p.owner_id=u.id WHERE p.broker_id=$user_id ORDER BY p.created_at DESC");
    $mine_count = $mine_q ? mysqli_num_rows($mine_q) : 0;
    ?>
    <?php if ($mine_count === 0): ?>
    <div style="text-align:center;padding:60px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px">
      <div style="font-size:48px;margin-bottom:16px">📌</div>
      <div style="font-size:16px;font-weight:600;color:var(--white);margin-bottom:8px">No listings assigned yet</div>
      <div style="font-size:13px;color:var(--muted);max-width:400px;margin:0 auto 20px">The admin will assign properties to you. Contact support to request assignment.</div>
      <button class="sbtn" style="width:auto;padding:10px 24px" onclick="show('properties',null)">Browse Available Properties →</button>
    </div>
    <?php else: ?>
    <table class="dt">
      <thead><tr><th>Property</th><th>Type</th><th>Rent/Mo (UGX)</th><th>Units</th><th>Status</th><th>Commission Est.</th><th>Owner</th></tr></thead>
      <tbody>
      <?php while($pr = mysqli_fetch_assoc($mine_q)):
        $est_comm = ($pr['rent_amount']??0) * ($commission_rate/100);
      ?>
      <tr>
        <td><div style="font-weight:600"><?= htmlspecialchars($pr['property_name']) ?></div><div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($pr['address']??'—') ?></div></td>
        <td style="color:var(--muted)"><?= htmlspecialchars($pr['property_type']??'—') ?></td>
        <td style="color:var(--gold)"><?= number_format($pr['rent_amount']??0) ?></td>
        <td style="text-align:center"><?= (int)($pr['units']??1) ?></td>
        <td><span class="bx <?= strtolower($pr['status']??'')=='available'?'green':'gold' ?>"><?= htmlspecialchars(ucfirst($pr['status']??'Unknown')) ?></span></td>
        <td style="color:#86efac">UGX <?= number_format($est_comm) ?>/deal</td>
        <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($pr['owner_name']??'—') ?></td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- ══ COMMISSIONS ══ -->
  <div class="pg <?= $page==='commissions'?'active':'' ?>" id="pg-commissions">
    <div class="ey">Earnings</div>
    <h2 class="sh">Commissions & <em>Earnings</em></h2>
    <p class="sp">Track every commission earned. Your current rate is <?= $commission_rate ?>%.</p>
    <div class="ch3" style="margin-bottom:24px">
      <div class="card" style="text-align:center">
        <div style="font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:8px">Total Earned</div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:38px;font-weight:700;color:var(--gold)">UGX <?= number_format($total_commission) ?></div>
      </div>
      <div class="card" style="text-align:center">
        <div style="font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:8px">Deals Closed</div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:38px;font-weight:700;color:#86efac"><?= $deals_closed ?></div>
      </div>
      <div class="card" style="text-align:center">
        <div style="font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:8px">My Rate</div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:38px;font-weight:700;color:var(--gold)"><?= $commission_rate ?>%</div>
      </div>
    </div>
    <!-- Tier Progress -->
    <div class="card" style="margin-bottom:20px">
      <div class="chead"><div class="ct">Tier Progress</div></div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
        <?php foreach([['New Broker','8%',0,5,'rgba(200,164,60,.15)','var(--gold)'],['Silver','12%',5,20,'rgba(192,192,192,.15)','silver'],['Gold','18%',20,50,'rgba(200,164,60,.25)','var(--gold-l)']] as [$tn,$rate,$min,$max,$bg,$col]):
          $is_c = $deals_closed >= $min && $deals_closed < $max;
          $pct = $min >= $max ? 100 : min(100, round(($deals_closed - $min) / ($max - $min) * 100));
          if ($deals_closed >= $max) $pct = 100;
        ?>
        <div style="padding:16px;background:<?=$bg?>;border:1px solid <?=$is_c?$col:'var(--border)'?>;border-radius:10px">
          <div style="font-size:14px;font-weight:700;color:<?=$col?>;margin-bottom:2px"><?=$tn?></div>
          <div style="font-size:22px;font-family:'Cormorant Garamond',serif;font-weight:700;color:var(--white)"><?=$rate?></div>
          <div style="font-size:10px;color:var(--muted);margin-bottom:10px"><?=$min?>–<?=$max?> deals</div>
          <div style="height:4px;background:rgba(255,255,255,.08);border-radius:2px"><div style="height:100%;width:<?=$pct?>%;background:<?=$col?>;border-radius:2px"></div></div>
          <?php if($is_c): ?><div style="font-size:10px;color:<?=$col?>;margin-top:6px">← Current Tier</div><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <!-- Payment History -->
    <div class="card">
      <div class="chead"><div class="ct">Commission History</div></div>
      <?php
      $pay_hist = null;
      $r_bi = mysqli_query($conn,"SHOW COLUMNS FROM properties LIKE 'broker_id'");
      if ($r_bi && mysqli_num_rows($r_bi) > 0) {
          $pay_hist = mysqli_query($conn,
              "SELECT pay.amount, pay.date, pay.status, pay.payment_method, pr.property_name,
                      (pay.amount * $commission_rate / 100) AS my_commission
               FROM payments pay JOIN properties pr ON pay.property_id=pr.id
               WHERE pr.broker_id=$user_id ORDER BY pay.date DESC LIMIT 20");
      }
      if (!$pay_hist || mysqli_num_rows($pay_hist) === 0): ?>
      <div style="text-align:center;padding:30px;color:var(--muted);font-size:13px">No commission records yet.</div>
      <?php else: ?>
      <table class="dt">
        <thead><tr><th>Property</th><th>Deal Amount</th><th>My Commission</th><th>Method</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
        <?php while($pay = mysqli_fetch_assoc($pay_hist)):
          $st = strtolower($pay['status']??'pending');
          $bc = in_array($st,['paid','completed'])?'green':($st==='pending'?'gold':'red');
        ?>
        <tr>
          <td style="font-weight:600"><?= htmlspecialchars($pay['property_name']) ?></td>
          <td><?= number_format($pay['amount']) ?></td>
          <td style="color:#86efac;font-weight:600"><?= number_format($pay['my_commission']) ?></td>
          <td style="color:var(--muted)"><?= htmlspecialchars(ucwords(str_replace('_',' ',$pay['payment_method']??''))) ?></td>
          <td style="color:var(--muted)"><?= $pay['date']?date('d M Y',strtotime($pay['date'])):'—' ?></td>
          <td><span class="bx <?=$bc?>"><?= ucfirst($st) ?></span></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- ══ CLIENT PIPELINE ══ -->
  <div class="pg <?= $page==='pipeline'?'active':'' ?>" id="pg-pipeline">
    <div class="ey">Business</div>
    <h2 class="sh">Client <em>Pipeline</em></h2>
    <p class="sp">Track tenant applications on your listed properties.</p>
    <?php
    $apps_q = null;
    $ta_check = mysqli_query($conn, "SHOW TABLES LIKE 'tenant_applications'");
    if ($ta_check && mysqli_num_rows($ta_check) > 0) {
      $bi_check = mysqli_query($conn,"SHOW COLUMNS FROM properties LIKE 'broker_id'");
      if ($bi_check && mysqli_num_rows($bi_check) > 0) {
        $apps_q = mysqli_query($conn,
            "SELECT ta.*, p.property_name, p.rent_amount FROM tenant_applications ta
             JOIN properties p ON ta.property_id=p.id
             WHERE p.broker_id=$user_id ORDER BY ta.created_at DESC");
      } else {
        $apps_q = mysqli_query($conn,
            "SELECT ta.*, p.property_name, p.rent_amount FROM tenant_applications ta
             LEFT JOIN properties p ON ta.property_id=p.id ORDER BY ta.created_at DESC LIMIT 20");
      }
    }
    ?>
    <div class="pipeline">
      <?php foreach([['pending','⏳ Pending','gold'],['reviewing','🔍 Reviewing','blue'],['approved','✅ Approved','green']] as [$status,$label,$color]):
        $stage_q = null;
        if ($ta_check && mysqli_num_rows($ta_check) > 0) {
          $bi2 = mysqli_query($conn,"SHOW COLUMNS FROM properties LIKE 'broker_id'");
          if ($bi2 && mysqli_num_rows($bi2) > 0) {
            $stage_q = mysqli_query($conn,
              "SELECT ta.fullname, ta.email, p.property_name, ta.created_at
               FROM tenant_applications ta JOIN properties p ON ta.property_id=p.id
               WHERE p.broker_id=$user_id AND ta.status='$status'");
          }
        }
        $stage_count = $stage_q ? mysqli_num_rows($stage_q) : 0;
      ?>
      <div class="pipe-col">
        <div class="pipe-col-h"><?=$label?><span class="bx <?=$color?>"><?=$stage_count?></span></div>
        <?php if ($stage_count === 0): ?>
        <div style="text-align:center;padding:20px;font-size:12px;color:rgba(255,255,255,.2)">No clients</div>
        <?php else: while($ap = mysqli_fetch_assoc($stage_q)): ?>
        <div class="pipe-item">
          <div class="pipe-name"><?= htmlspecialchars($ap['fullname']) ?></div>
          <div class="pipe-prop">📍 <?= htmlspecialchars($ap['property_name']??'—') ?></div>
          <div style="font-size:11px;color:var(--muted);margin-top:3px">📧 <?= htmlspecialchars($ap['email']??'—') ?></div>
          <div class="pipe-date"><?= $ap['created_at']?date('d M Y',strtotime($ap['created_at'])):'—' ?></div>
        </div>
        <?php endwhile; endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="card">
      <div class="chead"><div class="ct">All Applications</div></div>
      <?php if (!$apps_q || mysqli_num_rows($apps_q) === 0): ?>
      <div style="text-align:center;padding:30px;color:var(--muted);font-size:13px">No applications yet.</div>
      <?php else: mysqli_data_seek($apps_q, 0); ?>
      <table class="dt">
        <thead><tr><th>Applicant</th><th>Property</th><th>Rent</th><th>Move-in</th><th>Applied</th><th>Status</th></tr></thead>
        <tbody>
        <?php while($ap = mysqli_fetch_assoc($apps_q)):
          $st = strtolower($ap['status']??'pending');
          $bc = $st==='approved'?'green':($st==='rejected'?'red':($st==='reviewing'?'blue':'gold'));
        ?>
        <tr>
          <td><div style="font-weight:600"><?= htmlspecialchars($ap['fullname']) ?></div><div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($ap['email']??'—') ?></div></td>
          <td><?= htmlspecialchars($ap['property_name']??'—') ?></td>
          <td style="color:var(--gold)"><?= number_format($ap['rent_amount']??0) ?></td>
          <td style="color:var(--muted)"><?= $ap['desired_move_in']?date('d M Y',strtotime($ap['desired_move_in'])):'—' ?></td>
          <td style="color:var(--muted)"><?= $ap['created_at']?date('d M Y',strtotime($ap['created_at'])):'—' ?></td>
          <td><span class="bx <?=$bc?>"><?= ucfirst($st) ?></span></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- ══ VIEWING REQUESTS ══ -->
  <div class="pg <?= $page==='viewings'?'active':'' ?>" id="pg-viewings">
    <div class="ey">Viewings</div>
    <h2 class="sh">Viewing <em>Requests</em></h2>
    <p class="sp">Upcoming and past property viewing requests from prospective tenants.</p>
    <?php
    $vr_check = mysqli_query($conn,"SHOW TABLES LIKE 'property_viewing_requests'");
    $vr_q = null;
    if ($vr_check && mysqli_num_rows($vr_check) > 0)
      $vr_q = mysqli_query($conn,"SELECT * FROM property_viewing_requests ORDER BY inspection_date DESC, created_at DESC LIMIT 30");
    ?>
    <?php if (!$vr_q || mysqli_num_rows($vr_q) === 0): ?>
    <div style="text-align:center;padding:60px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px">
      <div style="font-size:48px;margin-bottom:16px">📅</div>
      <div style="font-size:16px;font-weight:600;color:var(--white);margin-bottom:8px">No viewing requests yet</div>
    </div>
    <?php else: ?>
    <div class="card">
      <table class="dt">
        <thead><tr><th>Visitor</th><th>Property</th><th>Date & Time</th><th>Type</th><th>Contact</th><th>Status</th></tr></thead>
        <tbody>
        <?php while($vr = mysqli_fetch_assoc($vr_q)):
          $st = strtolower($vr['status']??'pending');
          $bc = $st==='approved'?'green':($st==='completed'?'blue':($st==='rejected'?'red':'gold'));
        ?>
        <tr>
          <td style="font-weight:600"><?= htmlspecialchars($vr['fullname']) ?></td>
          <td><?= htmlspecialchars($vr['property_name']) ?></td>
          <td style="color:var(--muted)"><?= $vr['inspection_date']?date('d M Y',strtotime($vr['inspection_date'])):'—' ?><br><?= htmlspecialchars($vr['inspection_time']??'') ?></td>
          <td><?= htmlspecialchars($vr['visitor_type']) ?></td>
          <td style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($vr['phone']) ?><?php if($vr['email']): ?><br><?= htmlspecialchars($vr['email']) ?><?php endif; ?></td>
          <td><span class="bx <?=$bc?>"><?= ucfirst($st) ?></span></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- ══ CLIENT NOTES ══ -->
  <div class="pg <?= $page==='notes'?'active':'' ?>" id="pg-notes">
    <div class="ey">CRM</div>
    <h2 class="sh">Client <em>Notes</em></h2>
    <p class="sp">Keep private notes on clients, leads, and prospects.</p>
    <?php if(isset($_SESSION['broker_success'])): ?>
      <div class="alert s">✅ <?= htmlspecialchars($_SESSION['broker_success']) ?></div>
      <?php unset($_SESSION['broker_success']); ?>
    <?php endif; ?>
    <div class="ch2">
      <div class="card">
        <div class="chead"><div class="ct">Add Note</div></div>
        <form method="POST">
          <div class="fl"><label>Client Name</label><input type="text" name="client_name" placeholder="e.g. John Mutebe" required></div>
          <div class="fl"><label>Note</label><textarea name="note" placeholder="Call scheduled, interested in 3-bed unit..." required></textarea></div>
          <button type="submit" name="save_note" class="sbtn">Save Note →</button>
        </form>
      </div>
      <div>
        <?php
        $notes_q = mysqli_query($conn,"SELECT * FROM broker_client_notes WHERE broker_id=$user_id ORDER BY created_at DESC LIMIT 30");
        if (!$notes_q || mysqli_num_rows($notes_q) === 0): ?>
        <div style="text-align:center;padding:40px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;color:var(--muted)">No notes yet. Add your first client note.</div>
        <?php else: while($note = mysqli_fetch_assoc($notes_q)): ?>
        <div class="note-card">
          <div style="display:flex;justify-content:space-between;align-items:flex-start">
            <div>
              <div class="note-client">👤 <?= htmlspecialchars($note['client_name']) ?></div>
              <div class="note-text"><?= htmlspecialchars($note['note']) ?></div>
              <div class="note-date"><?= date('d M Y, H:i', strtotime($note['created_at'])) ?></div>
            </div>
            <a href="broker_dashboard.php?view=notes&delete_note=<?= $note['id'] ?>" onclick="return confirm('Delete note?')" style="font-size:11px;color:#fca5a5;text-decoration:none;flex-shrink:0;padding:4px 8px;border:1px solid rgba(239,68,68,.2);border-radius:5px">🗑</a>
          </div>
        </div>
        <?php endwhile; endif; ?>
      </div>
    </div>
  </div>

  <!-- ══ MARKET INSIGHTS ══ -->
  <div class="pg <?= $page==='insights'?'active':'' ?>" id="pg-insights">
    <div class="ey">Insights</div>
      <div class="card">
        <div class="chead"><div class="ct">Market Insights</div></div>
        <div style="display:flex;flex-direction:column;gap:12px">
          <div style="padding:14px;background:rgba(22,163,74,.06);border:1px solid rgba(22,163,74,.15);border-radius:8px">
            <div style="font-size:11px;font-weight:700;color:#86efac;letter-spacing:1px;text-transform:uppercase;margin-bottom:5px">Your Portfolio</div>
            <div style="font-size:22px;font-family:'Cormorant Garamond',serif;font-weight:700;color:var(--white)"><?= $my_props_count ?> properties</div>
            <div style="font-size:12px;color:var(--muted);margin-top:3px">Active listings under your name</div>
          </div>
          <div style="padding:14px;background:rgba(200,164,60,.06);border:1px solid var(--gb);border-radius:8px">
            <div style="font-size:11px;font-weight:700;color:var(--gold);letter-spacing:1px;text-transform:uppercase;margin-bottom:5px">Commission Rate</div>
            <div style="font-size:22px;font-family:'Cormorant Garamond',serif;font-weight:700;color:var(--white)"><?= $commission_rate ?>%</div>
            <div style="font-size:12px;color:var(--muted);margin-top:3px">Set by admin · <?= $deals_closed < 5 ? 'New Broker tier' : ($deals_closed < 20 ? 'Silver tier' : 'Gold tier') ?></div>
          </div>
          <div style="padding:14px;background:rgba(14,90,200,.06);border:1px solid rgba(14,90,200,.2);border-radius:8px">
            <div style="font-size:11px;font-weight:700;color:#5b9cff;letter-spacing:1px;text-transform:uppercase;margin-bottom:5px">Avg. Commission/Deal</div>
            <?php
            $avg_deal = $deals_closed > 0 ? $total_commission / $deals_closed : 0;
            ?>
            <div style="font-size:22px;font-family:'Cormorant Garamond',serif;font-weight:700;color:var(--white)">UGX <?= number_format($avg_deal) ?></div>
            <div style="font-size:12px;color:var(--muted);margin-top:3px">Per closed transaction</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══ NOTIFICATIONS ══ -->
  <div class="pg <?= $page==='notifications'?'active':'' ?>" id="pg-notifications">
    <div class="ey">Updates</div>
    <h2 class="sh"><em>Notifications</em></h2>
    <p class="sp">All alerts and messages from HousingHub.</p>
    <?php $notif_q = mysqli_query($conn,"SELECT * FROM notifications WHERE user_id=$user_id OR user_id=0 ORDER BY date DESC LIMIT 30"); ?>
    <div class="card">
      <?php if (!$notif_q || mysqli_num_rows($notif_q) === 0): ?>
      <div style="text-align:center;padding:40px;color:var(--muted)">No notifications yet.</div>
      <?php else: while($n = mysqli_fetch_assoc($notif_q)):
        $is_unread = ($n['status']==='unread' || $n['is_read']==0);
      ?>
      <div class="ni-item <?= $is_unread?'unread':'' ?>">
        <div class="ni-icon">🔔</div>
        <div>
          <div class="ni-t"><?= htmlspecialchars($n['title']??'Notification') ?></div>
          <div class="ni-m"><?= htmlspecialchars($n['message']??'') ?></div>
          <div class="ni-d"><?= $n['date']?date('d M Y, g:i A',strtotime($n['date'])):'—' ?></div>
        </div>
      </div>
      <?php endwhile; endif; ?>
    </div>
  </div>

  <!-- ══ PROFILE ══ -->
  <div class="pg <?= $page==='profile'?'active':'' ?>" id="pg-profile">
    <div class="ey">Account</div>
    <h2 class="sh">My <em>Profile</em></h2>
    <p class="sp">Your broker account details and settings.</p>
    <?php if(isset($_SESSION['broker_success'])): ?>
      <div class="alert s">✅ <?= htmlspecialchars($_SESSION['broker_success']) ?></div>
      <?php unset($_SESSION['broker_success']); ?>
    <?php endif; ?>
    <?php if(isset($_SESSION['broker_error'])): ?>
      <div class="alert e">⚠️ <?= htmlspecialchars($_SESSION['broker_error']) ?></div>
      <?php unset($_SESSION['broker_error']); ?>
    <?php endif; ?>
    <div class="ph">
      <div class="pav"><?= $initials ?></div>
      <div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;color:var(--white);margin-bottom:4px"><?= $fullname ?></div>
        <div style="font-size:13px;color:var(--muted);margin-bottom:8px"><?= $email ?></div>
        <span class="sb-verified">✓ Verified Broker</span>
      </div>
    </div>
    <div class="pgrid">
      <div class="pf"><div class="pfl">Full Name</div><div class="pfv"><?= $fullname ?></div></div>
      <div class="pf"><div class="pfl">Email</div><div class="pfv"><?= $email ?></div></div>
      <div class="pf"><div class="pfl">Phone</div><div class="pfv"><?= $phone ?></div></div>
      <div class="pf"><div class="pfl">Commission Rate</div><div class="pfv" style="color:var(--gold)"><?= $commission_rate ?>%</div></div>
      <div class="pf"><div class="pfl">Deals Closed</div><div class="pfv"><?= $deals_closed ?></div></div>
      <div class="pf"><div class="pfl">Total Earnings</div><div class="pfv" style="color:#86efac">UGX <?= number_format($total_commission) ?></div></div>
      <div class="pf"><div class="pfl">Tier</div><div class="pfv"><?= $deals_closed < 5 ? 'New Broker' : ($deals_closed < 20 ? 'Silver' : 'Gold') ?></div></div>
      <div class="pf"><div class="pfl">My Listed Properties</div><div class="pfv"><?= $my_props_count ?></div></div>
    </div>
    <div class="ch2">
      <div class="card">
        <div class="chead"><div class="ct">Change Password</div></div>
        <form method="POST">
          <input type="hidden" name="change_password" value="1">
          <div class="fl"><label>Current Password</label><input type="password" name="current_password" required></div>
          <div class="fl"><label>New Password</label><input type="password" name="new_password" required></div>
          <div class="fl"><label>Confirm New Password</label><input type="password" name="confirm_password" required></div>
          <button type="submit" class="sbtn">Update Password →</button>
        </form>
      </div>
      <div class="card">
        <div class="chead"><div class="ct">Quick Links</div></div>
        <div style="display:flex;flex-direction:column;gap:10px">
          <a href="join.php" style="display:flex;align-items:center;gap:12px;padding:12px;background:rgba(200,164,60,.06);border:1px solid var(--gb);border-radius:8px;text-decoration:none;color:var(--white);font-size:13px;transition:all .2s">
            <span style="font-size:20px">📄</span><div><div style="font-weight:600">Update Verification Documents</div><div style="font-size:11px;color:var(--muted)">Submit updated ID documents</div></div>
          </a>
          <a href="mailto:support@housinghuborg.ug" style="display:flex;align-items:center;gap:12px;padding:12px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;text-decoration:none;color:var(--white);font-size:13px">
            <span style="font-size:20px">📧</span><div><div style="font-weight:600">Contact Support</div><div style="font-size:11px;color:var(--muted)">support@housinghuborg.ug</div></div>
          </a>
          <a href="logout.php" style="display:flex;align-items:center;gap:12px;padding:12px;background:rgba(239,68,68,.06);border:1px solid rgba(239,68,68,.2);border-radius:8px;text-decoration:none;color:#fca5a5;font-size:13px">
            <span style="font-size:20px">🚪</span><div><div style="font-weight:600">Sign Out</div></div>
          </a>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
/* PAGE NAV */
var titles={overview:'Overview',properties:'Available Properties',my_listings:'My Listings',commissions:'Commissions & Earnings',pipeline:'Client Pipeline',viewings:'Viewing Requests',notes:'Client Notes',assistant:'AI Assistant',notifications:'Notifications',profile:'My Profile'};
function show(id,btn){
  document.querySelectorAll('.pg').forEach(p=>p.classList.remove('active'));
  var el=document.getElementById('pg-'+id);
  if(el)el.classList.add('active');
  document.querySelectorAll('.na').forEach(l=>l.classList.remove('active'));
  if(btn){btn.classList.add('active');}else{
    document.querySelectorAll('.na').forEach(l=>{var oc=l.getAttribute('onclick');if(oc&&oc.indexOf("'"+id+"'")!==-1)l.classList.add('active');});
  }
  var t=document.getElementById('tb-title');
  if(t)t.textContent=titles[id]||id;
  window.scrollTo({top:0,behavior:'smooth'});
}
(function(){var page='<?= $page ?>';var el=document.getElementById('pg-'+page);if(el){document.querySelectorAll('.pg').forEach(p=>p.classList.remove('active'));el.classList.add('active');}var t=document.getElementById('tb-title');if(t&&titles[page])t.textContent=titles[page];})();

/* EARNINGS CHART */
var ctx=document.getElementById('earningsChart');
if(ctx){
  new Chart(ctx,{
    type:'bar',
    data:{
      labels:<?= json_encode(array_column($monthly_data,'label')) ?>,
      datasets:[{
        label:'Commission (UGX)',
        data:<?= json_encode(array_column($monthly_data,'value')) ?>,
        backgroundColor:'rgba(200,164,60,.35)',
        borderColor:'rgba(200,164,60,.8)',
        borderWidth:1,
        borderRadius:4,
      }]
    },
    options:{
      responsive:true,maintainAspectRatio:true,
      plugins:{legend:{display:false},tooltip:{callbacks:{label:function(c){return 'UGX '+c.raw.toLocaleString();}}}},
      scales:{
        x:{grid:{color:'rgba(255,255,255,.04)'},ticks:{color:'rgba(255,255,255,.4)',font:{size:11}}},
        y:{grid:{color:'rgba(255,255,255,.04)'},ticks:{color:'rgba(255,255,255,.4)',font:{size:11},callback:function(v){return 'UGX '+v.toLocaleString();}},beginAtZero:true}
      }
    }
  });
}

/* AI ASSISTANT */
var aiMsgs=document.getElementById('ai-msgs');
var aiInp=document.getElementById('ai-inp');
function addMsg(text,role){
  var d=document.createElement('div');
  d.className=role==='user'?'msg-user':'msg-ai';
  d.textContent=text;
  aiMsgs.appendChild(d);
  aiMsgs.scrollTop=aiMsgs.scrollHeight;
}
async function sendAI(){
  var msg=aiInp.value.trim();
  if(!msg)return;
  addMsg(msg,'user');
  aiInp.value='';
  addMsg('Thinking...','ai');
  try{
    var res=await fetch('https://api.anthropic.com/v1/messages',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({
        model:'claude-sonnet-4-20250514',
        max_tokens:600,
        system:'You are a real estate broker assistant for HousingHub Uganda. Give concise, practical advice for property brokers. Keep responses under 150 words.',
        messages:[{role:'user',content:msg}]
      })
    });
    var data=await res.json();
    aiMsgs.lastChild.remove();
    var reply=data.content&&data.content[0]?data.content[0].text:'I could not get a response. Please try again.';
    addMsg(reply,'ai');
  }catch(e){
    aiMsgs.lastChild.remove();
    addMsg('Sorry, the assistant is unavailable right now.','ai');
  }
}
if(aiInp)aiInp.addEventListener('keydown',function(e){if(e.key==='Enter')sendAI();});
</script>
</body>
</html>