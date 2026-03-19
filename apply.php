
<?php
include "db_connect.php";
require_once __DIR__ . "/send_mail.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
if (!isset($_GET['job'])) { die("Job not specified."); }
 
$job_ref = mysqli_real_escape_string($conn, $_GET['job']);
 
// Look up by id first (slug is NULL in your table), fallback to slug
$job = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM jobs WHERE id='$job_ref' LIMIT 1"));
if (!$job) {
    $job = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM jobs WHERE slug='$job_ref' LIMIT 1"));
}
if (!$job) { die("Job not found. <a href='employment.php' style='color:#c8a43c;font-family:sans-serif'>← Back to Careers</a>"); }
 
$message = "";
$success = false;
 
if (isset($_POST['apply']) && $job['status'] === 'open') {
    $full_name      = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email          = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone          = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $position_title = mysqli_real_escape_string($conn, $job['title'] ?? 'Unknown Position');
    $resumeName     = "";
 
    if (!empty($_FILES['resume']['name'])) {
        $resumeName = time() . "_" . basename($_FILES['resume']['name']);
        if (!is_dir("uploads")) mkdir("uploads", 0755, true);
        move_uploaded_file($_FILES['resume']['tmp_name'], "uploads/" . $resumeName);
    }
 
    $insert = mysqli_query($conn,
        "INSERT INTO job_applications (full_name, email, phone, position, resume, status, created_at)
         VALUES ('$full_name','$email','$phone','$position_title','$resumeName','pending',NOW())");
 
    if ($insert) {
        $subj  = "Application Received — " . $job['title'] . " | HousingHub";
        $body  = "Dear " . $_POST['full_name'] . ",\n\n"
               . "Thank you for applying for " . $job['title'] . " at HousingHub.\n\n"
               . "We have received your application and will review it carefully.\n"
               . "You will be notified by email once a decision is made.\n\n"
               . "What happens next?\n"
               . "  1. HR team reviews applications (3-5 business days)\n"
               . "  2. Shortlisted candidates contacted for interview\n"
               . "  3. You receive an update email regardless of outcome\n\n"
               . "Questions? Email careers@housinghuborg.ug\n\n"
               . "Best regards,\nHousingHub HR Team";
        send_mail($_POST['email'], $subj, $body);
 
        $message = "Application submitted! A confirmation email has been sent to " . htmlspecialchars($_POST['email']) . ".";
        $success = true;
    } else {
        $message = "Something went wrong. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply — <?= htmlspecialchars($job['title']) ?> | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25);--header-h:100px}
html{scroll-behavior:smooth}
body{font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden;min-height:100vh;padding-top:var(--header-h);cursor:none}
.page-bg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(14,90,200,.18),transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12),transparent 50%),var(--ink)}
.page-grid{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.02) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.02) 1px,transparent 1px);background-size:72px 72px}
header{position:fixed;top:0;left:0;right:0;height:var(--header-h);z-index:9999;display:flex;justify-content:space-between;align-items:center;padding:0 60px;background:var(--gold);border-bottom:1px solid rgba(0,0,0,.15);box-shadow:0 2px 20px rgba(0,0,0,.3)}
.header-logo{display:flex;align-items:center;gap:14px}
.logo-circle{width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.3)}
.logo-text{font-family:"Cormorant Garamond",serif;font-size:28px;font-weight:900;letter-spacing:2px;text-transform:uppercase;color:var(--white);line-height:1}
.logo-slogan{font-size:12px;color:rgba(0,0,80,.7);font-style:italic;display:block;margin-top:3px}
nav{display:flex;align-items:center;gap:4px;position:relative;z-index:10000}
nav>a{font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--white);text-decoration:none;padding:8px 12px;transition:opacity .2s}
nav>a:hover{opacity:.75}
.dropdown{position:relative;z-index:10001}
.dd-btn{font-family:"Outfit",sans-serif;font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:rgba(0,0,80,.8);background:none;border:none;padding:8px 12px;cursor:pointer;white-space:nowrap;transition:color .2s}
.dd-btn:hover,.dd-btn.open{color:var(--white)}
.dd-menu{display:none;position:absolute;top:calc(100% + 6px);left:0;min-width:220px;background:rgba(4,9,26,.99);border:1px solid var(--gb);border-radius:6px;padding:6px 0;box-shadow:0 20px 50px rgba(0,0,0,.8);z-index:10002}
.dd-menu.open{display:block}
.dd-menu a{display:block;font-size:12px;color:var(--muted);text-decoration:none;padding:10px 20px;transition:color .2s,background .2s;white-space:nowrap}
.dd-menu a:hover{color:var(--gold);background:rgba(200,164,60,.08)}
.page-wrap{position:relative;z-index:10;padding:20px 24px 60px;display:flex;justify-content:center}
.page-wrap.success{padding-top:0}
.apply-card{width:100%;max-width:680px}
.back-link{display:inline-flex;align-items:center;gap:8px;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);text-decoration:none;margin-bottom:20px;transition:color .3s}
.back-link:hover{color:var(--gold)}
.back-link::before{content:"←";font-size:15px}
.eyebrow{font-size:11px;font-weight:500;letter-spacing:4px;text-transform:uppercase;color:var(--gold);display:flex;align-items:center;gap:10px;margin-bottom:12px}
.eyebrow::before{content:"";width:24px;height:1px;background:var(--gold)}
.job-title{font-family:"Cormorant Garamond",serif;font-size:clamp(28px,4vw,48px);font-weight:700;color:var(--white);line-height:1.1;margin-bottom:14px}
.job-title em{color:var(--gold);font-style:italic}
.job-tags{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px}
.job-tag{font-size:11px;letter-spacing:1px;text-transform:uppercase;padding:5px 12px;border:1px solid var(--border);border-radius:4px;color:var(--muted)}
.job-tag.loc{border-color:rgba(200,164,60,.3);color:var(--gold)}
.job-desc{font-size:14px;color:var(--muted);line-height:1.7;padding:18px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;margin-bottom:24px}
.success-block{text-align:center;padding:28px;background:rgba(52,199,89,.05);border:1px solid rgba(52,199,89,.2);border-radius:14px}
.success-block h3{font-family:"Cormorant Garamond",serif;font-size:28px;color:var(--white);margin-bottom:10px}
.success-block p{font-size:14px;color:var(--muted);line-height:1.6;margin-bottom:20px}
.timeline{text-align:left;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:18px 22px;margin:18px 0}
.t-step{display:flex;gap:12px;margin-bottom:14px;align-items:flex-start}
.t-step:last-child{margin-bottom:0}
.t-num{width:26px;height:26px;border-radius:50%;background:rgba(200,164,60,.12);border:1px solid var(--gold);color:var(--gold);font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px}
.t-step.done .t-num{background:rgba(52,199,89,.15);border-color:#34c759;color:#34c759}
.t-step.now .t-num{animation:pulse 2s infinite}
@keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(200,164,60,.4)}50%{box-shadow:0 0 0 6px rgba(200,164,60,0)}}
.t-title{font-size:13px;font-weight:600;color:var(--white);margin-bottom:2px}
.t-desc{font-size:12px;color:var(--muted);line-height:1.5}
.form-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:14px;padding:36px;margin-top:14px}
.form-card h3{font-family:"Cormorant Garamond",serif;font-size:22px;color:var(--white);margin-bottom:6px}
.form-sub{font-size:13px;color:var(--muted);margin-bottom:28px;line-height:1.6}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-group{margin-bottom:18px}
.form-group label{display:block;font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:7px}
.form-group label span{font-size:10px;color:rgba(200,164,60,.5);font-weight:400;text-transform:none;font-style:italic;letter-spacing:0}
.form-group input,.form-group textarea{width:100%;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:6px;padding:12px 14px;font-family:"Outfit",sans-serif;font-size:14px;color:var(--white);outline:none;transition:border-color .3s}
.form-group input::placeholder,.form-group textarea::placeholder{color:rgba(255,255,255,.2)}
.form-group input:focus,.form-group textarea:focus{border-color:rgba(200,164,60,.5);background:rgba(200,164,60,.03)}
.form-group textarea{min-height:110px;resize:vertical}
.file-hint{font-size:11px;color:rgba(255,255,255,.2);margin-top:6px;line-height:1.5}
.error-msg{padding:12px 16px;background:rgba(255,59,48,.08);border:1px solid rgba(255,59,48,.25);border-radius:6px;font-size:13px;color:#ff8f8a;margin-bottom:16px}
.submit-row{display:flex;align-items:center;gap:16px;margin-top:6px;flex-wrap:wrap}
.form-note{font-size:11px;color:rgba(255,255,255,.2);line-height:1.6}
.btn-gold{display:inline-block;padding:12px 28px;background:var(--gold);color:var(--ink);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:3px;border:none;cursor:pointer;transition:all .3s;font-family:"Outfit",sans-serif}
.btn-gold:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,164,60,.3)}
footer{position:relative;z-index:10;padding:24px 60px;border-top:1px solid var(--border);text-align:center;font-size:11px;letter-spacing:1.5px;color:rgba(255,255,255,.2)}
#cur-dot{width:8px;height:8px;background:var(--gold);border-radius:50%;position:fixed;z-index:99999;pointer-events:none;transform:translate(-50%,-50%)}
#cur-ring{width:20px;height:20px;border:1.5px solid rgba(200,164,60,.6);border-radius:50%;position:fixed;z-index:99998;pointer-events:none;transform:translate(-50%,-50%);transition:left .08s,top .08s}
body.ch #cur-dot{background:#fff}
body.ch #cur-ring{width:28px;height:28px;background:rgba(200,164,60,.06)}
@media(max-width:700px){
  :root{--header-h:80px}
  header{padding:0 20px}
  .logo-text{font-size:20px}
  .logo-circle{width:44px;height:44px}
  nav .dropdown,.nav-links{display:none}
  .form-row{grid-template-columns:1fr}
  .form-card{padding:20px}
  .page-wrap{padding:14px 16px 60px}
  body{cursor:auto}
  #cur-dot,#cur-ring{display:none}
}
</style>
</head>
<body>
<div id="cur-dot"></div>
<div id="cur-ring"></div>
<div class="page-bg"></div>
<div class="page-grid"></div>
 
<header>
  <div class="header-logo">
    <img src="image/hub.jpg" alt="Logo" class="logo-circle">
    <div><h1 class="logo-text">HOUSING HUB</h1><span class="logo-slogan">"Your Property, Our Priority"</span></div>
  </div>
  <nav>
    <div class="dropdown"><button class="dd-btn">Home &#9660;</button><div class="dd-menu"><a href="index.html">Welcome</a><a href="works.php">How It Works</a></div></div>
    <div class="dropdown"><button class="dd-btn">Features &#9660;</button><div class="dd-menu"><a href="virtual.php">Virtual Property Tours</a><a href="visitor.php">Visitor Management</a><a href="applications.php">Tenant Applications</a><a href="maintenance.php">Maintenance</a><a href="rent_collection.php">Rent Collection</a></div></div>
    <div class="dropdown"><button class="dd-btn">Properties &#9660;</button><div class="dd-menu"><a href="properties.php">All Properties</a><a href="properties.php?type=Commercial">Commercial</a><a href="properties.php?type=Residential">Residential</a><a href="properties.php?type=Industrial">Industrial</a></div></div>
    <a href="index.php">Login</a>
    <div class="dropdown"><button class="dd-btn">About Us &#9660;</button><div class="dd-menu"><a href="who.php">Who We Are</a><a href="what.php">What We Do</a><a href="contact.php">Contact Us</a></div></div>
  </nav>
</header>
 
<div class="page-wrap<?= $success ? ' success' : '' ?>">
  <div class="apply-card">
 
    <a href="employment.php" class="back-link">Back to Careers</a>
 
    <?php if (!$success): ?>
    <div class="eyebrow">Career Opportunity</div>
    <h1 class="job-title">Apply for <em><?= htmlspecialchars($job['title']) ?></em></h1>
    <div class="job-tags">
      <span class="job-tag loc">📍 <?= htmlspecialchars($job['location'] ?? 'Uganda') ?></span>
      <span class="job-tag"><?= htmlspecialchars($job['type'] ?? 'Full Time') ?></span>
      <span class="job-tag"><?= ($job['status']==='open') ? '🟢 Open' : '🔴 Filled' ?></span>
    </div>
    <div class="job-desc"><?= nl2br(htmlspecialchars($job['description'] ?? '')) ?></div>
    <?php endif; ?>
 
    <?php if ($job['status'] === 'taken'): ?>
      <div style="text-align:center;padding:40px 28px;background:rgba(255,59,48,.05);border:1px solid rgba(255,59,48,.15);border-radius:14px">
        <div style="font-size:48px;margin-bottom:14px">🚫</div>
        <h3 style="font-family:'Cormorant Garamond',serif;font-size:26px;color:var(--white);margin-bottom:10px">Position No Longer Available</h3>
        <p style="font-size:14px;color:var(--muted);margin-bottom:20px">This role has been filled. Check our other open positions.</p>
        <a href="employment.php" class="btn-gold">View Other Positions</a>
      </div>
 
    <?php elseif ($success): ?>
      <div class="success-block">
        <div style="font-size:52px;margin-bottom:14px">🎉</div>
        <h3>Application Submitted!</h3>
        <p><?= htmlspecialchars($message) ?></p>
        <div class="timeline">
          <div class="t-step done">
            <div class="t-num">✓</div>
            <div><div class="t-title">Application Received</div><div class="t-desc">Your application is recorded and a confirmation email has been sent.</div></div>
          </div>
          <div class="t-step now">
            <div class="t-num">2</div>
            <div><div class="t-title">Under Review</div><div class="t-desc">Our HR team will review your application within 3–5 business days.</div></div>
          </div>
          <div class="t-step">
            <div class="t-num">3</div>
            <div><div class="t-title">Interview (if shortlisted)</div><div class="t-desc">Shortlisted candidates will be contacted by phone or email.</div></div>
          </div>
          <div class="t-step">
            <div class="t-num">4</div>
            <div><div class="t-title">Final Decision</div><div class="t-desc">You will receive an email update regardless of the outcome.</div></div>
          </div>
        </div>
        <a href="employment.php" class="btn-gold">Back to Careers</a>
      </div>
 
    <?php else: ?>
      <?php if ($message): ?>
        <div class="error-msg">⚠️ <?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <div class="form-card">
        <h3>Your Application</h3>
        <p class="form-sub">Fill in your details below. A confirmation email will be sent immediately after submission.</p>
        <form method="POST" action="apply.php?job=<?= urlencode($job_ref) ?>" enctype="multipart/form-data">
          <div class="form-row">
            <div class="form-group"><label>Full Name</label><input type="text" name="full_name" placeholder="e.g. Nakato Sandra" required></div>
            <div class="form-group"><label>Email Address</label><input type="email" name="email" placeholder="you@email.com" required></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label>Phone Number</label><input type="text" name="phone" placeholder="+256 700 000 000" required></div>
            <div class="form-group"><label>Address <span>(optional)</span></label><input type="text" name="address" placeholder="e.g. Kampala, Uganda"></div>
          </div>
          <div class="form-group"><label>Experience &amp; Skills</label><textarea name="experience" placeholder="Describe your work experience, relevant skills, and why you are interested in this position..." required></textarea></div>
          <div class="form-group">
            <label>Resume / CV <span>(optional)</span></label>
            <input type="file" name="resume" accept=".pdf,.doc,.docx">
            <p class="file-hint">Accepted: PDF, DOC, DOCX · Max 5MB</p>
          </div>
          <div class="submit-row">
            <button type="submit" name="apply" class="btn-gold">Submit Application →</button>
            <p class="form-note">By submitting you agree to our<br><a href="policies.html" style="color:var(--gold)">Terms &amp; Policies</a></p>
          </div>
        </form>
      </div>
    <?php endif; ?>
 
  </div>
</div>
 
<footer>&copy; 2026 HousingHub | All Rights Reserved</footer>
<script>
function closeAll(){document.querySelectorAll('.dd-menu.open').forEach(m=>m.classList.remove('open'));document.querySelectorAll('.dd-btn.open').forEach(b=>b.classList.remove('open'));}
document.querySelectorAll('.dropdown').forEach(dd=>{const btn=dd.querySelector('.dd-btn'),menu=dd.querySelector('.dd-menu');if(!btn||!menu)return;btn.addEventListener('click',e=>{e.stopPropagation();const o=menu.classList.contains('open');closeAll();if(!o){menu.classList.add('open');btn.classList.add('open');}});menu.addEventListener('mousedown',e=>e.stopPropagation());});
document.addEventListener('click',closeAll);
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeAll();});
const cd=document.getElementById('cur-dot'),cr=document.getElementById('cur-ring');
let mx=-200,my=-200,rx=-200,ry=-200;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;cd.style.left=mx+'px';cd.style.top=my+'px';});
(function loop(){rx+=(mx-rx)*.18;ry+=(my-ry)*.18;cr.style.left=rx+'px';cr.style.top=ry+'px';requestAnimationFrame(loop);})();
document.querySelectorAll('a,button,input,textarea').forEach(el=>{el.addEventListener('mouseenter',()=>document.body.classList.add('ch'));el.addEventListener('mouseleave',()=>document.body.classList.remove('ch'));});
</script>
</body>
</html>