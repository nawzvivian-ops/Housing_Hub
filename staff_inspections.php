
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
 
$user_id = (int)$_SESSION['user_id'];
$user    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id' LIMIT 1"));
if (!$user || strtolower($user['role']) !== 'staff') {
    echo "<h2 style='color:red;text-align:center;font-family:sans-serif;padding:40px'>Access Denied!</h2>"; exit();
}
 
// Handle add inspection
if (isset($_POST['add_inspection'])) {
    $property_id     = (int)$_POST['property_id'];
    $tenant_id       = !empty($_POST['tenant_id']) ? (int)$_POST['tenant_id'] : 'NULL';
    $inspector       = mysqli_real_escape_string($conn, $_POST['inspector_name']);
    $inspection_date = mysqli_real_escape_string($conn, $_POST['inspection_date']);
    $situation       = mysqli_real_escape_string($conn, $_POST['situation']);
    $status          = mysqli_real_escape_string($conn, $_POST['status']);
 
    mysqli_query($conn, "
        INSERT INTO inspections (property_id, tenant_id, inspector_name, inspection_date, `condition`, status)
        VALUES ($property_id, $tenant_id, '$inspector', '$inspection_date', '$situation', '$status')
    ");
    header("Location: staff_inspections.php"); exit();
}
 
// Fetch data
$inspections = mysqli_query($conn, "
    SELECT i.*, p.property_name, t.fullname AS tenant_name
    FROM inspections i
    LEFT JOIN properties p ON i.property_id = p.id
    LEFT JOIN tenants t ON i.tenant_id = t.id
    ORDER BY i.inspection_date DESC");
 
$properties = mysqli_query($conn, "SELECT id, property_name FROM properties ORDER BY property_name ASC");
$tenants    = mysqli_query($conn, "SELECT id, fullname FROM tenants ORDER BY fullname ASC");
 
// Stats
$total     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM inspections"))['c'] ?? 0;
$pending   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM inspections WHERE status='Pending'"))['c'] ?? 0;
$completed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM inspections WHERE status='Completed'"))['c'] ?? 0;
$overdue   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM inspections WHERE status='Pending' AND inspection_date < CURDATE()"))['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Property Inspections | HousingHub Staff</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25);--red:#ef4444;--green:#16a34a}
html,body{min-height:100vh;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white)}
body{background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%),var(--ink)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
.wrap{position:relative;z-index:10;max-width:1100px;margin:0 auto;padding:32px 24px}
.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px}
.back{display:inline-flex;align-items:center;gap:8px;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);text-decoration:none;transition:color .2s}
.back:hover{color:var(--gold)}
.page-title{font-family:"Cormorant Garamond",serif;font-size:32px;font-weight:700;color:var(--white)}
.page-title em{color:var(--gold);font-style:italic}
.page-sub{font-size:13px;color:var(--muted);margin-top:4px}
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.stat{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:16px;text-align:center;transition:border-color .3s}
.stat:hover{border-color:var(--gb)}
.stat-val{font-family:"Cormorant Garamond",serif;font-size:32px;font-weight:700;color:var(--gold);line-height:1}
.stat-lbl{font-size:11px;color:var(--muted);margin-top:4px;letter-spacing:.5px}
.stat.red .stat-val{color:#fca5a5}
.stat.green .stat-val{color:#86efac}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:24px}
.card-title{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--white);margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--border)}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.fl{margin-bottom:14px}
.fl label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.fl input,.fl select,.fl textarea{width:100%;padding:11px 13px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:6px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .25s}
.fl input:focus,.fl select:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
.fl input::placeholder{color:var(--muted)}
.fl select option{background:var(--ink)}
.btn{padding:11px 24px;border:none;border-radius:6px;font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:1px;cursor:pointer;transition:all .25s}
.btn-gold{background:var(--gold);color:var(--ink)}.btn-gold:hover{background:var(--gold-l);transform:translateY(-1px)}
.table-wrap{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:20px}
table{width:100%;border-collapse:collapse}
th{background:rgba(200,164,60,.1);color:var(--gold);font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:12px 16px;text-align:left;border-bottom:1px solid var(--border)}
td{padding:13px 16px;font-size:13px;color:rgba(255,255,255,.8);border-bottom:1px solid rgba(255,255,255,.04)}
tr:last-child td{border-bottom:none}
tr:hover td{background:rgba(200,164,60,.03)}
tr.overdue-row td{background:rgba(239,68,68,.04)}
.bx{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase}
.bx.pending{background:rgba(200,164,60,.1);border:1px solid var(--gb);color:var(--gold)}
.bx.completed{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.bx.overdue{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
@media(max-width:700px){
  .stats-row{grid-template-columns:1fr 1fr}
  .form-row{grid-template-columns:1fr}
  table{font-size:12px}
  th,td{padding:9px 10px}
  .wrap{padding:16px}
}
</style>
</head>
<body>
<div class="wrap">
 
  <div class="topbar">
    <a href="staff_dashboard.php" class="back">← Staff Dashboard</a>
    <div style="text-align:right">
      <div class="page-title">Property <em>Inspections</em></div>
      <div class="page-sub">Log and track all property inspections</div>
    </div>
  </div>
 
  <!-- STATS -->
  <div class="stats-row">
    <div class="stat"><div class="stat-val"><?= $total ?></div><div class="stat-lbl">Total</div></div>
    <div class="stat"><div class="stat-val"><?= $pending ?></div><div class="stat-lbl">Pending</div></div>
    <div class="stat green"><div class="stat-val"><?= $completed ?></div><div class="stat-lbl">Completed</div></div>
    <div class="stat <?= $overdue>0?'red':'' ?>"><div class="stat-val"><?= $overdue ?></div><div class="stat-lbl">Overdue</div></div>
  </div>
 
  <!-- ADD FORM -->
  <div class="card">
    <div class="card-title">🔍 Schedule New Inspection</div>
    <form method="POST">
      <div class="form-row">
        <div class="fl">
          <label>Property</label>
          <select name="property_id" required>
            <option value="">— Select Property —</option>
            <?php while($p = mysqli_fetch_assoc($properties)): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['property_name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="fl">
          <label>Tenant (optional)</label>
          <select name="tenant_id">
            <option value="">— Select Tenant —</option>
            <?php while($t = mysqli_fetch_assoc($tenants)): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['fullname']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="fl">
          <label>Inspector Name</label>
          <input type="text" name="inspector_name" placeholder="e.g. Kato Emmanuel" required
                 value="<?= htmlspecialchars($user['fullname']) ?>">
        </div>
        <div class="fl">
          <label>Inspection Date</label>
          <input type="date" name="inspection_date" required>
        </div>
      </div>
      <div class="form-row">
        <div class="fl">
          <label>Situation / Findings</label>
          <input type="text" name="situation" placeholder="e.g. Roof leaking, walls damp..." required>
        </div>
        <div class="fl">
          <label>Status</label>
          <select name="status">
            <option value="Pending">Pending</option>
            <option value="Completed">Completed</option>
          </select>
        </div>
      </div>
      <button type="submit" name="add_inspection" class="btn btn-gold">+ Add Inspection</button>
    </form>
  </div>
 
  <!-- TABLE -->
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Property</th>
          <th>Tenant</th>
          <th>Inspector</th>
          <th>Date</th>
          <th>Situation</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $i = 1; $count = 0;
      while ($ins = mysqli_fetch_assoc($inspections)):
        $count++;
        $is_over = ($ins['status']==='Pending' && !empty($ins['inspection_date']) && $ins['inspection_date'] < date('Y-m-d'));
        $badge   = $is_over ? 'overdue' : strtolower($ins['status']);
        $label   = $is_over ? 'Overdue' : $ins['status'];
      ?>
      <tr class="<?= $is_over?'overdue-row':'' ?>">
        <td style="color:var(--muted)"><?= $i++ ?></td>
        <td style="font-weight:600"><?= htmlspecialchars($ins['property_name']??'N/A') ?></td>
        <td style="color:var(--muted)"><?= htmlspecialchars($ins['tenant_name']??'—') ?></td>
        <td><?= htmlspecialchars($ins['inspector_name']??'—') ?></td>
        <td style="font-size:12px;white-space:nowrap"><?= $ins['inspection_date'] ? date('d M Y', strtotime($ins['inspection_date'])) : '—' ?></td>
        <td style="color:var(--muted);max-width:180px"><?= htmlspecialchars($ins['condition']??'—') ?></td>
        <td><span class="bx <?= $badge ?>"><?= $label ?></span></td>
      </tr>
      <?php endwhile; ?>
      <?php if($count===0): ?>
      <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">No inspections recorded yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
 
</div>
</body>
</html>