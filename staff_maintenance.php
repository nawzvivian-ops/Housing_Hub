
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
 
// Update status
if (isset($_GET['update'])) {
    $id  = (int)$_GET['update'];
    $st  = mysqli_real_escape_string($conn, $_GET['status']);
    mysqli_query($conn, "UPDATE maintenance_requests SET status='$st' WHERE id=$id");
    header("Location: staff_maintenance.php"); exit();
}
 
// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM maintenance_requests WHERE id=$id");
    header("Location: staff_maintenance.php"); exit();
}
 
// Fetch
$requests = mysqli_query($conn, "
    SELECT m.*, p.property_name, u.fullname AS staff_name
    FROM maintenance_requests m
    LEFT JOIN properties p ON m.property_id = p.id
    LEFT JOIN users u ON m.assigned_staff = u.id
    ORDER BY m.created_at DESC");
 
// Stats
$total    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM maintenance_requests"))['c']??0;
$pending  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM maintenance_requests WHERE status='pending'"))['c']??0;
$progress = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM maintenance_requests WHERE status='in_progress'"))['c']??0;
$done     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM maintenance_requests WHERE status='completed'"))['c']??0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Maintenance Requests | HousingHub Staff</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25);--red:#ef4444}
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
.stat.blue .stat-val{color:#5b9cff}
.stat.green .stat-val{color:#86efac}
.stat.red .stat-val{color:#fca5a5}
.table-wrap{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;overflow:hidden}
table{width:100%;border-collapse:collapse}
th{background:rgba(200,164,60,.1);color:var(--gold);font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:12px 16px;text-align:left;border-bottom:1px solid var(--border)}
td{padding:13px 16px;font-size:13px;color:rgba(255,255,255,.8);border-bottom:1px solid rgba(255,255,255,.04)}
tr:last-child td{border-bottom:none}
tr:hover td{background:rgba(200,164,60,.03)}
.bx{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase}
.bx.pending{background:rgba(200,164,60,.1);border:1px solid var(--gb);color:var(--gold)}
.bx.in_progress{background:rgba(14,90,200,.1);border:1px solid rgba(14,90,200,.3);color:#5b9cff}
.bx.completed{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.bx.low{background:rgba(22,163,74,.08);border:1px solid rgba(22,163,74,.2);color:#86efac}
.bx.medium{background:rgba(200,164,60,.08);border:1px solid var(--gb);color:var(--gold)}
.bx.high{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#fca5a5}
.act-btn{display:inline-block;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:11px;font-weight:600;transition:all .2s;white-space:nowrap}
.act-btn:hover{transform:translateY(-1px)}
.btn-pending{background:rgba(200,164,60,.15);border:1px solid var(--gb);color:var(--gold)}
.btn-progress{background:rgba(14,90,200,.15);border:1px solid rgba(14,90,200,.3);color:#5b9cff}
.btn-done{background:rgba(22,163,74,.15);border:1px solid rgba(22,163,74,.3);color:#86efac}
.btn-del{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);color:#fca5a5}
.empty{text-align:center;padding:40px;color:var(--muted);font-size:14px}
@media(max-width:700px){
  .stats-row{grid-template-columns:1fr 1fr}
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
      <div class="page-title">Maintenance <em>Requests</em></div>
      <div class="page-sub">Review and update tenant repair requests</div>
    </div>
  </div>
 
  <div class="stats-row">
    <div class="stat"><div class="stat-val"><?= $total ?></div><div class="stat-lbl">Total</div></div>
    <div class="stat red"><div class="stat-val"><?= $pending ?></div><div class="stat-lbl">Pending</div></div>
    <div class="stat blue"><div class="stat-val"><?= $progress ?></div><div class="stat-lbl">In Progress</div></div>
    <div class="stat green"><div class="stat-val"><?= $done ?></div><div class="stat-lbl">Completed</div></div>
  </div>
 
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Property</th>
          <th>Issue</th>
          <th>Priority</th>
          <th>Assigned Staff</th>
          <th>Status</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php $i=1; $count=0; while($r = mysqli_fetch_assoc($requests)): $count++; $pri = strtolower($r['priority']??'medium'); $st = strtolower($r['status']??'pending'); ?>
      <tr>
        <td style="color:var(--muted)"><?= $i++ ?></td>
        <td style="font-weight:600"><?= htmlspecialchars($r['property_name']??'Unknown') ?></td>
        <td style="max-width:180px;color:var(--muted)"><?= htmlspecialchars(substr($r['issue']??'',0,60)) ?><?= strlen($r['issue']??'')>60?'...':'' ?></td>
        <td><span class="bx <?= $pri ?>"><?= ucfirst($pri) ?></span></td>
        <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($r['staff_name']??'Unassigned') ?></td>
        <td><span class="bx <?= $st ?>"><?= ucfirst(str_replace('_',' ',$st)) ?></span></td>
        <td style="font-size:12px;color:var(--muted);white-space:nowrap"><?= $r['created_at'] ? date('d M Y', strtotime($r['created_at'])) : '—' ?></td>
        <td style="white-space:nowrap">
          <a class="act-btn btn-pending" href="?update=<?=$r['id']?>&status=pending">Pending</a>
          <a class="act-btn btn-progress" href="?update=<?=$r['id']?>&status=in_progress">In Progress</a>
          <a class="act-btn btn-done" href="?update=<?=$r['id']?>&status=completed">Completed</a>
          <a class="act-btn btn-del" href="?delete=<?=$r['id']?>" onclick="return confirm('Delete this request?')">🗑</a>
        </td>
      </tr>
      <?php endwhile; ?>
      <?php if($count===0): ?><tr><td colspan="8" class="empty">No maintenance requests found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
 
</div>
</body>
</html>