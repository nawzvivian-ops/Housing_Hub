<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>What We Do – Housing Hub</title>
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
/* HEADER */
header{position:sticky;top:0;z-index:9000;display:flex;justify-content:space-between;align-items:center;padding:18px 60px;background:var(--gold);border-bottom:1px solid var(--border);animation:fadeDown .8s ease both;overflow:visible}
@keyframes fadeDown{from{opacity:0;transform:translateY(-16px)}to{opacity:1;transform:translateY(0)}}
.header-logo{display:flex;align-items:center;gap:14px}
.logo-circle{width:65px;height:65px;border-radius:50%;object-fit:cover;border:2px solid var(--gb)}
.logo-text{font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:900;letter-spacing:2px;text-transform:uppercase;color:var(--white);line-height:1}
.logo-slogan{font-size:14px;color:darkblue;font-style:italic;display:block;margin-top:3px}
nav{display:flex;align-items:center;gap:4px;overflow:visible;position:relative;z-index:9001}
nav>a{font-size:12px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:var(--white);text-decoration:none;padding:8px 14px;border-radius:2px;transition:color .3s}
nav>a:hover{color:var(--white);opacity:.8}
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
.about-hero p{font-size:17px;font-weight:300;color:var(--muted);max-width:640px;margin:24px auto 0;line-height:1.8;opacity:0;animation:fadeUp .9s ease .6s both}

/* INTRO */
.services-intro{position:relative;z-index:10;padding:80px 10%;text-align:center;border-bottom:1px solid var(--border);background:rgba(200,164,60,.02)}
.services-intro h2{font-family:'Cormorant Garamond',serif;font-size:clamp(28px,3.5vw,44px);font-weight:700;color:var(--white);margin-bottom:18px}
.services-intro h2 em{color:var(--gold);font-style:italic}
.services-intro p{font-size:16px;font-weight:300;color:var(--muted);max-width:750px;margin:0 auto;line-height:1.9}

/* SERVICES GRID */
.services-grid{position:relative;z-index:10;display:grid;grid-template-columns:repeat(3,1fr);gap:20px;padding:80px 8%;border-bottom:1px solid var(--border)}
.service-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:36px 28px;border-top:2px solid var(--gold);transition:transform .3s,border-color .3s,background .3s;display:flex;flex-direction:column}
.service-card:hover{transform:translateY(-6px);background:rgba(200,164,60,.05);border-color:var(--gb)}
.service-icon{font-size:36px;margin-bottom:16px;display:block}
.service-card h3{font-family:'Cormorant Garamond',serif;color:var(--gold);font-size:20px;font-weight:700;margin-bottom:12px;line-height:1.2}
.service-card p{color:var(--muted);font-size:14px;line-height:1.75;flex:1}

/* HOW IT WORKS */
.how-it-works{position:relative;z-index:10;padding:100px 10%;text-align:center;border-bottom:1px solid var(--border);background:rgba(14,90,200,.05)}
.how-it-works h2{font-family:'Cormorant Garamond',serif;font-size:clamp(30px,4vw,48px);font-weight:700;color:var(--white);margin-bottom:56px}
.how-it-works h2 em{color:var(--gold);font-style:italic}
.steps-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:30px}
.step-number{width:60px;height:60px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:var(--ink);margin:0 auto 20px}
.step h4{font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:700;color:var(--white);margin-bottom:10px}
.step p{font-size:14px;color:var(--muted);line-height:1.7}

/* WHO WE SERVE */
.who-we-serve{position:relative;z-index:10;padding:100px 10%;text-align:center;border-bottom:1px solid var(--border)}
.who-we-serve h2{font-family:'Cormorant Garamond',serif;font-size:clamp(30px,4vw,48px);font-weight:700;color:var(--white);margin-bottom:48px}
.who-we-serve h2 em{color:var(--gold);font-style:italic}
.serve-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.serve-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:44px 28px;transition:transform .3s,border-color .3s;text-align:center;display:flex;flex-direction:column;align-items:center}
.serve-card:hover{transform:translateY(-6px);border-color:var(--gb);background:rgba(200,164,60,.04)}
.serve-card .icon{font-size:44px;margin-bottom:18px;display:block}
.serve-card h4{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;color:var(--gold);margin-bottom:10px}
.serve-card p{color:var(--muted);font-size:14px;line-height:1.6}

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

@media(max-width:900px){
  header{padding:16px 24px;flex-wrap:wrap;gap:12px}
  nav{flex-wrap:wrap}
  .services-grid{grid-template-columns:repeat(2,1fr);padding:60px 6%}
  .serve-grid{grid-template-columns:repeat(2,1fr)}
  .steps-grid{grid-template-columns:repeat(2,1fr)}
  .who-we-serve,.how-it-works,.services-intro,.cta{padding:80px 6%}
  .quick-links{padding:60px 24px}
  .quick-container{grid-template-columns:1fr;gap:40px}
}
@media(max-width:600px){
  .services-grid{grid-template-columns:1fr}
  .serve-grid{grid-template-columns:1fr 1fr}
  .steps-grid{grid-template-columns:1fr 1fr}
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
  <div class="eyebrow">Our Services</div>
  <h1>What We <em>Do</em></h1>
  <p>We simplify property management from end to end — so you can focus on what matters most.</p>
</div>

<section class="services-intro z reveal">
  <h2>A Complete Property Management <em>Ecosystem</em></h2>
  <p>Housing Hub is more than a platform — it's a complete solution designed to handle every aspect of property management. Whether you're listing a single apartment or overseeing a portfolio of commercial properties, we give you the tools, automation, and support to do it efficiently and confidently.</p>
</section>

<section class="services-grid z reveal">
  <div class="service-card"><div class="service-icon">🏠</div><h3>Property Listing &amp; Management</h3><p>List residential, commercial, industrial, and agricultural properties with ease. Manage availability, pricing, and tenant details from one dashboard.</p></div>
  <div class="service-card"><div class="service-icon">💳</div><h3>Online Rent Collection</h3><p>Accept rent payments digitally, track payment history, send automated reminders, and generate receipts — no more chasing tenants.</p></div>
  <div class="service-card"><div class="service-icon">📋</div><h3>Tenant Applications</h3><p>Receive and process tenant applications online. Screen applicants, review documents, and approve leases from anywhere.</p></div>
  <div class="service-card"><div class="service-icon">🔧</div><h3>Maintenance Management</h3><p>Tenants can log maintenance requests digitally. Track, assign, and resolve issues 3x faster with our structured workflow.</p></div>
  <div class="service-card"><div class="service-icon">📄</div><h3>Online Lease Management</h3><p>Create, sign, and store leases digitally. Set automatic renewal reminders and maintain a full audit trail of all agreements.</p></div>
  <div class="service-card"><div class="service-icon">🔔</div><h3>Smart Notifications</h3><p>Keep everyone informed with automated alerts for rent due dates, lease renewals, maintenance updates, and more.</p></div>
  <div class="service-card"><div class="service-icon">📊</div><h3>Owner Reporting &amp; Analytics</h3><p>Get real-time insights into your property portfolio — income, occupancy rates, expenses, and tenant history at a glance.</p></div>
  <div class="service-card"><div class="service-icon">🏛️</div><h3>Legal &amp; Compliance Support</h3><p>Stay compliant with local property laws. Access legal document templates, compliance checklists, and guided dispute resolution.</p></div>
  <div class="service-card"><div class="service-icon">💬</div><h3>Complaints &amp; Feedback Hub</h3><p>A dedicated channel for tenants to raise concerns and for landlords to respond — professional and fully documented.</p></div>
</section>

<section class="how-it-works z reveal">
  <h2>How It <em>Works</em></h2>
  <div class="steps-grid">
    <div class="step"><div class="step-number">1</div><h4>Create an Account</h4><p>Sign up as a landlord, tenant, or broker in minutes.</p></div>
    <div class="step"><div class="step-number">2</div><h4>List or Find a Property</h4><p>Landlords add properties; tenants browse and apply online.</p></div>
    <div class="step"><div class="step-number">3</div><h4>Sign &amp; Move In</h4><p>Complete lease agreements digitally and finalize onboarding.</p></div>
    <div class="step"><div class="step-number">4</div><h4>Manage Everything</h4><p>Collect rent, handle maintenance, and track reports — all in one place.</p></div>
  </div>
</section>

<section class="who-we-serve z reveal">
  <h2>Who We <em>Serve</em></h2>
  <div class="serve-grid">
    <div class="serve-card"><div class="icon">🏘️</div><h4>Landlords</h4><p>Manage tenants, collect rent, and handle maintenance without the headaches.</p></div>
    <div class="serve-card"><div class="icon">🔑</div><h4>Tenants</h4><p>Find a home, pay rent, and raise issues — all from your phone.</p></div>
    <div class="serve-card"><div class="icon">🤝</div><h4>Brokers</h4><p>List properties, earn commissions, and grow your client base.</p></div>
    <div class="serve-card"><div class="icon">💼</div><h4>Property Owners</h4><p>Get full visibility into your portfolio performance and income.</p></div>
  </div>
</section>

<section class="cta z reveal">
  <h3>Experience the <em>Difference</em></h3>
  <p>Join thousands of users who manage smarter with Housing Hub.</p>
  <a href="index.php">Get Started Free</a>
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