<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>How It Works | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25)}
body{cursor:none;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden}
#cur-dot{width:8px;height:8px;background:var(--gold);border-radius:50%;position:fixed;z-index:99999;pointer-events:none;transform:translate(-50%,-50%);mix-blend-mode:difference}
#cur-ring{width:20px;height:20px;border:1.5px solid rgba(200,164,60,.7);border-radius:50%;position:fixed;z-index:99998;pointer-events:none;transform:translate(-50%,-50%);transition:width .45s cubic-bezier(.23,1,.32,1),height .45s}
#cur-trail{width:30px;height:30px;border:1px solid rgba(200,164,60,.15);border-radius:50%;position:fixed;z-index:99997;pointer-events:none;transform:translate(-50%,-50%);transition:width .7s,height .7s}
body.cursor-hover #cur-dot{width:8px;height:8px;background:#fff}
body.cursor-hover #cur-ring{width:20px;height:20px;border-color:var(--gold);background:rgba(200,164,60,.06)}
body.cursor-click #cur-dot{width:8px;height:8px}
body.cursor-click #cur-ring{width:20px;height:20px}
.page-bg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(14,90,200,.18) 0%,transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12) 0%,transparent 50%),var(--ink);animation:atmo 14s ease-in-out infinite alternate}
@keyframes atmo{0%{filter:brightness(1)}100%{filter:brightness(1.08) hue-rotate(4deg)}}
.page-grid{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:72px 72px}
.ptcl{position:fixed;border-radius:50%;pointer-events:none;z-index:1;animation:pdrift linear infinite}
@keyframes pdrift{0%{transform:translateY(100vh) scale(0);opacity:0}5%{opacity:1}95%{opacity:.5}100%{transform:translateY(-10vh) translateX(50px) scale(1.4);opacity:0}}
.z{position:relative;z-index:10}
.reveal{opacity:0;transform:translateY(28px);transition:opacity .75s ease,transform .75s ease}
.reveal.visible{opacity:1;transform:translateY(0)}

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

/* ── HEADER ── */
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

/* ── HERO ── */
.page-hero{position:relative;z-index:10;text-align:center;padding:120px 60px 90px;border-bottom:1px solid var(--border)}
.page-eyebrow{font-size:11px;font-weight:500;letter-spacing:4px;text-transform:uppercase;color:var(--gold);display:inline-flex;align-items:center;gap:14px;margin-bottom:24px;opacity:0;animation:fadeUp .8s ease .2s both}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.page-eyebrow::before,.page-eyebrow::after{content:"";width:36px;height:1px;background:var(--gold)}
.page-hero h1{font-family:"Cormorant Garamond",serif;font-size:clamp(48px,7vw,88px);font-weight:700;color:var(--white);line-height:1;margin-bottom:20px;opacity:0;animation:fadeUp 1s ease .35s both}
.page-hero h1 em{color:var(--gold);font-style:italic}
.page-hero p{font-size:17px;color:var(--muted);max-width:600px;margin:0 auto 40px;line-height:1.8;opacity:0;animation:fadeUp .9s ease .5s both}
.hero-tabs{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;opacity:0;animation:fadeUp .9s ease .65s both}
.tab-btn{padding:11px 28px;border:1px solid var(--border);border-radius:30px;font-size:12px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);background:none;cursor:pointer;transition:all .3s}
.tab-btn.active,.tab-btn:hover{background:var(--gold);color:var(--ink);border-color:var(--gold)}

/* ── JOURNEY SECTIONS ── */
.journey{position:relative;z-index:10;padding:80px 60px}
.journey-label{font-size:11px;font-weight:600;letter-spacing:3px;text-transform:uppercase;color:var(--gold);display:flex;align-items:center;gap:10px;margin-bottom:48px}
.journey-label::before{content:"";width:28px;height:1px;background:var(--gold)}
.journey h2{font-family:"Cormorant Garamond",serif;font-size:clamp(32px,4vw,52px);font-weight:700;color:var(--white);margin-bottom:12px;line-height:1.1}
.journey h2 em{color:var(--gold);font-style:italic}
.journey-sub{font-size:15px;color:var(--muted);max-width:520px;line-height:1.7;margin-bottom:64px}
.journey-divider{height:1px;background:var(--border);margin:0 60px}

/* ── STEP CARDS ── */
.steps-flow{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:0;position:relative}
.steps-flow::before{content:"";position:absolute;top:52px;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,var(--gb),var(--gb),transparent);pointer-events:none;z-index:0}
.step{position:relative;z-index:1;padding:0 28px 40px;text-align:center}
.step-bubble{width:64px;height:64px;border-radius:50%;background:rgba(200,164,60,.1);border:1px solid var(--gb);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:24px;transition:all .4s;position:relative}
.step-bubble::after{content:attr(data-num);position:absolute;top:-8px;right:-8px;width:22px;height:22px;background:var(--gold);border-radius:50%;font-size:10px;font-weight:700;color:var(--ink);display:flex;align-items:center;justify-content:center;font-family:"Outfit",sans-serif}
.step:hover .step-bubble{background:rgba(200,164,60,.2);transform:translateY(-4px);box-shadow:0 16px 40px rgba(200,164,60,.2)}
.step-title{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--white);margin-bottom:10px}
.step-desc{font-size:13px;color:var(--muted);line-height:1.7}
.step-arrow{position:absolute;top:32px;right:-12px;font-size:18px;color:var(--gold);opacity:.5;z-index:2}

/* ── ROLE PANELS ── */
.role-panel{display:none;animation:fadeUp .5s ease both}
.role-panel.active{display:block}

/* ── DETAIL STRIP ── */
.detail-strip{display:grid;grid-template-columns:1fr 1fr;gap:0;border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-top:48px}
.detail-col{padding:40px}
.detail-col:first-child{border-right:1px solid var(--border);background:rgba(255,255,255,.02)}
.detail-col h3{font-family:"Cormorant Garamond",serif;font-size:22px;color:var(--white);margin-bottom:20px}
.detail-item{display:flex;align-items:flex-start;gap:14px;margin-bottom:16px;font-size:13px;color:var(--muted);line-height:1.6}
.detail-dot{width:6px;height:6px;border-radius:50%;background:var(--gold);flex-shrink:0;margin-top:6px}

/* ── COMPARISON TABLE ── */
.compare-wrap{position:relative;z-index:10;padding:80px 60px}
.compare-table{width:100%;border-collapse:collapse;border:1px solid var(--border);border-radius:12px;overflow:hidden}
.compare-table th{padding:16px 20px;font-size:11px;letter-spacing:2px;text-transform:uppercase;font-weight:600;text-align:left}
.compare-table th:first-child{background:rgba(255,255,255,.03);color:var(--muted)}
.compare-table th.col-before{background:rgba(255,59,48,.06);color:#ff6b6b}
.compare-table th.col-after{background:rgba(200,164,60,.08);color:var(--gold)}
.compare-table td{padding:14px 20px;font-size:13px;border-top:1px solid var(--border);vertical-align:top;line-height:1.5}
.compare-table td:first-child{color:rgba(255,255,255,.6);font-weight:500;background:rgba(255,255,255,.02)}
.compare-table td.before{color:rgba(255,120,100,.8);background:rgba(255,59,48,.02)}
.compare-table td.after{color:rgba(200,220,100,.9);background:rgba(200,164,60,.02)}
.compare-table tr:hover td{background:rgba(255,255,255,.03)}

/* ── FAQ ── */
.faq-wrap{position:relative;z-index:10;padding:80px 60px}
.faq-list{max-width:760px;margin:0 auto}
.faq-item{border-bottom:1px solid var(--border);padding:22px 0}
.faq-q{font-size:15px;font-weight:600;color:var(--white);cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:16px;transition:color .3s}
.faq-q:hover{color:var(--gold)}
.faq-q::after{content:"+";font-size:22px;color:var(--gold);flex-shrink:0;transition:transform .3s}
.faq-item.open .faq-q::after{transform:rotate(45deg)}
.faq-a{font-size:13px;color:var(--muted);line-height:1.8;max-height:0;overflow:hidden;transition:max-height .4s ease,padding .3s}
.faq-item.open .faq-a{max-height:240px;padding-top:14px}

/* ── CTA ── */
.cta-block{position:relative;z-index:10;margin:0 60px 80px;background:linear-gradient(135deg,rgba(200,164,60,.12),rgba(14,90,200,.08));border:1px solid var(--border);border-radius:16px;padding:72px;text-align:center}
.cta-block h2{font-family:"Cormorant Garamond",serif;font-size:clamp(32px,4vw,52px);font-weight:700;color:var(--white);margin-bottom:16px}
.cta-block h2 em{color:var(--gold);font-style:italic}
.cta-block p{font-size:16px;color:var(--muted);max-width:480px;margin:0 auto 36px;line-height:1.7}
.cta-btns{display:flex;gap:16px;justify-content:center;flex-wrap:wrap}
.btn-gold{padding:15px 34px;background:var(--gold);color:var(--ink);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:2px;transition:all .3s;display:inline-block}
.btn-gold:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,164,60,.35)}
.btn-outline{padding:15px 34px;border:1px solid rgba(200,164,60,.4);color:var(--gold);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:2px;transition:all .3s;display:inline-block}
.btn-outline:hover{background:rgba(200,164,60,.08);transform:translateY(-2px)}

footer{padding:28px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}

@media(max-width:900px){
  header,.page-hero,.journey,.journey-divider,.compare-wrap,.faq-wrap,.cta-block,footer{padding-left:24px;padding-right:24px}
  .cta-block{margin-left:16px;margin-right:16px;padding:40px 24px}
  .detail-strip{grid-template-columns:1fr}
  .detail-col:first-child{border-right:none;border-bottom:1px solid var(--border)}
  .steps-flow::before{display:none}
  .step-arrow{display:none}
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
    <div><h1 class="logo-text">HOUSING HUB</h1><span class="logo-slogan">"Your Property, Our Priority"</span></div>
  </div>
  <nav>
    <div class="dropdown"><button class="dd-btn">Home &#9660;</button><div class="dd-menu">
      <a href="index.html">Welcome</a><a href="works.php">How It Works</a>
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
  </nav>
</header>

<!-- HERO -->
<div class="page-hero z">
  <div class="page-eyebrow">Simple. Fast. Transparent.</div>
  <h1>How <em>HousingHub</em> Works</h1>
  <p>Whether you are a tenant looking for a home, a landlord or PropertyOwner managing properties, or a broker closing deals — HousingHub makes every step simple. Select your role to see your journey.</p>
  <div class="hero-tabs">
    <button class="tab-btn active" onclick="switchRole('tenant',this)">&#127968; I am a Tenant</button>
    <button class="tab-btn" onclick="switchRole('PropertyOwner',this)">&#128273; I am a PropertyOwner</button>
    <button class="tab-btn" onclick="switchRole('broker',this)">&#129514; I am a Broker</button>
  </div>
</div>

<!-- ════════════ TENANT JOURNEY ════════════ -->
<div id="role-tenant" class="role-panel active">

  <div class="journey z reveal">
    <div class="journey-label">Tenant Journey</div>
    <h2>Find, Apply &amp; <em>Move In</em></h2>
    <p class="journey-sub">From browsing your first listing to paying rent each month here is exactly how HousingHub works for tenants in Uganda.</p>

    <div class="steps-flow" >
      <div class="step">
        <div class="step-bubble" data-num="1">&#127968;</div>
        <div class="step-title">Create Account</div>
        <div class="step-desc">Register freely within 2 minutes. No deposit or card required to browse all listings.</div>
        <div class="step-arrow">&#8594;</div>
      </div>
      <div class="step">
        <div class="step-bubble" data-num="2">&#9997;</div>
        <div class="step-title">Login</div>
        <div class="step-desc">Feed in your information to continue with previous workload.</div>
        <div class="step-arrow">&#8594;</div>
      </div>
      <div class="step">
        <div class="step-bubble" data-num="3">&#128269;</div>
        <div class="step-title">Search &amp; Filter</div>
        <div class="step-desc">Browse by location, type, price, and size. Take 360° virtual tours before visiting in person.</div>
        <div class="step-arrow">&#8594;</div>
      </div><br>
      
      <div class="step">
        <div class="step-bubble" data-num="4">&#9997;</div>
        <div class="step-title">Sign Your Lease</div>
        <div class="step-desc">Review and sign your digital lease agreement online. No printing or physical visits needed.</div>
        <div class="step-arrow">&#8594;</div>
      </div>
      <div class="step">
        <div class="step-bubble" data-num="5">&#128178;</div>
        <div class="step-title">Pay Rent</div>
        <div class="step-desc">Pay monthly via MTN MoMo, Airtel Money, card, or bank. Get instant digital receipts every time.</div>
        <div class="step-arrow">&#8594;</div>
      </div>
      <div class="step">
        <div class="step-bubble" data-num="6">&#128295;</div>
        <div class="step-title">Manage Your Stay</div>
        <div class="step-desc">Log maintenance requests, track repairs, view your lease, and communicate — all from your dashboard.</div>
      </div>
    </div>

    <div class="detail-strip reveal">
      <div class="detail-col">
        <h3>What You Can Do as a Tenant</h3>
        <div class="detail-item"><div class="detail-dot"></div>Browse and filter 1,200+ verified property listings</div>
        <div class="detail-item"><div class="detail-dot"></div>Take virtual 360° property tours from your phone</div>
        <div class="detail-item"><div class="detail-dot"></div>Submit applications with documents digitally</div>
        <div class="detail-item"><div class="detail-dot"></div>Sign your lease agreement online — no printing</div>
        <div class="detail-item"><div class="detail-dot"></div>Pay rent via MoMo, Airtel, card, or bank transfer</div>
        <div class="detail-item"><div class="detail-dot"></div>Log and track maintenance requests in real time</div>
        <div class="detail-item"><div class="detail-dot"></div>Raise complaints formally through the platform</div>
        <div class="detail-item"><div class="detail-dot"></div>Access all your receipts and lease history anytime</div>
      </div>
      <div class="detail-col">
        <h3>Your Tenant Dashboard Includes</h3>
        <div class="detail-item"><div class="detail-dot"></div>Current lease details, dates, and terms at a glance</div>
        <div class="detail-item"><div class="detail-dot"></div>Full payment history with downloadable receipts</div>
        <div class="detail-item"><div class="detail-dot"></div>Live maintenance request tracker with status updates</div>
        <div class="detail-item"><div class="detail-dot"></div>Notification inbox for rent reminders and updates</div>
        <div class="detail-item"><div class="detail-dot"></div>Complaint submission and resolution tracker</div>
        <div class="detail-item"><div class="detail-dot"></div>Saved property listings and application history</div>
        <div class="detail-item"><div class="detail-dot"></div>Direct messaging with your landlord or property manager</div>
        <div class="detail-item"><div class="detail-dot"></div>Visitor pre-approval for expected guests</div>
      </div>
    </div>
  </div>

</div>

<!-- ════════════ LANDLORD JOURNEY ════════════ -->
<div id="role-landlord" class="role-panel">

  <div class="journey z reveal">
    <div class="journey-label">PropertyOwner Journey</div>
    <h2>List, Manage &amp; <em>Get Paid</em></h2>
    <p class="journey-sub">From listing your first property to tracking rent across your entire portfolio here is how HousingHub works for landlords and property owners.</p>

    <div class="steps-flow">
      <div class="step">
        <div class="step-bubble" data-num="1">&#128273;</div>
        <div class="step-title">Create Account</div>
        <div class="step-desc">Register as a Property Owner. Verification takes less than 24 hours.</div>
        <div class="step-arrow">&#8594;</div>
      </div>
      <div class="step">
        <div class="step-bubble" data-num="2">&#127968;</div>
        <div class="step-title">List Your Property</div>
        <div class="step-desc">Add your property with photos, virtual tour, location, amenities, and rental price.</div>
        <div class="step-arrow">&#8594;</div>
      </div>
      <div class="step">
        <div class="step-bubble" data-num="3">&#128203;</div>
        <div class="step-title">Review Applications</div>
        <div class="step-desc">Receive tenant applications digitally. Review documents, screen applicants, and approve.</div>
        <div class="step-arrow">&#8594;</div>
      </div>
      <div class="step">
        <div class="step-bubble" data-num="4">&#9997;</div>
        <div class="step-title">Send Lease</div>
        <div class="step-desc">Generate a digital lease from your template. Tenant reviews and signs online instantly.</div>
        <div class="step-arrow">&#8594;</div>
      </div>
      <div class="step">
        <div class="step-bubble" data-num="5">&#128200;</div>
        <div class="step-title">Collect Rent</div>
        <div class="step-desc">Rent arrives via MoMo, card, or bank. Automated reminders reduce late payments to near zero.</div>
        <div class="step-arrow">&#8594;</div>
      </div>
      <div class="step">
        <div class="step-bubble" data-num="6">&#128229;</div>
        <div class="step-title">Report &amp; Grow</div>
        <div class="step-desc">Export monthly income reports, track occupancy rates, and manage your full portfolio from one place.</div>
      </div>
    </div>

    <div class="detail-strip reveal">
      <div class="detail-col">
        <h3>What You Can Do as a PropertyOwner</h3>
        <div class="detail-item"><div class="detail-dot"></div>List unlimited properties with photos and virtual tours</div>
        <div class="detail-item"><div class="detail-dot"></div>Receive and manage tenant applications digitally</div>
        <div class="detail-item"><div class="detail-dot"></div>Create and send digital lease agreements to tenants</div>
        <div class="detail-item"><div class="detail-dot"></div>Collect rent via MoMo, Airtel, card, and bank transfer</div>
        <div class="detail-item"><div class="detail-dot"></div>Track maintenance requests across all your units</div>
        <div class="detail-item"><div class="detail-dot"></div>Manage visitor and guest access per property</div>
        <div class="detail-item"><div class="detail-dot"></div>Handle tenant complaints through a formal channel</div>
        <div class="detail-item"><div class="detail-dot"></div>Generate monthly and annual financial reports</div>
      </div>
      <div class="detail-col">
        <h3>Your Owner Portal Includes</h3>
        <div class="detail-item"><div class="detail-dot"></div>Multi-property dashboard with real-time occupancy</div>
        <div class="detail-item"><div class="detail-dot"></div>Live payment status for every tenant across all units</div>
        <div class="detail-item"><div class="detail-dot"></div>Automated overdue rent alerts and tenant reminders</div>
        <div class="detail-item"><div class="detail-dot"></div>Maintenance job tracker with technician assignment</div>
        <div class="detail-item"><div class="detail-dot"></div>Digital lease library — all signed agreements stored</div>
        <div class="detail-item"><div class="detail-dot"></div>Tenant profiles with contact info and payment history</div>
        <div class="detail-item"><div class="detail-dot"></div>Complaint and feedback resolution dashboard</div>
        <div class="detail-item"><div class="detail-dot"></div>Exportable income and occupancy reports (PDF / CSV)</div>
      </div>
    </div>
  </div>

</div>

<!-- ════════════ BROKER JOURNEY ════════════ -->
<div id="role-broker" class="role-panel">

  <div class="journey z reveal">
    <div class="journey-label">Broker Journey</div>
    <h2>List, Connect &amp; <em>Earn</em></h2>
    <p class="journey-sub">From creating your broker profile to closing deals and tracking commissions — here is how HousingHub works for brokers and property agents across Uganda.</p>

    <div class="steps-flow">
      <div class="step">
        <div class="step-bubble" data-num="1">&#129514;</div>
        <div class="step-title">Register as Broker</div>
        <div class="step-desc">Create your verified broker profile on HousingHub. Get your broker badge after verification.</div>
        <div class="step-arrow">&#8594;</div>
      </div>
      <div class="step">
        <div class="step-bubble" data-num="2">&#127968;</div>
        <div class="step-title">List Properties</div>
        <div class="step-desc">Upload properties on behalf of owners. Add photos, virtual tours, pricing, and location details.</div>
        <div class="step-arrow">&#8594;</div>
      </div>
      <div class="step">
        <div class="step-bubble" data-num="3">&#128101;</div>
        <div class="step-title">Manage Leads</div>
        <div class="step-desc">Receive and track all tenant enquiries in one place. Follow up and schedule viewings from your dashboard.</div>
        <div class="step-arrow">&#8594;</div>
      </div>
      <div class="step">
        <div class="step-bubble" data-num="4">&#128203;</div>
        <div class="step-title">Process Applications</div>
        <div class="step-desc">Forward tenant applications to property owners digitally. Track approvals and responses in real time.</div>
        <div class="step-arrow">&#8594;</div>
      </div>
      <div class="step">
        <div class="step-bubble" data-num="5">&#128176;</div>
        <div class="step-title">Close the Deal</div>
        <div class="step-desc">Deal is signed and payment confirmed on HousingHub. Your commission is calculated and recorded automatically.</div>
        <div class="step-arrow">&#8594;</div>
      </div>
      <div class="step">
        <div class="step-bubble" data-num="6">&#128200;</div>
        <div class="step-title">Track &amp; Repeat</div>
        <div class="step-desc">View your commission history, active listings, and deal pipeline from your broker dashboard at any time.</div>
      </div>
    </div>

    <div class="detail-strip reveal">
      <div class="detail-col">
        <h3>What You Can Do as a Broker</h3>
        <div class="detail-item"><div class="detail-dot"></div>Create a verified broker profile with your badge</div>
        <div class="detail-item"><div class="detail-dot"></div>List properties on behalf of landlords and owners</div>
        <div class="detail-item"><div class="detail-dot"></div>Manage all tenant leads and enquiries in one place</div>
        <div class="detail-item"><div class="detail-dot"></div>Offer 360° virtual tours to clients remotely</div>
        <div class="detail-item"><div class="detail-dot"></div>Forward and track tenant applications digitally</div>
        <div class="detail-item"><div class="detail-dot"></div>Close deals and have commissions tracked automatically</div>
        <div class="detail-item"><div class="detail-dot"></div>Share professional property listings with direct links</div>
        <div class="detail-item"><div class="detail-dot"></div>Build your reputation through verified deal history</div>
      </div>
      <div class="detail-col">
        <h3>Your Broker Dashboard Includes</h3>
        <div class="detail-item"><div class="detail-dot"></div>Active listings with views, enquiries, and status</div>
        <div class="detail-item"><div class="detail-dot"></div>Lead manager with follow-up reminders per client</div>
        <div class="detail-item"><div class="detail-dot"></div>Application tracker — submitted, pending, approved</div>
        <div class="detail-item"><div class="detail-dot"></div>Commission history with full deal breakdown records</div>
        <div class="detail-item"><div class="detail-dot"></div>Verified broker badge visible on all your listings</div>
        <div class="detail-item"><div class="detail-dot"></div>Client communication history per property</div>
        <div class="detail-item"><div class="detail-dot"></div>Alerts for new enquiries and application responses</div>
        <div class="detail-item"><div class="detail-dot"></div>Exportable deal and commission reports (PDF / CSV)</div>
      </div>
    </div>
  </div>

</div>

<div class="journey-divider z"></div>

<!-- COMPARISON TABLE -->
<div class="compare-wrap z reveal">
  <div class="journey-label">Why It Matters</div>
  <h2 class="journey z" style="padding:0 0 12px;font-family:'Cormorant Garamond',serif;font-size:clamp(28px,3.5vw,46px);font-weight:700;color:var(--white)">Manual system Vs <em style="color:var(--gold);font-style:italic">HousingHub</em></h2>
  <p style="font-size:15px;color:var(--muted);margin-bottom:40px;line-height:1.7;max-width:560px">The old way of managing property in Uganda was manual, slow, and full of gaps. Here is how HousingHub changes every step.</p>
  <table class="compare-table">
    <thead>
      <tr>
        <th>Area</th>
        <th class="col-before">&#128683; Manual System</th>
        <th class="col-after">&#10003; HousingHub</th>
      </tr>
    </thead>
    <tbody>
      <tr><td>Finding a Property</td><td class="before">Walking around looking for "To Let" signs</td><td class="after">Browse 1,200+ verified listings online with filters and virtual tours</td></tr>
      <tr><td>Applying</td><td class="before">Paper forms dropped off physically at the property</td><td class="after">Digital application submitted in 5 minutes from your phone</td></tr>
      <tr><td>Lease Agreement</td><td class="before">Printed documents signed in person, often lost</td><td class="after">Digital lease created, signed online, and stored permanently</td></tr>
      <tr><td>Paying Rent</td><td class="before">Cash collected door-to-door or sent via multiple transfers</td><td class="after">Pay via Mobile Money, card, or bank and receipts sent instantly</td></tr>
      <tr><td>Maintenance</td><td class="before">Calling landlord on WhatsApp with no guarantee of follow-up</td><td class="after">Logged formally with live tracking from submission to completion</td></tr>
      <tr><td>Complaints</td><td class="before">Verbal complaints with no record or resolution path</td><td class="after">Formal complaint filed, tracked, and resolved with full audit trail</td></tr>
      <tr><td>Reporting</td><td class="before">Manual Excel sheets or exercise book records</td><td class="after">Auto-generated reports exportable as PDF or CSV anytime</td></tr>
      <tr><td>Visitor Control</td><td class="before">Paper logbooks at the gate, easily lost or falsified</td><td class="after">Digital visitor log with pre-approvals and real-time alerts</td></tr>
    </tbody>
  </table>
</div>

<!-- FAQ -->
<div class="faq-wrap z reveal">
  <div class="journey-label">Common Questions</div>
  <h2 style="font-family:'Cormorant Garamond',serif;font-size:clamp(28px,3.5vw,46px);font-weight:700;color:var(--white);margin-bottom:12px"><em>FAQS </em><em style="color:var(--gold);font-style:italic">(Frequently Asked <em style="color:var(--gold);font-style:italic">Questions)</em></h2>
  <p style="font-size:15px;color:var(--muted);margin-bottom:48px;line-height:1.7;max-width:560px">Everything you need to know before getting started on HousingHub.</p>
  <div class="faq-list">
    <div class="faq-item"><div class="faq-q">Is HousingHub free to use?</div><div class="faq-a">Creating an account and browsing all property listings is completely free. Tenants pay no fee to apply. Landlords and brokers may have listing or subscription plans — check the Pricing page for current rates.</div></div>
    <div class="faq-item"><div class="faq-q">How do I know a property listing is legitimate?</div><div class="faq-a">All landlords and properties on HousingHub are verified before their listings go live. Look for the verified badge on any listing. If you ever encounter a suspicious listing, report it directly to our support team.</div></div>
    <div class="faq-item"><div class="faq-q">What payment methods are accepted?</div><div class="faq-a">HousingHub accepts MTN Mobile Money, Airtel Money, debit and credit cards via Flutterwave, and direct bank transfer. All payments generate instant digital receipts stored on your dashboard.</div></div>
    <div class="faq-item"><div class="faq-q">Can I manage more than one property as a propertyowner?</div><div class="faq-a">Yes. The HousingHub Owner Portal supports unlimited properties. You get a single dashboard showing all your units, tenants, payments, and maintenance jobs across your entire portfolio.</div></div>
    <div class="faq-item"><div class="faq-q">What happens after my application is submitted?</div><div class="faq-a">You will receive a real-time status notification at each stage — Received, Under Review, Approved, or Rejected. If approved, the landlord will send your digital lease for review and signing directly on the platform.</div></div>
    <div class="faq-item"><div class="faq-q">How are maintenance requests handled?</div><div class="faq-a">Tenants log requests with a description and photos on HousingHub. The landlord is notified instantly, assigns a technician, and the tenant can track live progress until the job is closed and recorded.</div></div>
    <div class="faq-item"><div class="faq-q">Is my personal data safe on HousingHub?</div><div class="faq-a">Yes. All personal data, documents, and payment records are encrypted and stored securely. HousingHub complies with local data protection laws and never sells or shares your data with third parties for commercial purposes.</div></div>
    <div class="faq-item"><div class="faq-q">How do brokers earn commissions on HousingHub?</div><div class="faq-a">Brokers list properties, manage leads, and close deals on the platform. When a deal is confirmed and payment is verified, the commission is calculated automatically based on the agreed rate and recorded in the broker's dashboard.</div></div>
  </div>
</div>

<!-- CTA -->
<div class="cta-block z reveal">
  <h2>Ready to Get <em>Started?</em></h2>
  <p>Join thousands of tenants, PropertyOwners, and brokers across Uganda who already manage everything on HousingHub.</p>
  <div class="cta-btns">
    <a href="properties.php" class="btn-gold">Browse Properties</a>
    <a href="index.php" class="btn-outline">Create Account</a>
  </div>
</div>

<footer class="z">&copy; 2026 HousingHub | All Rights Reserved</footer>

<script>
/* ROLE TABS */
function switchRole(role, btn) {
  document.querySelectorAll('.role-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('role-' + role).classList.add('active');
  btn.classList.add('active');
  document.getElementById('role-' + role).scrollIntoView({behavior:'smooth', block:'start'});
}

/* DROPDOWNS */
function closeAllMenus(){document.querySelectorAll('.dd-menu.open').forEach(m=>m.classList.remove('open'));document.querySelectorAll('.dd-btn.open').forEach(b=>b.classList.remove('open'));}
document.querySelectorAll('.dropdown').forEach(dd=>{var btn=dd.querySelector('.dd-btn'),menu=dd.querySelector('.dd-menu');if(!btn||!menu)return;btn.addEventListener('click',e=>{e.stopPropagation();var o=menu.classList.contains('open');closeAllMenus();if(!o){menu.classList.add('open');btn.classList.add('open');}});menu.addEventListener('mousedown',e=>e.stopPropagation());menu.addEventListener('click',e=>e.stopPropagation());});
document.addEventListener('click',closeAllMenus);
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeAllMenus();});

/* CURSOR */
const dot=document.getElementById('cur-dot'),ring=document.getElementById('cur-ring'),trail=document.getElementById('cur-trail');
let mx=-200,my=-200,rx=-200,ry=-200,tx=-200,ty=-200;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;dot.style.left=mx+'px';dot.style.top=my+'px';});
(function anim(){rx+=(mx-rx)*.15;ry+=(my-ry)*.15;tx+=(mx-tx)*.06;ty+=(my-ty)*.06;ring.style.left=rx+'px';ring.style.top=ry+'px';trail.style.left=tx+'px';trail.style.top=ty+'px';requestAnimationFrame(anim);})();
document.querySelectorAll('a,button,.step,.faq-q').forEach(el=>{el.addEventListener('mouseenter',()=>document.body.classList.add('cursor-hover'));el.addEventListener('mouseleave',()=>document.body.classList.remove('cursor-hover'));});
document.addEventListener('mousedown',()=>document.body.classList.add('cursor-click'));
document.addEventListener('mouseup',()=>document.body.classList.remove('cursor-click'));

/* PARTICLES */
for(let i=0;i<18;i++){const p=document.createElement('div');p.classList.add('ptcl');const sz=Math.random()*3+1;p.style.cssText=`width:${sz}px;height:${sz}px;left:${Math.random()*100}%;background:rgba(200,164,60,${(Math.random()*.5+.15).toFixed(2)});animation-duration:${Math.random()*22+10}s;animation-delay:${Math.random()*18}s;`;document.body.appendChild(p);}

/* SCROLL REVEAL */
const ro=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');ro.unobserve(e.target);}});},{threshold:.08});
document.querySelectorAll('.reveal').forEach(el=>ro.observe(el));

/* FAQ ACCORDION */
document.querySelectorAll('.faq-q').forEach(q=>{q.addEventListener('click',()=>{const item=q.parentElement;const was=item.classList.contains('open');document.querySelectorAll('.faq-item.open').forEach(i=>i.classList.remove('open'));if(!was)item.classList.add('open');});});
</script>
</body>
</html>