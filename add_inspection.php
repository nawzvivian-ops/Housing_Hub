
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE id='{$_SESSION['user_id']}' LIMIT 1"));
if (!$admin || strtolower($admin['role']) !== 'admin') { header("Location: dashboard.php"); exit(); }
 
if (isset($_POST['add_inspection'])) {
    $property_id     = intval($_POST['property_id']);
    $tenant_id       = !empty($_POST['tenant_id']) ? intval($_POST['tenant_id']) : NULL;
    $inspector_name  = mysqli_real_escape_string($conn, $_POST['inspector_name']);
    $inspection_date = mysqli_real_escape_string($conn, $_POST['inspection_date']);
    $situation       = mysqli_real_escape_string($conn, $_POST['situation']);
    $notes           = mysqli_real_escape_string($conn, $_POST['notes']);
    mysqli_query($conn, "INSERT INTO inspections
        (property_id, tenant_id, inspector_name, inspection_date, condition, status, notified)
        VALUES
        ($property_id, " . ($tenant_id ? $tenant_id : "NULL") . ",
         '$inspector_name', '$inspection_date', '$situation', 'Pending', 0)");
    $_SESSION['admin_success'] = "Inspection scheduled successfully.";
    header("Location: admin_dashboard.php?page=inspections");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Schedule Inspection | HousingHub Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25);--sw:260px}
body{font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);min-height:100vh;padding:36px 40px;margin-left:var(--sw)}
body::before{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.12),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.08),transparent 50%)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
.wrap{position:relative;z-index:1;max-width:680px}
h2{font-family:"Cormorant Garamond",serif;font-size:28px;font-weight:700;color:var(--gold);margin-bottom:24px;padding-bottom:12px;border-bottom:2px solid var(--gb)}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:32px;position:relative;overflow:hidden}
.card::before{content:"";position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,var(--gold),transparent)}
.card-title{font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;color:var(--white);margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid var(--border)}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.fl{margin-bottom:16px}
label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
input,select,textarea{width:100%;padding:11px 14px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .25s}
input:focus,select:focus,textarea:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
input::placeholder,textarea::placeholder{color:rgba(255,255,255,.2)}
select option{background:#04091a;color:#fff}
input[type="date"]{color-scheme:dark}
textarea{resize:vertical;min-height:100px}
.btn-submit{padding:13px 32px;background:var(--gold);border:none;border-radius:7px;color:var(--ink);font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;transition:all .3s}
.btn-submit:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 8px 24px rgba(200,164,60,.3)}
.btn-back{padding:13px 24px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--muted);font-family:"Outfit",sans-serif;font-size:12px;font-weight:600;text-decoration:none;transition:all .3s}
.btn-back:hover{border-color:var(--gb);color:var(--white)}
.info-box{background:rgba(200,164,60,.06);border:1px solid var(--gb);border-radius:8px;padding:12px 16px;margin-bottom:24px;font-size:13px;color:var(--muted);line-height:1.7}
.info-box strong{color:var(--gold)}
</style>
</head>
<body>
<div class="wrap">
  <h2>🔍 Schedule Property Inspection</h2>
 
  <div class="info-box">
    <strong>ℹ️ Note:</strong> Fill in the property, inspector details, and date. Tenant selection is optional — leave blank for general property inspections.
  </div>
 
  <div class="card">
    <div class="card-title">Inspection Details</div>
    <form method="POST">
 
      <div class="grid2">
        <div class="fl">
          <label>Property *</label>
          <select name="property_id" required>
            <option value="">— Select Property —</option>
            <?php
            $properties = mysqli_query($conn, "SELECT id, property_name, address FROM properties ORDER BY property_name ASC");
            while($p = mysqli_fetch_assoc($properties)):
            ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['property_name']) ?><?= $p['address'] ? ' — '.htmlspecialchars($p['address']) : '' ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="fl">
          <label>Tenant (Optional)</label>
          <select name="tenant_id">
            <option value="">— No specific tenant —</option>
            <?php
            $tenants = mysqli_query($conn, "SELECT t.id, t.fullname, p.property_name FROM tenants t LEFT JOIN properties p ON t.property_id=p.id ORDER BY t.fullname ASC");
            while($t = mysqli_fetch_assoc($tenants)):
            ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['fullname']) ?><?= $t['property_name'] ? ' — '.htmlspecialchars($t['property_name']) : '' ?></option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
 
      <div class="grid2">
        <div class="fl">
          <label>Inspector Name *</label>
          <input type="text" name="inspector_name" placeholder="e.g. John Muwanga" required>
        </div>
        <div class="fl">
          <label>Inspection Date *</label>
          <input type="date" name="inspection_date" required min="<?= date('Y-m-d') ?>">
        </div>
      </div>
 
      <div class="fl">
        <label>Condition / Situation *</label>
        <select name="situation" required>
          <option value="">— Select condition —</option>
          <option value="Good">Good — Property is in excellent condition</option>
          <option value="Fair">Fair — Minor issues noted</option>
          <option value="Needs Repair">Needs Repair — Repairs required</option>
          <option value="Damaged">Damaged — Significant damage found</option>
          <option value="Under Renovation">Under Renovation</option>
        </select>
      </div>
 
      <div class="fl">
        <label>Inspection Notes</label>
        <textarea name="notes" placeholder="Describe the inspection findings, areas checked, recommendations..."></textarea>
      </div>
 
      <div style="display:flex;gap:12px;align-items:center;margin-top:8px">
        <button type="submit" name="add_inspection" class="btn-submit">🔍 Schedule Inspection</button>
        <a href="admin_dashboard.php?page=inspections" class="btn-back">← Back to Inspections</a>
      </div>
 
    </form>
  </div>
</div>
</body>
</html>