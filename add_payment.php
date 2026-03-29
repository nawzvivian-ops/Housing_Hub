
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$me = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE id='{$_SESSION['user_id']}' LIMIT 1"));
if (!$me || strtolower($me['role']) !== 'admin') { header("Location: dashboard.php"); exit(); }
 
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenant_id     = (int)$_POST['tenant_id'];
    $property_id   = (int)$_POST['property_id'];
    $amount        = (float)$_POST['amount'];
    $date          = mysqli_real_escape_string($conn, $_POST['date']);
    $method        = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? 'Cash');
    $status        = mysqli_real_escape_string($conn, $_POST['status'] ?? 'paid');
    $ref           = mysqli_real_escape_string($conn, trim($_POST['transaction_ref'] ?? ''));
    if (!$tenant_id || !$amount || !$date) { $error = "Tenant, amount, and date are required."; }
    else {
        mysqli_query($conn, "INSERT INTO payments (tenant_id,property_id,amount,date,payment_method,status,transaction_ref) VALUES ($tenant_id,$property_id,$amount,'$date','$method','$status','$ref')");
        $_SESSION['admin_success'] = "Payment of UGX " . number_format($amount) . " recorded.";
        header("Location: admin_dashboard.php?page=tenant_payments"); exit();
    }
}
$tenants    = mysqli_query($conn, "SELECT id,fullname FROM tenants ORDER BY fullname ASC");
$properties = mysqli_query($conn, "SELECT id,property_name FROM properties ORDER BY property_name ASC");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Record Payment | HousingHub Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25);--sw:260px}
body{font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);min-height:100vh;padding:36px 40px;margin-left:var(--sw)}
body::before{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.12),transparent 55%)}
.wrap{position:relative;z-index:1;max-width:620px}
h2{font-family:"Cormorant Garamond",serif;font-size:28px;font-weight:700;color:var(--gold);margin-bottom:24px;padding-bottom:12px;border-bottom:2px solid var(--gb)}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:32px;position:relative;overflow:hidden}
.card::before{content:"";position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,var(--gold),transparent)}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.fl{margin-bottom:16px}
label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
input,select{width:100%;padding:11px 14px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .25s}
input:focus,select:focus{border-color:var(--gb)}
input::placeholder{color:rgba(255,255,255,.2)}
input[type=date]{color-scheme:dark}
select option{background:#04091a}
.alert{padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:20px}
.alert.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.btn-submit{padding:13px 32px;background:var(--gold);border:none;border-radius:7px;color:var(--ink);font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;transition:all .3s}
.btn-submit:hover{background:var(--gold-l);transform:translateY(-2px)}
.btn-back{padding:13px 24px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--muted);font-family:"Outfit",sans-serif;font-size:12px;font-weight:600;text-decoration:none;transition:all .3s}
.btn-back:hover{border-color:var(--gb);color:var(--white)}
</style>
</head>
<body><div class="wrap">
<h2>💳 Record Payment</h2>
<?php if($error): ?><div class="alert error">⚠️ <?= $error ?></div><?php endif; ?>
<div class="card">
  <form method="POST">
    <div class="grid2">
      <div class="fl"><label>Tenant *</label><select name="tenant_id" required><option value="">— Select tenant —</option><?php while($t=mysqli_fetch_assoc($tenants)): ?><option value="<?=$t['id']?>"><?=htmlspecialchars($t['fullname'])?></option><?php endwhile;?></select></div>
      <div class="fl"><label>Property</label><select name="property_id"><option value="">— Select property —</option><?php while($p=mysqli_fetch_assoc($properties)): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['property_name'])?></option><?php endwhile;?></select></div>
    </div>
    <div class="grid2">
      <div class="fl"><label>Amount (UGX) *</label><input type="number" name="amount" min="0" placeholder="e.g. 500000" required></div>
      <div class="fl"><label>Payment Date *</label><input type="date" name="date" required value="<?=date('Y-m-d')?>"></div>
    </div>
    <div class="grid2">
      <div class="fl"><label>Payment Method</label><select name="payment_method"><option value="Cash">Cash</option><option value="Mobile Money">Mobile Money</option><option value="Bank Transfer">Bank Transfer</option><option value="Cheque">Cheque</option></select></div>
      <div class="fl"><label>Status</label><select name="status"><option value="paid">Paid</option><option value="pending">Pending</option></select></div>
    </div>
    <div class="fl"><label>Transaction Reference</label><input type="text" name="transaction_ref" placeholder="e.g. MTN Ref #123456"></div>
    <div style="display:flex;gap:12px;margin-top:8px">
      <button type="submit" class="btn-submit">💳 Record Payment</button>
      <a href="admin_dashboard.php?page=tenant_payments" class="btn-back">← Back</a>
    </div>
  </form>
</div>
</div></body></html>