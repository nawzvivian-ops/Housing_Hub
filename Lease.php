<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Online Lease Management | HousingHub</title>
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
.page-bg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(14,90,200,.18) 0%,transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12) 0%,transparent 50%),var(--ink);animation:atmo 14s ease-in-out infinite alternate}
@keyframes atmo{0%{filter:brightness(1)}100%{filter:brightness(1.08)}}
.page-grid{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:72px 72px}
.ptcl{position:fixed;border-radius:50%;pointer-events:none;z-index:1;animation:pdrift linear infinite}
@keyframes pdrift{0%{transform:translateY(100vh) scale(0);opacity:0}5%{opacity:1}95%{opacity:.5}100%{transform:translateY(-10vh) translateX(50px) scale(1.4);opacity:0}}
.z{position:relative;z-index:10}
.reveal{opacity:0;transform:translateY(24px);transition:opacity .7s ease,transform .7s ease}
.reveal.visible{opacity:1;transform:translateY(0)}

/* HEADER */
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

/* HERO */
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

/* SECTIONS */
section{padding:100px 60px;position:relative;z-index:10}
.section-eyebrow{font-size:11px;font-weight:500;letter-spacing:4px;text-transform:uppercase;color:var(--gold);display:flex;align-items:center;gap:12px;margin-bottom:20px}
.section-eyebrow::before{content:"";width:28px;height:1px;background:var(--gold)}
.section-title{font-family:"Cormorant Garamond",serif;font-size:clamp(32px,4vw,52px);font-weight:700;color:var(--white);line-height:1.1;margin-bottom:16px}
.section-title em{color:var(--gold);font-style:italic}
.section-sub{font-size:16px;color:var(--muted);max-width:560px;line-height:1.7;margin-bottom:56px}

/* BEFORE / AFTER */
.pain-grid{display:grid;grid-template-columns:1fr 1fr;gap:0;border:1px solid var(--border);border-radius:14px;overflow:hidden}
.pain-col{padding:40px}
.pain-col.before{background:rgba(255,59,48,.04);border-right:1px solid var(--border)}
.pain-col.after{background:rgba(200,164,60,.04)}
.pain-col-label{font-size:10px;font-weight:700;letter-spacing:3px;text-transform:uppercase;margin-bottom:24px;display:flex;align-items:center;gap:8px}
.before .pain-col-label{color:#ff6b6b}
.after .pain-col-label{color:var(--gold)}
.pain-item{display:flex;align-items:flex-start;gap:12px;margin-bottom:18px;font-size:14px;line-height:1.6;color:var(--muted)}
.pain-icon{font-size:16px;flex-shrink:0;margin-top:1px}

/* HOW IT WORKS */
.steps-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:24px}
.step-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:28px;text-align:center;transition:all .4s}
.step-card:hover{border-color:var(--gb);transform:translateY(-4px)}
.step-num{font-family:"Cormorant Garamond",serif;font-size:52px;font-weight:700;color:rgba(200,164,60,.15);line-height:1;margin-bottom:12px}
.step-title{font-size:15px;font-weight:600;color:var(--white);margin-bottom:8px}
.step-desc{font-size:13px;color:var(--muted);line-height:1.6}

/* FEATURES */
.features-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px}
.feat-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:28px;transition:all .4s}
.feat-card:hover{border-color:var(--gb);background:rgba(200,164,60,.05);transform:translateY(-4px)}
.feat-icon{font-size:32px;margin-bottom:16px}
.feat-title{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--white);margin-bottom:8px}
.feat-desc{font-size:13px;color:var(--muted);line-height:1.6}

/* STATS STRIP */
.stats-strip{background:rgba(200,164,60,.05);border:1px solid var(--border);border-radius:14px;padding:48px;display:grid;grid-template-columns:repeat(4,1fr);gap:24px;text-align:center}
.stat-num{font-family:"Cormorant Garamond",serif;font-size:42px;font-weight:700;color:var(--gold)}
.stat-label{font-size:12px;color:var(--muted);letter-spacing:1px;margin-top:4px}

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

/* FAQ */
.faq-list{max-width:760px}
.faq-item{border-bottom:1px solid var(--border);padding:20px 0}
.faq-q{font-size:15px;font-weight:600;color:var(--white);cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:16px;transition:color .3s}
.faq-q:hover{color:var(--gold)}
.faq-q::after{content:"+";font-size:22px;color:var(--gold);flex-shrink:0;transition:transform .3s}
.faq-item.open .faq-q::after{transform:rotate(45deg)}
.faq-a{font-size:13px;color:var(--muted);line-height:1.8;max-height:0;overflow:hidden;transition:max-height .4s ease,padding .3s}
.faq-item.open .faq-a{max-height:240px;padding-top:14px}

/* CTA */
.cta-block{background:linear-gradient(135deg,rgba(200,164,60,.12),rgba(14,90,200,.1));border:1px solid var(--border);border-radius:16px;padding:72px;text-align:center}
.cta-block h2{font-family:"Cormorant Garamond",serif;font-size:clamp(32px,4vw,52px);font-weight:700;color:var(--white);margin-bottom:16px}
.cta-block h2 em{color:var(--gold);font-style:italic}
.cta-block p{font-size:16px;color:var(--muted);max-width:480px;margin:0 auto 36px;line-height:1.7}
.cta-btns{display:flex;gap:16px;justify-content:center;flex-wrap:wrap}

footer{padding:32px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}

@media(max-width:900px){
  header,section,.hero,footer{padding-left:24px;padding-right:24px}
  .pain-grid{grid-template-columns:1fr}
  .pain-col.before{border-right:none;border-bottom:1px solid var(--border)}
  .stats-strip{grid-template-columns:1fr 1fr;gap:32px}
  .cta-block{padding:40px 24px}
  body{cursor:auto}
  #cur-dot,#cur-ring,#cur-trail{display:none}
}
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
</style>
</head>
<body>
<div id="cur-dot"></div><div id="cur-ring"></div><div id="cur-trail"></div>
<div class="page-bg"></div><div class="page-grid"></div>

<!-- HEADER -->
<header class="z">
  <div class="header-logo">
    <img src="image/hub.jpg" alt="Logo" class="logo-circle">
    <div><h1 class="logo-text">HOUSING HUB</h1><span class="logo-slogan">"Your Property, Our Priority"</span></div>
  </div>
  <nav>
    <div class="dropdown"><button class="dd-btn">Home &#9660;</button><div class="dd-menu">
      <a href="index.html">Welcome</a>
      <a href="works.php">How It Works</a>
      <a href="testimonials.php">Testimonials</a>
      <a href="whyus.php">Why Choose Us</a>
      <a href="pricing.php">Pricing</a>
    </div></div>
    <div class="dropdown"><button class="dd-btn">Features &#9660;</button><div class="dd-menu">
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
    </div></div>
    <div class="dropdown"><button class="dd-btn">Use Cases &#9660;</button><div class="dd-menu">
      <a href="tenants.php">Tenants</a>
      <a href="propertyowners.php">Property Owners</a>
      <a href="broker.php">Broker</a>
      <a href="employment.php">Employment</a>
    </div></div>
    <div class="dropdown"><button class="dd-btn">Properties &#9660;</button><div class="dd-menu">
      <a href="properties.php">All Properties</a>
      <div class="dd-divider"></div>
      <a href="properties.php?type=Commercial">Commercial</a>
      <a href="properties.php?type=Residential">Residential</a>
      <a href="properties.php?type=Industrial">Industrial</a>
      <a href="properties.php?type=Agricultural">Agricultural</a>
      <a href="properties.php?type=Special+Purpose">Special Purpose</a>
      <a href="properties.php?type=Land">Land</a>
    </div></div>
    <a href="index.php">Login</a>
    <div class="dropdown"><button class="dd-btn">About Us &#9660;</button><div class="dd-menu">
      <a href="who.php">Who We Are</a>
      <a href="what.php">What We Do</a>
      <a href="vision.php">Our Vision</a>
      <a href="values.php">Core Values</a>
      <a href="contact.php">Contact Us</a>
    </div></div>
  </nav>
</header>

<!-- HERO -->
<section class="hero z">
  <div class="hero-content">
    <div class="hero-eyebrow">Platform Feature</div>
    <h1>Sign &amp; Manage<br><em>Leases</em><br><span class="stroke">Digitally.</span></h1>
    <p class="hero-sub">HousingHub lets landlords create, send, and manage lease agreements fully online and tenants can review, sign, and access their lease anytime. No printing, no courier, no lost documents.</p>
    <div class="hero-btns">
      <a href="index.php" class="btn-primary">Get Started</a>
      <a href="properties.php" class="btn-secondary">Browse Properties</a>
    </div>
    <div class="hero-stats">
      <div><div class="hstat-num">100%</div><div class="hstat-label">Paperless Leases</div></div>
      <div><div class="hstat-num">Instant</div><div class="hstat-label">Digital Signing</div></div>
      <div><div class="hstat-num">Always</div><div class="hstat-label">Accessible</div></div>
    </div>
  </div>
</section>

<!-- BEFORE / AFTER -->
<section class="z reveal">
  <div class="section-eyebrow">The Problem &amp; The Fix</div>
  <h2 class="section-title">Before &amp; <em>After HousingHub</em></h2>
  <p class="section-sub">See how HousingHub eliminates the frustration of paper lease management for landlords and tenants across Uganda.</p>
  <div class="pain-grid">
    <div class="pain-col before">
      <div class="pain-col-label">&#128683; Before HousingHub</div>
      <div class="pain-item"><span class="pain-icon">&#128683;</span>Printing and physically signing lease agreements at the property</div>
      <div class="pain-item"><span class="pain-icon">&#128683;</span>Lease documents stored in files that get lost, damaged, or forgotten</div>
      <div class="pain-item"><span class="pain-icon">&#128683;</span>Tenants uncertain of their lease terms because they lost their copy</div>
      <div class="pain-item"><span class="pain-icon">&#128683;</span>Landlords manually tracking lease start and end dates in notebooks</div>
      <div class="pain-item"><span class="pain-icon">&#128683;</span>No reminders when leases are about to expire or need renewal</div>
      <div class="pain-item"><span class="pain-icon">&#128683;</span>Disputes over lease terms with no verifiable written record</div>
    </div>
    <div class="pain-col after">
      <div class="pain-col-label">&#10003; With HousingHub</div>
      <div class="pain-item"><span class="pain-icon">&#10004;</span>Create professional lease agreements from a template in minutes</div>
      <div class="pain-item"><span class="pain-icon">&#10004;</span>Send to tenant for digital review and signature via HousingHub</div>
      <div class="pain-item"><span class="pain-icon">&#10004;</span>Both parties receive a secure digital copy stored on the platform</div>
      <div class="pain-item"><span class="pain-icon">&#10004;</span>Automated reminders 30, 14, and 7 days before lease expiry</div>
      <div class="pain-item"><span class="pain-icon">&#10004;</span>Tenants access their full lease terms anytime from their dashboard</div>
      <div class="pain-item"><span class="pain-icon">&#10004;</span>Timestamped signatures serve as legal proof in case of any dispute</div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="z reveal">
  <div class="section-eyebrow">Getting Started</div>
  <h2 class="section-title">How It <em>Works</em></h2>
  <p class="section-sub">Four simple steps from creating a lease to having it signed and stored — all without leaving HousingHub.</p>
  <div class="steps-grid">
    <div class="step-card">
      <div class="step-num">01</div>
      <div class="step-title">Create the Lease</div>
      <div class="step-desc">Landlord opens a Uganda-compliant lease template and fills in the unit details, rent amount, duration, and terms.</div>
    </div>
    <div class="step-card">
      <div class="step-num">02</div>
      <div class="step-title">Send to Tenant</div>
      <div class="step-desc">Tenant receives an instant notification on HousingHub to review the lease. No email chains or printed copies needed.</div>
    </div>
    <div class="step-card">
      <div class="step-num">03</div>
      <div class="step-title">Tenant Reviews &amp; Signs</div>
      <div class="step-desc">Tenant reads the full document on their phone or laptop, asks any questions, and signs digitally with one click.</div>
    </div>
    <div class="step-card">
      <div class="step-num">04</div>
      <div class="step-title">Stored for Both Parties</div>
      <div class="step-desc">The signed lease is timestamped and saved permanently. Both landlord and tenant access it anytime from their dashboard.</div>
    </div>
  </div>
</section>

<!-- KEY FEATURES -->
<section class="z reveal">
  <div class="section-eyebrow">What You Get</div>
  <h2 class="section-title">Key <em>Features</em></h2>
  <p class="section-sub">Everything you need to create, sign, and manage lease agreements digitally — built into HousingHub.</p>
  <div class="features-grid">
    <div class="feat-card">
      <div class="feat-icon">&#128196;</div>
      <h3 class="feat-title">Lease Templates</h3>
      <p class="feat-desc">Professional, Uganda-compliant lease templates ready to customise with your specific terms, rent, and duration.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon">&#9997;</div>
      <h3 class="feat-title">Digital Signing</h3>
      <p class="feat-desc">Tenants review and sign lease agreements directly on HousingHub — no printing, scanning, or physical visits required.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon">&#128274;</div>
      <h3 class="feat-title">Secure Storage</h3>
      <p class="feat-desc">All signed leases are encrypted and stored permanently on HousingHub — accessible by both parties at any time.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon">&#128276;</div>
      <h3 class="feat-title">Expiry Reminders</h3>
      <p class="feat-desc">Automated alerts at 30, 14, and 7 days before any lease expires — giving both parties time to renew or arrange vacating.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon">&#128203;</div>
      <h3 class="feat-title">Lease History</h3>
      <p class="feat-desc">Full record of all current, expired, and terminated leases per property and per tenant — always available for review.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon">&#128241;</div>
      <h3 class="feat-title">Mobile Access</h3>
      <p class="feat-desc">Tenants and landlords access, review, and download lease documents from any smartphone, tablet, or desktop anytime.</p>
    </div>
  </div>
</section>

<!-- STATS STRIP -->
<section class="z reveal">
  <div class="stats-strip">
    <div><div class="stat-num">100%</div><div class="stat-label">Paperless Process</div></div>
    <div><div class="stat-num">Instant</div><div class="stat-label">Digital Signing</div></div>
    <div><div class="stat-num">Permanent</div><div class="stat-label">Secure Storage</div></div>
    <div><div class="stat-num">Auto</div><div class="stat-label">Expiry Reminders</div></div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="z reveal">
  <div class="section-eyebrow">What People Say</div>
  <h2 class="section-title">Real <em>Experiences</em></h2>
  <p class="section-sub">Landlords and tenants across Uganda share how digital leases on HousingHub changed their experience.</p>
  <div class="testi-grid">
    <div class="testi-card">
      <div class="testi-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
      <p class="testi-text">"I used to keep lease copies in a folder that got damaged in the rain. Now everything is on HousingHub and I can pull up any lease from my phone in seconds. I will never go back to paper."</p>
      <div class="testi-author">
        <div class="testi-avatar">&#128104;</div>
        <div><div class="testi-name">Ssemakula Robert</div><div class="testi-role">Landlord &mdash; Wakiso, Kampala</div></div>
      </div>
    </div>
    <div class="testi-card">
      <div class="testi-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
      <p class="testi-text">"My landlord sent me the lease on HousingHub. I read it on my phone, asked a question through the platform, and signed the same day. No trips, no printing. Very easy."</p>
      <div class="testi-author">
        <div class="testi-avatar">&#128105;</div>
        <div><div class="testi-name">Namukasa Joyce</div><div class="testi-role">Tenant &mdash; Ntinda, Kampala</div></div>
      </div>
    </div>
    <div class="testi-card">
      <div class="testi-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
      <p class="testi-text">"We had a disagreement with a tenant over what was agreed in the lease. Because it was signed digitally on HousingHub with a clear timestamp, the matter was resolved immediately."</p>
      <div class="testi-author">
        <div class="testi-avatar">&#128104;</div>
        <div><div class="testi-name">Tumusiime David</div><div class="testi-role">Property Owner &mdash; Entebbe</div></div>
      </div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="z reveal">
  <div class="section-eyebrow">Common Questions</div>
  <h2 class="section-title">Lease <em>FAQs</em></h2>
  <p class="section-sub">Everything you need to know about digital lease agreements on HousingHub.</p>
  <div class="faq-list">
    <div class="faq-item">
      <div class="faq-q">Is a digital lease legally binding in Uganda?</div>
      <div class="faq-a">Yes. Digital agreements that are clearly accepted by both parties carry legal weight in Uganda. HousingHub leases include timestamps, IP records, and acknowledgement logs that can serve as evidence in any dispute.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Can I customise the lease template with my own terms?</div>
      <div class="faq-a">Yes. HousingHub provides a base Uganda-compliant template which landlords can fully customise with specific clauses, rent amounts, maintenance responsibilities, and any additional terms agreed between both parties.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">What happens if a tenant refuses to sign digitally?</div>
      <div class="faq-a">The lease remains in "Pending Signature" status on your dashboard. You can send reminders directly through the platform. If unresolved, you can download the document and arrange a physical signature as a fallback.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Can I access old leases after a tenant has moved out?</div>
      <div class="faq-a">Yes. All leases — active, expired, and terminated — are stored permanently on HousingHub. You can access, download, and export any past lease from your owner portal at any time.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Will I be reminded when a lease is about to expire?</div>
      <div class="faq-a">Absolutely. HousingHub automatically sends reminders to both the landlord and tenant at 30 days, 14 days, and 7 days before any lease expires — giving both parties time to arrange renewal or a smooth vacating process.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Can a tenant download a copy of their lease?</div>
      <div class="faq-a">Yes. Tenants can download a PDF copy of their signed lease directly from their dashboard at any time. The document includes both parties' names, signatures, and the signing timestamp.</div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="z reveal" style="padding-top:40px">
  <div class="cta-block">
    <h2>Go Paperless with <em>Digital Leases.</em></h2>
    <p>Create, sign, and store all your lease agreements on HousingHub. No printing, no couriers, no lost documents — ever again.</p>
    <div class="cta-btns">
      <a href="index.php" class="btn-primary">Get Started Free</a>
      <a href="works.php" class="btn-secondary">See How It Works</a>
    </div>
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
document.querySelectorAll('a,button,.feat-card,.step-card,.testi-card,.faq-q').forEach(el=>{
  el.addEventListener('mouseenter',()=>document.body.classList.add('cursor-hover'));
  el.addEventListener('mouseleave',()=>document.body.classList.remove('cursor-hover'));
});
document.addEventListener('mousedown',()=>document.body.classList.add('cursor-click'));
document.addEventListener('mouseup',()=>document.body.classList.remove('cursor-click'));

for(let i=0;i<18;i++){
  const p=document.createElement('div');p.classList.add('ptcl');
  const sz=Math.random()*3+1;
  p.style.cssText=`width:${sz}px;height:${sz}px;left:${Math.random()*100}%;background:rgba(200,164,60,${(Math.random()*.5+.15).toFixed(2)});animation-duration:${Math.random()*22+10}s;animation-delay:${Math.random()*18}s;`;
  document.body.appendChild(p);
}

const ro=new IntersectionObserver(entries=>{
  entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');ro.unobserve(e.target);}});
},{threshold:.08});
document.querySelectorAll('.reveal').forEach(el=>ro.observe(el));

document.querySelectorAll('.faq-q').forEach(q=>{
  q.addEventListener('click',()=>{
    const item=q.parentElement;
    const was=item.classList.contains('open');
    document.querySelectorAll('.faq-item.open').forEach(i=>i.classList.remove('open'));
    if(!was)item.classList.add('open');
  });
});
</script>
</body>
</html>