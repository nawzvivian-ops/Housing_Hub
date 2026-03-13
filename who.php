<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Who We Are – Housing Hub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#ffffff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25)}
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

/* NAV & DROPDOWNS — fully clickable */
nav{display:flex;align-items:center;gap:4px;overflow:visible;position:relative;z-index:9001}
nav>a{font-size:12px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:var(--white);text-decoration:none;padding:8px 14px;transition:color .3s}
nav>a:hover{opacity:.8}
.dropdown{position:relative;overflow:visible;z-index:9002}
.dd-btn{display:block;font-family:'Outfit',sans-serif;font-size:12px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:darkblue;background:none;border:none;padding:8px 14px;white-space:nowrap;cursor:pointer;transition:color .3s}
.dd-btn:hover,.dd-btn.open{color:var(--white)}
.dd-menu{display:none;position:absolute;top:calc(100% + 8px);left:0;min-width:230px;z-index:99999;background:rgba(4,9,26,.99);border:1px solid var(--gb);border-radius:5px;padding:6px 0;box-shadow:0 24px 60px rgba(0,0,0,.85)}
.dd-menu.open{display:block}
.dd-menu a{display:block;font-size:12px;font-weight:400;letter-spacing:1px;color:var(--muted);text-decoration:none;padding:11px 22px;transition:color .2s,background .2s;white-space:nowrap}
.dd-menu a:hover{color:var(--gold);background:rgba(200,164,60,.08)}
.dd-divider{height:1px;background:var(--border);margin:5px 0}
.dd-all{color:var(--gold)!important;font-weight:600!important}

/* PAGE SECTIONS */
.about-hero{position:relative;z-index:10;text-align:center;padding:120px 8% 100px;border-bottom:1px solid var(--border)}
.about-hero .eyebrow{display:inline-flex;align-items:center;gap:14px;font-size:11px;font-weight:500;letter-spacing:3px;text-transform:uppercase;color:var(--gold);margin-bottom:28px;opacity:0;animation:fadeUp .8s ease .2s both}
.about-hero .eyebrow::before{content:'';width:40px;height:1px;background:var(--gold)}
.about-hero h1{font-family:'Cormorant Garamond',serif;font-size:clamp(48px,7vw,88px);font-weight:700;color:var(--white);opacity:0;animation:fadeUp 1s ease .4s both}
.about-hero h1 em{color:var(--gold);font-style:italic}
.about-hero p{font-size:17px;font-weight:300;color:var(--muted);max-width:640px;margin:24px auto 0;line-height:1.8;opacity:0;animation:fadeUp .9s ease .6s both}
.story-section{position:relative;z-index:10;padding:100px 10%;display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center;border-bottom:1px solid var(--border)}
.story-label{font-size:11px;font-weight:600;letter-spacing:3px;text-transform:uppercase;color:var(--gold);margin-bottom:20px;display:block}
.story-section h2{font-family:'Cormorant Garamond',serif;font-size:clamp(32px,3.5vw,48px);font-weight:700;color:var(--white);margin-bottom:28px;line-height:1.15}
.story-section h2 em{color:var(--gold);font-style:italic}
.story-section p{font-size:15px;font-weight:300;color:var(--muted);line-height:1.9;margin-bottom:18px}
.story-visual{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.story-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:28px 22px;transition:border-color .4s,transform .4s;cursor:default}
.story-card:hover{border-color:var(--gb);transform:translateY(-4px)}
.story-card .icon{font-size:26px;margin-bottom:14px;display:block}
.story-card h4{font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:700;color:var(--gold);margin-bottom:8px}
.story-card p{font-size:13px;color:var(--muted);line-height:1.6;margin:0}
.milestones{position:relative;z-index:10;padding:100px 10%;background:rgba(200,164,60,.03);border-bottom:1px solid var(--border);text-align:center}
.milestones h2{font-family:'Cormorant Garamond',serif;font-size:clamp(32px,4vw,48px);font-weight:700;color:var(--white);margin-bottom:14px}
.milestones h2 em{color:var(--gold);font-style:italic}
.milestones .sub{font-size:15px;font-weight:300;color:var(--muted);margin-bottom:60px}
.milestone-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:2px;border:1px solid var(--border);border-radius:8px;overflow:hidden}
.milestone-item{padding:48px 28px;background:rgba(255,255,255,.025);position:relative;overflow:hidden;transition:background .4s;border-right:1px solid var(--border)}
.milestone-item:last-child{border-right:none}
.milestone-item::before{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--gold),transparent);transform:scaleX(0);transform-origin:left;transition:transform .5s cubic-bezier(.23,1,.32,1)}
.milestone-item:hover{background:rgba(200,164,60,.05)}
.milestone-item:hover::before{transform:scaleX(1)}
.milestone-item h3{font-family:'Cormorant Garamond',serif;font-size:52px;font-weight:700;color:var(--gold);line-height:1}
.milestone-item p{font-size:12px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-top:12px}
.partners-section{position:relative;z-index:10;padding:80px 0;border-bottom:1px solid var(--border);text-align:center;overflow:hidden}
.partners-header{padding:0 8%;margin-bottom:48px}
.partners-header .eyebrow-sm{font-size:11px;font-weight:600;letter-spacing:3px;text-transform:uppercase;color:var(--gold);display:block;margin-bottom:12px}
.partners-header h2{font-family:'Cormorant Garamond',serif;font-size:clamp(28px,3vw,40px);font-weight:700;color:var(--white);margin-bottom:10px}
.partners-header p{font-size:14px;color:var(--muted)}
.logo-ticker-wrap{overflow:hidden;mask-image:linear-gradient(to right,transparent,black 8%,black 92%,transparent);-webkit-mask-image:linear-gradient(to right,transparent,black 8%,black 92%,transparent)}
.logo-ticker{display:flex;gap:20px;width:max-content;animation:ticker 22s linear infinite}
.logo-ticker:hover{animation-play-state:paused}
@keyframes ticker{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.logo-item{flex-shrink:0}
.logo-placeholder{width:140px;height:80px;border-radius:10px;display:flex;align-items:center;justify-content:center;text-align:center;color:var(--white);font-weight:700;font-size:13px;line-height:1.4;opacity:.75;transition:opacity .3s,transform .3s;border:1px solid rgba(255,255,255,.08)}
.logo-placeholder:hover{opacity:1;transform:scale(1.06)}
.walkthrough-section{position:relative;z-index:10;padding:100px 8%;border-bottom:1px solid var(--border)}
.wt-inner{display:grid;grid-template-columns:1fr 1fr;gap:70px;align-items:center}
.wt-eyebrow{font-size:11px;font-weight:600;letter-spacing:3px;text-transform:uppercase;color:var(--gold);display:block;margin-bottom:16px}
.wt-text h2{font-family:'Cormorant Garamond',serif;font-size:clamp(30px,3.5vw,46px);font-weight:700;color:var(--white);line-height:1.2;margin-bottom:18px}
.wt-text h2 em{color:var(--gold);font-style:italic}
.wt-text p{font-size:15px;font-weight:300;color:var(--muted);line-height:1.85;margin-bottom:28px}
.wt-highlights{list-style:none;padding:0;margin:0}
.wt-highlights li{padding:10px 0;font-size:14px;color:rgba(255,255,255,.75);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px}
.wt-highlights li::before{content:'✦';color:var(--gold);font-size:11px;flex-shrink:0}
.wt-media{position:relative}
.video-frame{border-radius:16px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.5);margin-bottom:20px;border:1px solid var(--border)}
.video-placeholder{background:rgba(255,255,255,.03);border:2px dashed rgba(200,164,60,.3);border-radius:16px;height:260px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--muted);text-align:center;gap:12px;transition:background .3s}
.video-placeholder:hover{background:rgba(200,164,60,.05)}
.play-btn{width:60px;height:60px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;color:var(--ink);cursor:pointer;transition:transform .2s,background .2s;border:none}
.play-btn:hover{transform:scale(1.1);background:var(--gold-l)}
.video-placeholder p{font-size:15px;color:var(--white);margin:0}
.video-placeholder small{font-size:12px;color:var(--muted)}
.slideshow{position:relative;border-radius:16px;overflow:hidden;height:260px;box-shadow:0 24px 64px rgba(0,0,0,.4);border:1px solid var(--border)}
.slide{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .7s ease;pointer-events:none}
.slide.active{opacity:1;pointer-events:auto}
.slide-content{text-align:center;padding:30px}
.slide-icon{font-size:48px;margin-bottom:14px}
.slide-content h3{font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:700;margin-bottom:10px;color:var(--white)}
.slide-content p{font-size:14px;color:rgba(255,255,255,.8)}
.slide-dots{position:absolute;bottom:14px;left:50%;transform:translateX(-50%);display:flex;gap:8px;z-index:5}
.dot{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,.3);cursor:pointer;transition:background .3s}
.dot.active{background:var(--gold)}
.slide-prev,.slide-next{position:absolute;top:50%;transform:translateY(-50%);background:rgba(4,9,26,.6);border:1px solid var(--border);color:var(--gold);font-size:22px;width:36px;height:36px;border-radius:50%;cursor:pointer;z-index:5;transition:background .2s,border-color .2s}
.slide-prev:hover,.slide-next:hover{background:rgba(200,164,60,.15);border-color:var(--gold)}
.slide-prev{left:12px}.slide-next{right:12px}
.cta{padding:100px 60px;text-align:center;position:relative;z-index:10;overflow:hidden}
.cta::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 70% 70% at 50% 50%,rgba(37,99,235,.10) 0%,transparent 70%)}
.cta h3{font-family:'Cormorant Garamond',serif;font-size:clamp(34px,5vw,60px);font-weight:700;color:var(--white);position:relative;margin-bottom:14px}
.cta h3 em{color:var(--gold);font-style:italic}
.cta p{font-size:15px;font-weight:300;color:var(--muted);position:relative}
.cta a{display:inline-block;margin-top:40px;background:var(--gold);color:var(--ink);padding:18px 56px;border-radius:3px;font-size:12px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;text-decoration:none;transition:all .3s;position:relative}
.cta a:hover{background:var(--gold-l);transform:translateY(-3px);box-shadow:0 14px 40px rgba(200,164,60,.28)}
footer{padding:28px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}
.quick-links{padding:80px 60px;border-top:1px solid var(--border);position:relative;z-index:10}
.quick-container{display:grid;grid-template-columns:repeat(3,1fr);gap:60px;max-width:1000px;margin:0 auto}
.quick-col h3{font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:700;color:var(--gold);letter-spacing:1px;margin-bottom:18px}
.quick-col a{display:block;font-size:13px;font-weight:300;color:var(--muted);text-decoration:none;padding:5px 0;border-bottom:1px solid transparent;transition:color .3s,border-color .3s}
.quick-col a:hover{color:var(--gold);border-color:rgba(200,164,60,.2)}

@keyframes fadeUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}

@media(max-width:900px){
  header{padding:16px 24px;flex-wrap:wrap;gap:12px}
  nav{flex-wrap:wrap}
  .story-section{grid-template-columns:1fr;gap:50px;padding:80px 6%}
  .story-visual{grid-template-columns:1fr 1fr}
  .milestone-grid{grid-template-columns:1fr 1fr}
  .wt-inner{grid-template-columns:1fr;gap:40px}
  .milestones,.partners-section,.walkthrough-section,.cta{padding:80px 6%}
  .quick-links{padding:60px 24px}
  .quick-container{grid-template-columns:1fr;gap:40px}
}
@media(max-width:600px){
  .milestone-grid{grid-template-columns:1fr 1fr}
  .story-visual{grid-template-columns:1fr}
  .about-hero{padding:80px 6% 70px}
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
  <div class="eyebrow">About Housing Hub</div>
  <h1>Who We <em>Are</em></h1>
  <p>A passionate team dedicated to making property management simple, transparent, and stress-free for everyone across Uganda.</p>
</div>

<section class="story-section z reveal">
  <div class="story-text">
    <span class="story-label">Our Origin</span>
    <h2>Built to Solve a <em>Real Problem</em></h2>
    <p>Housing Hub was founded in Kampala, Uganda, with one clear goal — to eliminate the stress and confusion that comes with managing and finding property. We saw landlords buried in paperwork, tenants struggling to communicate issues, and property owners unable to track their investments in real time.</p>
    <p>Housing Hub is a modern, all-in-one housing management platform that connects landlords, tenants, brokers, and property owners in a single seamless ecosystem — from listing a property to collecting rent online, from filing a complaint to signing a lease.</p>
    <p>Today, Housing Hub serves hundreds of property managers and thousands of tenants across Uganda, with a growing presence in East Africa.</p>
  </div>
  <div class="story-visual">
    <div class="story-card"><span class="icon">🏠</span><h4>Founded in Kampala</h4><p>Born from firsthand experience with Uganda's property market challenges.</p></div>
    <div class="story-card"><span class="icon">🤝</span><h4>Community First</h4><p>Built for landlords, tenants, brokers, and owners — all in one place.</p></div>
    <div class="story-card"><span class="icon">📱</span><h4>Fully Digital</h4><p>Every process — from leasing to maintenance — handled online.</p></div>
    <div class="story-card"><span class="icon">🌍</span><h4>East Africa Focus</h4><p>Growing presence beyond Uganda into the wider East African region.</p></div>
  </div>
</section>

<section class="milestones z reveal">
  <h2>Our Journey <em>in Numbers</em></h2>
  <p class="sub">Milestones that mark our commitment to excellence</p>
  <div class="milestone-grid">
    <div class="milestone-item"><h3>2025</h3><p>Year Founded</p></div>
    <div class="milestone-item"><h3>100+</h3><p>Properties Managed</p></div>
    <div class="milestone-item"><h3>1,000+</h3><p>Happy Tenants</p></div>
    <div class="milestone-item"><h3>99.9%</h3><p>System Uptime</p></div>
  </div>
</section>

<section class="partners-section z reveal">
  <div class="partners-header">
    <span class="eyebrow-sm">Trusted By</span>
    <h2>Our Partners &amp; Clients</h2>
    <p>We work alongside leading institutions to deliver the best property experience.</p>
  </div>
  <div class="logo-ticker-wrap"><div class="logo-ticker">
    <div class="logo-item"><div class="logo-placeholder" style="background:rgba(245,166,35,.25);">Stanbic<br>Bank</div></div>
    <div class="logo-item"><div class="logo-placeholder" style="background:rgba(0,48,135,.35);">Centenary<br>Bank</div></div>
    <div class="logo-item"><div class="logo-placeholder" style="background:rgba(230,57,70,.25);">Knight<br>Frank</div></div>
    <div class="logo-item"><div class="logo-placeholder" style="background:rgba(45,106,79,.3);">Habitat<br>Uganda</div></div>
    <div class="logo-item"><div class="logo-placeholder" style="background:rgba(200,164,60,.2);">NSSF<br>Uganda</div></div>
    <div class="logo-item"><div class="logo-placeholder" style="background:rgba(107,45,139,.3);">MTN<br>Uganda</div></div>
    <div class="logo-item"><div class="logo-placeholder" style="background:rgba(214,40,40,.25);">Airtel<br>Uganda</div></div>
    <div class="logo-item"><div class="logo-placeholder" style="background:rgba(245,166,35,.25);">Stanbic<br>Bank</div></div>
    <div class="logo-item"><div class="logo-placeholder" style="background:rgba(0,48,135,.35);">Centenary<br>Bank</div></div>
    <div class="logo-item"><div class="logo-placeholder" style="background:rgba(230,57,70,.25);">Knight<br>Frank</div></div>
    <div class="logo-item"><div class="logo-placeholder" style="background:rgba(45,106,79,.3);">Habitat<br>Uganda</div></div>
    <div class="logo-item"><div class="logo-placeholder" style="background:rgba(200,164,60,.2);">NSSF<br>Uganda</div></div>
    <div class="logo-item"><div class="logo-placeholder" style="background:rgba(107,45,139,.3);">MTN<br>Uganda</div></div>
    <div class="logo-item"><div class="logo-placeholder" style="background:rgba(214,40,40,.25);">Airtel<br>Uganda</div></div>
  </div></div>
</section>

<section class="walkthrough-section z reveal">
  <div class="wt-inner">
    <div class="wt-text">
      <span class="wt-eyebrow">See It In Action</span>
      <h2>A Quick Tour of <em>Housing Hub</em></h2>
      <p>Watch how landlords list properties, tenants apply online, and owners track income — all in under 60 seconds.</p>
      <ul class="wt-highlights">
        <li>List &amp; manage properties in minutes</li>
        <li>Collect rent without cash or queues</li>
        <li>Resolve maintenance with one tap</li>
        <li>Real-time reports for property owners</li>
      </ul>
    </div>
    <div class="wt-media">
      <div class="video-frame">
        <div class="video-placeholder" id="videoPlaceholder">
          <button class="play-btn" id="playBtn">&#9654;</button>
          <p>Your Demo Video Goes Here</p>
          <small>Upload to YouTube then paste the embed link</small>
        </div>
      </div>
      <div class="slideshow" id="slideshow">
        <div class="slide active" style="background:linear-gradient(135deg,rgba(14,90,200,.4),rgba(200,164,60,.2))"><div class="slide-content"><div class="slide-icon">🏠</div><h3>List Your Property</h3><p>Add photos, set rent, publish — live in under 5 minutes.</p></div></div>
        <div class="slide" style="background:linear-gradient(135deg,rgba(15,118,110,.4),rgba(200,164,60,.15))"><div class="slide-content"><div class="slide-icon">📝</div><h3>Tenant Applications</h3><p>Tenants apply, you review and approve online. No paperwork.</p></div></div>
        <div class="slide" style="background:linear-gradient(135deg,rgba(124,58,237,.35),rgba(200,164,60,.15))"><div class="slide-content"><div class="slide-icon">💳</div><h3>Rent Collection</h3><p>Auto-reminders, mobile money, instant receipts.</p></div></div>
        <div class="slide" style="background:linear-gradient(135deg,rgba(180,83,9,.35),rgba(200,164,60,.25))"><div class="slide-content"><div class="slide-icon">📊</div><h3>Owner Dashboard</h3><p>See income, vacancies, and expenses — live, any time.</p></div></div>
        <div class="slide-dots" id="slideDots"></div>
        <button class="slide-prev" onclick="moveSlide(-1)">&#8249;</button>
        <button class="slide-next" onclick="moveSlide(1)">&#8250;</button>
      </div>
    </div>
  </div>
</section>

<section class="cta z reveal">
  <h3>Ready to Join the <em>Housing Hub Family?</em></h3>
  <p>Whether you're a landlord, tenant, or broker — we have a place for you.</p>
  <a href="index.php">Get Started Today</a>
</section>

<section class="quick-links z">
  <div class="quick-container">
    <div class="quick-col">
      <h3>Home</h3>
      <a href="index.html#welcome">Welcome</a>
      <a href="index.html#how-it-works">How It Works</a>
      <a href="index.html#testimonials">Testimonials</a>
      <a href="index.html#faqs">FAQs</a>
      <a href="contact.php">Contact Us</a>
    </div>
    <div class="quick-col">
      <h3>Properties</h3>
      <a href="properties.php">All Properties</a>
      <a href="properties.php?type=Commercial">Commercial</a>
      <a href="properties.php?type=Residential">Residential</a>
      <a href="properties.php?type=Industrial">Industrial</a>
      <a href="properties.php?type=Agricultural">Agricultural</a>
      <a href="properties.php?type=Land">Land</a>
    </div>
    <div class="quick-col">
      <h3>Account</h3>
      <a href="index.php">Login</a>
      <a href="register.php">Register</a>
      <a href="policies.html">Policies</a>
      <h3 style="margin-top:30px">About HousingHub</h3>
      <a href="who.php">Who We Are</a>
      <a href="contact.php">Contact</a>
      <a href="index.html#faqs">FAQs</a>
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
  var p=document.createElement('div'); p.classList.add('ptcl');
  var sz=Math.random()*3+1;
  p.style.cssText='width:'+sz+'px;height:'+sz+'px;left:'+(Math.random()*100)+'%;'
    +'background:rgba(200,164,60,'+(Math.random()*.5+.15).toFixed(2)+');'
    +'animation-duration:'+(Math.random()*22+10)+'s;animation-delay:'+(Math.random()*18)+'s;';
  document.body.appendChild(p);
}

/* ── SCROLL REVEAL ───────────────────────────────────────────── */
var ro=new IntersectionObserver(function(e,o){
  e.forEach(function(x){ if(x.isIntersecting){ x.target.classList.add('visible'); o.unobserve(x.target); } });
},{threshold:.1});
document.querySelectorAll('.reveal').forEach(function(el){ ro.observe(el); });

/* ── SLIDESHOW ───────────────────────────────────────────────── */
(function(){
  var slides=document.querySelectorAll('.slide');
  var dotsWrap=document.getElementById('slideDots');
  var current=0, timer;
  slides.forEach(function(_,i){
    var d=document.createElement('div');
    d.className='dot'+(i===0?' active':'');
    d.addEventListener('click',function(){ goTo(i); });
    dotsWrap.appendChild(d);
  });
  function goTo(n){
    slides[current].classList.remove('active');
    dotsWrap.children[current].classList.remove('active');
    current=(n+slides.length)%slides.length;
    slides[current].classList.add('active');
    dotsWrap.children[current].classList.add('active');
    resetTimer();
  }
  function resetTimer(){ clearInterval(timer); timer=setInterval(function(){ goTo(current+1); },4000); }
  window.moveSlide=function(dir){ goTo(current+dir); };
  resetTimer();
  document.getElementById('playBtn').addEventListener('click',function(){
    document.querySelector('.video-frame').style.display='none';
    document.getElementById('slideshow').style.display='block';
  });
})();
</script>
</body>
</html>