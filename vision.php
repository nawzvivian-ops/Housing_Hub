<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Our Vision – Housing Hub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25)}
html{scroll-behavior:smooth}
body{font-family:'Outfit',sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden;cursor:auto!important}

/* BACKGROUND */
.page-bg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(14,90,200,.18) 0%,transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12) 0%,transparent 50%),radial-gradient(ellipse 40% 40% at 50% 50%,rgba(20,60,140,.10) 0%,transparent 60%),var(--ink);animation:atmo 14s ease-in-out infinite alternate}
@keyframes atmo{0%{filter:brightness(1)}100%{filter:brightness(1.1) hue-rotate(6deg)}}
.page-grid{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:72px 72px}
.ptcl{position:fixed;border-radius:50%;pointer-events:none;z-index:1;animation:pdrift linear infinite}
@keyframes pdrift{0%{transform:translateY(100vh) scale(0);opacity:0}5%{opacity:1}95%{opacity:.5}100%{transform:translateY(-10vh) translateX(50px) scale(1.4);opacity:0}}
.z{position:relative;z-index:10}
.reveal{opacity:0;transform:translateY(28px);transition:opacity .8s ease,transform .8s ease}
.reveal.visible{opacity:1;transform:translateY(0)}

/* HEADER */
header{position:sticky;top:0;z-index:9000;display:flex;justify-content:space-between;align-items:center;padding:18px 60px;background:var(--gold);border-bottom:1px solid var(--border);animation:fadeDown .8s ease both;overflow:visible}
@keyframes fadeDown{from{opacity:0;transform:translateY(-16px)}to{opacity:1;transform:translateY(0)}}
.header-logo{display:flex;align-items:center;gap:14px}
.logo-circle{width:65px;height:65px;border-radius:50%;object-fit:cover;border:2px solid var(--gb)}
.logo-text{font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:900;letter-spacing:2px;text-transform:uppercase;color:var(--white);line-height:1}
.logo-slogan{font-size:14px;color:darkblue;font-style:italic;display:block;margin-top:3px}
nav{display:flex;align-items:center;gap:4px;overflow:visible;position:relative;z-index:9001}
nav>a{font-size:12px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:white;text-decoration:none;padding:8px 14px;border-radius:2px;transition:color .3s}
nav>a:hover{opacity:.8}
.dropdown{position:relative;overflow:visible;z-index:9002}
.dd-btn{font-family:'Outfit',sans-serif;font-size:12px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:darkblue;background:none;border:none;padding:8px 14px;border-radius:2px;white-space:nowrap;cursor:pointer;transition:color .3s}
.dd-btn:hover,.dd-btn.open{color:var(--white)}
.dd-menu{display:none;position:absolute;top:calc(100% + 8px);left:0;min-width:230px;z-index:99000;background:rgba(4,9,26,.99);border:1px solid var(--gb);border-radius:5px;padding:6px 0;box-shadow:0 24px 60px rgba(0,0,0,.85)}
.dd-menu.open{display:block}
.dd-menu a{display:block;font-size:12px;font-weight:400;letter-spacing:1px;color:var(--muted);text-decoration:none;padding:11px 22px;transition:color .2s,background .2s;white-space:nowrap}
.dd-menu a:hover{color:var(--gold);background:rgba(200,164,60,.08)}
.dd-divider{height:1px;background:var(--border);margin:5px 0}
.dd-all{color:var(--gold)!important;font-weight:600!important}

/* HERO */
.about-hero{position:relative;z-index:10;text-align:center;padding:120px 8% 100px;border-bottom:1px solid var(--border)}
.about-hero .eyebrow{display:inline-flex;align-items:center;gap:14px;font-size:11px;font-weight:500;letter-spacing:3px;text-transform:uppercase;color:var(--gold);margin-bottom:28px;opacity:0;animation:fadeUp .8s ease .2s both}
.about-hero .eyebrow::before{content:'';width:40px;height:1px;background:var(--gold)}
.about-hero h1{font-family:'Cormorant Garamond',serif;font-size:clamp(48px,7vw,88px);font-weight:700;color:var(--white);opacity:0;animation:fadeUp 1s ease .4s both}
.about-hero h1 em{color:var(--gold);font-style:italic}
.about-hero .vision-tagline{font-size:18px;font-weight:300;color:var(--muted);max-width:640px;margin:24px auto 0;line-height:1.8;font-style:italic;opacity:0;animation:fadeUp .9s ease .6s both}

/* VISION STATEMENT */
.vision-statement{position:relative;z-index:10;padding:90px 10%;text-align:center;border-bottom:1px solid var(--border);background:rgba(200,164,60,.02)}
.vision-statement h2{font-family:'Cormorant Garamond',serif;font-size:clamp(28px,3.5vw,44px);font-weight:700;color:var(--white);margin-bottom:36px}
.vision-statement h2 em{color:var(--gold);font-style:italic}
.vision-quote{font-size:18px;font-style:italic;color:var(--white);border-left:3px solid var(--gold);padding:24px 32px;background:rgba(200,164,60,.06);border-radius:0 8px 8px 0;max-width:820px;margin:0 auto 36px;text-align:left;line-height:1.9}
.vision-statement>p{font-size:15px;font-weight:300;color:var(--muted);max-width:800px;margin:0 auto;line-height:1.9}

/* PILLARS */
.pillars{position:relative;z-index:10;padding:90px 8%;border-bottom:1px solid var(--border)}
.pillars h2{font-family:'Cormorant Garamond',serif;font-size:clamp(28px,3.5vw,44px);font-weight:700;color:var(--white);text-align:center;margin-bottom:56px}
.pillars h2 em{color:var(--gold);font-style:italic}
.pillars-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.pillar-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:40px 26px;text-align:center;border-top:2px solid var(--gold);transition:transform .3s,border-color .3s,background .3s;display:flex;flex-direction:column;align-items:center}
.pillar-card:hover{transform:translateY(-6px);background:rgba(200,164,60,.05);border-color:var(--gb)}
.pillar-icon{font-size:46px;margin-bottom:20px}
.pillar-card h3{font-family:'Cormorant Garamond',serif;color:var(--gold);font-size:20px;font-weight:700;margin-bottom:12px}
.pillar-card p{color:var(--muted);font-size:14px;line-height:1.75;flex:1}

/* MISSION STANDALONE */
.mission-standalone{position:relative;z-index:10;padding:90px 8%;border-bottom:1px solid var(--border);background:rgba(14,90,200,.04)}
.mission-standalone h2{font-family:'Cormorant Garamond',serif;font-size:clamp(28px,3.5vw,44px);font-weight:700;color:var(--white);text-align:center;margin-bottom:10px}
.mission-standalone h2 em{color:var(--gold);font-style:italic}
.section-sub{text-align:center;color:var(--muted);font-size:15px;margin-bottom:48px;font-weight:300}
.mission-quote{font-size:17px;font-style:italic;color:var(--white);border-left:3px solid var(--gold);padding:24px 32px;background:rgba(200,164,60,.06);border-radius:0 8px 8px 0;max-width:820px;margin:0 auto 52px;line-height:1.9}
.mission-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.mission-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:32px 24px;border-bottom:2px solid var(--gold);transition:transform .3s,background .3s;display:flex;flex-direction:column}
.mission-card:hover{transform:translateY(-5px);background:rgba(200,164,60,.05)}
.mission-card .m-icon{font-size:34px;margin-bottom:14px}
.mission-card h4{font-family:'Cormorant Garamond',serif;color:var(--gold);font-size:19px;font-weight:700;margin-bottom:10px}
.mission-card p{color:var(--muted);font-size:14px;line-height:1.7;flex:1}

/* MISSION + STATS */
.mission-section{position:relative;z-index:10;padding:90px 10%;display:grid;grid-template-columns:1fr 1fr;gap:70px;align-items:center;border-bottom:1px solid var(--border)}
.mission-text h2{font-family:'Cormorant Garamond',serif;font-size:clamp(28px,3vw,42px);font-weight:700;color:var(--white);margin-bottom:20px}
.mission-text h2 em{color:var(--gold);font-style:italic}
.mission-text p{font-size:15px;font-weight:300;color:var(--muted);line-height:1.9}
.mission-stats{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.stat-box{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:30px;text-align:center;transition:border-color .3s,background .3s}
.stat-box:hover{border-color:var(--gb);background:rgba(200,164,60,.05)}
.stat-box h3{font-family:'Cormorant Garamond',serif;font-size:44px;font-weight:700;color:var(--gold);line-height:1;margin-bottom:8px}
.stat-box p{font-size:12px;letter-spacing:2px;text-transform:uppercase;color:var(--muted)}

/* ROADMAP */
.roadmap{position:relative;z-index:10;padding:90px 8%;text-align:center;border-bottom:1px solid var(--border);background:rgba(200,164,60,.02)}
.roadmap h2{font-family:'Cormorant Garamond',serif;font-size:clamp(28px,3.5vw,44px);font-weight:700;color:var(--white);margin-bottom:60px}
.roadmap h2 em{color:var(--gold);font-style:italic}
.road-steps{display:grid;grid-template-columns:repeat(5,1fr);gap:16px}
.road-step{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:30px 18px;text-align:center;transition:transform .3s,border-color .3s}
.road-step:hover{transform:translateY(-5px);border-color:var(--gb)}
.road-step .year{color:var(--gold);font-weight:700;font-size:18px;font-family:'Cormorant Garamond',serif;margin-bottom:10px}
.road-step h4{font-family:'Cormorant Garamond',serif;color:var(--white);font-size:17px;font-weight:700;margin-bottom:8px}
.road-step p{color:var(--muted);font-size:13px;line-height:1.6}
.road-step.active{background:rgba(200,164,60,.12);border-color:var(--gold)}
.road-step.active .year{color:var(--gold-l)}
.road-step.active h4{color:var(--white)}
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
/* CTA */
.cta{padding:100px 60px;text-align:center;position:relative;z-index:10;overflow:hidden}
.cta::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 70% 70% at 50% 50%,rgba(37,99,235,.10) 0%,transparent 70%)}
.cta h3{font-family:'Cormorant Garamond',serif;font-size:clamp(34px,5vw,60px);font-weight:700;color:var(--white);position:relative;margin-bottom:14px}
.cta h3 em{color:var(--gold);font-style:italic}
.cta p{font-size:15px;font-weight:300;color:var(--muted);position:relative}
.cta a{display:inline-block;margin-top:40px;background:var(--gold);color:var(--ink);padding:18px 56px;border-radius:3px;font-size:12px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;text-decoration:none;transition:all .3s;position:relative}
.cta a:hover{background:var(--gold-l);transform:translateY(-3px);box-shadow:0 14px 40px rgba(200,164,60,.28)}

/* QUICK LINKS */
.quick-links{padding:80px 60px;border-top:1px solid var(--border);position:relative;z-index:10}
.quick-container{display:grid;grid-template-columns:repeat(3,1fr);gap:60px;max-width:1000px;margin:0 auto}
.quick-col h3{font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:700;color:var(--gold);letter-spacing:1px;margin-bottom:18px}
.quick-col a{display:block;font-size:13px;font-weight:300;color:var(--muted);text-decoration:none;padding:5px 0;border-bottom:1px solid transparent;transition:color .3s,border-color .3s}
.quick-col a:hover{color:var(--gold);border-color:rgba(200,164,60,.2)}
footer{padding:28px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}

@keyframes fadeUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}

@media(max-width:1100px){.pillars-grid,.mission-cards,.road-steps{grid-template-columns:repeat(2,1fr)}}
@media(max-width:900px){
  header{padding:16px 24px;flex-wrap:wrap;gap:12px}
  nav{flex-wrap:wrap}
  .mission-section{grid-template-columns:1fr;gap:40px}
  .vision-statement,.pillars,.mission-standalone,.mission-section,.roadmap,.cta{padding:80px 6%}
  .quick-links{padding:60px 24px}
  .quick-container{grid-template-columns:1fr;gap:40px}
}
@media(max-width:600px){
  .pillars-grid,.mission-cards,.mission-stats,.road-steps{grid-template-columns:1fr 1fr}
}
</style>
</head>
<body>
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
        <a href="index.html#latest-news">Latest News</a>
        <a href="index.html#current-offers">Current Offers</a>
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
        <a href="index.html#landlord">Landlord</a>
        <a href="index.html#property-owner">Property Owner</a>
        <a href="index.html#brokers">Brokers / Property Owners</a>
        <a href="index.html#broker">Broker</a>
        <a href="index.html#employmentSection">Employment</a>
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

<div class="about-hero z">
  <div class="eyebrow">Looking Ahead</div>
  <h1>Our <em>Vision</em></h1>
  <p class="vision-tagline">"To be Africa's most trusted and innovative property management platform."</p>
</div>

<section class="vision-statement z reveal">
  <h2>Where We're <em>Headed</em></h2>
  <div class="vision-quote">"We envision a future where every landlord, tenant, and property owner in Africa has access to digital tools that make housing fair, transparent, and stress-free — regardless of where they live or how big their portfolio is."</div>
  <p>At Housing Hub, we believe technology should work for everyone. Our vision is to democratize property management across East Africa and beyond — ensuring a small landlord in Mukono has access to the same powerful tools as a large property firm in Nairobi.</p>
</section>

<section class="pillars z reveal">
  <h2>The Pillars of Our <em>Vision</em></h2>
  <div class="pillars-grid">
    <div class="pillar-card"><div class="pillar-icon">🌍</div><h3>Pan-African Reach</h3><p>Growing beyond Uganda, aiming to serve landlords and tenants across East and Central Africa with localized, culturally relevant tools.</p></div>
    <div class="pillar-card"><div class="pillar-icon">⚡</div><h3>Digital-First Housing</h3><p>Every rental transaction — from application to lease signing to rent payment — happening online, seamlessly and securely.</p></div>
    <div class="pillar-card"><div class="pillar-icon">🤝</div><h3>Community &amp; Trust</h3><p>A platform where landlords and tenants respect each other, supported by transparent records, fair policies, and real accountability.</p></div>
    <div class="pillar-card"><div class="pillar-icon">🚀</div><h3>Continuous Innovation</h3><p>Investing in AI, automation, and smart data to keep Housing Hub ahead — constantly improving to meet evolving user needs.</p></div>
  </div>
</section>

<section class="mission-standalone z reveal">
  <h2>Our <em>Mission</em></h2>
  <p class="section-sub">The purpose that drives every feature and every team member at Housing Hub.</p>
  <div class="mission-quote">"To provide landlords, property managers, tenants, and brokers with an intelligent, easy-to-use platform that reduces manual work, improves communication, ensures timely rent collection, and delivers full visibility over every property — empowering smarter housing decisions across Africa."</div>
  <div class="mission-cards">
    <div class="mission-card"><div class="m-icon">⚡</div><h4>Reduce Manual Work</h4><p>Automate rent collection, lease renewals, maintenance tracking, and tenant communication so managers can focus on growth.</p></div>
    <div class="mission-card"><div class="m-icon">🔗</div><h4>Connect Everyone</h4><p>Bring landlords, tenants, brokers, and owners onto one shared platform where communication is clear and fully documented.</p></div>
    <div class="mission-card"><div class="m-icon">📊</div><h4>Deliver Full Visibility</h4><p>Real-time insights into income, occupancy, maintenance costs, and tenant satisfaction — anytime, from any device.</p></div>
    <div class="mission-card"><div class="m-icon">🌍</div><h4>Empower Africa</h4><p>Make world-class property management tools accessible to every landlord and tenant in Africa — small or large portfolio.</p></div>
  </div>
</section>

<section class="mission-section z reveal">
  <div class="mission-text">
    <h2>Driven by <em>Purpose</em></h2>
    <p>To provide landlords, property managers, tenants, and brokers with an intelligent platform that reduces manual work, improves communication, ensures timely rent collection, and delivers full visibility over every property — empowering smarter housing decisions across Africa.</p>
  </div>
  <div class="mission-stats">
    <div class="stat-box"><h3>500+</h3><p>Properties on Platform</p></div>
    <div class="stat-box"><h3>5K+</h3><p>Active Tenants</p></div>
    <div class="stat-box"><h3>3</h3><p>Cities Covered</p></div>
    <div class="stat-box"><h3>2030</h3><p>Target: 10 Countries</p></div>
  </div>
</section>

<section class="roadmap z reveal">
  <h2>Our Growth <em>Roadmap</em></h2>
  <div class="road-steps">
    <div class="road-step"><div class="year">2020</div><h4>Founded</h4><p>Launched in Kampala with core property listing features.</p></div>
    <div class="road-step"><div class="year">2022</div><h4>Expanded</h4><p>Added rent collection, maintenance, and tenant portals.</p></div>
    <div class="road-step active"><div class="year">2026 ✓</div><h4>Now</h4><p>Full-suite platform serving Kampala, Jinja &amp; Mukono.</p></div>
    <div class="road-step"><div class="year">2028</div><h4>Grow</h4><p>Expand to Kenya, Tanzania, and Rwanda.</p></div>
    <div class="road-step"><div class="year">2030</div><h4>Lead</h4><p>Africa's #1 property management platform.</p></div>
  </div>
</section>

<section class="cta z reveal">
  <h3>Be Part of the <em>Vision</em></h3>
  <p>Join Housing Hub and help shape the future of property management in Africa.</p>
  <a href="register.php">Join Us Today</a>
</section>

<section class="quick-links z">
  <div class="quick-container">
    <div class="quick-col">
      <h3>Home</h3>
      <a href="index.html#welcome">Welcome</a><a href="index.html#how-it-works">How It Works</a>
      <a href="index.html#testimonials">Testimonials</a><a href="index.html#faqs">FAQs</a>
      <a href="contact.php">Contact Us</a>
    </div>
    <div class="quick-col">
      <h3>Properties</h3>
      <a href="properties.php">All Properties</a><a href="properties.php?type=Commercial">Commercial</a>
      <a href="properties.php?type=Residential">Residential</a><a href="properties.php?type=Industrial">Industrial</a>
      <a href="properties.php?type=Agricultural">Agricultural</a><a href="properties.php?type=Land">Land</a>
    </div>
    <div class="quick-col">
      <h3>Account</h3>
      <a href="index.php">Login</a><a href="register.php">Register</a><a href="policies.html">Policies</a>
      <h3 style="margin-top:30px">About HousingHub</h3>
      <a href="who.php">Who We Are</a><a href="contact.php">Contact</a><a href="index.html#faqs">FAQs</a>
    </div>
  </div>
</section>

<footer class="z">&copy; 2026 HousingHub | All Rights Reserved</footer>

<script>

/* ── DROPDOWNS ─────────────────────────────────────────────────
   Pure JS. No CSS hover. Click opens, click again closes.
   Clicks inside menu stop propagation so links fire normally.
────────────────────────────────────────────────────────────── */
function closeAllMenus() {
  document.querySelectorAll('.dd-menu.open').forEach(function(m){ m.classList.remove('open'); });
  document.querySelectorAll('.dd-btn.open').forEach(function(b){ b.classList.remove('open'); });
}

document.querySelectorAll('.dropdown').forEach(function(dd) {
  var btn  = dd.querySelector('.dd-btn');
  var menu = dd.querySelector('.dd-menu');
  if (!btn || !menu) return;

  btn.addEventListener('click', function(e) {
    e.stopPropagation();
    var isOpen = menu.classList.contains('open');
    closeAllMenus();
    if (!isOpen) {
      menu.classList.add('open');
      btn.classList.add('open');
    }
  });

  // Stop menu clicks reaching document so links can actually fire
  menu.addEventListener('mousedown', function(e) { e.stopPropagation(); });
  menu.addEventListener('click',     function(e) { e.stopPropagation(); });
});

// Click anywhere outside → close
document.addEventListener('click', closeAllMenus);
document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeAllMenus(); });

/* ── PARTICLES ───────────────────────────────────────────────── */
for(var i=0;i<18;i++){
  var p=document.createElement('div');p.className='ptcl';
  var sz=Math.random()*3+1;
  p.style.cssText='width:'+sz+'px;height:'+sz+'px;left:'+(Math.random()*100)+'%;background:rgba(200,164,60,'+(Math.random()*.5+.15).toFixed(2)+');animation-duration:'+(Math.random()*22+10)+'s;animation-delay:'+(Math.random()*18)+'s;';
  document.body.appendChild(p);
}

/* ── SCROLL REVEAL ───────────────────────────────────────────── */
var ro=new IntersectionObserver(function(e,o){e.forEach(function(x){if(x.isIntersecting){x.target.classList.add('visible');o.unobserve(x.target);}});},{threshold:.1});
document.querySelectorAll('.reveal').forEach(function(el){ro.observe(el);});
</script>
</body>
</html>