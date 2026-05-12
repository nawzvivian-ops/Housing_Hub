<?php
$name     = htmlspecialchars($_GET['name']     ?? 'Applicant');
$email    = htmlspecialchars($_GET['email']    ?? '');
$position = htmlspecialchars($_GET['position'] ?? 'the position');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Application Submitted | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25);--hh:100px}
body{font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);min-height:100vh;padding-top:var(--hh);cursor:none;
  background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(14,90,200,.18),transparent 55%),
             radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12),transparent 50%),var(--ink)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;
  background-image:linear-gradient(rgba(255,255,255,.02) 1px,transparent 1px),
                   linear-gradient(90deg,rgba(255,255,255,.02) 1px,transparent 1px);
  background-size:72px 72px}
header{position:fixed;top:0;left:0;right:0;height:var(--hh);z-index:9999;
  display:flex;justify-content:space-between;align-items:center;padding:0 60px;
  background:var(--gold);border-bottom:1px solid rgba(0,0,0,.15);box-shadow:0 2px 20px rgba(0,0,0,.3)}
.header-logo{display:flex;align-items:center;gap:14px}
.logo-circle{width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.3)}
.logo-text{font-family:"Cormorant Garamond",serif;font-size:28px;font-weight:900;letter-spacing:2px;text-transform:uppercase;color:var(--white);line-height:1}
.logo-slogan{font-size:12px;color:rgba(0,0,80,.7);font-style:italic;display:block;margin-top:3px}
 
/* â”€â”€ CENTER the content right below the header, no extra gap â”€â”€ */
.wrap{
  position:relative;z-index:10;
  min-height:calc(100vh - var(--hh));
  display:flex;
  align-items:center;
  justify-content:center;
  padding:20px 24px;
}
.card{width:100%;max-width:600px;text-align:center}
.emoji{font-size:64px;display:block;margin-bottom:18px;animation:pop .6s cubic-bezier(.23,1,.32,1) both}
@keyframes pop{from{transform:scale(0);opacity:0}to{transform:scale(1);opacity:1}}
h1{font-family:"Cormorant Garamond",serif;font-size:clamp(28px,5vw,42px);font-weight:700;color:var(--white);margin-bottom:10px}
h1 em{color:var(--gold);font-style:italic}
.sub{font-size:14px;color:var(--muted);line-height:1.7;margin-bottom:6px}
.email-note{font-size:13px;color:rgba(200,164,60,.8);margin-bottom:28px}
.timeline{text-align:left;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:20px 24px;margin-bottom:28px}
.t-step{display:flex;gap:14px;margin-bottom:14px;align-items:flex-start}
.t-step:last-child{margin-bottom:0}
.t-num{width:28px;height:28px;border-radius:50%;background:rgba(200,164,60,.1);border:1px solid var(--gold);color:var(--gold);font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px}
.t-step.done .t-num{background:rgba(52,199,89,.15);border-color:#34c759;color:#34c759}
.t-step.now .t-num{animation:pulse 2s infinite}
@keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(200,164,60,.4)}50%{box-shadow:0 0 0 6px rgba(200,164,60,0)}}
.t-title{font-size:13px;font-weight:600;color:var(--white);margin-bottom:3px}
.t-desc{font-size:12px;color:var(--muted);line-height:1.5}
.btn-gold{display:inline-block;padding:13px 32px;background:var(--gold);color:var(--ink);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:3px;transition:all .3s;font-family:"Outfit",sans-serif}
.btn-gold:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,164,60,.3)}
#cur-dot{width:8px;height:8px;background:var(--gold);border-radius:50%;position:fixed;z-index:99999;pointer-events:none;transform:translate(-50%,-50%)}
#cur-ring{width:20px;height:20px;border:1.5px solid rgba(200,164,60,.6);border-radius:50%;position:fixed;z-index:99998;pointer-events:none;transform:translate(-50%,-50%);transition:left .08s,top .08s}
body.ch #cur-dot{background:#fff}
body.ch #cur-ring{width:28px;height:28px;background:rgba(200,164,60,.06)}
@media(max-width:700px){
  :root{--hh:80px}
  header{padding:0 16px}
  .logo-text{font-size:20px}
  .logo-circle{width:44px;height:44px}
  .wrap{padding:16px}
  body{cursor:auto}
  #cur-dot,#cur-ring{display:none}
}
</style>
</head>
<body>
<div id="cur-dot"></div><div id="cur-ring"></div>
 
<header>
  <div class="header-logo">
    <img src="image/hub.jpg" alt="Logo" class="logo-circle">
    <div><h1 class="logo-text">HOUSING HUB</h1><span class="logo-slogan">"Your Property, Our Priority"</span></div>
  </div>
</header>
 
<div class="wrap">
  <div class="card">
    <span class="emoji">ðŸŽ‰</span>
    <h1>Application <em>Submitted!</em></h1>
    <p class="sub">Thank you, <strong style="color:var(--white)"><?= $name ?></strong>! Your application for <strong style="color:var(--gold)"><?= $position ?></strong> has been received.</p>
    <?php if ($email): ?>
    <p class="email-note">ðŸ“§ A confirmation email has been sent to <strong><?= $email ?></strong></p>
    <?php endif; ?>
 
    <div class="timeline">
      <div class="t-step done">
        <div class="t-num">âœ“</div>
        <div><div class="t-title">Application Received</div><div class="t-desc">Your application is recorded in our system.</div></div>
      </div>
      <div class="t-step now">
        <div class="t-num">2</div>
        <div><div class="t-title">Under Review</div><div class="t-desc">Our HR team will review your application within 3â€“5 business days.</div></div>
      </div>
      <div class="t-step">
        <div class="t-num">3</div>
        <div><div class="t-title">Interview (if shortlisted)</div><div class="t-desc">Shortlisted candidates will be contacted by phone or email.</div></div>
      </div>
      <div class="t-step">
        <div class="t-num">4</div>
        <div><div class="t-title">Final Decision</div><div class="t-desc">You will receive an email update regardless of the outcome. Check your spam too.</div></div>
      </div>
    </div>
 
    <a href="Employment.php" class="btn-gold">â† Back to Careers</a>
  </div>
</div>
 
<script>
const cd=document.getElementById('cur-dot'),cr=document.getElementById('cur-ring');
let mx=-200,my=-200,rx=-200,ry=-200;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;cd.style.left=mx+'px';cd.style.top=my+'px';});
(function loop(){rx+=(mx-rx)*.18;ry+=(my-ry)*.18;cr.style.left=rx+'px';cr.style.top=ry+'px';requestAnimationFrame(loop);})();
document.querySelectorAll('a,button').forEach(el=>{el.addEventListener('mouseenter',()=>document.body.classList.add('ch'));el.addEventListener('mouseleave',()=>document.body.classList.remove('ch'));});
</script>
</body>
</html>