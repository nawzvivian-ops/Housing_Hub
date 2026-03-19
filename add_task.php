<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
 
if (isset($_POST['add_task'])) {
    $title       = mysqli_real_escape_string($conn, trim($_POST['title']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    $assigned_to = (int)$_POST['assigned_to'];
    $due_date    = mysqli_real_escape_string($conn, $_POST['due_date']);
    $priority    = mysqli_real_escape_string($conn, $_POST['priority']);
 
    mysqli_query($conn, "INSERT INTO tasks (title, description, assigned_to, due_date, priority, status, assigned_by, created_at)
        VALUES ('$title','$description',$assigned_to,'$due_date','$priority','Pending','Admin',NOW())");
 
    // ── Auto-notify the assigned staff member ──
    $due_label = $due_date ? date('d M Y', strtotime($due_date)) : 'No due date';
    $notif_msg = mysqli_real_escape_string($conn, "You have been assigned a new task: $title. Priority: $priority. Due: $due_label.");
    $notif_title = mysqli_real_escape_string($conn, "New Task Assigned: $title");
    mysqli_query($conn, "INSERT INTO notifications (user_id, tenant_id, title, message, status, date)
        VALUES ($assigned_to, 0, '$notif_title', '$notif_msg', 'unread', NOW())");
 
    header("Location: admin_dashboard.php?page=staff_tasks"); exit();
}
 
$staff = mysqli_query($conn, "SELECT id, fullname FROM users WHERE role='staff' ORDER BY fullname ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Assign Task | HousingHub Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25)}
html,body{min-height:100vh;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);display:flex;align-items:center;justify-content:center;padding:40px 20px}
body{background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%),var(--ink)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
.wrap{position:relative;z-index:10;width:100%;max-width:500px}
.back{display:inline-flex;align-items:center;gap:8px;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);text-decoration:none;margin-bottom:24px;transition:color .2s}
.back:hover{color:var(--gold)}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:14px;padding:36px;box-shadow:0 20px 60px rgba(0,0,0,.4)}
.card-eyebrow{font-size:10px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gold);text-align:center;margin-bottom:8px}
.card-title{font-family:"Cormorant Garamond",serif;font-size:30px;font-weight:700;color:var(--white);text-align:center;margin-bottom:6px}
.card-title em{color:var(--gold);font-style:italic}
.card-sub{font-size:12px;color:var(--muted);text-align:center;margin-bottom:28px}
.divider{height:1px;background:var(--border);margin-bottom:24px}
.fl{margin-bottom:14px}
.fl label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.fl input,.fl select,.fl textarea{width:100%;padding:11px 13px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .25s}
.fl input:focus,.fl select:focus,.fl textarea:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
.fl input::placeholder,.fl textarea::placeholder{color:var(--muted)}
.fl select option{background:var(--ink);color:var(--white)}
.fl textarea{resize:vertical;min-height:90px}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
/* PRIORITY PILLS */
.priority-row{display:flex;gap:10px;margin-bottom:14px}
.priority-pill{flex:1;text-align:center;padding:10px;border-radius:7px;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;cursor:pointer;border:1px solid var(--border);background:rgba(255,255,255,.03);color:var(--muted);transition:all .2s}
.priority-pill.low.selected{background:rgba(22,163,74,.15);border-color:rgba(22,163,74,.4);color:#86efac}
.priority-pill.medium.selected{background:rgba(200,164,60,.15);border-color:var(--gb);color:var(--gold)}
.priority-pill.high.selected{background:rgba(239,68,68,.15);border-color:rgba(239,68,68,.4);color:#fca5a5}
.btn{width:100%;padding:13px;background:var(--gold);border:none;color:var(--ink);font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;border-radius:7px;cursor:pointer;transition:all .3s;margin-top:8px}
.btn:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 8px 24px rgba(200,164,60,.3)}
@media(max-width:500px){.grid2{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="wrap">
  <a href="admin_dashboard.php?page=staff_tasks" class="back">← Back to Tasks</a>
  <div class="card">
    <div class="card-eyebrow">HousingHub Admin</div>
    <div class="card-title">Assign <em>New Task</em></div>
    <div class="card-sub">Create and assign a task to a staff member</div>
    <div class="divider"></div>
 
    <form method="POST">
      <div class="fl">
        <label>Task Title</label>
        <input type="text" name="title" placeholder="e.g. Inspect Unit 3B at Greenview" required>
      </div>
      <div class="fl">
        <label>Description</label>
        <textarea name="description" placeholder="Describe what needs to be done..."></textarea>
      </div>
      <div class="fl">
        <label>Assign To</label>
        <select name="assigned_to" required>
          <option value="">— Select Staff Member —</option>
          <?php while($s = mysqli_fetch_assoc($staff)): ?>
          <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['fullname']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="fl">
        <label>Due Date</label>
        <input type="date" name="due_date" required>
      </div>
      <div class="fl">
        <label>Priority</label>
        <div class="priority-row">
          <div class="priority-pill low" onclick="setPriority('Low')">🟢 Low</div>
          <div class="priority-pill medium selected" onclick="setPriority('Medium')">🟡 Medium</div>
          <div class="priority-pill high" onclick="setPriority('High')">🔴 High</div>
        </div>
        <input type="hidden" name="priority" id="priority-val" value="Medium">
      </div>
      <button type="submit" name="add_task" class="btn">Assign Task →</button>
    </form>
  </div>
</div>
 
<script>
function setPriority(val) {
  document.getElementById('priority-val').value = val;
  document.querySelectorAll('.priority-pill').forEach(p => p.classList.remove('selected'));
  document.querySelectorAll('.priority-pill').forEach(p => {
    if (p.textContent.toLowerCase().includes(val.toLowerCase())) p.classList.add('selected');
  });
}
</script>
</body>
</html>