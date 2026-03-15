
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
 
// ── Detect which column is used for staff assignment ──
// Some tables use staff_id, others use assigned_to
$col_check = mysqli_query($conn, "SHOW COLUMNS FROM tasks LIKE 'assigned_to'");
$use_assigned_to = ($col_check && mysqli_num_rows($col_check) > 0);
$staff_col = $use_assigned_to ? 'assigned_to' : 'staff_id';
 
// Handle mark complete
if (isset($_POST['complete'])) {
    $tid = (int)$_POST['complete'];
    mysqli_query($conn, "UPDATE tasks SET status='Completed' WHERE id=$tid AND $staff_col='$user_id'");
    header("Location: staff_tasks.php"); exit();
}
 
// Handle delete
if (isset($_POST['delete'])) {
    $tid = (int)$_POST['delete'];
    mysqli_query($conn, "DELETE FROM tasks WHERE id=$tid AND $staff_col='$user_id'");
    header("Location: staff_tasks.php"); exit();
}
 
// Filters
$filter_status   = mysqli_real_escape_string($conn, $_POST['status']   ?? '');
$filter_priority = mysqli_real_escape_string($conn, $_POST['priority'] ?? '');
$search          = mysqli_real_escape_string($conn, $_POST['search']   ?? '');
 
$where = "WHERE $staff_col='$user_id'";
if ($filter_status)   $where .= " AND status='$filter_status'";
if ($filter_priority) $where .= " AND priority='$filter_priority'";
if ($search)          $where .= " AND (title LIKE '%$search%' OR description LIKE '%$search%')";
 
$tasksQ    = mysqli_query($conn, "SELECT * FROM tasks $where ORDER BY due_date ASC");
$total     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tasks WHERE $staff_col='$user_id'"))['c'] ?? 0;
$completed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tasks WHERE $staff_col='$user_id' AND status='Completed'"))['c'] ?? 0;
$pending   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tasks WHERE $staff_col='$user_id' AND status!='Completed'"))['c'] ?? 0;
$overdue   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tasks WHERE $staff_col='$user_id' AND status!='Completed' AND due_date < CURDATE()"))['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Tasks | HousingHub Staff</title>
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
 
/* STATS */
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.stat{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:16px;text-align:center;transition:border-color .3s}
.stat:hover{border-color:var(--gb)}
.stat-val{font-family:"Cormorant Garamond",serif;font-size:32px;font-weight:700;color:var(--gold);line-height:1}
.stat-lbl{font-size:11px;color:var(--muted);margin-top:4px;letter-spacing:.5px}
.stat.red .stat-val{color:#fca5a5}
.stat.green .stat-val{color:#86efac}
 
/* FILTER */
.filter-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:18px 20px;margin-bottom:20px}
.filter-row{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
.filter-row select,.filter-row input{background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:6px;padding:9px 12px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .2s}
.filter-row select:focus,.filter-row input:focus{border-color:var(--gb)}
.filter-row select option{background:var(--ink)}
.filter-row input::placeholder{color:var(--muted)}
.filter-row input{flex:1;min-width:200px}
.btn{padding:9px 20px;border:none;border-radius:6px;font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:1px;cursor:pointer;transition:all .25s}
.btn-gold{background:var(--gold);color:var(--ink)}.btn-gold:hover{background:var(--gold-l)}
.btn-green{background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac}.btn-green:hover{background:rgba(22,163,74,.35)}
.btn-red{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5}.btn-red:hover{background:rgba(239,68,68,.3)}
 
/* TABLE */
.table-wrap{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:20px}
table{width:100%;border-collapse:collapse}
th{background:rgba(200,164,60,.1);color:var(--gold);font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:12px 16px;text-align:left;border-bottom:1px solid var(--border)}
td{padding:13px 16px;font-size:13px;color:rgba(255,255,255,.8);border-bottom:1px solid rgba(255,255,255,.04)}
tr:last-child td{border-bottom:none}
tr:hover td{background:rgba(200,164,60,.03)}
tr.overdue td{background:rgba(239,68,68,.05)}
.completed-row td{opacity:.5}
.completed-row .task-title{text-decoration:line-through}
 
/* BADGES */
.bx{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase}
.bx.pending{background:rgba(200,164,60,.1);border:1px solid var(--gb);color:var(--gold)}
.bx.completed{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.bx.overdue{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.bx.low{background:rgba(22,163,74,.08);border:1px solid rgba(22,163,74,.2);color:#86efac}
.bx.medium{background:rgba(200,164,60,.08);border:1px solid var(--gb);color:var(--gold)}
.bx.high{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#fca5a5}
 
.empty{text-align:center;padding:40px;color:var(--muted);font-size:14px}
 
@media(max-width:700px){
  .stats-row{grid-template-columns:1fr 1fr}
  .filter-row{flex-direction:column}
  table{font-size:12px}
  th,td{padding:10px}
}
</style>
</head>
<body>
<div class="wrap">
 
  <div class="topbar">
    <a href="staff_dashboard.php" class="back">← Staff Dashboard</a>
    <div style="text-align:right">
      <div class="page-title">My <em>Tasks</em></div>
      <div class="page-sub">Welcome, <?= htmlspecialchars($user['fullname']) ?></div>
    </div>
  </div>
 
  <!-- STATS -->
  <div class="stats-row">
    <div class="stat"><div class="stat-val"><?= $total ?></div><div class="stat-lbl">Total Tasks</div></div>
    <div class="stat green"><div class="stat-val"><?= $completed ?></div><div class="stat-lbl">Completed</div></div>
    <div class="stat"><div class="stat-val"><?= $pending ?></div><div class="stat-lbl">Pending</div></div>
    <div class="stat <?= $overdue>0?'red':'' ?>"><div class="stat-val"><?= $overdue ?></div><div class="stat-lbl">Overdue</div></div>
  </div>
 
  <!-- FILTER -->
  <div class="filter-card">
    <form method="POST" action="">
      <div class="filter-row">
        <select name="status">
          <option value="">All Status</option>
          <option value="Pending"   <?= $filter_status==='Pending'  ?'selected':'' ?>>Pending</option>
          <option value="In Progress" <?= $filter_status==='In Progress'?'selected':'' ?>>In Progress</option>
          <option value="Completed" <?= $filter_status==='Completed'?'selected':'' ?>>Completed</option>
        </select>
        <select name="priority">
          <option value="">All Priority</option>
          <option value="Low"    <?= $filter_priority==='Low'   ?'selected':'' ?>>Low</option>
          <option value="Medium" <?= $filter_priority==='Medium'?'selected':'' ?>>Medium</option>
          <option value="High"   <?= $filter_priority==='High'  ?'selected':'' ?>>High</option>
        </select>
        <input type="text" name="search" placeholder="Search tasks..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-gold">Filter</button>
      </div>
    </form>
  </div>
 
  <!-- TASK TABLE -->
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Task Title</th>
          <th>Description</th>
          <th>Priority</th>
          <th>Due Date</th>
          <th>Status</th>
          <th>Assigned By</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $i = 1;
      $task_count = 0;
      while ($task = mysqli_fetch_assoc($tasksQ)):
          $task_count++;
          $st      = strtolower($task['status'] ?? 'pending');
          $pri     = strtolower($task['priority'] ?? 'medium');
          $is_over = ($st !== 'completed' && !empty($task['due_date']) && $task['due_date'] < date('Y-m-d'));
          $row_class = $st==='completed' ? 'completed-row' : ($is_over ? 'overdue' : '');
          $status_badge = $is_over && $st!=='completed' ? 'overdue' : $st;
      ?>
      <tr class="<?= $row_class ?>">
        <td style="color:var(--muted)"><?= $i++ ?></td>
        <td class="task-title" style="font-weight:600"><?= htmlspecialchars($task['title']) ?></td>
        <td style="max-width:200px;color:var(--muted)"><?= htmlspecialchars(substr($task['description']??'',0,60)) ?><?= strlen($task['description']??'')>60?'...':'' ?></td>
        <td><span class="bx <?= $pri ?>"><?= ucfirst($pri) ?></span></td>
        <td style="white-space:nowrap;font-size:12px">
          <?= $task['due_date'] ? date('d M Y', strtotime($task['due_date'])) : '—' ?>
          <?php if($is_over): ?><br><span style="font-size:10px;color:#fca5a5">Overdue</span><?php endif; ?>
        </td>
        <td><span class="bx <?= $status_badge ?>"><?= ucfirst($task['status']??'pending') ?></span></td>
        <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($task['assigned_by']??'Admin') ?></td>
        <td style="white-space:nowrap">
          <?php if(strtolower($task['status']) !== 'completed'): ?>
          <form method="POST" style="display:inline">
            <input type="hidden" name="complete" value="<?= $task['id'] ?>">
            <button type="submit" class="btn btn-green" style="padding:6px 12px;font-size:11px">✓ Done</button>
          </form>
          <?php endif; ?>
          <form method="POST" style="display:inline;margin-left:4px" onsubmit="return confirm('Delete this task?')">
            <input type="hidden" name="delete" value="<?= $task['id'] ?>">
            <button type="submit" class="btn btn-red" style="padding:6px 12px;font-size:11px">🗑</button>
          </form>
        </td>
      </tr>
      <?php endwhile; ?>
      <?php if($task_count === 0): ?>
      <tr><td colspan="8" class="empty">No tasks found. <?= $filter_status||$filter_priority||$search ? 'Try clearing your filters.' : 'Your manager will assign tasks soon.' ?></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
 
</div>
</body>
</html>