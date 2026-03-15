
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25)}
body{cursor:none;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden}
#cur-dot{width:8px;height:8px;background:var(--gold);border-radius:50%;position:fixed;z-index:99999;pointer-events:none;transform:translate(-50%,-50%);mix-blend-mode:difference}
#cur-ring{width:40px;height:40px;border:1.5px solid rgba(200,164,60,.7);border-radius:50%;position:fixed;z-index:99998;pointer-events:none;transform:translate(-50%,-50%);transition:width .45s cubic-bezier(.23,1,.32,1),height .45s}
#cur-trail{width:80px;height:80px;border:1px solid rgba(200,164,60,.15);border-radius:50%;position:fixed;z-index:99997;pointer-events:none;transform:translate(-50%,-50%);transition:width .7s,height .7s}
body.cursor-hover #cur-dot{width:14px;height:14px;background:#fff}
body.cursor-hover #cur-ring{width:60px;height:60px;border-color:var(--gold);background:rgba(200,164,60,.06)}
body.cursor-click #cur-dot{width:5px;height:5px}
body.cursor-click #cur-ring{width:28px;height:28px}
.page-bg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(14,90,200,.18) 0%,transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12) 0%,transparent 50%),var(--ink)}
.page-grid{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:72px 72px}
.ptcl{position:fixed;border-radius:50%;pointer-events:none;z-index:1;animation:pdrift linear infinite}
@keyframes pdrift{0%{transform:translateY(100vh) scale(0);opacity:0}5%{opacity:1}95%{opacity:.5}100%{transform:translateY(-10vh) translateX(50px) scale(1.4);opacity:0}}
.z{position:relative;z-index:10}
.reveal{opacity:0;transform:translateY(24px);transition:opacity .7s ease,transform .7s ease}
.reveal.visible{opacity:1;transform:translateY(0)}
/* FIXED HEADER */
body{padding-top:106px!important}
header{position:fixed!important;top:0!important;left:0!important;right:0!important;width:100%!important;z-index:99999!important;box-shadow:0 2px 28px rgba(0,0,0,.28)!important}
nav{position:relative!important;z-index:100000!important}
.dropdown{z-index:100001!important}
.dd-menu{z-index:100002!important}
@media(max-width:900px){body{padding-top:80px!important}}
/* HEADER */
header{position:sticky;top:0;z-index:9000;display:flex;justify-content:space-between;align-items:center;padding:18px 60px;background:rgba(200,164,60,.95);border-bottom:1px solid rgba(255,255,255,.1);animation:fadeDown .8s ease both}
@keyframes fadeDown{from{opacity:0;transform:translateY(-14px)}to{opacity:1;transform:translateY(0)}}
.header-logo{display:flex;align-items:center;gap:14px}
.logo-circle{width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.3)}
.logo-text{font-family:"Cormorant Garamond",serif;font-size:22px;font-weight:700;letter-spacing:3px;color:var(--ink)}
.logo-slogan{font-size:10px;letter-spacing:2px;color:rgba(4,9,26,.6);text-transform:uppercase}
nav{display:flex;align-items:center;gap:6px}
.dropdown{position:relative}
.dd-btn{background:none;border:none;font-family:"Outfit",sans-serif;font-size:12px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--ink);padding:10px 14px;cursor:pointer;border-radius:2px;transition:background .2s}
.dd-btn:hover,.dd-btn.open{background:rgba(4,9,26,.1)}
.dd-menu{display:none;position:absolute;top:calc(100% + 6px);left:0;min-width:200px;background:var(--ink);border:1px solid var(--border);border-radius:8px;padding:8px 0;z-index:9999;box-shadow:0 16px 40px rgba(0,0,0,.5)}
.dd-menu.open{display:block}
.dd-menu a{display:block;padding:10px 18px;font-size:12px;letter-spacing:1px;color:var(--muted);text-decoration:none;transition:color .2s,background .2s}
.dd-menu a:hover{color:var(--gold);background:rgba(200,164,60,.06)}
.dd-divider{height:1px;background:var(--border);margin:6px 0}
nav>a{font-size:12px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--ink);text-decoration:none;padding:10px 14px;border-radius:2px;transition:background .2s}
nav>a:hover{background:rgba(4,9,26,.1)}
/* HERO */
.hero{min-height:92vh;display:flex;align-items:center;padding:100px 60px 80px;position:relative;z-index:10}
.hero-content{max-width:680px}
.hero-eyebrow{font-size:11px;font-weight:500;letter-spacing:4px;text-transform:uppercase;color:var(--gold);display:flex;align-items:center;gap:12px;margin-bottom:24px}
.hero-eyebrow::before{content:"";width:36px;height:1px;background:var(--gold)}
.hero h1{font-family:"Cormorant Garamond",serif;font-size:clamp(48px,7vw,88px);font-weight:700;line-height:1.0;margin-bottom:24px;color:var(--white)}
.hero h1 em{color:var(--gold);font-style:italic}
.hero h1 .stroke{-webkit-text-stroke:1px var(--gold);color:transparent}
.hero-sub{font-size:17px;line-height:1.7;color:var(--muted);max-width:520px;margin-bottom:40px}
.hero-btns{display:flex;gap:16px;flex-wrap:wrap}
.btn-primary{padding:15px 34px;background:var(--gold);color:var(--ink);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:2px;transition:all .3s}
.btn-primary:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,164,60,.35)}
.btn-secondary{padding:15px 34px;border:1px solid rgba(200,164,60,.4);color:var(--gold);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:2px;transition:all .3s}
.btn-secondary:hover{background:rgba(200,164,60,.08);transform:translateY(-2px)}
.hero-stats{display:flex;gap:48px;margin-top:56px;padding-top:40px;border-top:1px solid var(--border)}
.hstat-num{font-family:"Cormorant Garamond",serif;font-size:36px;font-weight:700;color:var(--gold)}
.hstat-label{font-size:11px;color:var(--muted);letter-spacing:1px;margin-top:2px}
/* SECTION COMMONS */
section{padding:100px 60px;position:relative;z-index:10}
.section-eyebrow{font-size:11px;font-weight:500;letter-spacing:4px;text-transform:uppercase;color:var(--gold);display:flex;align-items:center;gap:12px;margin-bottom:20px}
.section-eyebrow::before{content:"";width:28px;height:1px;background:var(--gold)}
.section-title{font-family:"Cormorant Garamond",serif;font-size:clamp(32px,4vw,52px);font-weight:700;color:var(--white);line-height:1.1;margin-bottom:16px}
.section-title em{color:var(--gold);font-style:italic}
.section-sub{font-size:16px;color:var(--muted);max-width:560px;line-height:1.7;margin-bottom:56px}
/* PAIN / SOLUTION */
.pain-grid{display:grid;grid-template-columns:1fr 1fr;gap:0;border:1px solid var(--border);border-radius:14px;overflow:hidden}
.pain-col{padding:40px}
.pain-col.before{background:rgba(255,59,48,.04);border-right:1px solid var(--border)}
.pain-col.after{background:rgba(200,164,60,.04)}
.pain-col-label{font-size:10px;font-weight:700;letter-spacing:3px;text-transform:uppercase;margin-bottom:24px;display:flex;align-items:center;gap:8px}
.before .pain-col-label{color:#ff6b6b}
.after .pain-col-label{color:var(--gold)}
.pain-item{display:flex;align-items:flex-start;gap:12px;margin-bottom:18px;font-size:14px;line-height:1.6;color:var(--muted)}
.pain-icon{font-size:16px;flex-shrink:0;margin-top:1px}
/* FEATURES */
.features-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px}
.feat-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:28px;transition:all .4s}
.feat-card:hover{border-color:var(--gb);background:rgba(200,164,60,.05);transform:translateY(-4px)}
.feat-icon{font-size:32px;margin-bottom:16px}
.feat-title{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--white);margin-bottom:8px}
.feat-desc{font-size:13px;color:var(--muted);line-height:1.6}
/* TESTIMONIALS */
.testi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px}
.testi-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:28px;transition:border-color .3s}
.testi-card:hover{border-color:var(--gb)}
.testi-stars{color:var(--gold);font-size:14px;margin-bottom:14px;letter-spacing:2px}
.testi-text{font-size:14px;color:rgba(255,255,255,.7);line-height:1.7;margin-bottom:20px;font-style:italic}
.testi-author{display:flex;align-items:center;gap:12px}
.testi-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--gb),rgba(14,90,200,.3));border:1px solid var(--gb);display:flex;align-items:center;justify-content:center;font-size:16px}
.testi-name{font-size:13px;font-weight:600;color:var(--white)}
.testi-role{font-size:11px;color:var(--muted)}
/* STATS */
.stats-strip{background:rgba(200,164,60,.05);border:1px solid var(--border);border-radius:14px;padding:48px;display:grid;grid-template-columns:repeat(4,1fr);gap:24px;text-align:center}
.stat-num{font-family:"Cormorant Garamond",serif;font-size:42px;font-weight:700;color:var(--gold)}
.stat-label{font-size:12px;color:var(--muted);letter-spacing:1px;margin-top:4px}
/* CTA */
.cta-block{background:linear-gradient(135deg,rgba(200,164,60,.12),rgba(14,90,200,.1));border:1px solid var(--border);border-radius:16px;padding:72px;text-align:center}
.cta-block h2{font-family:"Cormorant Garamond",serif;font-size:clamp(32px,4vw,52px);font-weight:700;color:var(--white);margin-bottom:16px}
.cta-block h2 em{color:var(--gold);font-style:italic}
.cta-block p{font-size:16px;color:var(--muted);max-width:480px;margin:0 auto 36px;line-height:1.7}
.cta-block .btn-primary{display:inline-block}
/* FAQ */
.faq-list{max-width:720px}
.faq-item{border-bottom:1px solid var(--border);padding:20px 0}
.faq-q{font-size:15px;font-weight:600;color:var(--white);cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:16px}
.faq-q::after{content:"+";font-size:20px;color:var(--gold);flex-shrink:0;transition:transform .3s}
.faq-item.open .faq-q::after{transform:rotate(45deg)}
.faq-a{font-size:13px;color:var(--muted);line-height:1.7;max-height:0;overflow:hidden;transition:max-height .4s ease,padding .3s}
.faq-item.open .faq-a{max-height:200px;padding-top:12px}
footer{padding:32px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}
@media(max-width:900px){
  header,section,.hero,footer{padding-left:24px;padding-right:24px}
  .hero{padding-top:80px;padding-bottom:60px}
  .pain-grid{grid-template-columns:1fr}
  .pain-col.before{border-right:none;border-bottom:1px solid var(--border)}
  .stats-strip{grid-template-columns:1fr 1fr;gap:32px}
  .cta-block{padding:40px 24px}
  body{cursor:auto}
  #cur-dot,#cur-ring,#cur-trail{display:none}
}
/* PERKS */
.perks-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.perk{display:flex;gap:16px;padding:22px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;transition:all .3s;align-items:flex-start}
.perk:hover{border-color:var(--gb);background:rgba(200,164,60,.04)}
.perk-icon{font-size:28px;flex-shrink:0}
.perk-title{font-size:15px;font-weight:700;color:var(--white);margin-bottom:5px}
.perk-desc{font-size:13px;color:var(--muted);line-height:1.65}
/* GROWTH */
.growth-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.growth-card{padding:24px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;text-align:center;transition:all .3s;position:relative}
.growth-card:hover{border-color:var(--gb);transform:translateY(-4px)}
.growth-arrow{position:absolute;right:-8px;top:50%;transform:translateY(-50%);color:var(--gold);font-size:18px;z-index:2}
.growth-card:last-child .growth-arrow{display:none}
.growth-icon{font-size:32px;margin-bottom:12px;display:block}
.growth-level{font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.growth-title{font-size:14px;font-weight:700;color:var(--white);margin-bottom:5px}
.growth-desc{font-size:12px;color:var(--muted);line-height:1.5}
/* VALUES */
.values-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.val-card{padding:28px 20px;text-align:center;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;transition:all .3s}
.val-card:hover{border-color:var(--gb);background:rgba(200,164,60,.05);transform:translateY(-4px)}
.val-icon{font-size:36px;margin-bottom:12px;display:block}
.val-title{font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;color:var(--gold);margin-bottom:8px}
.val-desc{font-size:13px;color:var(--muted);line-height:1.6}
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
    <div class="dropdown"><button class="dd-btn">Home &#9660;</button><div class="dd-menu"><a href="index.html">Welcome</a><a href="works.php">How It Works</a></div></div>
    <div class="dropdown"><button class="dd-btn">Features &#9660;</button><div class="dd-menu"><a href="virtual.php">Virtual Property Tours</a><a href="visitor.php">Visitor/Guest Management</a><a href="applications.php">Online Tenant Applications</a><a href="reporting.php">Rent/Buy Reporting</a><a href="lease.php">Online Lease</a><a href="maintenance.php">Maintenance</a><a href="rent_collection.php">Rent Collection</a><a href="notifications.php">Smart Notification Center</a><a href="complaints.php">Complaints &amp; Feedback HUB</a><a href="owner_portal.php">Owner Portal &amp; Reporting</a><a href="policies.html">Policies</a></div></div>
    <div class="dropdown"><button class="dd-btn">Use Cases &#9660;</button><div class="dd-menu"><a href="tenant.php">Tenants</a><a href="staff.php">Staff</a><a href="propertyowners.php">Property Owners</a><a href="broker.php">Broker</a><a href="employment.php">Employment</a></div></div>
    <div class="dropdown"><button class="dd-btn">Properties &#9660;</button><div class="dd-menu"><a href="properties.php">All Properties</a><div class="dd-divider"></div><a href="properties.php?type=Commercial">Commercial</a><a href="properties.php?type=Residential">Residential</a><a href="properties.php?type=Industrial">Industrial</a><a href="properties.php?type=Agricultural">Agricultural</a><a href="properties.php?type=Special+Purpose">Special Purpose</a><a href="properties.php?type=Land">Land</a></div></div>
    <a href="index.php">Login</a>
    <div class="dropdown"><button class="dd-btn">About Us &#9660;</button><div class="dd-menu"><a href="who.php">Who We Are</a><a href="what.php">What We Do</a><a href="vision.php">Our Vision</a><a href="values.php">Core Values</a><a href="contact.php">Contact Us</a></div></div>
  </nav>
</header>
 
<!-- HERO -->
<section class="hero z">
  <div class="hero-content">
    <div class="hero-eyebrow">For Staff Members</div>
    <h1>Work Smarter,<br>Manage <em>Better.</em></h1>
    <p class="hero-sub">HousingHub gives property staff a powerful portal to manage tenants, maintenance, inspections, visitor approvals and more &mdash; all from one organised dashboard, accessible on any device.</p>
    <div class="hero-btns">
      <a href="employment.php" class="btn-primary">Join Our Team</a>
      <a href="index.php" class="btn-secondary">Staff Login</a>
    </div>
    <div class="hero-stats">
      <div><div class="hstat-num">3x</div><div class="hstat-label">Faster Maintenance</div></div>
      <div><div class="hstat-num">98%</div><div class="hstat-label">Task Completion Rate</div></div>
      <div><div class="hstat-num">24/7</div><div class="hstat-label">Portal Access</div></div>
    </div>
  </div>
</section>
 
<!-- PAIN vs SOLUTION -->
<section class="z reveal">
  <div class="section-eyebrow">The Problem & The Fix</div>
  <h2 class="section-title">Staff Work<br><em>Old Way vs New Way</em></h2>
  <p class="section-sub">See how HousingHub staff went from scattered, manual work to a streamlined digital workflow.</p>
  <div class="pain-grid">
    <div class="pain-col before">
      <div class="pain-col-label">&#128683; Without HousingHub</div>
      <div class="pain-item"><span class="pain-icon">&#128683;</span>Tasks assigned verbally or via WhatsApp — easy to forget or lose</div>
      <div class="pain-item"><span class="pain-icon">&#128683;</span>No clear system for tracking maintenance request progress</div>
      <div class="pain-item"><span class="pain-icon">&#128683;</span>Visitor approvals handled manually with paper logbooks</div>
      <div class="pain-item"><span class="pain-icon">&#128683;</span>Inspection notes written on paper and often misplaced</div>
      <div class="pain-item"><span class="pain-icon">&#128683;</span>No visibility into upcoming tasks, schedules or deadlines</div>
      <div class="pain-item"><span class="pain-icon">&#128683;</span>Performance tracking done informally — no clear records</div>
    </div>
    <div class="pain-col after">
      <div class="pain-col-label">&#10003; With HousingHub</div>
      <div class="pain-item"><span class="pain-icon">&#10004;</span>Tasks assigned digitally with deadlines and priority levels</div>
      <div class="pain-item"><span class="pain-icon">&#10004;</span>Maintenance requests tracked in real time from open to resolved</div>
      <div class="pain-item"><span class="pain-icon">&#10004;</span>Visitor approvals reviewed and actioned from the portal instantly</div>
      <div class="pain-item"><span class="pain-icon">&#10004;</span>Inspection findings logged digitally and accessible anytime</div>
      <div class="pain-item"><span class="pain-icon">&#10004;</span>Full schedule and calendar view of upcoming work and appointments</div>
      <div class="pain-item"><span class="pain-icon">&#10004;</span>Performance tracked automatically — completed tasks and response times</div>
    </div>
  </div>
</section>
 
<!-- OUR VALUES -->
<section class="z reveal">
  <div class="section-eyebrow">What We Stand For</div>
  <h2 class="section-title">The Values That Drive <em>Our Team</em></h2>
  <p class="section-sub">These aren't slogans on a wall. These are the principles our staff live by every day at HousingHub.</p>
  <div class="values-grid">
    <div class="val-card"><span class="val-icon">🎯</span><div class="val-title">Accountability</div><div class="val-desc">We own our work. When something goes wrong we fix it, learn from it, and do better next time.</div></div>
    <div class="val-card"><span class="val-icon">🤝</span><div class="val-title">Respect</div><div class="val-desc">Every tenant, every colleague, every property owner deserves to be treated with dignity and care.</div></div>
    <div class="val-card"><span class="val-icon">💡</span><div class="val-title">Innovation</div><div class="val-desc">We are always looking for smarter, faster, better ways to do our work. Your ideas are welcome here.</div></div>
    <div class="val-card"><span class="val-icon">🏆</span><div class="val-title">Excellence</div><div class="val-desc">We do not settle for average. HousingHub staff take pride in doing every task to the highest standard.</div></div>
  </div>
</section>

<!-- CAREER GROWTH -->
<section class="z reveal">
  <div class="section-eyebrow">Career Progression</div>
  <h2 class="section-title">Your Path to <em>Leadership</em></h2>
  <p class="section-sub">HousingHub is a place where ambition is rewarded. Every great manager here started exactly where you would.</p>
  <div class="growth-grid">
    <div class="growth-card">
      <span class="growth-icon">🌱</span>
      <div class="growth-level">Level 1</div>
      <div class="growth-title">Junior Staff</div>
      <div class="growth-desc">Start here. Learn the systems, the properties, and the tenants. Build your foundation.</div>
      <span class="growth-arrow">→</span>
    </div>
    <div class="growth-card">
      <span class="growth-icon">⭐</span>
      <div class="growth-level">Level 2</div>
      <div class="growth-title">Senior Staff</div>
      <div class="growth-desc">Handle complex tasks independently. Mentor junior team members. Take on more responsibility.</div>
      <span class="growth-arrow">→</span>
    </div>
    <div class="growth-card">
      <span class="growth-icon">👑</span>
      <div class="growth-level">Level 3</div>
      <div class="growth-title">Team Lead</div>
      <div class="growth-desc">Lead a team. Coordinate inspections, maintenance schedules, and weekly reporting for your zone.</div>
      <span class="growth-arrow">→</span>
    </div>
    <div class="growth-card">
      <span class="growth-icon">🏢</span>
      <div class="growth-level">Level 4</div>
      <div class="growth-title">Property Manager</div>
      <div class="growth-desc">Oversee full property operations. Work directly with owners and management to drive results.</div>
    </div>
  </div>
</section>
<!-- PERKS & BENEFITS -->
<section class="z reveal">
  <div class="section-eyebrow">Perks & Benefits</div>
  <h2 class="section-title">We Take Care of <em>Our People</em></h2>
  <p class="section-sub">Great work deserves great rewards. Here's what HousingHub staff enjoy beyond their monthly salary.</p>
  <div class="perks-grid">
    <div class="perk"><span class="perk-icon">💳</span><div><div class="perk-title">Competitive Monthly Salary</div><div class="perk-desc">Salaries paid on time every last day of the month. Performance bonuses for outstanding task completion and tenant satisfaction scores.</div></div></div>
    <div class="perk"><span class="perk-icon">📱</span><div><div class="perk-title">Full Digital Toolkit</div><div class="perk-desc">Your own staff portal account with access to all tools you need — no personal subscriptions, no extra apps to buy.</div></div></div>
    <div class="perk"><span class="perk-icon">🎓</span><div><div class="perk-title">Training & Development</div><div class="perk-desc">Regular training sessions on property management, tenant relations, and digital tools to help you grow in your role.</div></div></div>
    <div class="perk"><span class="perk-icon">⏰</span><div><div class="perk-title">Structured Working Hours</div><div class="perk-desc">Clear, defined working hours with no unexpected overtime demands. Your schedule is organised and predictable.</div></div></div>
    <div class="perk"><span class="perk-icon">🏆</span><div><div class="perk-title">Staff Recognition Program</div><div class="perk-desc">Top performers are recognised monthly. Staff of the Month awards, public recognition, and tangible rewards for excellence.</div></div></div>
    <div class="perk"><span class="perk-icon">📈</span><div><div class="perk-title">Clear Promotion Path</div><div class="perk-desc">Every staff member has a defined career ladder. Junior staff can progress to Senior, then Team Lead, then Management based purely on performance.</div></div></div>
  </div>
</section>

 
<!-- STATS -->
<section class="z reveal">
  <div class="stats-strip">
    <div><div class="stat-num">98%</div><div class="stat-label">Task Completion Rate</div></div>
    <div><div class="stat-num">3x</div><div class="stat-label">Faster Maintenance Response</div></div>
    <div><div class="stat-num">100%</div><div class="stat-label">Paperless Workflow</div></div>
    <div><div class="stat-num">24/7</div><div class="stat-label">Portal Availability</div></div>
  </div>
</section>
 
<!-- TESTIMONIALS -->
<section class="z reveal">
  <div class="section-eyebrow">Staff Stories</div>
  <h2 class="section-title">Hear From<br><em>Our Staff</em></h2>
  <p class="section-sub">HousingHub staff share how the portal transformed their daily work experience.</p>
  <div class="testi-grid">
    <div class="testi-card"><div class="testi-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div><p class="testi-text">"Before HousingHub I used to get task assignments on WhatsApp and forget half of them. Now everything is in my dashboard with due dates. My manager is always happy."</p><div class="testi-author"><div class="testi-avatar">&#128104;</div><div><div class="testi-name">Kato Emmanuel</div><div class="testi-role">Maintenance Officer &mdash; Kampala</div></div></div></div>
    <div class="testi-card"><div class="testi-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div><p class="testi-text">"The inspection module saves me so much time. I log findings directly on my phone during the inspection instead of writing on paper and retyping later."</p><div class="testi-author"><div class="testi-avatar">&#128105;</div><div><div class="testi-name">Nakato Patience</div><div class="testi-role">Property Inspector &mdash; Wakiso</div></div></div></div>
    <div class="testi-card"><div class="testi-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div><p class="testi-text">"I can see all tenant visitor requests from my dashboard and approve them instantly. No more phone calls back and forth. It is so much more professional."</p><div class="testi-author"><div class="testi-avatar">&#128104;</div><div><div class="testi-name">Tumwine Robert</div><div class="testi-role">Guest Manager &mdash; Entebbe</div></div></div></div>
  </div>
</section>
 
<!-- HOW TO JOIN -->
<section class="z reveal">
  <div class="section-eyebrow">How to Join</div>
  <h2 class="section-title">Become Part of the<br><em>HousingHub Team</em></h2>
  <p class="section-sub">Getting hired and onboarded as a HousingHub staff member is fast and fully digital.</p>
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;position:relative">
    <div style="position:absolute;top:28px;left:10%;right:10%;height:1px;background:linear-gradient(90deg,transparent,var(--gb),transparent)"></div>
    <?php $steps=[['1','Apply Online','Browse open positions on our Employment page and submit your application in minutes.'],['2','Get Reviewed','Our HR team reviews all applications within 3–5 business days and notifies you by email.'],['3','Receive Login','Once approved, you automatically receive your staff login email and temporary password.'],['4','Start Working','Log in to your staff dashboard and begin managing tasks, maintenance, and inspections right away.']];foreach($steps as $s):?>
    <div style="text-align:center;position:relative;z-index:1">
      <div style="width:56px;height:56px;border-radius:50%;background:rgba(200,164,60,.1);border:1px solid var(--gb);color:var(--gold);font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 14px"><?=$s[0]?></div>
      <div style="font-size:14px;font-weight:600;color:var(--white);margin-bottom:6px"><?=$s[1]?></div>
      <div style="font-size:12px;color:var(--muted);line-height:1.6"><?=$s[2]?></div>
    </div>
    <?php endforeach;?>
  </div>
</section>
 
<!-- FAQ -->
<section class="z reveal">
  <div class="section-eyebrow">Questions</div>
  <h2 class="section-title">Staff <em>FAQs</em></h2>
  <div class="faq-list">
    <div class="faq-item"><div class="faq-q">How do I apply to become HousingHub staff?</div><div class="faq-a">Visit our Employment page, browse open positions, and submit your application online. You will receive a confirmation email immediately and a decision within 3–5 business days.</div></div>
    <div class="faq-item"><div class="faq-q">What happens after I am hired?</div><div class="faq-a">You will receive an email with your staff login credentials. Log in to the portal, change your temporary password, and your manager will assign your first tasks.</div></div>
    <div class="faq-item"><div class="faq-q">Can I access the portal from my phone?</div><div class="faq-a">Yes. The HousingHub staff portal is fully responsive and works on any device — desktop, tablet, or smartphone.</div></div>
    <div class="faq-item"><div class="faq-q">When are staff salaries paid?</div><div class="faq-a">Staff salaries are processed on the last day of each month. Ensure all task updates and reports are submitted before the payroll cut-off date.</div></div>
    <div class="faq-item"><div class="faq-q">What if I have an issue with my account or tasks?</div><div class="faq-a">Contact your line manager directly through the portal notifications, or email HR at careers@housinghuborg.ug for account-related issues.</div></div>
  </div>
</section>
 
<!-- CTA -->
<section class="z reveal" style="padding-top:40px">
  <div class="cta-block">
    <h2>Ready to <em>Join</em> the Team?</h2>
    <p>Browse our open positions and apply today. We are always looking for dedicated, hardworking people to grow with us.</p>
    <a href="employment.php" class="btn-primary">View Open Positions</a>
  </div>
</section>
 
<!-- QUICK LINKS -->
<section class="z" style="padding:60px 60px 40px">
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:40px;border-top:1px solid var(--border);padding-top:48px">
    <div><h4 style="font-family:'Cormorant Garamond',serif;color:var(--gold);font-size:18px;margin-bottom:16px">Use Cases</h4><a href="tenant.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:8px" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Tenants</a><a href="staff.php" style="display:block;font-size:12px;color:var(--gold);text-decoration:none;margin-bottom:8px">Staff</a><a href="propertyowners.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:8px" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Property Owners</a><a href="broker.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:8px" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Broker</a><a href="employment.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Employment</a></div>
    <div><h4 style="font-family:'Cormorant Garamond',serif;color:var(--gold);font-size:18px;margin-bottom:16px">Features</h4><a href="maintenance.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:8px" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Maintenance</a><a href="virtual.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:8px" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Virtual Tours</a><a href="notifications.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:8px" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Notifications</a><a href="visitor.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Visitor Management</a></div>
    <div><h4 style="font-family:'Cormorant Garamond',serif;color:var(--gold);font-size:18px;margin-bottom:16px">Company</h4><a href="who.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:8px" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Who We Are</a><a href="contact.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:8px" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Contact</a><a href="policies.html" style="display:block;font-size:12px;color:var(--muted);text-decoration:none" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Policies</a></div>
  </div>
</section>
 
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
document.querySelectorAll('a,button,.feat-card,.testi-card').forEach(el=>{el.addEventListener('mouseenter',()=>document.body.classList.add('cursor-hover'));el.addEventListener('mouseleave',()=>document.body.classList.remove('cursor-hover'));});
document.addEventListener('mousedown',()=>document.body.classList.add('cursor-click'));
document.addEventListener('mouseup',()=>document.body.classList.remove('cursor-click'));
for(let i=0;i<18;i++){const p=document.createElement('div');p.classList.add('ptcl');const sz=Math.random()*3+1;p.style.cssText=`width:${sz}px;height:${sz}px;left:${Math.random()*100}%;background:rgba(200,164,60,${(Math.random()*.5+.15).toFixed(2)});animation-duration:${Math.random()*22+10}s;animation-delay:${Math.random()*18}s;`;document.body.appendChild(p);}
const ro=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');ro.unobserve(e.target);}});},{threshold:.08});
document.querySelectorAll('.reveal').forEach(el=>ro.observe(el));
document.querySelectorAll('.faq-q').forEach(q=>{q.addEventListener('click',()=>{const item=q.parentElement;const wasOpen=item.classList.contains('open');document.querySelectorAll('.faq-item.open').forEach(i=>i.classList.remove('open'));if(!wasOpen)item.classList.add('open');});});
</script>
</body>
</html>