<?php
include "db_connect.php";
if (!isset($_GET['job'])) {
    die("Job not specified.");
}
$slug = mysqli_real_escape_string($conn, $_GET['job']);
$jobQuery = mysqli_query($conn, "SELECT * FROM jobs WHERE slug='$slug'");
if (mysqli_num_rows($jobQuery) == 0) {
    die("Job not found.");
}
$job = mysqli_fetch_assoc($jobQuery);
$message = "";
$success = false;
if (isset($_POST['apply']) && $job['status'] === 'open') {
    $full_name  = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $phone      = mysqli_real_escape_string($conn, $_POST['phone']);
    $address    = mysqli_real_escape_string($conn, $_POST['address']);
    $experience = mysqli_real_escape_string($conn, $_POST['experience']);
    $resumeName = "";
    if (!empty($_FILES['resume']['name'])) {
        $resumeName = time() . "_" . $_FILES['resume']['name'];
        move_uploaded_file($_FILES['resume']['tmp_name'], "uploads/" . $resumeName);
    }
    $insert = mysqli_query($conn, "
        INSERT INTO applications (job_id, full_name, email, phone, address, resume, experience)
        VALUES ('{$job['id']}', '$full_name', '$email', '$phone', '$address', '$resumeName', '$experience')
    ");
    if ($insert) {
        $message = "Your application has been submitted successfully. We will be in touch soon.";
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
<title>Apply &mdash; <?= htmlspecialchars($job['title']) ?> | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25)}
body{cursor:none;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden;min-height:100vh}
#cur-dot{width:8px;height:8px;background:var(--gold);border-radius:50%;position:fixed;z-index:99999;pointer-events:none;transform:translate(-50%,-50%);mix-blend-mode:difference}
#cur-ring{width:20px;height:20px;border:1.5px solid rgba(200,164,60,.7);border-radius:50%;position:fixed;z-index:99998;pointer-events:none;transform:translate(-50%,-50%);transition:width .45s cubic-bezier(.23,1,.32,1),height .45s}
#cur-trail{width:30px;height:30px;border:1px solid rgba(200,164,60,.15);border-radius:50%;position:fixed;z-index:99997;pointer-events:none;transform:translate(-50%,-50%);transition:width .7s,height .7s}
body.cursor-hover #cur-dot{width:14px;height:14px;background:#fff}
body.cursor-hover #cur-ring{width:20px;height:20px;border-color:var(--gold);background:rgba(200,164,60,.06)}
body.cursor-click #cur-dot{width:5px;height:5px}
body.cursor-click #cur-ring{width:20px;height:20px}
.page-bg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(14,90,200,.18) 0%,transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12) 0%,transparent 50%),var(--ink)}
.page-grid{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:72px 72px}
.ptcl{position:fixed;border-radius:50%;pointer-events:none;z-index:1;animation:pdrift linear infinite}
@keyframes pdrift{0%{transform:translateY(100vh) scale(0);opacity:0}5%{opacity:1}95%{opacity:.5}100%{transform:translateY(-10vh) translateX(50px) scale(1.4);opacity:0}}
.z{position:relative;z-index:10}

/* HEADER */
header{position:sticky;top:0;z-index:9000;display:flex;justify-content:space-between;align-items:center;padding:18px 60px;background:var(--gold);border-bottom:1px solid var(--border);animation:fadeDown .8s ease both;overflow:visible}
@keyframes fadeDown{from{opacity:0;transform:translateY(-16px)}to{opacity:1;transform:translateY(0)}}
.header-logo{display:flex;align-items:center;gap:14px}
.logo-circle{width:65px;height:65px;border-radius:50%;object-fit:cover;border:2px solid var(--gb)}
.logo-text{font-family:"Cormorant Garamond",serif;font-size:32px;font-weight:900;letter-spacing:2px;text-transform:uppercase;color:var(--white);line-height:1}
.logo-slogan{font-size:14px;color:darkblue;font-style:italic;display:block;margin-top:3px}
nav{display:flex;align-items:center;gap:4px;overflow:visible;position:relative;z-index:9001}
nav > a{font-size:12px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:var(--white);text-decoration:none;padding:8px 14px;transition:color .3s}
nav > a:hover{opacity:.8}
.dropdown{position:relative;overflow:visible;z-index:9002}
.dd-btn{display:block;font-family:"Outfit",sans-serif;font-size:12px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:darkblue;background:none;border:none;padding:8px 14px;white-space:nowrap;cursor:pointer;transition:color .3s}
.dd-btn:hover,.dd-btn.open{color:var(--white)}
.dd-menu{display:none;position:absolute;top:calc(100% + 8px);left:0;min-width:230px;z-index:99999;background:rgba(4,9,26,.99);border:1px solid var(--gb);border-radius:5px;padding:6px 0;box-shadow:0 24px 60px rgba(0,0,0,.85)}
.dd-menu.open{display:block}
.dd-menu a{display:block;font-size:12px;font-weight:400;letter-spacing:1px;color:var(--muted);text-decoration:none;padding:11px 22px;transition:color .2s,background .2s;white-space:nowrap}
.dd-menu a:hover{color:var(--gold);background:rgba(200,164,60,.08)}
.dd-divider{height:1px;background:var(--border);margin:5px 0}

/* PAGE */
.page-wrap{position:relative;z-index:10;min-height:calc(100vh - 120px);display:flex;align-items:flex-start;justify-content:center;padding:60px 24px 80px}
.apply-card{width:100%;max-width:680px}

/* BACK LINK */
.back-link{display:inline-flex;align-items:center;gap:8px;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);text-decoration:none;margin-bottom:32px;transition:color .3s}
.back-link:hover{color:var(--gold)}
.back-link::before{content:"←";font-size:16px}

/* JOB META */
.job-meta{margin-bottom:36px}
.job-eyebrow{font-size:11px;font-weight:500;letter-spacing:4px;text-transform:uppercase;color:var(--gold);display:flex;align-items:center;gap:10px;margin-bottom:14px}
.job-eyebrow::before{content:"";width:28px;height:1px;background:var(--gold)}
.job-title{font-family:"Cormorant Garamond",serif;font-size:clamp(32px,5vw,52px);font-weight:700;color:var(--white);line-height:1.1;margin-bottom:16px}
.job-title em{color:var(--gold);font-style:italic}
.job-tags{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:16px}
.job-tag{font-size:11px;letter-spacing:1px;text-transform:uppercase;padding:6px 14px;border:1px solid var(--border);border-radius:4px;color:var(--muted)}
.job-tag.loc{border-color:rgba(200,164,60,.3);color:var(--gold)}
.job-desc{font-size:14px;color:var(--muted);line-height:1.7;padding:20px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px}

/* TAKEN */
.taken-block{text-align:center;padding:48px 32px;background:rgba(255,59,48,.05);border:1px solid rgba(255,59,48,.15);border-radius:14px;margin-top:16px}
.taken-icon{font-size:48px;margin-bottom:16px}
.taken-block h3{font-family:"Cormorant Garamond",serif;font-size:28px;color:var(--white);margin-bottom:10px}
.taken-block p{font-size:14px;color:var(--muted);line-height:1.6;margin-bottom:20px}
.btn-gold{display:inline-block;padding:13px 28px;background:var(--gold);color:var(--ink);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:2px;transition:all .3s;border:none;cursor:pointer}
.btn-gold:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,164,60,.3)}

/* SUCCESS */
.success-block{text-align:center;padding:48px 32px;background:rgba(52,199,89,.05);border:1px solid rgba(52,199,89,.2);border-radius:14px;margin-top:16px}
.success-block .success-icon{font-size:48px;margin-bottom:16px}
.success-block h3{font-family:"Cormorant Garamond",serif;font-size:28px;color:var(--white);margin-bottom:10px}
.success-block p{font-size:14px;color:var(--muted);line-height:1.6;margin-bottom:24px}

/* FORM */
.form-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:14px;padding:40px;margin-top:16px}
.form-card h3{font-family:"Cormorant Garamond",serif;font-size:24px;color:var(--white);margin-bottom:6px}
.form-card .form-sub{font-size:13px;color:var(--muted);margin-bottom:32px;line-height:1.6}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{margin-bottom:20px}
.form-group label{display:block;font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:8px}
.form-group label span{font-size:10px;letter-spacing:1px;color:rgba(200,164,60,.6);font-weight:400;text-transform:none;font-style:italic}
.form-group input,.form-group textarea,.form-group select{width:100%;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:6px;padding:13px 16px;font-family:"Outfit",sans-serif;font-size:14px;color:var(--white);outline:none;transition:border-color .3s}
.form-group input::placeholder,.form-group textarea::placeholder{color:rgba(255,255,255,.25)}
.form-group input:focus,.form-group textarea:focus{border-color:rgba(200,164,60,.5);background:rgba(200,164,60,.04)}
.form-group textarea{min-height:120px;resize:vertical}
.file-hint{font-size:11px;color:rgba(255,255,255,.25);margin-top:6px;line-height:1.5}
.error-msg{font-size:12px;color:#ff6b6b;margin-top:4px;padding:10px 16px;background:rgba(255,59,48,.08);border:1px solid rgba(255,59,48,.2);border-radius:6px}
.submit-row{display:flex;align-items:center;gap:16px;margin-top:8px}
.form-note{font-size:11px;color:rgba(255,255,255,.2);line-height:1.5}

footer{padding:28px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}

@media(max-width:700px){
  header,footer{padding-left:24px;padding-right:24px}
  .form-row{grid-template-columns:1fr}
  .form-card{padding:24px}
  body{cursor:auto}
  #cur-dot,#cur-ring,#cur-trail{display:none}
}
</style>
</head>
<body>
<div id="cur-dot"></div><div id="cur-ring"></div><div id="cur-trail"></div>
<div class="page-bg"></div><div class="page-grid"></div>

<!-- HEADER -->
<header class="z">
  <div class="header-logo">
    <img src="image/hub.jpg" alt="Logo" class="logo-circle">
    <div>
      <h1 class="logo-text">HOUSING HUB</h1>
      <span class="logo-slogan">"Your Property, Our Priority"</span>
    </div>
  </div>
  <nav>
    <div class="dropdown">
      <button class="dd-btn">Home &#9660;</button>
      <div class="dd-menu">
        <a href="index.html#welcome">Welcome</a>
        <a href="index.html#how-it-works">How It Works</a>
        <a href="index.html#testimonials">Testimonials</a>
        <a href="index.html#our-stats">Our Stats</a>
        <a href="index.html#faqs">FAQs</a>
        <a href="index.html#contact-us">Contact Us</a>
      </div>
    </div>
    <div class="dropdown">
      <button class="dd-btn">Features &#9660;</button>
      <div class="dd-menu">
        <a href="virtual.php">Virtual Property Tours</a>
        <a href="visitor.php">Visitor/Guest Management</a>
        <a href="applications.php">Online Tenant Applications</a>
        <a href="reporting.php">Rent/Buy Reporting</a>
        <a href="lease.php">Online Lease</a>
        <a href="maintenance.php">Maintenance</a>
        <a href="rent_collection.php">Rent Collection</a>
        <a href="notifications.php">Smart Notification Center</a>
        <a href="complaints.php">Complaints &amp; Feedback HUB</a>
        <a href="owner_portal.php">Owner Portal &amp; Reporting</a>
        <a href="policies.html">Policies</a>
      </div>
    </div>
    <div class="dropdown">
      <button class="dd-btn">Use Cases &#9660;</button>
      <div class="dd-menu">
        <a href="tenants.php">Tenants</a>
        <a href="propertyowners.php">Property Owners</a>
        <a href="broker.php">Broker</a>
        <a href="employment.php">Employment</a>
      </div>
    </div>
    <div class="dropdown">
      <button class="dd-btn">Properties &#9660;</button>
      <div class="dd-menu">
        <a href="properties.php">All Properties</a>
        <div class="dd-divider"></div>
        <a href="properties.php?type=Commercial">Commercial</a>
        <a href="properties.php?type=Residential">Residential</a>
        <a href="properties.php?type=Industrial">Industrial</a>
        <a href="properties.php?type=Agricultural">Agricultural</a>
        <a href="properties.php?type=Special+Purpose">Special Purpose</a>
        <a href="properties.php?type=Land">Land</a>
      </div>
    </div>
    <a href="index.php">Login</a>
    <div class="dropdown">
      <button class="dd-btn">About Us &#9660;</button>
      <div class="dd-menu">
        <a href="who.php">Who We Are</a>
        <a href="what.php">What We Do</a>
        <a href="vision.php">Our Vision</a>
        <a href="values.php">Core Values</a>
        <a href="contact.php">Contact Us</a>
      </div>
    </div>
  </nav>
</header>

<div class="page-wrap">
  <div class="apply-card z">

    <a href="employment.php" class="back-link">Back to Careers</a>

    <div class="job-meta">
      <div class="job-eyebrow">Career Opportunity</div>
      <h1 class="job-title">Apply for <em><?= htmlspecialchars($job['title']) ?></em></h1>
      <div class="job-tags">
        <span class="job-tag loc">&#128205; <?= htmlspecialchars($job['location']) ?></span>
        <span class="job-tag"><?= htmlspecialchars($job['type']) ?></span>
        <span class="job-tag"><?= $job['status'] === 'open' ? '&#128994; Open' : '&#128308; Filled' ?></span>
      </div>
      <div class="job-desc"><?= nl2br(htmlspecialchars($job['description'])) ?></div>
    </div>

    <?php if ($job['status'] === 'taken'): ?>
      <div class="taken-block">
        <div class="taken-icon">&#128683;</div>
        <h3>Position No Longer Available</h3>
        <p>This role has already been filled. Check our other open positions or send a speculative application to <strong style="color:var(--gold)">careers@housinghub.ug</strong></p>
        <a href="employment.php" class="btn-gold">View Other Positions</a>
      </div>

    <?php elseif ($success): ?>
      <div class="success-block">
        <div class="success-icon">&#10003;</div>
        <h3>Application Submitted!</h3>
        <p><?= htmlspecialchars($message) ?></p>
        <a href="employment.php" class="btn-gold">Back to Careers</a>
      </div>

    <?php else: ?>
      <?php if ($message): ?>
        <div class="error-msg"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <div class="form-card">
        <h3>Your Application</h3>
        <p class="form-sub">Fill in your details below. Fields marked optional are not required but help your application stand out.</p>

        <form method="POST" enctype="multipart/form-data">
          <div class="form-row">
            <div class="form-group">
              <label>Full Name</label>
              <input type="text" name="full_name" placeholder="e.g. Nakato Sandra" required>
            </div>
            <div class="form-group">
              <label>Email Address</label>
              <input type="email" name="email" placeholder="you@email.com" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" name="phone" placeholder="+256 700 000 000" required>
            </div>
            <div class="form-group">
              <label>Address <span>(optional)</span></label>
              <input type="text" name="address" placeholder="e.g. Kampala, Uganda">
            </div>
          </div>

          <div class="form-group">
            <label>Experience &amp; Skills</label>
            <textarea name="experience" placeholder="Describe your work experience, relevant skills, and why you are interested in this position at HousingHub..." required></textarea>
          </div>

          <div class="form-group">
            <label>Resume / CV <span>(optional)</span></label>
            <input type="file" name="resume" accept=".pdf,.doc,.docx">
            <p class="file-hint">Accepted formats: PDF, DOC, DOCX. You can leave this blank and rely on your experience description above.</p>
          </div>

          <div class="submit-row">
            <button type="submit" name="apply" class="btn-gold">Submit Application</button>
            <p class="form-note">By submitting you agree to our<br><a href="policies.html" style="color:var(--gold)">Terms &amp; Policies</a></p>
          </div>
        </form>
      </div>
    <?php endif; ?>

  </div>
</div>

<footer class="z">&copy; 2026 HousingHub | All Rights Reserved</footer>

<script>
function closeAllMenus(){document.querySelectorAll('.dd-menu.open').forEach(function(m){m.classList.remove('open')});document.querySelectorAll('.dd-btn.open').forEach(function(b){b.classList.remove('open')})}
document.querySelectorAll('.dropdown').forEach(function(dd){var btn=dd.querySelector('.dd-btn');var menu=dd.querySelector('.dd-menu');if(!btn||!menu)return;btn.addEventListener('click',function(e){e.stopPropagation();var isOpen=menu.classList.contains('open');closeAllMenus();if(!isOpen){menu.classList.add('open');btn.classList.add('open')}});menu.addEventListener('mousedown',function(e){e.stopPropagation()});menu.addEventListener('click',function(e){e.stopPropagation()})});
document.addEventListener('click',closeAllMenus);
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeAllMenus()});
const dot=document.getElementById('cur-dot'),ring=document.getElementById('cur-ring'),trail=document.getElementById('cur-trail');
let mx=-200,my=-200,rx=-200,ry=-200,tx=-200,ty=-200;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;dot.style.left=mx+'px';dot.style.top=my+'px';});
(function anim(){rx+=(mx-rx)*.15;ry+=(my-ry)*.15;tx+=(mx-tx)*.06;ty+=(my-ty)*.06;ring.style.left=rx+'px';ring.style.top=ry+'px';trail.style.left=tx+'px';trail.style.top=ty+'px';requestAnimationFrame(anim);})();
document.querySelectorAll('a,button,input,textarea').forEach(el=>{el.addEventListener('mouseenter',()=>document.body.classList.add('cursor-hover'));el.addEventListener('mouseleave',()=>document.body.classList.remove('cursor-hover'));});
document.addEventListener('mousedown',()=>document.body.classList.add('cursor-click'));
document.addEventListener('mouseup',()=>document.body.classList.remove('cursor-click'));
for(let i=0;i<16;i++){const p=document.createElement('div');p.classList.add('ptcl');const sz=Math.random()*3+1;p.style.cssText=`width:${sz}px;height:${sz}px;left:${Math.random()*100}%;background:rgba(200,164,60,${(Math.random()*.5+.15).toFixed(2)});animation-duration:${Math.random()*22+10}s;animation-delay:${Math.random()*18}s;`;document.body.appendChild(p);}
</script>
</body>
</html>