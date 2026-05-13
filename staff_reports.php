<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$user    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id' LIMIT 1"));

if (!$user || strtolower(trim($user['role'])) !== 'staff') {
    echo "<h2 style='color:red;text-align:center;padding:40px;font-family:sans-serif'>Access Denied!</h2>";
    exit();
}

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS staff_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    report_type VARCHAR(100) DEFAULT 'general',
    priority VARCHAR(50) DEFAULT 'medium',
    report_body LONGTEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    admin_feedback TEXT DEFAULT NULL,
    reviewed_by VARCHAR(200) DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (staff_id),
    INDEX (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// submit report
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_report') {
    $title       = trim($_POST['title'] ?? '');
    $report_type = trim($_POST['report_type'] ?? 'general');
    $priority    = trim($_POST['priority'] ?? 'medium');
    $report_body = trim($_POST['report_body'] ?? '');

    if ($title !== '' && $report_body !== '') {
        $title_safe       = mysqli_real_escape_string($conn, $title);
        $report_type_safe = mysqli_real_escape_string($conn, $report_type);
        $priority_safe    = mysqli_real_escape_string($conn, $priority);
        $report_body_safe = mysqli_real_escape_string($conn, $report_body);

        mysqli_query($conn, "INSERT INTO staff_reports (staff_id, title, report_type, priority, report_body, status, created_at)
                             VALUES ('$user_id', '$title_safe', '$report_type_safe', '$priority_safe', '$report_body_safe', 'pending', NOW())");

        // notify admins
        $admin_q = mysqli_query($conn, "SELECT id FROM users WHERE role='admin'");
        while ($admin = mysqli_fetch_assoc($admin_q)) {
            $msg = $user['fullname'] . " submitted a new " . $report_type . " report: " . $title;
            $msg_safe = mysqli_real_escape_string($conn, $msg);
            mysqli_query($conn, "INSERT INTO notifications (user_id, tenant_id, title, message, status, date)
                                 VALUES ('".$admin['id']."', 0, 'New Staff Report', '$msg_safe', 'unread', NOW())");
        }

        $_SESSION['staff_success'] = "Report submitted successfully.";
    } else {
        $_SESSION['staff_error'] = "Please fill in the report title and details.";
    }

    header("Location: staff_reports.php");
    exit();
}

// optional delete own report if still pending
if (isset($_GET['delete'])) {
    $rid = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM staff_reports WHERE id='$rid' AND staff_id='$user_id' AND status='pending'");
    $_SESSION['staff_success'] = "Pending report deleted.";
    header("Location: staff_reports.php");
    exit();
}

$fullname = htmlspecialchars($user['fullname']);
$reports_q = mysqli_query($conn, "SELECT * FROM staff_reports WHERE staff_id='$user_id' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Reports | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --ink:#04091a;--gold:#c8a43c;--white:#fff;--muted:rgba(255,255,255,.45);
  --border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25);
  --red:#ef4444;--green:#16a34a;--blue:#3b82f6;
}
body{
  font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);min-height:100vh;
  padding:24px;
  background-image:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%);
}
.wrap{max-width:1200px;margin:auto}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;gap:12px;flex-wrap:wrap}
.title{font-family:"Cormorant Garamond",serif;font-size:34px;color:var(--gold);font-weight:700}
.sub{font-size:13px;color:var(--muted)}
.btn{
  display:inline-block;padding:10px 16px;border-radius:8px;text-decoration:none;
  border:1px solid var(--gb);color:var(--gold);font-size:12px;font-weight:600;
  background:rgba(200,164,60,.08)
}
.grid{display:grid;grid-template-columns:380px 1fr;gap:20px}
.card{
  background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:14px;padding:20px
}
.card h3{
  font-family:"Cormorant Garamond",serif;font-size:22px;margin-bottom:14px;color:var(--white)
}
label{
  display:block;font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);
  font-weight:700;margin-bottom:6px;margin-top:10px
}
input,select,textarea{
  width:100%;padding:11px 12px;border-radius:8px;border:1px solid var(--border);
  background:rgba(255,255,255,.05);color:var(--white);font-family:"Outfit",sans-serif;
  outline:none
}
textarea{resize:vertical}
button{
  width:100%;margin-top:14px;padding:12px;border:none;border-radius:8px;cursor:pointer;
  background:rgba(200,164,60,.22);border:1px solid var(--gb);color:var(--gold);
  font-weight:700;font-size:13px
}
.alert{padding:13px 16px;border-radius:10px;margin-bottom:18px;font-size:13px}
.success{background:rgba(22,163,74,.12);border:1px solid rgba(22,163,74,.3);color:#86efac}
.error{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.report{
  border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:14px;background:rgba(255,255,255,.02)
}
.report-top{display:flex;justify-content:space-between;gap:10px;align-items:flex-start;flex-wrap:wrap}
.report-title{font-size:16px;font-weight:700;color:var(--white);margin-bottom:4px}
.meta{font-size:11px;color:var(--muted);margin-bottom:8px}
.body{font-size:13px;line-height:1.7;color:rgba(255,255,255,.78);white-space:pre-wrap}
.badge{
  display:inline-block;padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;
  text-transform:uppercase;border:1px solid var(--border)
}
.pending{background:rgba(200,164,60,.1);border-color:var(--gb);color:var(--gold)}
.reviewed{background:rgba(59,130,246,.1);border-color:rgba(59,130,246,.3);color:#93c5fd}
.resolved{background:rgba(22,163,74,.1);border-color:rgba(22,163,74,.3);color:#86efac}
.rejected{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3);color:#fca5a5}
.feedback{
  margin-top:12px;padding:12px;border-radius:10px;background:rgba(59,130,246,.08);
  border:1px solid rgba(59,130,246,.2)
}
.feedback strong{color:#93c5fd;font-size:12px}
.feedback p{margin-top:6px;font-size:13px;color:rgba(255,255,255,.78);line-height:1.6;white-space:pre-wrap}
.delete-link{
  color:#fca5a5;text-decoration:none;font-size:12px;font-weight:600
}
.empty{color:var(--muted);font-size:13px;padding:20px;text-align:center}
@media(max-width:900px){.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="wrap">

  <div class="topbar">
    <div>
      <div class="title">📝 Staff Reports</div>
      <div class="sub">Welcome, <?= $fullname ?> — submit daily, incident, inspection, maintenance, or general reports.</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <a href="staff_dashboard.php" class="btn">← Back to Dashboard</a>
      <a href="logout.php" class="btn" style="border-color:rgba(239,68,68,.3);color:#fca5a5;background:rgba(239,68,68,.08)">Sign Out</a>
    </div>
  </div>

  <?php if(isset($_SESSION['staff_success'])): ?>
    <div class="alert success"><?= $_SESSION['staff_success']; unset($_SESSION['staff_success']); ?></div>
  <?php endif; ?>
  <?php if(isset($_SESSION['staff_error'])): ?>
    <div class="alert error"><?= $_SESSION['staff_error']; unset($_SESSION['staff_error']); ?></div>
  <?php endif; ?>

  <div class="grid">
    <div class="card">
      <h3>Submit New Report</h3>
      <form method="POST">
        <input type="hidden" name="action" value="submit_report">

        <label>Report Title</label>
        <input type="text" name="title" placeholder="e.g. Inspection findings for Block A" required>

        <label>Report Type</label>
        <select name="report_type">
          <option value="daily">Daily Report</option>
          <option value="inspection">Inspection Report</option>
          <option value="maintenance">Maintenance Report</option>
          <option value="incident">Incident Report</option>
          <option value="general">General Report</option>
        </select>

        <label>Priority</label>
        <select name="priority">
          <option value="low">Low</option>
          <option value="medium" selected>Medium</option>
          <option value="high">High</option>
        </select>

        <label>Report Details</label>
        <textarea name="report_body" rows="10" placeholder="Write full report details here..." required></textarea>

        <button type="submit">📨 Submit Report</button>
      </form>
    </div>

    <div class="card">
      <h3>My Submitted Reports</h3>

      <?php if(!$reports_q || mysqli_num_rows($reports_q) === 0): ?>
        <div class="empty">No reports submitted yet.</div>
      <?php else: while($r = mysqli_fetch_assoc($reports_q)): ?>
        <div class="report">
          <div class="report-top">
            <div>
              <div class="report-title"><?= htmlspecialchars($r['title']) ?></div>
              <div class="meta">
                <?= ucfirst(htmlspecialchars($r['report_type'])) ?> -
                Priority: <?= ucfirst(htmlspecialchars($r['priority'])) ?> -
                <?= $r['created_at'] ? date('d M Y, H:i', strtotime($r['created_at'])) : '' ?>
              </div>
            </div>
            <div>
              <span class="badge <?= strtolower($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span>
            </div>
          </div>

          <div class="body"><?= htmlspecialchars($r['report_body']) ?></div>

          <?php if(!empty($r['admin_feedback'])): ?>
            <div class="feedback">
              <strong>Admin Feedback</strong>
              <p><?= htmlspecialchars($r['admin_feedback']) ?></p>
            </div>
          <?php endif; ?>

          <?php if(($r['status'] ?? '') === 'pending'): ?>
            <div style="margin-top:12px">
              <a class="delete-link" href="staff_reports.php?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this pending report?')">🗑 Delete Pending Report</a>
            </div>
          <?php endif; ?>
        </div>
      <?php endwhile; endif; ?>
    </div>
  </div>
</div>
</body>
</html>