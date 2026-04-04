<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Brokers | HousingHub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25)}
body{cursor:none;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden}

/* CURSOR */
#cur-dot{width:8px;height:8px;background:var(--gold);border-radius:50%;position:fixed;z-index:99999;pointer-events:none;transform:translate(-50%,-50%);mix-blend-mode:difference}
#cur-ring{width:20px;height:20px;border:1.5px solid rgba(200,164,60,.7);border-radius:50%;position:fixed;z-index:99998;pointer-events:none;transform:translate(-50%,-50%);transition:width .45s cubic-bezier(.23,1,.32,1),height .45s}
#cur-trail{width:30px;height:30px;border:1px solid rgba(200,164,60,.15);border-radius:50%;position:fixed;z-index:99997;pointer-events:none;transform:translate(-50%,-50%);transition:width .7s,height .7s}
body.cursor-hover #cur-dot{background:#fff}
body.cursor-hover #cur-ring{border-color:var(--gold);background:rgba(200,164,60,.06)}
body.cursor-click #cur-dot{width:5px;height:5px}
body.cursor-click #cur-ring{width:28px;height:28px}

.page-bg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(200,164,60,.13) 0%,transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(14,90,200,.12) 0%,transparent 50%),var(--ink)}
.page-grid{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:72px 72px}
.ptcl{position:fixed;border-radius:50%;pointer-events:none;z-index:1;animation:pdrift linear infinite}
@keyframes pdrift{0%{transform:translateY(100vh) scale(0);opacity:0}5%{opacity:1}95%{opacity:.5}100%{transform:translateY(-10vh) translateX(40px) scale(1.4);opacity:0}}
.z{position:relative;z-index:10}
.reveal{opacity:0;transform:translateY(24px);transition:opacity .7s ease,transform .7s ease}
.reveal.visible{opacity:1;transform:translateY(0)}

/* HEADER */
header{position:sticky;top:0;z-index:9000;display:flex;justify-content:space-between;align-items:center;padding:18px 60px;background:var(--gold);border-bottom:1px solid rgba(0,0,0,.1);animation:fadeDown .8s ease both;overflow:visible}
@keyframes fadeDown{from{opacity:0;transform:translateY(-16px)}to{opacity:1;transform:translateY(0)}}
.header-logo{display:flex;align-items:center;gap:14px}
.logo-circle{width:65px;height:65px;border-radius:50%;object-fit:cover;border:2px solid rgba(4,9,26,.2)}
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

/* HERO */
.hero{min-height:88vh;display:flex;align-items:center;padding:100px 60px 80px;position:relative;z-index:10;overflow:hidden}
.hero-content{max-width:660px;position:relative;z-index:2}
.hero-eyebrow{font-size:11px;font-weight:500;letter-spacing:4px;text-transform:uppercase;color:var(--gold);display:flex;align-items:center;gap:12px;margin-bottom:24px;animation:fadeUp .8s .2s both}
.hero-eyebrow::before{content:"";width:36px;height:1px;background:var(--gold)}
.hero h1{font-family:"Cormorant Garamond",serif;font-size:clamp(48px,7vw,84px);font-weight:700;line-height:1.0;margin-bottom:24px;color:var(--white);animation:fadeUp .8s .35s both}
.hero h1 em{color:var(--gold);font-style:italic}
.hero h1 .stroke{-webkit-text-stroke:1px var(--gold);color:transparent}
.hero-sub{font-size:17px;line-height:1.75;color:var(--muted);max-width:520px;margin-bottom:40px;animation:fadeUp .8s .5s both}
.hero-btns{display:flex;gap:16px;flex-wrap:wrap;animation:fadeUp .8s .65s both}
.btn-primary{padding:15px 34px;background:var(--gold);color:var(--ink);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:2px;transition:all .3s;display:inline-block}
.btn-primary:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,164,60,.35)}
.btn-secondary{padding:15px 34px;border:1px solid rgba(200,164,60,.4);color:var(--gold);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:2px;transition:all .3s;display:inline-block}
.btn-secondary:hover{background:rgba(200,164,60,.08);transform:translateY(-2px)}
.hero-stats{display:flex;gap:48px;margin-top:56px;padding-top:40px;border-top:1px solid var(--border);animation:fadeUp .8s .8s both}
.hstat-num{font-family:"Cormorant Garamond",serif;font-size:36px;font-weight:700;color:var(--gold)}
.hstat-label{font-size:11px;color:var(--muted);letter-spacing:1px;margin-top:2px}

/* HERO FLOATING CARDS */
.hero-float{position:absolute;right:60px;top:50%;transform:translateY(-50%);display:flex;flex-direction:column;gap:16px;z-index:1;animation:fadeUp .9s .4s both}
.hf-card{background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:14px;padding:18px 22px;width:240px;backdrop-filter:blur(12px);transition:all .4s}
.hf-card:hover{border-color:var(--gb);background:rgba(200,164,60,.06);transform:translateX(-6px)}
.hf-icon{font-size:26px;margin-bottom:10px}
.hf-title{font-family:"Cormorant Garamond",serif;font-size:16px;font-weight:700;color:var(--white);margin-bottom:4px}
.hf-desc{font-size:12px;color:var(--muted);line-height:1.5}
.hf-val{font-family:"Cormorant Garamond",serif;font-size:22px;font-weight:700;color:var(--gold);margin-top:6px}

@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}

/* SECTIONS */
section.pad{padding:100px 60px;position:relative;z-index:10}
.section-eyebrow{font-size:11px;font-weight:500;letter-spacing:4px;text-transform:uppercase;color:var(--gold);display:flex;align-items:center;gap:12px;margin-bottom:20px}
.section-eyebrow::before{content:"";width:28px;height:1px;background:var(--gold)}
.section-title{font-family:"Cormorant Garamond",serif;font-size:clamp(32px,4vw,52px);font-weight:700;color:var(--white);line-height:1.1;margin-bottom:16px}
.section-title em{color:var(--gold);font-style:italic}
.section-sub{font-size:16px;color:var(--muted);max-width:560px;line-height:1.7;margin-bottom:52px}

/* HOW IT WORKS - TIMELINE */
.timeline{position:relative;max-width:800px}
.timeline::before{content:"";position:absolute;left:28px;top:0;bottom:0;width:1px;background:linear-gradient(to bottom,var(--gold),rgba(200,164,60,.1));z-index:0}
.tl-item{display:flex;gap:32px;margin-bottom:40px;position:relative;align-items:flex-start}
.tl-num{width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.2),rgba(14,90,200,.15));border:1.5px solid var(--gb);display:flex;align-items:center;justify-content:center;font-family:"Cormorant Garamond",serif;font-size:22px;font-weight:700;color:var(--gold);flex-shrink:0;position:relative;z-index:1;transition:all .3s}
.tl-item:hover .tl-num{background:rgba(200,164,60,.25);border-color:var(--gold);transform:scale(1.08)}
.tl-body{flex:1;padding-top:8px}
.tl-title{font-size:17px;font-weight:700;color:var(--white);margin-bottom:6px}
.tl-desc{font-size:14px;color:var(--muted);line-height:1.7}

/* BENEFITS GRID */
.benefits-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.ben-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:14px;padding:28px;transition:all .4s;position:relative;overflow:hidden}
.ben-card::before{content:"";position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--gold),transparent);opacity:0;transition:opacity .4s}
.ben-card:hover{border-color:var(--gb);background:rgba(200,164,60,.04);transform:translateY(-5px)}
.ben-card:hover::before{opacity:1}
.ben-icon{font-size:36px;margin-bottom:16px;display:block}
.ben-title{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--white);margin-bottom:8px}
.ben-desc{font-size:13px;color:var(--muted);line-height:1.65}
.ben-tag{display:inline-block;margin-top:14px;padding:4px 12px;background:rgba(200,164,60,.1);border:1px solid var(--gb);border-radius:20px;font-size:10px;font-weight:700;letter-spacing:1px;color:var(--gold)}

/* COMMISSION CALCULATOR */
.calc-wrap{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:16px;padding:36px;max-width:580px}
.calc-title{font-family:"Cormorant Garamond",serif;font-size:24px;font-weight:700;color:var(--white);margin-bottom:6px}
.calc-sub{font-size:13px;color:var(--muted);margin-bottom:28px}
.calc-row{margin-bottom:20px}
.calc-label{font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:8px;display:flex;justify-content:space-between;align-items:center}
.calc-label span{font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;color:var(--white);letter-spacing:0;text-transform:none}
.calc-slider{width:100%;-webkit-appearance:none;appearance:none;height:4px;border-radius:2px;background:rgba(255,255,255,.1);outline:none;cursor:pointer}
.calc-slider::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:18px;height:18px;border-radius:50%;background:var(--gold);cursor:pointer;border:2px solid var(--ink);box-shadow:0 0 0 3px rgba(200,164,60,.25)}
.calc-result{background:rgba(200,164,60,.08);border:1px solid var(--gb);border-radius:10px;padding:20px;text-align:center;margin-top:24px}
.calc-result-lbl{font-size:11px;color:var(--muted);letter-spacing:1px;text-transform:uppercase;margin-bottom:6px}
.calc-result-val{font-family:"Cormorant Garamond",serif;font-size:42px;font-weight:700;color:var(--gold)}
.calc-result-sub{font-size:12px;color:var(--muted);margin-top:4px}

/* COMPARE TABLE */
.compare-wrap{overflow-x:auto}
.compare-table{width:100%;border-collapse:collapse;min-width:600px}
.compare-table th{padding:14px 20px;font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);border-bottom:1px solid var(--border);text-align:left}
.compare-table th:nth-child(2){background:rgba(200,164,60,.06);border-radius:8px 8px 0 0;text-align:center;color:var(--gold-l)}
.compare-table td{padding:14px 20px;font-size:13px;color:rgba(255,255,255,.8);border-bottom:1px solid rgba(255,255,255,.04)}
.compare-table td:nth-child(2){background:rgba(200,164,60,.04);text-align:center;font-weight:600}
.compare-table tr:last-child td{border-bottom:none}
.compare-table tr:hover td{background:rgba(255,255,255,.02)}
.compare-table tr:hover td:nth-child(2){background:rgba(200,164,60,.08)}
.chk{color:#86efac;font-size:16px}
.crs{color:#fca5a5;font-size:14px}


.faq-list{max-width:720px}
.faq-item{border-bottom:1px solid var(--border);padding:20px 0}
.faq-q{font-size:15px;font-weight:600;color:var(--white);cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:16px}
.faq-q::after{content:"+";font-size:20px;color:var(--gold);flex-shrink:0;transition:transform .3s}
.faq-item.open .faq-q::after{transform:rotate(45deg)}
.faq-a{font-size:13px;color:var(--muted);line-height:1.7;max-height:0;overflow:hidden;transition:max-height .4s ease,padding .3s}
.faq-item.open .faq-a{max-height:200px;padding-top:12px}

/* CTA BLOCK */
.cta-block{background:linear-gradient(135deg,rgba(200,164,60,.12),rgba(14,90,200,.1));border:1px solid var(--border);border-radius:16px;padding:72px;text-align:center}
.cta-block h2{font-family:"Cormorant Garamond",serif;font-size:clamp(32px,4vw,52px);font-weight:700;color:var(--white);margin-bottom:16px}
.cta-block h2 em{color:var(--gold);font-style:italic}
.cta-block p{font-size:16px;color:var(--muted);max-width:480px;margin:0 auto 36px;line-height:1.7}
.cta-btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}

/* SPLIT LAYOUT */
.split{display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.split.rev{direction:rtl}.split.rev>*{direction:ltr}

footer{padding:32px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}

@media(max-width:1100px){.hero-float{display:none}.split{grid-template-columns:1fr}}
@media(max-width:900px){
  header,section.pad,.hero,footer{padding-left:24px;padding-right:24px}
  .hero{padding-top:80px;padding-bottom:60px;min-height:auto}
  .benefits-grid,.testi-grid{grid-template-columns:1fr}
  .earnings-strip{grid-template-columns:1fr 1fr;gap:32px}
  .cta-block{padding:40px 24px}
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
    <div class="dropdown"><button class="dd-btn">Home &#9660;</button><div class="dd-menu"><a href="index.html">Welcome</a><a href="works.php">How It Works</a></div></div>
    <div class="dropdown"><button class="dd-btn">Features &#9660;</button><div class="dd-menu"><a href="virtual.php">Virtual Property Tours</a><a href="visitor.php">Visitor/Guest Management</a><a href="applications.php">Online Tenant Applications</a><a href="reporting.php">Rent/Buy Reporting</a><a href="lease.php">Online Lease</a><a href="maintenance.php">Maintenance</a><a href="rent_collection.php">Rent Collection</a><a href="notifications.php">Smart Notification Center</a><a href="complaints.php">Complaints &amp; Feedback HUB</a><a href="owner_portal.php">Owner Portal &amp; Reporting</a><a href="policies.html">Policies</a></div></div>
    <div class="dropdown"><button class="dd-btn">Use Cases &#9660;</button><div class="dd-menu"><a href="tenant.php">Tenants</a><a href="Broker.php">Brokers</a><a href="staff.php">Staff</a><a href="Visitor.php">Guests</a><a href="propertyowners.php">Property Owners</a><a href="broker.php">Broker-Free</a><a href="employment.php">Employment</a></div></div>
    <div class="dropdown"><button class="dd-btn">Properties &#9660;</button><div class="dd-menu"><a href="properties.php">All Properties</a><div class="dd-divider"></div><a href="properties.php?type=Commercial">Commercial</a><a href="properties.php?type=Residential">Residential</a><a href="properties.php?type=Industrial">Industrial</a><a href="properties.php?type=Agricultural">Agricultural</a><a href="properties.php?type=Special+Purpose">Special Purpose</a><a href="properties.php?type=Land">Land</a></div></div>
    <a href="index.php">Login</a>
    <div class="dropdown"><button class="dd-btn">About Us &#9660;</button><div class="dd-menu"><a href="who.php">Who We Are</a><a href="what.php">What We Do</a><a href="vision.php">Our Vision</a><a href="values.php">Core Values</a><a href="contact.php">Contact Us</a></div></div>
  </nav>
</header>

<!-- HERO -->
<section class="hero z">
  <div class="hero-content">
    <div class="hero-eyebrow">For Brokers &amp; Agents</div>
    <h1>Earn More.<br>Work <em>Smarter.</em><br><span class="stroke">Build Wealth.</span></h1>
    <p class="hero-sub">Partner with HousingHub and unlock a steady stream of listings, verified clients, and automatic commission tracking — all from one powerful platform built for Ugandan property professionals.</p>
    <div class="hero-btns">
      <a href="join.php" class="btn-primary">Join us</a>
      <a href="#how-it-works" class="btn-secondary">See How It Works</a>
    </div>
    <div class="hero-stats">
      <div><div class="hstat-num" data-count="350" data-suffix="+">350</div><div class="hstat-label">Active Brokers</div></div>
      <div><div class="hstat-num" data-count="12" data-suffix="%">12</div><div class="hstat-label">Avg Commission Rate</div></div>
      <div><div class="hstat-num" data-count="48" data-suffix="hrs">48</div><div class="hstat-label">Avg Deal Closure</div></div>
    </div>
  </div>

  <!-- Floating benefit cards -->
  <div class="hero-float">
    
      
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="pad z reveal" id="how-it-works">
  <div class="section-eyebrow">Getting Started</div>
  <h2 class="section-title">How It <em>Works</em></h2>
  <p class="section-sub">From signup to your first commission cheque — here's the full journey as a HousingHub broker.</p>
  <div class="split">
    <div class="timeline">
      <div class="tl-item">
        <div class="tl-num">1</div>
        <div class="tl-body">
          <div class="tl-title">Create Your Broker Account</div>
          <div class="tl-desc">Register for free in under 3 minutes. Submit your NIN or business registration, and our team verifies you within 24 hours.</div>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-num">2</div>
        <div class="tl-body">
          <div class="tl-title">Browse &amp; Claim Listings</div>
          <div class="tl-desc">Access the full HousingHub property catalogue. Claim listings you want to market and share them with your client base.</div>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-num">3</div>
        <div class="tl-body">
          <div class="tl-title">Match Clients to Properties</div>
          <div class="tl-desc">Use your dashboard to track prospects, schedule viewings, and manage application pipelines for multiple clients at once.</div>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-num">4</div>
        <div class="tl-body">
          <div class="tl-title">Close Deal &amp; Get Paid</div>
          <div class="tl-desc">Once a lease or sale is signed, your commission is automatically calculated and paid out to your registered account. No chasing anyone.</div>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-num">5</div>
        <div class="tl-body">
          <div class="tl-title">Track &amp; Scale</div>
          <div class="tl-desc">Monitor earnings, completed deals, and client reviews from your broker portal. The more deals you close, the better your tier and commission rate.</div>
        </div>
      </div>
    </div>
    <div class="calc-wrap">
      <div class="calc-title">Commission Calculator</div>
      <div class="calc-sub">Estimate your monthly earnings based on deal volume and property values.</div>
      <div class="calc-row">
        <div class="calc-label">Deals per month <span id="deals-val">4 deals</span></div>
        <input type="range" class="calc-slider" id="deals" min="1" max="20" value="4" oninput="calcCommission()">
      </div>
      <div class="calc-row">
        <div class="calc-label">Avg property value (UGX M) <span id="value-val">10M</span></div>
        <input type="range" class="calc-slider" id="propval" min="10" max="500" step="10" value="10" oninput="calcCommission()">
      </div>
      <div class="calc-row">
        <div class="calc-label">Commission rate <span id="rate-val">10%</span></div>
        <input type="range" class="calc-slider" id="rate" min="5" max="20" value="10" oninput="calcCommission()">
      </div>
      <div class="calc-result">
        <div class="calc-result-lbl">Estimated Monthly Earnings</div>
        <div class="calc-result-val" id="calc-result">UGX 32,000,000</div>
        <div class="calc-result-sub">Based on <span id="calc-breakdown">4 deals × UGX 80M × 10%</span></div>
      </div>
    </div>
  </div>
</section>

<!-- BENEFITS -->
<section class="pad z reveal">
  <div class="section-eyebrow">Why Partner With Us</div>
  <h2 class="section-title">Built for <em>Brokers</em></h2>
  <p class="section-sub">Every tool, feature, and workflow on HousingHub is designed to help you close deals faster and earn more consistently.</p>
  <div class="benefits-grid">
    <div class="ben-card">
      <span class="ben-icon"></span>
      <div class="ben-title">Massive Verified Inventory</div>
      <div class="ben-desc">Access hundreds of residential, commercial, and industrial listings across Kampala ready to market.</div>
      <span class="ben-tag">500+ Listings</span>
    </div>
    <div class="ben-card">
      <span class="ben-icon"></span>
      <div class="ben-title">Broker Portal &amp; App</div>
      <div class="ben-desc">Your own dedicated dashboard to manage clients, track applications, schedule viewings, and monitor your commissions — all from mobile or desktop.</div>
      <span class="ben-tag">Always On</span>
    </div>
    <div class="ben-card">
      <span class="ben-icon"></span>
      <div class="ben-title">Automatic Commission Payouts</div>
      <div class="ben-desc">No more chasing landlords for your cut. HousingHub automatically calculates and processes your commission as soon as a deal is sealed.</div>
      <span class="ben-tag">Fast Payouts</span>
    </div>
    <div class="ben-card">
      <span class="ben-icon"></span>
      <div class="ben-title">Marketing Support</div>
      <div class="ben-desc">Get co-branded listing materials, social media templates, and featured placement in search results to help you win more clients.</div>
      <span class="ben-tag">Grow Your Brand</span>
    </div>
    <div class="ben-card">
      <span class="ben-icon"></span>
      <div class="ben-title">Verified Leads Only</div>
      <div class="ben-desc">No more time-wasting. Every client lead you receive through HousingHub has been pre-screened and is actively looking to rent or buy.</div>
      <span class="ben-tag">Quality Leads</span>
    </div>
    <div class="ben-card">
      <span class="ben-icon"></span>
      <div class="ben-title">Tiered Commission Rates</div>
      <div class="ben-desc">The more deals you close, the higher your tier. Top brokers unlock premium commission rates, exclusive listings, and priority client matching.</div>
      <span class="ben-tag">Up to 20% Commission</span>
    </div>
  </div>
</section>

<!-- COMPARE: HousingHub vs Going Alone -->
<section class="pad z reveal">
  <div class="section-eyebrow">The Difference</div>
  <h2 class="section-title">HousingHub vs<br><em>Going It Alone</em></h2>
  <p class="section-sub">See exactly why hundreds of brokers across Uganda chose to partner with HousingHub over working independently.</p>
  <div class="compare-wrap">
    <table class="compare-table">
      <thead>
        <tr>
          <th>Feature</th>
          <th>✦ With HousingHub</th>
          <th>Without HousingHub</th>
        </tr>
      </thead>
      <tbody>
        <tr><td>Access to verified listings</td><td><span class="chk">✓ 500+ properties</span></td><td><span class="crs">✗ Source yourself</span></td></tr>
        <tr><td>Client lead generation</td><td><span class="chk">✓ Platform sends verified leads</span></td><td><span class="crs">✗ Cold calls &amp; word of mouth</span></td></tr>
        <tr><td>Commission tracking</td><td><span class="chk">✓ Automated, real-time</span></td><td><span class="crs">✗ Manual spreadsheets</span></td></tr>
        <tr><td>Commission payment</td><td><span class="chk">✓ Auto-processed on deal close</span></td><td><span class="crs">✗ Wait and chase landlords</span></td></tr>
        <tr><td>Virtual property tours</td><td><span class="chk">✓ Built-in 360° tours</span></td><td><span class="crs">✗ Physical visits only</span></td></tr>
        <tr><td>Digital lease &amp; applications</td><td><span class="chk">✓ Fully online</span></td><td><span class="crs">✗ Paper-based process</span></td></tr>
        <tr><td>Marketing materials</td><td><span class="chk">✓ Provided by HousingHub</span></td><td><span class="crs">✗ Create and fund yourself</span></td></tr>
        <tr><td>Dispute resolution</td><td><span class="chk">✓ HousingHub mediates</span></td><td><span class="crs">✗ You're on your own</span></td></tr>
      </tbody>
    </table>
  </div>
</section>

<!-- FAQ -->
<section class="pad z reveal">
  <div class="section-eyebrow">Questions</div>
  <h2 class="section-title">Broker <em>FAQs</em></h2>
  <div class="faq-list">
    <div class="faq-item"><div class="faq-q">Is it free to join HousingHub as a broker?</div><div class="faq-a">Yes — creating a broker account is completely free. HousingHub earns only when you earn: we take a small platform fee from each successful commission, so our interests are always aligned with yours.</div></div>
    <div class="faq-item"><div class="faq-q">What is the commission rate for brokers?</div><div class="faq-a">Commission rates start at 8% for new brokers and increase based on your deal volume and performance tier. Top-tier brokers earn up to 20% per deal. Rates are agreed upfront with each property owner.</div></div>
    <div class="faq-item"><div class="faq-q">How quickly do I get paid after a deal closes?</div><div class="faq-a">Once a lease is signed and the first payment is received, your commission is released within 3–5 business days directly to your registered mobile money or bank account.</div></div>
    <div class="faq-item"><div class="faq-q">Do I need to be a licensed real estate agent?</div><div class="faq-a">You do not need a formal license to sign up. However, brokers with professional credentials get a verified badge on their profile, which increases client trust and deal volume.</div></div>
    <div class="faq-item"><div class="faq-q">What areas does HousingHub currently cover?</div><div class="faq-a">We currently operate in Kampala, Jinja, and Mukono, with Entebbe, Wakiso, and Mbarara coming soon. Brokers operating in or near these areas will benefit most from the platform right now.</div></div>
    <div class="faq-item"><div class="faq-q">Can I bring my own property listings to the platform?</div><div class="faq-a">Yes. If you have existing relationships with property owners, you can recommend them to list on HousingHub. Once verified, those properties appear in your portfolio and you get first-match priority on client leads.</div></div>
  </div>
</section>

<!-- CTA -->
<section class="pad z reveal" style="padding-top:40px">
  <div class="cta-block">
    <h2>Ready to Start <em>Earning?</em></h2>
    <p>Join hundreds of brokers across Uganda already growing their income with HousingHub. Sign up in minutes — no paperwork, no fees, no waiting.</p>
    <div class="cta-btns">
      <a href="register.php" class="btn-primary">Join as a Broker</a>
      <a href="contact.php" class="btn-secondary">Talk to Our Team</a>
    </div>
  </div>
</section>

<!-- QUICK LINKS -->
<section class="quick-links z reveal">
  <div class="quick-container">
    <div class="quick-col"><h3>Home</h3><a href="index.html">Welcome</a><a href="works.php">How It Works</a></div>
    <div class="quick-col"><h3>Features</h3><a href="virtual.php">Virtual Property Tours</a><a href="visitor.php">Visitor/Guest Management</a><a href="applications.php">Online Tenant Applications</a><a href="reporting.php">Rent/Buy Reporting</a><a href="lease.php">Online Lease</a><a href="maintenance.php">Maintenance</a><a href="rent_collection.php">Rent Collection</a><a href="notifications.php">Smart Notification Center</a><a href="complaints.php">Complaints &amp; Feedback HUB</a><a href="owner_portal.php">Owner Portal &amp; Reporting</a></div>
    <div class="quick-col"><h3>Use Cases</h3><a href="tenant.php">Tenants</a><a href="Broker.php">Brokers</a><a href="staff.php">Staff</a><a href="Visitor.php">Guests</a><a href="propertyowners.php">Property Owners</a><a href="broker.php">Broker-Free</a><a href="employment.php">Employment</a></div>
    <div class="quick-col"><h3>Properties</h3><a href="properties.php">All Properties</a><a href="properties.php?type=Commercial">Commercial</a><a href="properties.php?type=Residential">Residential</a><a href="properties.php?type=Industrial">Industrial</a><a href="properties.php?type=Agricultural">Agricultural</a><a href="properties.php?type=Special+Purpose">Special Purpose</a><a href="properties.php?type=Land">Land</a></div>
    <div class="quick-col"><h3>Account</h3><a href="index.php">Login</a><a href="register.php">Register</a></div>
    <div class="quick-col"><h3>About HousingHub</h3><a href="who.php">Who We Are</a><a href="what.php">What We Do</a><a href="vision.php">Our Vision</a><a href="values.php">Core Values</a><a href="contact.php">Contact Us</a></div>
  </div>
</section>

<footer class="z">&copy; 2026 HousingHub | All Rights Reserved</footer>

<script>
/* DROPDOWNS */
function closeAllMenus(){document.querySelectorAll('.dd-menu.open').forEach(function(m){m.classList.remove('open')});document.querySelectorAll('.dd-btn.open').forEach(function(b){b.classList.remove('open')})}
document.querySelectorAll('.dropdown').forEach(function(dd){var btn=dd.querySelector('.dd-btn'),menu=dd.querySelector('.dd-menu');if(!btn||!menu)return;btn.addEventListener('click',function(e){e.stopPropagation();var isOpen=menu.classList.contains('open');closeAllMenus();if(!isOpen){menu.classList.add('open');btn.classList.add('open')}});menu.addEventListener('mousedown',function(e){e.stopPropagation()});menu.addEventListener('click',function(e){e.stopPropagation()})});
document.addEventListener('click',closeAllMenus);
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeAllMenus()});

/* CURSOR */
var dot=document.getElementById('cur-dot'),ring=document.getElementById('cur-ring'),trail=document.getElementById('cur-trail');
var mx=-200,my=-200,rx=-200,ry=-200,tx=-200,ty=-200;
document.addEventListener('mousemove',function(e){mx=e.clientX;my=e.clientY;dot.style.left=mx+'px';dot.style.top=my+'px'});
(function tick(){rx+=(mx-rx)*.15;ry+=(my-ry)*.15;tx+=(mx-tx)*.06;ty+=(my-ty)*.06;ring.style.left=rx+'px';ring.style.top=ry+'px';trail.style.left=tx+'px';trail.style.top=ty+'px';requestAnimationFrame(tick)})();
document.querySelectorAll('a,button,.ben-card,.testi-card,.hf-card,.tl-item').forEach(function(el){el.addEventListener('mouseenter',function(){document.body.classList.add('cursor-hover')});el.addEventListener('mouseleave',function(){document.body.classList.remove('cursor-hover')})});
document.addEventListener('mousedown',function(){document.body.classList.add('cursor-click')});
document.addEventListener('mouseup',function(){document.body.classList.remove('cursor-click')});

/* PARTICLES */
for(var i=0;i<18;i++){var p=document.createElement('div');p.classList.add('ptcl');var sz=Math.random()*3+1;p.style.cssText='width:'+sz+'px;height:'+sz+'px;left:'+(Math.random()*100)+'%;background:rgba(200,164,60,'+(Math.random()*.5+.15).toFixed(2)+');animation-duration:'+(Math.random()*22+10)+'s;animation-delay:'+(Math.random()*18)+'s;';document.body.appendChild(p)}

/* SCROLL REVEAL */
var ro=new IntersectionObserver(function(entries,obs){entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('visible');obs.unobserve(e.target)}})},{threshold:.08});
document.querySelectorAll('.reveal').forEach(function(el){ro.observe(el)});

/* COUNT-UP */
function easeOutQuart(t){return 1-(--t)*t*t*t}
function animateCount(el){
  var target=parseInt(el.dataset.count);
  var suffix=el.dataset.suffix||'';
  var prefix=el.dataset.prefix||'';
  var duration=1800;
  var start=performance.now();
  el.classList.add('counting');
  function step(now){
    var elapsed=now-start;
    var progress=Math.min(elapsed/duration,1);
    var eased=easeOutQuart(progress);
    var current=Math.round(eased*target);
    var display=target>=1000000?'UGX '+Math.round(current/1000000*10)/10+'M':(target>=1000?current.toLocaleString():current);
    el.textContent=prefix+(target>=1000000?display:display)+suffix;
    if(progress<1){requestAnimationFrame(step)}
    else{
      var final=target>=1000000?'UGX '+target/1000000+'M':(target>=1000?target.toLocaleString():target);
      el.textContent=prefix+(target>=1000000?final:final)+suffix;
      el.classList.remove('counting');
    }
  }
  requestAnimationFrame(step);
}
var countObs=new IntersectionObserver(function(entries){entries.forEach(function(e){if(e.isIntersecting&&!e.target.dataset.counted){e.target.dataset.counted='1';animateCount(e.target)}})},{threshold:0.3});
document.querySelectorAll('[data-count]').forEach(function(el){el.textContent='0';countObs.observe(el)});
window.addEventListener('load',function(){
  setTimeout(function(){
    document.querySelectorAll('.hstat-num[data-count]').forEach(function(el){if(!el.dataset.counted){el.dataset.counted='1';animateCount(el)}});
  },400);
});

/* COMMISSION CALCULATOR */
function calcCommission(){
  var deals=parseInt(document.getElementById('deals').value);
  var val=parseInt(document.getElementById('propval').value);
  var rate=parseInt(document.getElementById('rate').value);
  document.getElementById('deals-val').textContent=deals+' deal'+(deals>1?'s':'');
  document.getElementById('value-val').textContent=val+'M';
  document.getElementById('rate-val').textContent=rate+'%';
  var total=deals*(val*1000000)*(rate/100);
  document.getElementById('calc-result').textContent='UGX '+total.toLocaleString();
  document.getElementById('calc-breakdown').textContent=deals+' deals × UGX '+val+'M × '+rate+'%';
}
calcCommission();

/* FAQ */
document.querySelectorAll('.faq-q').forEach(function(q){q.addEventListener('click',function(){var item=q.parentElement;var wasOpen=item.classList.contains('open');document.querySelectorAll('.faq-item.open').forEach(function(i){i.classList.remove('open')});if(!wasOpen)item.classList.add('open')})});

/* SMOOTH SCROLL */
document.querySelectorAll('a[href^="#"]').forEach(function(a){a.addEventListener('click',function(e){var target=document.querySelector(a.getAttribute('href'));if(target){e.preventDefault();target.scrollIntoView({behavior:'smooth'})}})});
</script>
</body>
</html>