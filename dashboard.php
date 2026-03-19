<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
// ── GATE 1: Must be logged in ──
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
 
// ── GATE 2: Must be tenant role ──
if ($_SESSION['role'] !== 'tenant') {
    header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : ($_SESSION['role'] === 'staff' ? 'staff_dashboard.php' : 'login.php')));
    exit();
}
 
$user_id = (int)$_SESSION['user_id'];
 
// ── GATE 3: Must exist in tenants table ──
$chk = mysqli_prepare($conn, "SELECT id, status FROM tenants WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($chk, "i", $user_id);
mysqli_stmt_execute($chk);
$chk_row = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));
 
if (!$chk_row) { ?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pending | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Outfit:wght@400;600&display=swap" rel="stylesheet">
<style>*{box-sizing:border-box;margin:0;padding:0}:root{--ink:#04091a;--gold:#c8a43c;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07)}body{font-family:"Outfit",sans-serif;background:radial-gradient(ellipse 80% 60% at 70% 10%,rgba(14,90,200,.18),transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12),transparent 50%),var(--ink);color:var(--white);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}.card{width:100%;max-width:460px;background:rgba(10,16,40,.95);border:1px solid var(--border);border-radius:16px;padding:48px 40px;text-align:center;box-shadow:0 40px 100px rgba(0,0,0,.6);animation:up .6s cubic-bezier(.23,1,.32,1)}@keyframes up{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:none}}.icon{font-size:52px;display:block;margin-bottom:20px;animation:fl 3s ease-in-out infinite}@keyframes fl{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}h1{font-family:"Cormorant Garamond",serif;font-size:30px;color:var(--white);margin-bottom:10px}em{color:var(--gold);font-style:italic}p{font-size:13px;color:var(--muted);line-height:1.7;margin-bottom:22px}.steps{text-align:left;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:18px 22px;margin-bottom:26px}.step{display:flex;gap:12px;margin-bottom:12px;font-size:12px;color:var(--muted);line-height:1.5}.step:last-child{margin-bottom:0}.sn{width:20px;height:20px;border-radius:50%;background:rgba(200,164,60,.15);border:1px solid var(--gold);color:var(--gold);font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}.btn{display:inline-block;padding:12px 28px;border:1px solid rgba(200,164,60,.3);color:var(--gold);font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:6px}.brand{font-family:"Cormorant Garamond",serif;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:rgba(200,164,60,.35);margin-bottom:26px}</style>
</head><body><div class="card"><div class="brand">HOUSING HUB</div><span class="icon">⏳</span><h1>Account <em>Pending</em></h1><p>Your account exists but has not yet been linked to a managed tenancy by a HousingHub property manager.</p><div class="steps"><div class="step"><div class="sn">1</div><span>Your property manager adds your details and links your user account.</span></div><div class="step"><div class="sn">2</div><span>You'll be notified once your tenancy is confirmed.</span></div><div class="step"><div class="sn">3</div><span>Log back in to access your full tenant dashboard.</span></div></div><p style="font-size:11px;margin-bottom:22px">Questions? Email <span style="color:var(--gold)">support@housinghuborg.ug</span></p><a href="logout.php" class="btn">← Sign Out</a></div></body></html>
<?php exit(); }
 
// ── GATE 4: Must be active ──
if (in_array(strtolower($chk_row['status'] ?? 'active'), ['inactive','suspended','terminated'])) { ?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Inactive | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Outfit:wght@400;600&display=swap" rel="stylesheet">
<style>*{box-sizing:border-box;margin:0;padding:0}:root{--ink:#04091a;--gold:#c8a43c;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07)}body{font-family:"Outfit",sans-serif;background:radial-gradient(ellipse 80% 60% at 70% 10%,rgba(200,60,60,.12),transparent 55%),var(--ink);color:var(--white);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}.card{width:100%;max-width:460px;background:rgba(10,16,40,.95);border:1px solid rgba(255,95,87,.15);border-radius:16px;padding:48px 40px;text-align:center;box-shadow:0 40px 100px rgba(0,0,0,.6);animation:up .6s cubic-bezier(.23,1,.32,1)}@keyframes up{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:none}}h1{font-family:"Cormorant Garamond",serif;font-size:30px;color:var(--white);margin-bottom:10px}em{color:#ff8f8a;font-style:italic}p{font-size:13px;color:var(--muted);line-height:1.7;margin-bottom:22px}.btn{display:inline-block;padding:12px 28px;border:1px solid rgba(255,95,87,.3);color:#ff8f8a;font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:6px}.brand{font-family:"Cormorant Garamond",serif;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:rgba(200,164,60,.35);margin-bottom:26px}</style>
</head><body><div class="card"><div class="brand">HOUSING HUB</div><span style="font-size:52px;display:block;margin-bottom:20px">🚫</span><h1>Account <em>Inactive</em></h1><p>Your tenancy account has been deactivated. This may be because your lease ended or your account was suspended.</p><p style="font-size:11px;margin-bottom:22px">Contact your manager or email <span style="color:var(--gold)">support@housinghuborg.ug</span></p><a href="logout.php" class="btn">← Sign Out</a></div></body></html>
<?php exit(); }
 
// ════════════════════════════════
//  ALL GATES PASSED — load data
// ════════════════════════════════
 
// User details
$stmt = mysqli_prepare($conn, "SELECT fullname, email, phone FROM users WHERE id=? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$urow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$fullname = htmlspecialchars($urow['fullname'] ?? $_SESSION['fullname'] ?? 'Tenant');
$email    = htmlspecialchars($urow['email']    ?? '');
$phone    = htmlspecialchars($urow['phone']    ?? 'Not provided');
$parts    = explode(' ', $fullname, 2);
$fname    = $parts[0];
$lname    = $parts[1] ?? '';
$initials = strtoupper(substr($fname,0,1).substr($lname,0,1));
 
// Tenant + property details
$property           = 'Not yet assigned';
$address            = '';
$landlord           = 'HousingHub Manager';
$tenant_property_id = 0;
$rent_amount_raw    = 0;
$rent_amount        = '0';
$lease_start        = 'N/A';
$lease_end          = 'N/A';
$tenant_status      = 'Active';
$gender             = 'N/A';
$national_id        = 'N/A';
$occupation         = 'N/A';
$emergency_name     = 'N/A';
$emergency_phone    = 'N/A';
 
$ts = mysqli_prepare($conn,
    "SELECT tn.fullname, tn.phone, tn.property_id,
            tn.lease_start, tn.lease_end, tn.status,
            tn.gender, tn.national_id, tn.occupation,
            tn.emergency_name, tn.emergency_phone,
            p.property_name, p.address, p.rent_amount
     FROM tenants tn
     LEFT JOIN properties p ON tn.property_id = p.id
     WHERE tn.user_id = ? LIMIT 1");
if ($ts) {
    mysqli_stmt_bind_param($ts, "i", $user_id);
    mysqli_stmt_execute($ts);
    $trow = mysqli_fetch_assoc(mysqli_stmt_get_result($ts));
    if ($trow) {
        if (!empty($trow['fullname'])) { $fullname = htmlspecialchars($trow['fullname']); $parts = explode(' ',$fullname,2); $fname=$parts[0]; $lname=$parts[1]??''; $initials=strtoupper(substr($fname,0,1).substr($lname,0,1)); }
        if (!empty($trow['phone']))    $phone    = htmlspecialchars($trow['phone']);
        $tenant_property_id = (int)($trow['property_id'] ?? 0);
        $rent_amount_raw    = (float)($trow['rent_amount'] ?? 0);
        $rent_amount        = number_format($rent_amount_raw);
        $lease_start        = $trow['lease_start'] ? date('d M Y', strtotime($trow['lease_start'])) : 'N/A';
        $lease_end          = $trow['lease_end']   ? date('d M Y', strtotime($trow['lease_end']))   : 'N/A';
        $tenant_status      = htmlspecialchars($trow['status']          ?? 'Active');
        $gender             = htmlspecialchars($trow['gender']          ?? 'N/A');
        $national_id        = htmlspecialchars($trow['national_id']     ?? 'N/A');
        $occupation         = htmlspecialchars($trow['occupation']      ?? 'N/A');
        $emergency_name     = htmlspecialchars($trow['emergency_name']  ?? 'N/A');
        $emergency_phone    = htmlspecialchars($trow['emergency_phone'] ?? 'N/A');
        $property           = htmlspecialchars($trow['property_name']   ?? 'Not yet assigned');
        $address            = htmlspecialchars($trow['address']         ?? '');
    }
}
 
// Payments
$payments = [];
$ps = mysqli_prepare($conn,
    "SELECT pay.amount, pay.payment_method, pay.transaction_ref, pay.status, pay.date, p.property_name
     FROM payments pay LEFT JOIN properties p ON pay.property_id=p.id
     WHERE pay.tenant_id=? ORDER BY pay.date DESC LIMIT 10");
if ($ps) {
    mysqli_stmt_bind_param($ps, "i", $user_id);
    mysqli_stmt_execute($ps);
    $pr = mysqli_stmt_get_result($ps);
    while ($p = mysqli_fetch_assoc($pr)) $payments[] = $p;
}
 
// Maintenance
$maintenance = [];
$ms = mysqli_prepare($conn, "SELECT issue, status, created_at FROM maintenance WHERE tenant_id=? ORDER BY created_at DESC LIMIT 10");
if ($ms) {
    mysqli_stmt_bind_param($ms, "i", $user_id);
    mysqli_stmt_execute($ms);
    $mr = mysqli_stmt_get_result($ms);
    while ($m = mysqli_fetch_assoc($mr)) {
        $maintenance[] = [
            'issue_title'    => $m['issue'],
            'submitted_date' => $m['created_at'] ? date('d M Y', strtotime($m['created_at'])) : '—',
            'status'         => $m['status'] ?? 'open',
        ];
    }
}
 
// Notifications
$notifications = [];
$ns = mysqli_prepare($conn,
    "SELECT title, message, `date`, is_read, status FROM notifications
     WHERE tenant_id=? OR user_id=? ORDER BY `date` DESC LIMIT 20");
if ($ns) {
    mysqli_stmt_bind_param($ns, "ii", $user_id, $user_id);
    mysqli_stmt_execute($ns);
    $nr = mysqli_stmt_get_result($ns);
    while ($n = mysqli_fetch_assoc($nr)) {
        $notifications[] = [
            'title'      => $n['title'],
            'message'    => $n['message'],
            'created_at' => $n['date'] ? date('d M Y, g:i A', strtotime($n['date'])) : '—',
            'is_read'    => ($n['status']==='read' || $n['is_read']==1) ? 1 : 0,
        ];
    }
}
 
$unread_count = count(array_filter($notifications, fn($n) => !$n['is_read']));
$open_maint   = count(array_filter($maintenance,   fn($m) => in_array($m['status'], ['open','in_progress'])));
$total_paid   = array_sum(array_column($payments, 'amount'));
$pending_pay  = count(array_filter($payments, fn($p) => $p['status']==='pending'));

 
    // ── Tenant Documents ──
    $documents = [];
    $dq = mysqli_prepare($conn, "SELECT document_name, file_path, uploaded_at FROM tenant_documents WHERE tenant_id=? ORDER BY uploaded_at DESC");
    if ($dq) {
        $tenant_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM tenants WHERE user_id=$user_id LIMIT 1"));
        $tenant_rec_id = (int)($tenant_row['id'] ?? 0);
        if ($tenant_rec_id > 0) {
            mysqli_stmt_bind_param($dq, "i", $tenant_rec_id);
            mysqli_stmt_execute($dq);
            $dr = mysqli_stmt_get_result($dq);
            while ($d = mysqli_fetch_assoc($dr)) $documents[] = $d;
        }
    }
 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25);--red:#ff5f57;--green:#30d158;--sw:260px}
html,body{height:100%;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden}
body{cursor:none}
#cd{width:8px;height:8px;background:var(--gold);border-radius:50%;position:fixed;z-index:9999;pointer-events:none;transform:translate(-50%,-50%);mix-blend-mode:difference}
#cr{width:22px;height:22px;border:1.5px solid rgba(200,164,60,.6);border-radius:50%;position:fixed;z-index:9998;pointer-events:none;transform:translate(-50%,-50%);transition:left .08s,top .08s,width .3s,height .3s}
body.ch #cd{width:6px;height:6px;background:#fff}
body.ch #cr{width:30px;height:30px;background:rgba(200,164,60,.06)}
.pbg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.18),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.12),transparent 50%),var(--ink)}
.pgr{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.018) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.018) 1px,transparent 1px);background-size:72px 72px}
 
/* SIDEBAR */
.sb{width:var(--sw);background:rgba(6,12,28,.98);border-right:1px solid var(--border);position:fixed;top:0;left:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column;z-index:500;transition:transform .35s cubic-bezier(.23,1,.32,1)}
.sb::-webkit-scrollbar{width:3px}.sb::-webkit-scrollbar-thumb{background:var(--gb);border-radius:2px}
.sb-head{padding:24px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.sb-logo-icon{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--gb),rgba(14,90,200,.3));border:1.5px solid var(--gb);display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0}
.sb-logo-text{font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--white);line-height:1}
.sb-logo-sub{font-size:9px;color:var(--muted);letter-spacing:1px}
.sb-tenant{padding:16px 20px;border-bottom:1px solid var(--border)}
.sb-av{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.4),rgba(14,90,200,.4));border:2px solid var(--gb);display:flex;align-items:center;justify-content:center;font-family:"Cormorant Garamond",serif;font-size:17px;font-weight:700;color:var(--white);margin-bottom:10px}
.sb-name{font-size:13px;font-weight:600;color:var(--white);margin-bottom:2px}
.sb-unit{font-size:11px;color:var(--muted)}
.sb-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;background:rgba(48,209,88,.1);border:1px solid rgba(48,209,88,.2);border-radius:20px;font-size:9px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--green);margin-top:7px}
.sb-nav{padding:14px 0;flex:1}
.nl{font-size:9px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.18);padding:0 20px;margin-bottom:5px;margin-top:16px}
.nl:first-child{margin-top:0}
.na{display:flex;align-items:center;gap:10px;padding:10px 20px;font-size:13px;font-weight:500;color:var(--muted);border:none;background:none;width:100%;text-align:left;cursor:pointer;transition:all .2s;position:relative;text-decoration:none}
.na:hover{color:var(--white);background:rgba(255,255,255,.04)}
.na.active{color:var(--gold);background:rgba(200,164,60,.08)}
.na.active::before{content:"";position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:58%;background:var(--gold);border-radius:0 2px 2px 0}
.ni{font-size:15px;width:20px;text-align:center;flex-shrink:0}
.nb{margin-left:auto;padding:2px 7px;background:var(--gold);color:var(--ink);border-radius:10px;font-size:9px;font-weight:700}
.nb.r{background:var(--red)}
.sb-foot{padding:16px 20px;border-top:1px solid var(--border)}
.lo{width:100%;padding:10px;background:rgba(255,95,87,.07);border:1px solid rgba(255,95,87,.2);color:#ff8f8a;font-family:"Outfit",sans-serif;font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;border-radius:6px;cursor:pointer;transition:all .25s;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none}
.lo:hover{background:rgba(255,95,87,.14)}
 
/* MAIN */
.mc{margin-left:var(--sw);display:flex;flex-direction:column;min-height:100vh;position:relative;z-index:10}
.tb{display:flex;align-items:center;justify-content:space-between;padding:15px 32px;border-bottom:1px solid var(--border);background:rgba(4,9,26,.8);backdrop-filter:blur(20px);position:sticky;top:0;z-index:100}
.tbl{display:flex;align-items:center;gap:12px}
.mt{display:none;width:36px;height:36px;background:rgba(255,255,255,.06);border:1px solid var(--border);border-radius:8px;align-items:center;justify-content:center;cursor:pointer;font-size:17px;flex-shrink:0}
.pt{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--white)}
.pb{font-size:10px;color:var(--muted);letter-spacing:1px}
.tbr{display:flex;align-items:center;gap:10px}
.tbb{width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,.04);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;transition:all .2s;position:relative;color:var(--white);text-decoration:none}
.tbb:hover{background:rgba(200,164,60,.08);border-color:var(--gb)}
.nd{position:absolute;top:5px;right:5px;width:7px;height:7px;background:var(--red);border-radius:50%;border:1.5px solid var(--ink)}
 
/* PAGES */
.pg{display:none;padding:28px 32px;flex:1;animation:fu .4s ease both}
.pg.active{display:block}
@keyframes fu{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
 
/* SHARED */
.ey{font-size:10px;font-weight:600;letter-spacing:3.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px;display:flex;align-items:center;gap:10px}
.ey::before{content:"";width:18px;height:1px;background:var(--gold)}
.sh{font-family:"Cormorant Garamond",serif;font-size:clamp(22px,3vw,32px);font-weight:700;color:var(--white);margin-bottom:6px;line-height:1.1}
.sh em{color:var(--gold);font-style:italic}
.sp{font-size:13px;color:var(--muted);margin-bottom:24px;line-height:1.65;max-width:580px}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:22px;transition:border-color .3s}
.card:hover{border-color:var(--gb)}
.ch2{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
.ch3{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px}
.ch4{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
.ct{font-family:"Cormorant Garamond",serif;font-size:17px;font-weight:700;color:var(--white)}
.chead{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.ca{font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--gold);cursor:pointer;border:none;background:none}
 
/* BADGES */
.bx{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:9px;font-weight:700;letter-spacing:.5px;text-transform:uppercase}
.bx.paid,.bx.active,.bx.resolved,.bx.approved{background:rgba(48,209,88,.1);border:1px solid rgba(48,209,88,.2);color:var(--green)}
.bx.pending,.bx.open{background:rgba(200,164,60,.1);border:1px solid var(--gb);color:var(--gold)}
.bx.overdue,.bx.rejected,.bx.failed,.bx.due{background:rgba(255,95,87,.1);border:1px solid rgba(255,95,87,.2);color:var(--red)}
.bx.progress{background:rgba(14,90,200,.1);border:1px solid rgba(14,90,200,.25);color:#5b9cff}
 
/* METRICS */
.mc-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:18px;transition:all .35s}
.mc-card:hover{border-color:var(--gb);background:rgba(200,164,60,.04);transform:translateY(-3px)}
.mc-icon{font-size:22px;margin-bottom:10px}
.mc-val{font-family:"Cormorant Garamond",serif;font-size:28px;font-weight:700;color:var(--white);line-height:1}
.mc-val.g{color:var(--gold)}.mc-val.gr{color:var(--green)}.mc-val.r{color:var(--red)}
.mc-lbl{font-size:11px;color:var(--muted);letter-spacing:1px;margin-top:4px}
.mc-sub{font-size:11px;color:var(--green);margin-top:5px}
.mc-sub.n{color:var(--red)}
 
/* WELCOME */
.wb{background:linear-gradient(135deg,rgba(200,164,60,.1),rgba(14,90,200,.09));border:1px solid var(--border);border-radius:14px;padding:26px 30px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:20px}
.wb h2{font-family:"Cormorant Garamond",serif;font-size:24px;font-weight:700;color:var(--white);margin-bottom:5px}
.wb h2 em{color:var(--gold);font-style:italic}
.wb p{font-size:13px;color:var(--muted);line-height:1.6}
 
/* ACTIVITY */
.ai{display:flex;gap:12px;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--border)}
.ai:last-child{border-bottom:none;margin-bottom:0;padding-bottom:0}
.ad{width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,.05);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0}
.ab{flex:1}.at{font-size:13px;color:rgba(255,255,255,.8);line-height:1.5;margin-bottom:2px}.at strong{color:var(--white)}
.atm{font-size:11px;color:var(--muted)}
 
/* TABLE */
.dt{width:100%;border-collapse:collapse}
.dt th{font-size:9px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--muted);padding:8px 12px;border-bottom:1px solid var(--border);text-align:left;white-space:nowrap}
.dt td{font-size:12px;color:rgba(255,255,255,.8);padding:12px;border-bottom:1px solid rgba(255,255,255,.04)}
.dt tr:last-child td{border-bottom:none}
.dt tr:hover td{background:rgba(255,255,255,.02)}
 
/* FORMS */
.fl{margin-bottom:14px}
.fl label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.fl input,.fl select,.fl textarea{width:100%;padding:11px 13px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:6px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .25s}
.fl input:focus,.fl select:focus,.fl textarea:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
.fl input::placeholder,.fl textarea::placeholder{color:var(--muted)}
.fl select option{background:var(--ink)}
.fl textarea{resize:vertical;min-height:85px}
.fr{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.sbtn{width:100%;padding:12px;background:var(--gold);color:var(--ink);font-family:"Outfit",sans-serif;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;border:none;border-radius:6px;cursor:pointer;transition:all .3s;margin-top:4px}
.sbtn:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 8px 22px rgba(200,164,60,.3)}
 
/* PAYMENT CARDS */
.pc{border-radius:12px;padding:22px;border:1px solid var(--border)}
.pc-momo{border-color:rgba(255,196,0,.25);background:rgba(255,196,0,.04)}
.pc-card{border-color:rgba(59,130,246,.25);background:rgba(59,130,246,.04)}
.pc-bank{border-color:rgba(200,164,60,.25);background:rgba(200,164,60,.04)}
.pc-icon{font-size:36px;margin-bottom:12px}
.pc-title{font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;margin-bottom:4px}
.pc-sub{font-size:12px;color:var(--muted);margin-bottom:4px}
.pc-tag{font-size:11px;margin-bottom:18px}
.pc-momo .pc-title{color:#fcd34d}.pc-momo .pc-tag{color:rgba(255,196,0,.55)}
.pc-card .pc-title{color:#93c5fd}.pc-card .pc-tag{color:rgba(59,130,246,.55)}
.pc-bank .pc-title{color:var(--gold)}.pc-bank .pc-tag{color:rgba(200,164,60,.55)}
.pay-field label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:6px}
.pc-momo .pay-field label{color:rgba(255,196,0,.6)}
.pc-card .pay-field label{color:rgba(59,130,246,.6)}
.pc-bank .pay-field label{color:rgba(200,164,60,.6)}
.pay-field input{width:100%;padding:10px 12px;background:rgba(255,255,255,.05);border-radius:6px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;margin-bottom:12px}
.pc-momo .pay-field input{border:1px solid rgba(255,196,0,.2)}
.pc-card .pay-field input{border:1px solid rgba(59,130,246,.2)}
.pc-bank .pay-field input{border:1px solid var(--gb)}
.pay-btn{width:100%;padding:12px;font-family:"Outfit",sans-serif;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;border:none;border-radius:6px;cursor:pointer;transition:all .3s}
.pc-momo .pay-btn{background:#fcd34d;color:#1a1000}.pc-momo .pay-btn:hover{background:#fde68a}
.pc-card .pay-btn{background:#93c5fd;color:#0c1a3a}.pc-card .pay-btn:hover{background:#bfdbfe}
.pc-bank .pay-btn{background:var(--gold);color:var(--ink)}.pc-bank .pay-btn:hover{background:var(--gold-l)}
.pay-btn:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.3)}
.bank-details{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:14px;margin-bottom:14px;font-size:12px}
.bank-details div{color:var(--muted);margin-bottom:4px}.bank-details div:last-child{margin-bottom:0}
.bank-details strong{color:var(--white)}
 
/* RENT BAR */
.ra{font-family:"Cormorant Garamond",serif;font-size:40px;font-weight:700;color:var(--gold);line-height:1}
.rp{font-size:12px;color:var(--muted);margin:4px 0 16px}
.prb{height:5px;background:rgba(255,255,255,.07);border-radius:3px;overflow:hidden;margin-bottom:7px}
.prf{height:100%;border-radius:3px;background:linear-gradient(90deg,var(--gold),var(--gold-l));transition:width 1.2s cubic-bezier(.23,1,.32,1)}
.rr{display:flex;justify-content:space-between;font-size:12px;color:var(--muted)}
 
/* PRIORITY */
.prrow{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.prchip{padding:9px;border:1px solid var(--border);border-radius:7px;font-size:11px;font-weight:600;text-align:center;cursor:pointer;transition:all .25s;color:var(--muted);background:none;font-family:"Outfit",sans-serif}
.prchip.low.sel,.prchip.low:hover{border-color:var(--green);color:var(--green);background:rgba(48,209,88,.05)}
.prchip.med.sel,.prchip.med:hover{border-color:var(--gold);color:var(--gold);background:rgba(200,164,60,.05)}
.prchip.hi.sel,.prchip.hi:hover{border-color:var(--red);color:var(--red);background:rgba(255,95,87,.05)}
 
/* LEASE */
.ldoc{background:rgba(255,255,255,.02);border:1px solid var(--border);border-radius:12px;padding:26px;max-width:660px}
.ltop{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:22px;padding-bottom:18px;border-bottom:1px solid var(--border)}
.lbrand{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--gold)}
.lbsub{font-size:11px;color:var(--muted)}
.lparts{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px}
.lp{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:13px}
.lpr{font-size:9px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:5px}
.lpn{font-size:14px;font-weight:600;color:var(--white)}
.lpd{font-size:11px;color:var(--muted)}
.ls{margin-bottom:16px}
.ls h4{font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:7px}
.ls p{font-size:12px;color:rgba(255,255,255,.7);line-height:1.7}
.lsigs{display:flex;gap:12px;padding-top:18px;border-top:1px solid var(--border);margin-top:18px}
.lsig{flex:1;padding:13px;border:1px dashed rgba(200,164,60,.3);border-radius:8px;text-align:center}
.lsigl{font-size:9px;color:var(--muted);letter-spacing:1px;text-transform:uppercase}
.lsigv{font-family:"Cormorant Garamond",serif;font-size:17px;font-style:italic;color:var(--gold);margin-top:3px}
.dlbtn{padding:9px 18px;background:rgba(200,164,60,.1);border:1px solid var(--gb);color:var(--gold);font-family:"Outfit",sans-serif;font-size:10px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;border-radius:6px;cursor:pointer;transition:all .25s}
 
/* NOTIFS */
.ni-item{display:flex;gap:12px;padding:13px;border-radius:8px;transition:background .2s}
.ni-item:hover{background:rgba(255,255,255,.02)}
.ni-item.unread{background:rgba(200,164,60,.04)}
.ni-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;background:rgba(255,255,255,.04);border:1px solid var(--border)}
.ni-body{flex:1}
.ni-t{font-size:13px;font-weight:600;color:var(--white);margin-bottom:3px}
.ni-item.unread .ni-t::after{content:"●";font-size:6px;color:var(--gold);margin-left:6px;vertical-align:middle}
.ni-m{font-size:12px;color:var(--muted);line-height:1.6;margin-bottom:3px}
.ni-d{font-size:10px;color:rgba(255,255,255,.2)}
 
/* PROFILE */
.ph{display:flex;align-items:center;gap:20px;padding:24px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:14px;margin-bottom:18px}
.pav{width:68px;height:68px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.5),rgba(14,90,200,.4));border:2px solid var(--gb);display:flex;align-items:center;justify-content:center;font-family:"Cormorant Garamond",serif;font-size:26px;font-weight:700;color:var(--white);flex-shrink:0}
.pi h2{font-family:"Cormorant Garamond",serif;font-size:22px;font-weight:700;color:var(--white);margin-bottom:4px}
.pi p{font-size:13px;color:var(--muted);margin-bottom:8px}
.pgrid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.pf{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:13px}
.pfl{font-size:9px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:5px}
.pfv{font-size:13px;color:var(--white)}
 
/* TOAST */
.toast{position:fixed;bottom:26px;right:26px;z-index:9999;padding:13px 18px;border-radius:8px;background:rgba(8,18,48,.98);border:1px solid var(--gb);font-size:13px;color:var(--white);display:flex;align-items:center;gap:10px;box-shadow:0 20px 50px rgba(0,0,0,.5);transform:translateY(14px);opacity:0;pointer-events:none;transition:all .35s cubic-bezier(.23,1,.32,1);max-width:340px}
.toast.show{transform:translateY(0);opacity:1}
 
/* OVERLAY */
.ov{display:none;position:fixed;inset:0;z-index:499;background:rgba(0,0,0,.6);backdrop-filter:blur(4px)}
.ov.show{display:block}
 
/* STAR RATING */
.sr-row{display:flex;gap:6px;font-size:26px;margin-bottom:6px}
.sr-row span{cursor:pointer;opacity:.3;color:var(--gold);transition:opacity .2s}
 
@media(max-width:960px){
  body{cursor:auto}#cd,#cr{display:none}
  :root{--sw:0px}
  .sb{transform:translateX(-260px);width:260px}.sb.open{transform:translateX(0)}
  .mc{margin-left:0}.mt{display:flex}
  .ch4{grid-template-columns:1fr 1fr}.ch2,.ch3{grid-template-columns:1fr}
  .pg{padding:18px 14px}.tb{padding:13px 14px}
  .pgrid{grid-template-columns:1fr}.lparts{grid-template-columns:1fr}.fr{grid-template-columns:1fr}
}
</style>
</head>
<body>
<div id="cd"></div><div id="cr"></div>
<div class="pbg"></div><div class="pgr"></div>
<div class="toast" id="toast"><span id="ti">✓</span><span id="tm"></span></div>
<div class="ov" id="ov" onclick="closeSB()"></div>
 
<!-- ═══ SIDEBAR ═══ -->
<aside class="sb" id="sb">
  <div class="sb-head">
    <div class="sb-logo-icon">🏠</div>
    <div><div class="sb-logo-text">HOUSING HUB</div><div class="sb-logo-sub">Tenant Portal</div></div>
  </div>
  <div class="sb-tenant">
    <div class="sb-av"><?= $initials ?></div>
    <div class="sb-name"><?= $fullname ?></div>
    <div class="sb-unit"><?= $property ?></div>
    <div class="sb-badge">✓ Verified Tenant</div>
  </div>
  <nav class="sb-nav">
    <div class="nl">Main</div>
    <button class="na active" onclick="show('overview',this)"><span class="ni">🏠</span>Overview</button>
    <button class="na" onclick="show('property',this)"><span class="ni">🏢</span>My Property</button>
    <button class="na" onclick="show('payments',this)"><span class="ni">💳</span>Payments<?php if($pending_pay>0):?><span class="nb"><?=$pending_pay?></span><?php endif;?></button>
    <button class="na" onclick="show('lease',this)"><span class="ni">📄</span>Lease Agreement</button>
    <div class="nl">Services</div>
    <button class="na" onclick="show('maintenance',this)"><span class="ni">🔧</span>Maintenance<?php if($open_maint>0):?><span class="nb r"><?=$open_maint?></span><?php endif;?></button>
    <button class="na" onclick="show('visitors',this)"><span class="ni">🪪</span>Visitor Management</button>
    <button class="na" onclick="show('complaints',this)"><span class="ni">💬</span>Complaints &amp; Feedback</button>
    <div class="nl">Account</div>
    <button class="na" onclick="show('notifications',this)"><span class="ni">🔔</span>Notifications<?php if($unread_count>0):?><span class="nb"><?=$unread_count?></span><?php endif;?></button>
    <button class="na" onclick="show('profile',this)"><span class="ni">👤</span>My Profile</button>
    <button class="na" onclick="show('documents',this)"><span class="ni">📁</span>My Documents<?php if(!empty($documents)):?><span class="nb"><?=count($documents)?></span><?php endif;?></button>
  </nav>
  <div class="sb-foot"><a href="logout.php" class="lo">⬡ &nbsp;Sign Out</a></div>
</aside>
 
<!-- ═══ MAIN ═══ -->
<div class="mc">
 
  <!-- TOPBAR -->
  <div class="tb">
    <div class="tbl">
      <div class="mt" onclick="toggleSB()">☰</div>
      <div><div class="pt" id="pt">Overview</div><div class="pb">HousingHub · Tenant Dashboard</div></div>
    </div>
    <div class="tbr">
      <div class="tbb" onclick="show('notifications',null)">🔔<?php if($unread_count>0):?><span class="nd"></span><?php endif;?></div>
      <div class="tbb" onclick="show('profile',null)">👤</div>
    </div>
  </div>
 
  <!-- ══ OVERVIEW ══ -->
  <div class="pg active" id="pg-overview">
    <div class="wb">
      <div>
        <h2>Good day, <em><?= $fname ?></em>!</h2>
        <p>Here's a snapshot of your tenancy at <strong style="color:var(--white)"><?= $property ?></strong>.</p>
      </div>
      <div style="font-size:50px;opacity:.85;flex-shrink:0">🌤</div>
    </div>
    <div class="ch4">
      <div class="mc-card"><div class="mc-icon">💰</div><div class="mc-val g">UGX <?= $rent_amount ?></div><div class="mc-lbl">Monthly Rent</div><div class="mc-sub">Due 1st of month</div></div>
      <div class="mc-card"><div class="mc-icon">🔧</div><div class="mc-val <?= $open_maint>0?'r':'gr' ?>"><?= $open_maint ?></div><div class="mc-lbl">Open Maintenance</div><div class="mc-sub <?= $open_maint>0?'n':'' ?>"><?= $open_maint>0?'Needs attention':'All resolved' ?></div></div>
      <div class="mc-card"><div class="mc-icon">📄</div><div class="mc-val gr">Active</div><div class="mc-lbl">Lease Status</div><div class="mc-sub">Expires <?= $lease_end ?></div></div>
      <div class="mc-card"><div class="mc-icon">🔔</div><div class="mc-val <?= $unread_count>0?'g':'' ?>"><?= $unread_count ?></div><div class="mc-lbl">Unread Alerts</div><div class="mc-sub">Notifications</div></div>
    </div>
    <div class="ch2">
      <div class="card">
        <div class="chead"><div class="ct">Rent This Month</div><button class="ca" onclick="show('payments',null)">Pay Now →</button></div>
        <div class="ra">UGX <?= $rent_amount ?></div>
        <div class="rp">Due 1st of next month · <?= $property ?></div>
        <div class="prb"><div class="prf" style="width:0%" id="rb"></div></div>
        <div class="rr"><span>Monthly payment</span><span class="bx <?= $pending_pay>0?'due':'paid' ?>"><?= $pending_pay>0?'⏳ Pending':'✓ Up to date' ?></span></div>
        <button class="sbtn" style="margin-top:14px" onclick="show('payments',null)">Pay via MoMo / Card / Bank →</button>
      </div>
      <div class="card">
        <div class="chead"><div class="ct">Recent Activity</div></div>
        <?php if(empty($maintenance)&&empty($payments)): ?>
        <div class="ai"><div class="ad">👋</div><div class="ab"><div class="at"><strong>Welcome to HousingHub!</strong> Your dashboard is ready.</div><div class="atm">Just now</div></div></div>
        <?php endif; ?>
        <?php foreach(array_slice($payments,0,2) as $p): ?>
        <div class="ai"><div class="ad">💳</div><div class="ab"><div class="at"><strong>Payment</strong> of UGX <?= number_format($p['amount']) ?> — <?= htmlspecialchars($p['property_name']??'Property') ?></div><div class="atm"><?= $p['date']?date('d M Y',strtotime($p['date'])):'—' ?></div></div></div>
        <?php endforeach; ?>
        <?php foreach(array_slice($maintenance,0,2) as $m): ?>
        <div class="ai"><div class="ad">🔧</div><div class="ab"><div class="at"><strong><?= htmlspecialchars($m['issue_title']) ?></strong></div><div class="atm"><?= htmlspecialchars($m['submitted_date']) ?></div></div></div>
        <?php endforeach; ?>
        <?php foreach(array_slice($notifications,0,2) as $n): ?>
        <div class="ai"><div class="ad">🔔</div><div class="ab"><div class="at"><strong><?= htmlspecialchars($n['title']) ?></strong></div><div class="atm"><?= htmlspecialchars($n['created_at']) ?></div></div></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
 
  <!-- ══ MY PROPERTY ══ -->
  <div class="pg" id="pg-property">
    <div class="ey">My Home</div><h2 class="sh">My <em>Property</em></h2>
    <p class="sp">Details of the property you are currently renting through HousingHub.</p>
    <div class="card" style="margin-bottom:18px;padding:0;overflow:hidden">
      <div style="height:150px;background:linear-gradient(135deg,rgba(200,164,60,.2),rgba(14,90,200,.2));display:flex;align-items:center;justify-content:center;font-size:56px;border-radius:10px 10px 0 0;border-bottom:1px solid var(--border)">🏢</div>
      <div style="padding:20px">
        <div style="margin-bottom:10px"><span class="bx active">● Active Tenancy</span></div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;color:var(--white);margin-bottom:4px"><?= $property ?></div>
        <div style="font-size:13px;color:var(--muted);margin-bottom:14px">📍 <?= $address ?: 'Address not listed' ?></div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
          <div style="text-align:center;padding:10px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px"><div style="font-size:16px;font-weight:700;color:var(--gold)">UGX <?= $rent_amount ?></div><div style="font-size:10px;color:var(--muted);margin-top:2px">Monthly Rent</div></div>
          <div style="text-align:center;padding:10px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px"><div style="font-size:16px;font-weight:700;color:var(--gold)"><?= $lease_start ?></div><div style="font-size:10px;color:var(--muted);margin-top:2px">Lease Start</div></div>
          <div style="text-align:center;padding:10px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px"><div style="font-size:16px;font-weight:700;color:var(--gold)"><?= $lease_end ?></div><div style="font-size:10px;color:var(--muted);margin-top:2px">Lease End</div></div>
        </div>
      </div>
    </div>
    <div class="ch2">
      <div class="card"><div class="chead"><div class="ct">Landlord</div></div><div style="display:flex;gap:12px;align-items:center"><div style="width:44px;height:44px;border-radius:50%;background:rgba(200,164,60,.2);border:1px solid var(--gb);display:flex;align-items:center;justify-content:center;font-size:20px">👨‍💼</div><div><div style="font-size:14px;font-weight:600;color:var(--white)"><?= $landlord ?></div><div style="font-size:11px;color:var(--muted)">Property Owner</div></div></div></div>
      <div class="card"><div class="chead"><div class="ct">Property Manager</div></div><div style="display:flex;gap:12px;align-items:center"><div style="width:44px;height:44px;border-radius:50%;background:rgba(14,90,200,.2);border:1px solid rgba(14,90,200,.3);display:flex;align-items:center;justify-content:center;font-size:20px">👩‍💼</div><div><div style="font-size:14px;font-weight:600;color:var(--white)">HousingHub Manager</div><div style="font-size:11px;color:var(--muted)">Contact via portal</div></div></div></div>
    </div>
  </div>
 
  <!-- ══ PAYMENTS ══ -->
  <div class="pg" id="pg-payments">
    <div class="ey">Financial</div><h2 class="sh">Rent <em>Payments</em></h2>
    <p class="sp">Pay your rent directly from your dashboard. Choose your preferred method below.</p>
 
    <div class="ch3">
      <!-- MTN MOBILE MONEY -->
      <div class="pc pc-momo">
        <div class="pc-icon">📱</div>
        <div class="pc-title">MTN Mobile Money</div>
        <div class="pc-sub">MTN MoMo · Airtel Money</div>
        <div class="pc-tag">Instant confirmation</div>
        <form method="POST" action="process_payment.php">
          <input type="hidden" name="property_id" value="<?= $tenant_property_id ?>">
          <input type="hidden" name="action" value="rent">
          <input type="hidden" name="method" value="mobile_money">
          <div class="pay-field">
            <label>Phone Number</label>
            <input type="tel" name="phone_number" placeholder="e.g. 0772 000 000" required>
          </div>
          <div style="font-size:12px;color:rgba(255,196,0,.7);margin-bottom:12px">Amount: <strong style="color:#fcd34d">UGX <?= $rent_amount ?></strong></div>
          <button type="submit" class="pay-btn">Pay Now →</button>
        </form>
      </div>
 
      <!-- CARD -->
      <div class="pc pc-card">
        <div class="pc-icon">💳</div>
        <div class="pc-title">Debit / Credit Card</div>
        <div class="pc-sub">Visa · Mastercard · Verve</div>
        <div class="pc-tag">Secured by Flutterwave</div>
        <form method="POST" action="process_payment.php">
          <input type="hidden" name="property_id" value="<?= $tenant_property_id ?>">
          <input type="hidden" name="action" value="rent">
          <input type="hidden" name="method" value="card">
          <div class="pay-field">
            <label>Card Number</label>
            <input type="text" name="card_hint" placeholder="**** **** **** ****" maxlength="19">
          </div>
          <div style="font-size:12px;color:rgba(59,130,246,.7);margin-bottom:12px">Amount: <strong style="color:#93c5fd">UGX <?= $rent_amount ?></strong></div>
          <button type="submit" class="pay-btn">Pay Now →</button>
        </form>
      </div>
 
      <!-- BANK TRANSFER -->
      <div class="pc pc-bank">
        <div class="pc-icon">🏦</div>
        <div class="pc-title">Bank Transfer</div>
        <div class="pc-sub">Direct bank transfer</div>
        <div class="pc-tag">1–2 business days</div>
        <div class="bank-details">
          <div>Transfer to:</div>
          <div><strong>HousingHub Ltd</strong></div>
          <div>Bank: <strong>Stanbic Bank Uganda</strong></div>
          <div>A/C: <strong>9030012345678</strong></div>
          <div>Ref: <strong style="color:var(--gold)">TEN-<?= $user_id ?>-RENT</strong></div>
        </div>
        <form method="POST" action="process_payment.php">
          <input type="hidden" name="property_id" value="<?= $tenant_property_id ?>">
          <input type="hidden" name="action" value="rent">
          <input type="hidden" name="method" value="bank">
          <div class="pay-field">
            <label>Your Bank Receipt / Ref No.</label>
            <input type="text" name="bank_ref" placeholder="e.g. STB2026123456" required>
          </div>
          <div style="font-size:12px;color:rgba(200,164,60,.7);margin-bottom:12px">Amount: <strong style="color:var(--gold)">UGX <?= $rent_amount ?></strong></div>
          <button type="submit" class="pay-btn">Confirm Transfer →</button>
        </form>
      </div>
    </div>
 
    <!-- SUMMARY + HISTORY -->
    <?php
      $pc = count(array_filter($payments, fn($p)=>in_array($p['status'],['paid','completed','success'])));
      $pn = count(array_filter($payments, fn($p)=>$p['status']==='pending'));
    ?>
    <div class="ch2" style="margin-top:4px">
      <div class="card">
        <div class="chead"><div class="ct">Payment Summary</div></div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:14px">
          <div style="background:rgba(48,209,88,.06);border:1px solid rgba(48,209,88,.15);border-radius:8px;padding:12px;text-align:center"><div style="font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:var(--green)"><?= $pc ?></div><div style="font-size:10px;color:var(--muted);margin-top:3px">Completed</div></div>
          <div style="background:rgba(200,164,60,.06);border:1px solid var(--gb);border-radius:8px;padding:12px;text-align:center"><div style="font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:var(--gold)"><?= $pn ?></div><div style="font-size:10px;color:var(--muted);margin-top:3px">Pending</div></div>
          <div style="background:rgba(14,90,200,.06);border:1px solid rgba(14,90,200,.2);border-radius:8px;padding:12px;text-align:center"><div style="font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:#5b9cff"><?= count($payments) ?></div><div style="font-size:10px;color:var(--muted);margin-top:3px">Total</div></div>
        </div>
        <div style="font-size:13px;color:var(--muted)">Total paid: <strong style="color:var(--gold)">UGX <?= number_format($total_paid) ?></strong></div>
      </div>
      <div class="card">
        <div class="chead"><div class="ct">Current Rent Due</div></div>
        <div class="ra">UGX <?= $rent_amount ?></div>
        <div class="rp">Due 1st each month · <?= $property ?></div>
        <div class="prb"><div class="prf" style="width:0%"></div></div>
        <div class="rr"><span>Monthly payment</span><span class="bx <?= $pn>0?'due':'paid' ?>"><?= $pn>0?'⏳ Pending':'✓ Up to date' ?></span></div>
      </div>
    </div>
    <div class="card">
      <div class="chead"><div class="ct">Payment History</div></div>
      <?php if(empty($payments)): ?>
        <p style="font-size:13px;color:var(--muted);padding:10px 0">No payment records yet. Make your first payment above.</p>
      <?php else: ?>
      <table class="dt">
        <thead><tr><th>Property</th><th>Amount (UGX)</th><th>Method</th><th>Reference</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach($payments as $p):
          $st=strtolower($p['status']??'pending');
          $bc=in_array($st,['paid','completed','success'])?'paid':($st==='pending'?'pending':'overdue');
        ?>
        <tr>
          <td><?= htmlspecialchars($p['property_name']??'N/A') ?></td>
          <td><?= number_format($p['amount']) ?></td>
          <td><?= htmlspecialchars(ucwords(str_replace('_',' ',$p['payment_method']??''))) ?></td>
          <td style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($p['transaction_ref']??'—') ?></td>
          <td><?= $p['date']?date('d M Y',strtotime($p['date'])):'—' ?></td>
          <td><span class="bx <?= $bc ?>"><?= ucfirst($st) ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
 
  <!-- ══ LEASE ══ -->
  <div class="pg" id="pg-lease">
    <div class="ey">Legal</div><h2 class="sh">Lease <em>Agreement</em></h2>
    <p class="sp">Your current digital lease. Read or download anytime.</p>
    <div style="display:flex;gap:10px;margin-bottom:18px">
      <span class="bx active">● Active</span>
      <button class="dlbtn" onclick="toast('📄','Lease PDF downloaded.')">↓ Download PDF</button>
    </div>
    <div class="ldoc">
      <div class="ltop">
        <div><div class="lbrand">HOUSING HUB</div><div class="lbsub">Residential Tenancy Agreement</div></div>
        <div style="text-align:right;font-size:11px;color:var(--muted)"><div>Start: <?= $lease_start ?></div><div>Expires: <?= $lease_end ?></div></div>
      </div>
      <div class="lparts">
        <div class="lp"><div class="lpr">Landlord</div><div class="lpn"><?= $landlord ?></div><div class="lpd"><?= $address ?></div></div>
        <div class="lp"><div class="lpr">Tenant</div><div class="lpn"><?= $fullname ?></div><div class="lpd"><?= $property ?></div></div>
      </div>
      <div class="ls"><h4>1. Premises</h4><p>The Landlord agrees to let <?= $property ?>, Uganda to the Tenant for residential use only.</p></div>
      <div class="ls"><h4>2. Term</h4><p>The tenancy commences on <?= $lease_start ?> and continues until <?= $lease_end ?>, converting to month-to-month thereafter unless renewed.</p></div>
      <div class="ls"><h4>3. Rent</h4><p>The Tenant agrees to pay UGX <?= $rent_amount ?> per month on or before the 1st of each calendar month via the HousingHub payment portal.</p></div>
      <div class="ls"><h4>4. Utilities &amp; Maintenance</h4><p>Electricity and water are billed separately. Structural repairs are the Landlord's responsibility and shall be reported via the maintenance portal.</p></div>
      <div class="ls"><h4>5. Termination</h4><p>Either party may terminate with 30 days written notice via the HousingHub portal.</p></div>
      <div class="lsigs">
        <div class="lsig"><div class="lsigl">Landlord Signature</div><div class="lsigv"><?= substr($landlord,0,1) ?>. <?= explode(' ',$landlord)[1]??'' ?></div></div>
        <div class="lsig"><div class="lsigl">Tenant Signature</div><div class="lsigv"><?= $initials[0]??'' ?>. <?= $lname ?></div></div>
      </div>
    </div>
  </div>
 
  <!-- ══ MAINTENANCE ══ -->
  <div class="pg" id="pg-maintenance">
    <div class="ey">Services</div><h2 class="sh">Maintenance <em>Requests</em></h2>
    <p class="sp">Report issues and track resolution in real time.</p>
    <div class="ch2" style="margin-bottom:20px">
      <div class="card">
        <div class="chead"><div class="ct">New Request</div></div>
        <form method="POST" action="submit_maintenance.php">
          <input type="hidden" name="user_id" value="<?= $user_id ?>">
          <div class="fl"><label>Category</label><select name="category"><option>Plumbing</option><option>Electrical</option><option>Structural</option><option>Appliances</option><option>Security</option><option>Other</option></select></div>
          <div class="fl"><label>Issue Title</label><input type="text" name="issue_title" placeholder="Brief title" required></div>
          <div class="fl"><label>Description</label><textarea name="description" placeholder="Describe the issue in detail." required></textarea></div>
          <div class="fl"><label>Priority</label>
            <div class="prrow">
              <button type="button" class="prchip low" onclick="pri(this,'low')">🟢 Low</button>
              <button type="button" class="prchip med sel" onclick="pri(this,'medium')">🟡 Medium</button>
              <button type="button" class="prchip hi" onclick="pri(this,'high')">🔴 Urgent</button>
            </div>
            <input type="hidden" name="priority" id="pv" value="medium">
          </div>
          <button type="submit" class="sbtn">Submit Request →</button>
        </form>
      </div>
      <div class="card">
        <div class="chead"><div class="ct">Open Requests</div></div>
        <?php $op=array_filter($maintenance,fn($m)=>in_array($m['status'],['open','in_progress']));
        if(empty($op)): ?><p style="font-size:13px;color:var(--muted)">No open requests. 🎉</p>
        <?php else: foreach($op as $m): ?>
        <div class="ai"><div class="ad">🔧</div><div class="ab"><div class="at"><strong><?= htmlspecialchars($m['issue_title']) ?></strong></div><div style="margin-top:4px"><span class="bx <?= $m['status']==='in_progress'?'progress':'open' ?>"><?= $m['status']==='in_progress'?'In Progress':'Open' ?></span></div><div class="atm" style="margin-top:4px"><?= htmlspecialchars($m['submitted_date']) ?></div></div></div>
        <?php endforeach; endif; ?>
      </div>
    </div>
    <div class="card">
      <div class="chead"><div class="ct">History</div></div>
      <?php if(empty($maintenance)): ?><p style="font-size:13px;color:var(--muted);padding:10px 0">No records yet.</p>
      <?php else: ?>
      <table class="dt"><thead><tr><th>Issue</th><th>Submitted</th><th>Status</th></tr></thead><tbody>
      <?php foreach($maintenance as $m): $sc=$m['status']==='in_progress'?'progress':$m['status']; ?>
      <tr><td><?= htmlspecialchars($m['issue_title']) ?></td><td><?= htmlspecialchars($m['submitted_date']) ?></td><td><span class="bx <?= $sc ?>"><?= $m['status']==='in_progress'?'In Progress':ucfirst($m['status']) ?></span></td></tr>
      <?php endforeach; ?></tbody></table>
      <?php endif; ?>
    </div>
  </div>
 
  <!-- ══ VISITORS ══ -->
  <div class="pg" id="pg-visitors">
    <div class="ey">Services</div><h2 class="sh">Visitor <em>Management</em></h2>
    <p class="sp">Register guests visiting your unit. All passes are logged securely.</p>
    <div class="ch2">
      <div class="card">
        <div class="chead"><div class="ct">Register a Visitor</div></div>
        <form method="POST" action="register_visitor.php">
          <input type="hidden" name="tenant_id" value="<?= $user_id ?>">
          <input type="hidden" name="property_id" value="<?= $tenant_property_id ?>">
          <div class="fr">
            <div class="fl"><label>Visitor Name</label><input type="text" name="visitor_name" placeholder="Full name" required></div>
            <div class="fl"><label>Relationship</label><input type="text" name="relationship" placeholder="e.g. Friend"></div>
          </div>
          <div class="fr">
            <div class="fl"><label>Visitor Phone</label><input type="tel" name="visitor_phone" placeholder="+256 700 000000"></div>
            <div class="fl"><label>National ID (optional)</label><input type="text" name="visitor_id" placeholder="NIN / Passport"></div>
          </div>
          <div class="fr">
            <div class="fl"><label>Visit Date</label><input type="date" name="visit_date" required></div>
            <div class="fl"><label>Duration</label><input type="text" name="duration" placeholder="e.g. 2 hours"></div>
          </div>
          <div class="fl"><label>Purpose of Visit</label><input type="text" name="purpose" placeholder="e.g. Social visit"></div>
          <button type="submit" class="sbtn">Register Visitor →</button>
        </form>
      </div>
      <div class="card">
        <div class="chead"><div class="ct">Recent Visitors</div></div>
        <?php
        $vq = mysqli_prepare($conn, "SELECT visitor_name, relationship, visit_date, status FROM visitors WHERE tenant_id=? ORDER BY created_at DESC LIMIT 8");
        $vrows = [];
        if($vq){ mysqli_stmt_bind_param($vq,"i",$user_id); mysqli_stmt_execute($vq); $vr=mysqli_stmt_get_result($vq); while($v=mysqli_fetch_assoc($vr)) $vrows[]=$v; }
        if(empty($vrows)): ?><p style="font-size:13px;color:var(--muted)">No visitors registered yet.</p>
        <?php else: foreach($vrows as $v): $vs=strtolower($v['status']??'approved'); $vc=$vs==='approved'?'paid':($vs==='rejected'?'overdue':'pending'); ?>
        <div class="ai"><div class="ad">🪪</div><div class="ab"><div class="at"><strong><?= htmlspecialchars($v['visitor_name']) ?></strong><?php if($v['relationship']): ?> · <?= htmlspecialchars($v['relationship']) ?><?php endif; ?></div><div style="margin-top:4px"><span class="bx <?= $vc ?>"><?= ucfirst($vs) ?></span><span class="atm" style="margin-left:8px"><?= htmlspecialchars($v['visit_date']) ?></span></div></div></div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
 
  <!-- ══ COMPLAINTS ══ -->
  <div class="pg" id="pg-complaints">
    <div class="ey">Feedback</div><h2 class="sh">Complaints &amp; <em>Feedback</em></h2>
    <p class="sp">Lodge a complaint or rate your property experience.</p>
    <div class="ch2">
      <div class="card">
        <div class="chead"><div class="ct">Submit Complaint</div></div>
        <form method="POST" action="submit_complaint.php">
          <input type="hidden" name="tenant_id" value="<?= $user_id ?>">
          <div class="fl"><label>Category</label><select name="category"><option>Noise / Disturbance</option><option>Property Condition</option><option>Landlord Conduct</option><option>Billing Issue</option><option>Security Concern</option><option>Other</option></select></div>
          <div class="fl"><label>Message</label><textarea name="message" placeholder="Describe your complaint in detail." required></textarea></div>
          <button type="submit" class="sbtn">Submit Complaint →</button>
        </form>
      </div>
      <div class="card">
        <div class="chead"><div class="ct">Rate Your Property</div></div>
        <p style="font-size:13px;color:var(--muted);margin-bottom:18px;line-height:1.6">Rate your property experience across three categories.</p>
        <form method="POST" action="submit_rating.php">
          <input type="hidden" name="user_id" value="<?= $user_id ?>">
          <input type="hidden" name="property_id" value="<?= $tenant_property_id ?>">
          <div style="margin-bottom:16px">
            <div style="font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:8px">🧹 Cleanliness</div>
            <div class="sr-row" id="sr-cl"><?php for($i=1;$i<=5;$i++): ?><span onclick="rate('cl',<?=$i?>)">★</span><?php endfor; ?></div>
            <input type="hidden" name="rating_cleanliness" id="v-cl" value="0">
          </div>
          <div style="margin-bottom:16px">
            <div style="font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:8px">🔒 Security</div>
            <div class="sr-row" id="sr-sc"><?php for($i=1;$i<=5;$i++): ?><span onclick="rate('sc',<?=$i?>)">★</span><?php endfor; ?></div>
            <input type="hidden" name="rating_security" id="v-sc" value="0">
          </div>
          <div style="margin-bottom:18px">
            <div style="font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:8px">💰 Value for Money</div>
            <div class="sr-row" id="sr-va"><?php for($i=1;$i<=5;$i++): ?><span onclick="rate('va',<?=$i?>)">★</span><?php endfor; ?></div>
            <input type="hidden" name="rating_value" id="v-va" value="0">
          </div>
          <div class="fl"><label>Comment (optional)</label><textarea name="comment" placeholder="Any additional feedback..."></textarea></div>
          <button type="submit" class="sbtn" onclick="return valRating()">Submit Rating →</button>
        </form>
      </div>
    </div>
  </div>
 
  <!-- ══ NOTIFICATIONS ══ -->
  <div class="pg" id="pg-notifications">
    <div class="ey">Updates</div><h2 class="sh"><em>Notifications</em></h2>
    <p class="sp">All alerts and messages from HousingHub.</p>
    <div class="card">
      <?php if(empty($notifications)): ?><p style="font-size:13px;color:var(--muted);padding:10px 0">No notifications yet.</p>
      <?php else: foreach($notifications as $n): ?>
      <div class="ni-item <?= !$n['is_read']?'unread':'' ?>">
        <div class="ni-icon">🔔</div>
        <div class="ni-body"><div class="ni-t"><?= htmlspecialchars($n['title']) ?></div><div class="ni-m"><?= htmlspecialchars($n['message']) ?></div><div class="ni-d"><?= htmlspecialchars($n['created_at']) ?></div></div>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

    <!-- ══ DOCUMENTS ══ -->
  <div class="pg" id="pg-documents">
    <div class="ey">Files</div><h2 class="sh">My <em>Documents</em></h2>
    <p class="sp">Documents uploaded to your profile by HousingHub management. Click any file to view or download.</p>
    <div class="card">
      <?php if(empty($documents)): ?>
        <div style="text-align:center;padding:40px 20px">
          <div style="font-size:48px;margin-bottom:16px">📂</div>
          <div style="font-size:15px;color:var(--white);font-weight:600;margin-bottom:8px">No documents yet</div>
          <div style="font-size:13px;color:var(--muted);line-height:1.6">Your property manager will upload documents such as your lease agreement, ID copies, receipts and other files here.</div>
        </div>
      <?php else: ?>
        <div style="display:grid;gap:12px">
          <?php foreach($documents as $doc):
            $ext = strtolower(pathinfo($doc['file_path']??'', PATHINFO_EXTENSION));
            $icon = in_array($ext,['pdf']) ? '📄' : (in_array($ext,['doc','docx']) ? '📝' : (in_array($ext,['jpg','jpeg','png']) ? '🖼️' : '📎'));
          ?>
          <div style="display:flex;align-items:center;gap:14px;padding:14px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;transition:border-color .2s" onmouseover="this.style.borderColor='var(--gb)'" onmouseout="this.style.borderColor='rgba(255,255,255,.07)'">
            <div style="width:44px;height:44px;border-radius:9px;background:rgba(200,164,60,.1);border:1px solid var(--gb);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0"><?= $icon ?></div>
            <div style="flex:1">
              <div style="font-size:14px;font-weight:600;color:var(--white);margin-bottom:3px"><?= htmlspecialchars($doc['document_name']) ?></div>
              <div style="font-size:11px;color:var(--muted)"><?= strtoupper($ext) ?> · Uploaded <?= $doc['uploaded_at'] ? date('d M Y', strtotime($doc['uploaded_at'])) : '—' ?></div>
            </div>
            <?php if(!empty($doc['file_path'])): ?>
            <a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank"
               style="padding:8px 16px;background:rgba(200,164,60,.1);border:1px solid var(--gb);border-radius:6px;color:var(--gold);font-size:11px;font-weight:700;text-decoration:none;letter-spacing:1px;white-space:nowrap;transition:all .2s"
               onmouseover="this.style.background='rgba(200,164,60,.2)'"
               onmouseout="this.style.background='rgba(200,164,60,.1)'"
               download>↓ View</a>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
 
 
  <!-- ══ PROFILE ══ -->
  <div class="pg" id="pg-profile">
    <div class="ey">Account</div><h2 class="sh">My <em>Profile</em></h2>
    <p class="sp">Your personal and tenancy details as registered on HousingHub.</p>
    <div class="ph">
      <div class="pav"><?= $initials ?></div>
      <div class="pi"><h2><?= $fullname ?></h2><p><?= $email ?></p><span class="bx active">✓ Verified Tenant</span></div>
    </div>
    <div class="pgrid" style="margin-bottom:20px">
      <div class="pf"><div class="pfl">Full Name</div><div class="pfv"><?= $fullname ?></div></div>
      <div class="pf"><div class="pfl">Email</div><div class="pfv"><?= $email ?></div></div>
      <div class="pf"><div class="pfl">Phone</div><div class="pfv"><?= $phone ?></div></div>
      <div class="pf"><div class="pfl">Gender</div><div class="pfv"><?= $gender ?></div></div>
      <div class="pf"><div class="pfl">National ID</div><div class="pfv"><?= $national_id ?></div></div>
      <div class="pf"><div class="pfl">Occupation</div><div class="pfv"><?= $occupation ?></div></div>
      <div class="pf"><div class="pfl">Property</div><div class="pfv"><?= $property ?></div></div>
      <div class="pf"><div class="pfl">Lease Start</div><div class="pfv"><?= $lease_start ?></div></div>
      <div class="pf"><div class="pfl">Lease End</div><div class="pfv"><?= $lease_end ?></div></div>
      <div class="pf"><div class="pfl">Monthly Rent</div><div class="pfv">UGX <?= $rent_amount ?></div></div>
      <div class="pf"><div class="pfl">Emergency Contact</div><div class="pfv"><?= $emergency_name ?></div></div>
      <div class="pf"><div class="pfl">Emergency Phone</div><div class="pfv"><?= $emergency_phone ?></div></div>
      <div class="pf"><div class="pfl">Status</div><div class="pfv"><span class="bx <?= strtolower($tenant_status)==='active'?'active':'pending' ?>"><?= $tenant_status ?></span></div></div>
    </div>
    <div class="card" style="max-width:460px">
      <div class="chead"><div class="ct">Change Password</div></div>
      <form method="POST" action="change_password.php">
        <input type="hidden" name="user_id" value="<?= $user_id ?>">
        <div class="fl"><label>Current Password</label><input type="password" name="current_password" placeholder="Current password" required></div>
        <div class="fl"><label>New Password</label><input type="password" name="new_password" placeholder="New password" required></div>
        <div class="fl"><label>Confirm New Password</label><input type="password" name="confirm_password" placeholder="Confirm new password" required></div>
        <button type="submit" class="sbtn">Update Password →</button>
      </form>
    </div>
  </div>
 
</div><!-- /mc -->
 
<?php if(isset($_SESSION['success'])): ?>
<script>window.addEventListener('DOMContentLoaded',()=>toast('✅','<?= addslashes($_SESSION['success']) ?>'));</script>
<?php unset($_SESSION['success']); endif; ?>
<?php if(isset($_SESSION['error'])): ?>
<script>window.addEventListener('DOMContentLoaded',()=>toast('⚠️','<?= addslashes($_SESSION['error']) ?>'));</script>
<?php unset($_SESSION['error']); endif; ?>
 
<script>
/* CURSOR */
const cd=document.getElementById('cd'),cr=document.getElementById('cr');
let mx=-200,my=-200,rx=-200,ry=-200;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;cd.style.left=mx+'px';cd.style.top=my+'px'});
(function a(){rx+=(mx-rx)*.18;ry+=(my-ry)*.18;cr.style.left=rx+'px';cr.style.top=ry+'px';requestAnimationFrame(a)})();
document.querySelectorAll('a,button,input,select,textarea').forEach(el=>{
  el.addEventListener('mouseenter',()=>document.body.classList.add('ch'));
  el.addEventListener('mouseleave',()=>document.body.classList.remove('ch'));
});
 
/* PAGE NAV */
const titles={overview:'Overview',property:'My Property',payments:'Payments',lease:'Lease Agreement',maintenance:'Maintenance',visitors:'Visitor Management',complaints:'Complaints & Feedback',notifications:'Notifications',documents:'My Documents',profile:'My Profile'};
function show(id,btn){
  document.querySelectorAll('.pg').forEach(p=>p.classList.remove('active'));
  document.getElementById('pg-'+id).classList.add('active');
  document.querySelectorAll('.na').forEach(l=>l.classList.remove('active'));
  if(btn) btn.classList.add('active');
  else document.querySelectorAll('.na').forEach(l=>{if(l.getAttribute('onclick')?.includes("'"+id+"'"))l.classList.add('active')});
  document.getElementById('pt').textContent=titles[id]||id;
  closeSB();
  window.scrollTo({top:0,behavior:'smooth'});
}
 
/* SIDEBAR */
function toggleSB(){document.getElementById('sb').classList.toggle('open');document.getElementById('ov').classList.toggle('show')}
function closeSB(){document.getElementById('sb').classList.remove('open');document.getElementById('ov').classList.remove('show')}
 
/* MAINTENANCE PRIORITY */
function pri(el,val){document.querySelectorAll('.prchip').forEach(c=>c.classList.remove('sel'));el.classList.add('sel');document.getElementById('pv').value=val}
 
/* STAR RATING */
function rate(cat,n){
  document.querySelectorAll('#sr-'+cat+' span').forEach((s,i)=>s.style.opacity=i<n?'1':'0.25');
  document.getElementById('v-'+cat).value=n;
}
function valRating(){
  if(!document.getElementById('v-cl').value||document.getElementById('v-cl').value=='0'||
     !document.getElementById('v-sc').value||document.getElementById('v-sc').value=='0'||
     !document.getElementById('v-va').value||document.getElementById('v-va').value=='0'){
    alert('Please rate all three categories before submitting.');return false;
  }return true;
}
 
/* TOAST */
function toast(icon,msg){
  const t=document.getElementById('toast');
  document.getElementById('ti').textContent=icon;
  document.getElementById('tm').textContent=msg;
  t.classList.add('show');clearTimeout(t._t);
  t._t=setTimeout(()=>t.classList.remove('show'),3800);
}
</script>
</body>
</html>
 