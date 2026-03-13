<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rent Collection | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
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
/* ── FIXED HEADER — cannot scroll with content ─────────────── */
body { padding-top: 106px !important; }
header {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  width: 100% !important;
  z-index: 99999 !important;
  box-shadow: 0 2px 28px rgba(0,0,0,.28) !important;
}
nav { position: relative !important; z-index: 100000 !important; }
.dropdown { z-index: 100001 !important; }
.dd-menu { z-index: 100002 !important; }
@media(max-width:900px){ body { padding-top: 80px !important; } }
.reveal{opacity:0;transform:translateY(24px);transition:opacity .7s ease,transform .7s ease}
.reveal.visible{opacity:1;transform:translateY(0)}
header{position:sticky;top:0;z-index:9000;display:flex;justify-content:space-between;align-items:center;padding:18px 60px;background:var(--gold);border-bottom:1px solid var(--border);animation:fadeDown .8s ease both;overflow:visible}
@keyframes fadeDown{from{opacity:0;transform:translateY(-16px)}to{opacity:1;transform:translateY(0)}}
.header-logo{display:flex;align-items:center;gap:14px}
.logo-circle{width:65px;height:65px;border-radius:50%;object-fit:cover;border:2px solid var(--gb)}
.logo-text{font-family:"Cormorant Garamond",serif;font-size:32px;font-weight:900;letter-spacing:2px;text-transform:uppercase;color:var(--white);line-height:1}
.logo-slogan{font-size:14px;color:darkblue;font-style:italic;display:block;margin-top:3px}
nav{display:flex;align-items:center;gap:4px;overflow:visible;position:relative;z-index:9001}
nav>a{font-size:12px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:var(--white);text-decoration:none;padding:8px 14px;transition:color .3s}
nav>a:hover{opacity:.8}
.dropdown{position:relative;overflow:visible;z-index:9002}
.dd-btn{display:block;font-family:"Outfit",sans-serif;font-size:12px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:darkblue;background:none;border:none;padding:8px 14px;white-space:nowrap;cursor:pointer;transition:color .3s}
.dd-btn:hover,.dd-btn.open{color:var(--white)}
.dd-menu{display:none;position:absolute;top:calc(100% + 8px);left:0;min-width:230px;z-index:99999;background:rgba(4,9,26,.99);border:1px solid var(--gb);border-radius:5px;padding:6px 0;box-shadow:0 24px 60px rgba(0,0,0,.85)}
.dd-menu.open{display:block}
.dd-menu a{display:block;font-size:12px;font-weight:400;letter-spacing:1px;color:var(--muted);text-decoration:none;padding:11px 22px;transition:color .2s,background .2s;white-space:nowrap}
.dd-menu a:hover{color:var(--gold);background:rgba(200,164,60,.08)}
.dd-divider{height:1px;background:var(--border);margin:5px 0}
.hero{min-height:88vh;display:flex;align-items:center;padding:100px 60px 80px;position:relative;z-index:10}
.hero-content{max-width:680px}
.hero-eyebrow{font-size:11px;font-weight:500;letter-spacing:4px;text-transform:uppercase;color:var(--gold);display:flex;align-items:center;gap:12px;margin-bottom:24px}
.hero-eyebrow::before{content:"";width:36px;height:1px;background:var(--gold)}
.hero h1{font-family:"Cormorant Garamond",serif;font-size:clamp(46px,7vw,84px);font-weight:700;line-height:1.0;margin-bottom:24px;color:var(--white)}
.hero h1 em{color:var(--gold);font-style:italic}
.hero h1 .stroke{-webkit-text-stroke:1px var(--gold);color:transparent}
.hero-sub{font-size:17px;line-height:1.7;color:var(--muted);max-width:520px;margin-bottom:40px}
.hero-btns{display:flex;gap:16px;flex-wrap:wrap}
.btn-primary{padding:15px 34px;background:var(--gold);color:var(--ink);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:2px;transition:all .3s;display:inline-block}
.btn-primary:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,164,60,.35)}
.btn-secondary{padding:15px 34px;border:1px solid rgba(200,164,60,.4);color:var(--gold);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:2px;transition:all .3s;display:inline-block}
.btn-secondary:hover{background:rgba(200,164,60,.08);transform:translateY(-2px)}
.hero-stats{display:flex;gap:48px;margin-top:56px;padding-top:40px;border-top:1px solid var(--border)}
.hstat-num{font-family:"Cormorant Garamond",serif;font-size:36px;font-weight:700;color:var(--gold)}
.hstat-label{font-size:11px;color:var(--muted);letter-spacing:1px;margin-top:2px}
section{padding:100px 60px;position:relative;z-index:10}
.section-eyebrow{font-size:11px;font-weight:500;letter-spacing:4px;text-transform:uppercase;color:var(--gold);display:flex;align-items:center;gap:12px;margin-bottom:20px}
.section-eyebrow::before{content:"";width:28px;height:1px;background:var(--gold)}
.section-title{font-family:"Cormorant Garamond",serif;font-size:clamp(32px,4vw,52px);font-weight:700;color:var(--white);line-height:1.1;margin-bottom:16px}
.section-title em{color:var(--gold);font-style:italic}
.section-sub{font-size:16px;color:var(--muted);max-width:560px;line-height:1.7;margin-bottom:56px}
.pain-grid{display:grid;grid-template-columns:1fr 1fr;gap:0;border:1px solid var(--border);border-radius:14px;overflow:hidden}
.pain-col{padding:40px}
.pain-col.before{background:rgba(255,59,48,.04);border-right:1px solid var(--border)}
.pain-col.after{background:rgba(200,164,60,.04)}
.pain-col-label{font-size:10px;font-weight:700;letter-spacing:3px;text-transform:uppercase;margin-bottom:24px;display:flex;align-items:center;gap:8px}
.before .pain-col-label{color:#ff6b6b}
.after .pain-col-label{color:var(--gold)}
.pain-item{display:flex;align-items:flex-start;gap:12px;margin-bottom:18px;font-size:14px;line-height:1.6;color:var(--muted)}
.pain-icon{font-size:16px;flex-shrink:0;margin-top:1px}
.steps-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:24px}
.step-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:28px;text-align:center;transition:all .4s}
.step-card:hover{border-color:var(--gb);transform:translateY(-4px)}
.step-num{font-family:"Cormorant Garamond",serif;font-size:52px;font-weight:700;color:rgba(200,164,60,.15);line-height:1;margin-bottom:12px}
.step-title{font-size:15px;font-weight:600;color:var(--white);margin-bottom:8px}
.step-desc{font-size:13px;color:var(--muted);line-height:1.6}
.features-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px}
.feat-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:28px;transition:all .4s}
.feat-card:hover{border-color:var(--gb);background:rgba(200,164,60,.05);transform:translateY(-4px)}
.feat-icon{font-size:32px;margin-bottom:16px}
.feat-title{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--white);margin-bottom:8px}
.feat-desc{font-size:13px;color:var(--muted);line-height:1.6}
.stats-strip{background:rgba(200,164,60,.05);border:1px solid var(--border);border-radius:14px;padding:48px;display:grid;grid-template-columns:repeat(4,1fr);gap:24px;text-align:center}
.stat-num{font-family:"Cormorant Garamond",serif;font-size:42px;font-weight:700;color:var(--gold)}
.stat-label{font-size:12px;color:var(--muted);letter-spacing:1px;margin-top:4px}
.cta-block{background:linear-gradient(135deg,rgba(200,164,60,.12),rgba(14,90,200,.1));border:1px solid var(--border);border-radius:16px;padding:72px;text-align:center}
.cta-block h2{font-family:"Cormorant Garamond",serif;font-size:clamp(32px,4vw,52px);font-weight:700;color:var(--white);margin-bottom:16px}
.cta-block h2 em{color:var(--gold);font-style:italic}
.cta-block p{font-size:16px;color:var(--muted);max-width:480px;margin:0 auto 36px;line-height:1.7}
footer{padding:32px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}
@media(max-width:900px){header,section,.hero,footer{padding-left:24px;padding-right:24px}.pain-grid{grid-template-columns:1fr}.pain-col.before{border-right:none;border-bottom:1px solid var(--border)}.stats-strip{grid-template-columns:1fr 1fr}.cta-block{padding:40px 24px}body{cursor:auto}#cur-dot,#cur-ring,#cur-trail{display:none}}</style>
</head>
<body>
<div id="cur-dot"></div><div id="cur-ring"></div><div id="cur-trail"></div>
<div class="page-bg"></div><div class="page-grid"></div>
<header class="z">  <div class="header-logo">
    <img src="image/hub.jpg" alt="Logo" class="logo-circle">
    <div><h1 class="logo-text">HOUSING HUB</h1><span class="logo-slogan">"Your Property, Our Priority"</span></div>
  </div>
  <nav>
    <div class="dropdown"><button class="dd-btn">Home &#9660;</button><div class="dd-menu">
        <a href="index.html#welcome">Welcome</a><a href="index.html#how-it-works">How It Works</a>
        <a href="index.html#testimonials">Testimonials</a><a href="index.html#our-stats">Our Stats</a>
        <a href="index.html#faqs">FAQs</a><a href="index.html#contact-us">Contact Us</a>
    </div></div>
    <div class="dropdown"><button class="dd-btn">Features &#9660;</button><div class="dd-menu">
        <a href="virtual.php">Virtual Property Tours</a><a href="visitor.php">Visitor/Guest Management</a>
        <a href="applications.php">Online Tenant Applications</a><a href="reporting.php">Rent/Buy Reporting</a>
        <a href="lease.php">Online Lease</a><a href="maintenance.php">Maintenance</a>
        <a href="rent_collection.php">Rent Collection</a><a href="notifications.php">Smart Notification Center</a>
        <a href="complaints.php">Complaints &amp; Feedback HUB</a><a href="owner_portal.php">Owner Portal &amp; Reporting</a>
        <a href="policies.html">Policies</a>
    </div></div>
    <div class="dropdown"><button class="dd-btn">Use Cases &#9660;</button><div class="dd-menu">
        <a href="tenants.php">Tenants</a><a href="propertyowners.php">Property Owners</a>
        <a href="broker.php">Broker</a><a href="employment.php">Employment</a>
    </div></div>
    <div class="dropdown"><button class="dd-btn">Properties &#9660;</button><div class="dd-menu">
        <a href="properties.php">All Properties</a><div class="dd-divider"></div>
        <a href="properties.php?type=Commercial">Commercial</a><a href="properties.php?type=Residential">Residential</a>
        <a href="properties.php?type=Industrial">Industrial</a><a href="properties.php?type=Agricultural">Agricultural</a>
        <a href="properties.php?type=Special+Purpose">Special Purpose</a><a href="properties.php?type=Land">Land</a>
    </div></div>
    <a href="index.php">Login</a>
    <div class="dropdown"><button class="dd-btn">About Us &#9660;</button><div class="dd-menu">
        <a href="who.php">Who We Are</a><a href="what.php">What We Do</a>
        <a href="vision.php">Our Vision</a><a href="values.php">Core Values</a><a href="contact.php">Contact Us</a>
    </div></div>
  </nav></header>
<section class="hero z">
  <div class="hero-content">
    <div class="hero-eyebrow">Platform Feature</div>
    <h1>Collect Rent<br><em>Automatically,</em><br><span class="stroke">Every Month.</span></h1>
    <p class="hero-sub">HousingHub automates rent collection for landlords across Uganda — tenants pay via MTN MoMo, Airtel Money, card, or bank transfer, and landlords get paid on time with full digital records.</p>
    <div class="hero-btns">
      <a href="index.php" class="btn-primary">Get Started</a>
      <a href="properties.php" class="btn-secondary">Browse Properties</a>
    </div>
    <div class="hero-stats">
      <div><div class="hstat-num">95%</div><div class="hstat-label">On-Time Payments</div></div>
      <div><div class="hstat-num">Zero</div><div class="hstat-label">Manual Collection</div></div>
      <div><div class="hstat-num">Instant</div><div class="hstat-label">Digital Receipts</div></div>
    </div>
  </div>
</section>
<section class="z reveal">
  <div class="section-eyebrow">The Problem &amp; The Fix</div>
  <h2 class="section-title">Before &amp; <em>After HousingHub</em></h2>
  <p class="section-sub">See how HousingHub transforms the experience for everyone involved.</p>
  <div class="pain-grid">
    <div class="pain-col before"><div class="pain-col-label">&#128683; Before HousingHub</div><div class="pain-item"><span class="pain-icon">&#128683;</span>Physically collecting cash from tenants door-to-door every month</div><div class="pain-item"><span class="pain-icon">&#128683;</span>Chasing tenants via phone calls and WhatsApp for overdue rent</div><div class="pain-item"><span class="pain-icon">&#128683;</span>No standard receipt — tenants lose proof of payment easily</div><div class="pain-item"><span class="pain-icon">&#128683;</span>Landlords with multiple properties struggling to track who has paid</div><div class="pain-item"><span class="pain-icon">&#128683;</span>No automated way to send payment reminders before due dates</div></div>
    <div class="pain-col after"><div class="pain-col-label">&#10003; With HousingHub</div><div class="pain-item"><span class="pain-icon">&#10004;</span>Tenants pay online via MTN MoMo, Airtel Money, card, or bank transfer</div><div class="pain-item"><span class="pain-icon">&#10004;</span>Automated payment reminders sent 7, 3, and 1 day before due date</div><div class="pain-item"><span class="pain-icon">&#10004;</span>Instant digital receipts generated and stored after every payment</div><div class="pain-item"><span class="pain-icon">&#10004;</span>Landlord dashboard shows paid, pending, and overdue tenants live</div><div class="pain-item"><span class="pain-icon">&#10004;</span>Late payment flags trigger automatic follow-up notifications</div></div>
  </div>
</section>
<section class="z reveal">
  <div class="section-eyebrow">Getting Started</div>
  <h2 class="section-title">How It <em>Works</em></h2>
  <p class="section-sub">Simple steps to get up and running on HousingHub.</p>
  <div class="steps-grid"><div class="step-card"><div class="step-num">01</div><div class="step-title">Set Up Your Units</div><div class="step-desc">Add your properties, units, and monthly rent amounts to HousingHub.</div></div><div class="step-card"><div class="step-num">02</div><div class="step-title">Tenants Get Notified</div><div class="step-desc">Automated reminders sent to tenants before rent is due each month.</div></div><div class="step-card"><div class="step-num">03</div><div class="step-title">Tenant Pays Online</div><div class="step-desc">Pay via MoMo, Airtel, card, or bank — receipt generated instantly.</div></div><div class="step-card"><div class="step-num">04</div><div class="step-title">You Track Everything</div><div class="step-desc">Your dashboard updates in real time with every payment received.</div></div></div>
</section>
<section class="z reveal">
  <div class="section-eyebrow">What You Get</div>
  <h2 class="section-title">Key <em>Features</em></h2>
  <p class="section-sub">Everything you need, built into one powerful platform.</p>
  <div class="features-grid"><div class="feat-card"><div class="feat-icon">&#128178;</div><h3 class="feat-title">Multi-Method Payments</h3><p class="feat-desc">Accept MTN MoMo, Airtel Money, debit/credit card, and bank transfer.</p></div><div class="feat-card"><div class="feat-icon">&#128276;</div><h3 class="feat-title">Payment Reminders</h3><p class="feat-desc">Automated reminders sent to tenants before and on the due date.</p></div><div class="feat-card"><div class="feat-icon">&#128196;</div><h3 class="feat-title">Instant Receipts</h3><p class="feat-desc">Every payment triggers an automatic numbered digital receipt.</p></div><div class="feat-card"><div class="feat-icon">&#128680;</div><h3 class="feat-title">Overdue Alerts</h3><p class="feat-desc">Tenants and landlords flagged automatically when rent is late.</p></div><div class="feat-card"><div class="feat-icon">&#128200;</div><h3 class="feat-title">Payment Dashboard</h3><p class="feat-desc">Live view of all payment statuses across every unit you manage.</p></div><div class="feat-card"><div class="feat-icon">&#128229;</div><h3 class="feat-title">Export Records</h3><p class="feat-desc">Download full payment history as PDF or CSV for any period.</p></div></div>
</section>
<section class="z reveal"><div class="stats-strip"><div><div class="stat-num">95%</div><div class="stat-label">On-Time Rate</div></div><div><div class="stat-num">Zero</div><div class="stat-label">Cash Handling</div></div><div><div class="stat-num">Instant</div><div class="stat-label">Receipts</div></div><div><div class="stat-num">100%</div><div class="stat-label">Transparency</div></div></div></section>
<section class="z reveal" style="padding-top:40px">
  <div class="cta-block">
    <h2>Get Paid On Time, <em>Every Time.</em></h2>
    <p>Automate rent collection for your entire portfolio. No more chasing tenants — HousingHub handles it all.</p>
    <a href="index.php" class="btn-primary">Get Started Free</a>
  </div>
</section>
<footer class="z">&copy; 2026 HousingHub | All Rights Reserved</footer>
<script>
function closeAllMenus(){document.querySelectorAll('.dd-menu.open').forEach(m=>m.classList.remove('open'));document.querySelectorAll('.dd-btn.open').forEach(b=>b.classList.remove('open'));}
document.querySelectorAll('.dropdown').forEach(dd=>{var btn=dd.querySelector('.dd-btn'),menu=dd.querySelector('.dd-menu');if(!btn||!menu)return;btn.addEventListener('click',e=>{e.stopPropagation();var o=menu.classList.contains('open');closeAllMenus();if(!o){menu.classList.add('open');btn.classList.add('open');}});menu.addEventListener('mousedown',e=>e.stopPropagation());menu.addEventListener('click',e=>e.stopPropagation());});
document.addEventListener('click',closeAllMenus);
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeAllMenus();});
const dot=document.getElementById('cur-dot'),ring=document.getElementById('cur-ring'),trail=document.getElementById('cur-trail');
let mx=-200,my=-200,rx=-200,ry=-200,tx=-200,ty=-200;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;dot.style.left=mx+'px';dot.style.top=my+'px';});
(function anim(){rx+=(mx-rx)*.15;ry+=(my-ry)*.15;tx+=(mx-tx)*.06;ty+=(my-ty)*.06;ring.style.left=rx+'px';ring.style.top=ry+'px';trail.style.left=tx+'px';trail.style.top=ty+'px';requestAnimationFrame(anim);})();
document.querySelectorAll('a,button,.feat-card,.step-card').forEach(el=>{el.addEventListener('mouseenter',()=>document.body.classList.add('cursor-hover'));el.addEventListener('mouseleave',()=>document.body.classList.remove('cursor-hover'));});
document.addEventListener('mousedown',()=>document.body.classList.add('cursor-click'));
document.addEventListener('mouseup',()=>document.body.classList.remove('cursor-click'));
for(let i=0;i<18;i++){const p=document.createElement('div');p.classList.add('ptcl');const sz=Math.random()*3+1;p.style.cssText=`width:${sz}px;height:${sz}px;left:${Math.random()*100}%;background:rgba(200,164,60,${(Math.random()*.5+.15).toFixed(2)});animation-duration:${Math.random()*22+10}s;animation-delay:${Math.random()*18}s;`;document.body.appendChild(p);}
const ro=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');ro.unobserve(e.target);}});},{threshold:.08});
document.querySelectorAll('.reveal').forEach(el=>ro.observe(el));
</script>
</body></html>