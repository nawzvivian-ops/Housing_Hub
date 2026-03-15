
<?php
session_start();
include "db_connect.php";
 
// Admin only
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE id='{$_SESSION['user_id']}' LIMIT 1"));
if (!$admin || strtolower($admin['role']) !== 'admin') { header("Location: dashboard.php"); exit(); }
 
$id = (int)($_GET['id'] ?? 0);
$app = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM job_applications WHERE id = $id LIMIT 1"));
 
if (!$app) { die("Application not found."); }
 
$status      = strtolower($app['status'] ?? 'pending');
$status_color = match($status) {
    'approved' => '#16a34a',
    'rejected' => '#ef4444',
    default    => '#c8a43c'
};
$status_bg = match($status) {
    'approved' => 'rgba(22,163,74,.1)',
    'rejected' => 'rgba(239,68,68,.1)',
    default    => 'rgba(200,164,60,.1)'
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Application — <?= htmlspecialchars($app['full_name']) ?> | HousingHub Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--white:#fff;--muted:rgba(255,255,255,.5);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25)}
body{font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);min-height:100vh;padding:40px 24px;
  background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),
             radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%),var(--ink)}
.wrap{max-width:760px;margin:auto}
.back{display:inline-flex;align-items:center;gap:8px;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);text-decoration:none;margin-bottom:28px;transition:color .3s}
.back:hover{color:var(--gold)}
.header-row{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:28px;flex-wrap:wrap}
h1{font-family:"Cormorant Garamond",serif;font-size:32px;font-weight:700;color:var(--white);margin-bottom:6px}
h1 em{color:var(--gold);font-style:italic}
.status-badge{padding:6px 16px;border-radius:20px;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;border:1px solid;white-space:nowrap;align-self:flex-start}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:20px}
.card-title{font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;color:var(--gold);margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.field-label{font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:5px}
.field-value{font-size:14px;color:var(--white);line-height:1.5}
.experience-box{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:16px;font-size:14px;color:rgba(255,255,255,.8);line-height:1.7;white-space:pre-wrap}
.btn-row{display:flex;gap:12px;flex-wrap:wrap;margin-top:8px}
.btn{display:inline-block;padding:12px 28px;border-radius:6px;font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;text-decoration:none;cursor:pointer;border:none;font-family:"Outfit",sans-serif;transition:all .3s}
.btn-approve{background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.4);color:#86efac}
.btn-approve:hover{background:rgba(22,163,74,.35);transform:translateY(-2px)}
.btn-reject{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.btn-reject:hover{background:rgba(239,68,68,.3);transform:translateY(-2px)}
.btn-back{background:rgba(255,255,255,.06);border:1px solid var(--border);color:var(--muted)}
.btn-back:hover{background:rgba(255,255,255,.1)}
.resume-link{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;background:rgba(200,164,60,.1);border:1px solid var(--gb);border-radius:6px;color:var(--gold);text-decoration:none;font-size:13px;font-weight:600;transition:all .3s}
.resume-link:hover{background:rgba(200,164,60,.2)}
.already-done{background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:8px;padding:16px 20px;font-size:13px;color:var(--muted);text-align:center}
@media(max-width:600px){.grid2{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="wrap">
  <a href="admin_dashboard.php?page=jobs" class="back">← Back to Applications</a>
 
  <div class="header-row">
    <div>
      <h1>Application — <em><?= htmlspecialchars($app['full_name']) ?></em></h1>
      <div style="font-size:13px;color:var(--muted)">
        Applying for: <strong style="color:var(--white)"><?= htmlspecialchars($app['position'] ?? 'Unknown Position') ?></strong>
      </div>
    </div>
    <span class="status-badge" style="background:<?= $status_bg ?>;color:<?= $status_color ?>;border-color:<?= $status_color ?>">
      <?= ucfirst($status) ?>
    </span>
  </div>
 
  <!-- APPLICANT DETAILS -->
  <div class="card">
    <div class="card-title">Applicant Details</div>
    <div class="grid2" style="margin-bottom:16px">
      <div><div class="field-label">Full Name</div><div class="field-value"><?= htmlspecialchars($app['full_name']) ?></div></div>
      <div><div class="field-label">Email</div><div class="field-value"><a href="mailto:<?= htmlspecialchars($app['email']) ?>" style="color:var(--gold)"><?= htmlspecialchars($app['email']) ?></a></div></div>
      <div><div class="field-label">Phone</div><div class="field-value"><a href="tel:<?= htmlspecialchars($app['phone']) ?>" style="color:var(--gold)"><?= htmlspecialchars($app['phone']) ?></a></div></div>
 
      <div><div class="field-label">Applied On</div><div class="field-value"><?= isset($app['created_at']) ? date('d M Y, g:i A', strtotime($app['created_at'])) : 'N/A' ?></div></div>
      <div><div class="field-label">Resume / CV</div><div class="field-value">
        <?php if (!empty($app['resume'])): ?>
          <a href="uploads/<?= htmlspecialchars($app['resume']) ?>" target="_blank" class="resume-link">📄 View Resume</a>
        <?php else: ?>
          <span style="color:var(--muted)">No file uploaded</span>
        <?php endif; ?>
      </div></div>
    </div>
  </div>
 
 
 
  <!-- ACTIONS -->
  <div class="card">
    <div class="card-title">Decision</div>
    <?php if ($status === 'approved'): ?>
      <div class="already-done">✅ This application has already been <strong style="color:#86efac">approved</strong>. A confirmation email was sent to <?= htmlspecialchars($app['email']) ?>.</div>
    <?php elseif ($status === 'rejected'): ?>
      <div class="already-done">❌ This application has been <strong style="color:#fca5a5">rejected</strong>. A notification email was sent to <?= htmlspecialchars($app['email']) ?>.</div>
    <?php else: ?>
      <p style="font-size:13px;color:var(--muted);margin-bottom:18px;line-height:1.6">
        Approving will send a congratulations email to the applicant.<br>
        Rejecting will send a polite decline email.
      </p>
      <div class="btn-row">
        <a href="approve_application.php?id=<?= $app['id'] ?>"
           class="btn btn-approve"
           onclick="return confirm('Approve this application and send email to <?= htmlspecialchars($app['email']) ?>?')">
          ✓ Approve &amp; Notify
        </a>
        <a href="reject_application.php?id=<?= $app['id'] ?>"
           class="btn btn-reject"
           onclick="return confirm('Reject this application and send decline email?')">
          ✕ Reject &amp; Notify
        </a>
      </div>
    <?php endif; ?>
    <div class="btn-row" style="margin-top:16px">
      <a href="admin_dashboard.php?page=jobs" class="btn btn-back">← Back to All Applications</a>
    </div>
  </div>
 
</div>
</body>
</html>