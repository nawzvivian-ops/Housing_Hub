<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Core Values – Housing Hub</title>
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
.logo-text{font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:900;letter-spacing:3px;text-transform:uppercase;color:white;line-height:1}
.logo-slogan{font-size:14px;color:darkblue;font-style:italic;display:block;margin-top:3px}
nav{display:flex;align-items:center;gap:4px;overflow:visible;position:relative;z-index:9001}
nav>a{font-size:12px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:white;text-decoration:none;padding:8px 14px;border-radius:2px;transition:color .3s}
nav>a:hover{color:var(--gold)}
.dropdown{position:relative;overflow:visible;z-index:9002}
.dd-btn{font-family:'Outfit',sans-serif;font-size:12px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:darkblue;background:none;border:none;padding:8px 14px;white-space:nowrap;cursor:pointer;transition:color .3s}
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
.about-hero p{font-size:17px;font-weight:300;color:var(--muted);max-width:650px;margin:24px auto 0;line-height:1.8;opacity:0;animation:fadeUp .9s ease .6s both}

/* INTRO — HOUSING HUB ACROSTIC */
.values-intro{position:relative;z-index:10;padding:90px 8%;border-bottom:1px solid var(--border);background:rgba(200,164,60,.02)}
.values-intro h2{font-family:'Cormorant Garamond',serif;font-size:clamp(28px,3.5vw,44px);font-weight:700;color:var(--white);text-align:center;margin-bottom:10px}
.values-intro h2 em{color:var(--gold);font-style:italic}
.values-intro>.sub{text-align:center;color:var(--muted);font-size:15px;font-weight:300;margin-bottom:60px}
.values-intro>.sub strong{color:var(--gold)}
.acrostic-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:16px}
.acrostic-item{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:28px 20px;display:flex;flex-direction:column;align-items:center;text-align:center;transition:transform .3s,border-color .3s,background .3s}
.acrostic-item:hover{transform:translateY(-5px);border-color:var(--gb);background:rgba(200,164,60,.05)}
.acrostic-letter{font-family:'Cormorant Garamond',serif;font-size:56px;font-weight:700;color:var(--gold);line-height:1;margin-bottom:8px}
.acrostic-item h4{font-family:'Cormorant Garamond',serif;font-size:16px;font-weight:700;color:var(--white);margin-bottom:6px}
.acrostic-item p{font-size:12px;color:var(--muted);line-height:1.6}

/* VALUES GRID */
.values-grid{position:relative;z-index:10;display:grid;grid-template-columns:repeat(4,1fr);border-bottom:1px solid var(--border)}
.value-card{padding:48px 32px;border-bottom:1px solid var(--border);border-right:1px solid var(--border);transition:background .3s,transform .3s;cursor:default;display:flex;flex-direction:column}
.value-card:hover{background:rgba(200,164,60,.05);transform:scale(1.01)}
.value-number{font-family:'Cormorant Garamond',serif;font-size:52px;font-weight:700;color:rgba(200,164,60,.18);line-height:1;margin-bottom:8px}
.value-icon{font-size:34px;margin-bottom:14px}
.value-card h3{font-family:'Cormorant Garamond',serif;color:var(--gold);font-size:22px;font-weight:700;margin-bottom:12px}
.value-card p{color:var(--muted);font-size:14px;line-height:1.85;flex:1}

/* PLEDGE */
.values-pledge{position:relative;z-index:10;padding:90px 8%;text-align:center;border-bottom:1px solid var(--border);background:rgba(14,90,200,.05)}
.values-pledge h2{font-family:'Cormorant Garamond',serif;font-size:clamp(28px,3.5vw,44px);font-weight:700;color:var(--white);margin-bottom:16px}
.values-pledge h2 em{color:var(--gold);font-style:italic}
.values-pledge>p{font-size:15px;font-weight:300;color:var(--muted);max-width:680px;margin:0 auto 52px;line-height:1.8}
.pledge-points{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;max-width:1000px;margin:0 auto}
.pledge-point{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:28px 22px;text-align:left;transition:border-color .3s,background .3s}
.pledge-point:hover{border-color:var(--gb);background:rgba(200,164,60,.05)}
.pledge-point .check{font-size:22px;margin-bottom:12px}
.pledge-point p{font-size:14px;color:var(--muted);line-height:1.7}

/* CTA */
.cta{padding:100px 60px;text-align:center;position:relative;z-index:10;overflow:hidden}
.cta::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 70% 70% at 50% 50%,rgba(37,99,235,.10) 0%,transparent 70%)}
.cta h3{font-family:'Cormorant Garamond',serif;font-size:clamp(34px,5vw,60px);font-weight:700;color:var(--white);position:relative;margin-bottom:14px}
.cta h3 em{color:var(--gold);font-style:italic}
.cta p{font-size:15px;font-weight:300;color:var(--muted);position:relative}
.cta a{display:inline-block;margin-top:40px;background:var(--gold);color:var(--ink);padding:18px 56px;border-radius:3px;font-size:12px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;text-decoration:none;transition:all .3s;position:relative}
.cta a:hover{background:var(--gold-l);transform:translateY(-3px);box-shadow:0 14px 40px rgba(200,164,60,.28)}
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
/* QUICK LINKS */
.quick-links{padding:80px 60px;border-top:1px solid var(--border);position:relative;z-index:10}
.quick-container{display:grid;grid-template-columns:repeat(3,1fr);gap:60px;max-width:1000px;margin:0 auto}
.quick-col h3{font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:700;color:var(--gold);letter-spacing:1px;margin-bottom:18px}
.quick-col a{display:block;font-size:13px;font-weight:300;color:var(--muted);text-decoration:none;padding:5px 0;border-bottom:1px solid transparent;transition:color .3s,border-color .3s}
.quick-col a:hover{color:var(--gold);border-color:rgba(200,164,60,.2)}
footer{padding:28px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}

@keyframes fadeUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}

@media(max-width:1100px){.values-grid{grid-template-columns:repeat(2,1fr)}.acrostic-grid{grid-template-columns:repeat(5,1fr)}}
@media(max-width:900px){
  header{padding:16px 24px;flex-wrap:wrap;gap:12px}
  nav{flex-wrap:wrap}
  .values-grid{grid-template-columns:repeat(2,1fr)}
  .acrostic-grid{grid-template-columns:repeat(2,1fr)}
  .pledge-points{grid-template-columns:repeat(2,1fr)}
  .values-intro,.values-pledge,.cta{padding:80px 6%}
  .quick-links{padding:60px 24px}
  .quick-container{grid-template-columns:1fr;gap:40px}
}
@media(max-width:600px){
  .values-grid{grid-template-columns:1fr}
  .acrostic-grid{grid-template-columns:repeat(2,1fr)}
  .pledge-points{grid-template-columns:1fr 1fr}
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
  <div class="eyebrow">What We Stand For</div>
  <h1>Our Core <em>Values</em></h1>
  <p>The principles that guide every decision we make, every feature we build, and every person we serve.</p>
</div>

<section class="values-intro z reveal">
  <h2>The Meaning Behind <em>HOUSING HUB</em></h2>
  <p class="sub">Each letter of <strong>HOUSING HUB</strong> represents a value that drives us.</p>
  <div class="acrostic-grid">
    <div class="acrostic-item"><div class="acrostic-letter">H</div><h4>Honesty</h4><p>Transparency and integrity in all our operations.</p></div>
    <div class="acrostic-item"><div class="acrostic-letter">O</div><h4>Optimization</h4><p>Streamlining processes to save time and reduce errors.</p></div>
    <div class="acrostic-item"><div class="acrostic-letter">U</div><h4>User-Focused</h4><p>Built with landlords and tenants in mind.</p></div>
    <div class="acrostic-item"><div class="acrostic-letter">S</div><h4>Security</h4><p>Data protection and safe property management.</p></div>
    <div class="acrostic-item"><div class="acrostic-letter">I</div><h4>Innovation</h4><p>Continuously improving with modern technology.</p></div>
    <div class="acrostic-item"><div class="acrostic-letter">N</div><h4>Networking</h4><p>Connecting landlords, tenants, and brokers effectively.</p></div>
    <div class="acrostic-item"><div class="acrostic-letter">G</div><h4>Growth</h4><p>Empowering users to expand and succeed.</p></div>
    <div class="acrostic-item"><div class="acrostic-letter">H</div><h4>Helpfulness</h4><p>Providing support and guidance at every step.</p></div>
    <div class="acrostic-item"><div class="acrostic-letter">U</div><h4>Unity</h4><p>Encouraging collaboration between all stakeholders.</p></div>
    <div class="acrostic-item"><div class="acrostic-letter">B</div><h4>Brilliance</h4><p>Excellence in service, design, and performance.</p></div>
  </div>
</section>

<section class="values-grid z reveal">
  <div class="value-card"><div class="value-number">01</div><div class="value-icon">🔍</div><h3>Transparency</h3><p>We believe in open, honest communication with every user. From pricing to policies to how data is used — nothing is hidden at Housing Hub. We empower users with clear information so they can make confident decisions.</p></div>
  <div class="value-card"><div class="value-number">02</div><div class="value-icon">⚖️</div><h3>Integrity</h3><p>We do what we say and say what we do. Our platform is built on trust — earned through honesty and accountability in every interaction, policy, and transaction processed through Housing Hub.</p></div>
  <div class="value-card"><div class="value-number">03</div><div class="value-icon">🌟</div><h3>Excellence</h3><p>We are committed to delivering the highest quality experience — whether it's the speed of our platform, the accuracy of our reports, or the responsiveness of our support team. Good enough is never enough.</p></div>
  <div class="value-card"><div class="value-number">04</div><div class="value-icon">❤️</div><h3>People First</h3><p>Behind every property is a person — a landlord who has invested their savings, a tenant who calls it home. Every feature we build is designed with real people's lives in mind.</p></div>
  <div class="value-card"><div class="value-number">05</div><div class="value-icon">💡</div><h3>Innovation</h3><p>We challenge the status quo. From virtual property tours to AI-driven analytics, we invest in technology that makes a real difference in how properties are managed across Africa.</p></div>
  <div class="value-card"><div class="value-number">06</div><div class="value-icon">🤝</div><h3>Community</h3><p>We are stronger together. Housing Hub fosters a community where landlords and tenants collaborate, not conflict — creating a rental ecosystem built on mutual respect and shared success.</p></div>
  <div class="value-card"><div class="value-number">07</div><div class="value-icon">🔒</div><h3>Security &amp; Privacy</h3><p>We take the protection of personal and financial data with the utmost seriousness. Industry-standard security ensures your data is always safe, private, and under your control.</p></div>
  <div class="value-card"><div class="value-number">08</div><div class="value-icon">♿</div><h3>Inclusivity</h3><p>Great property management tools should be available to everyone — regardless of location, property size, or technical skill. Housing Hub is designed to be simple, accessible, and affordable for all.</p></div>
</section>

<section class="values-pledge z reveal">
  <h2>Our Pledge <em>to You</em></h2>
  <p>When you use Housing Hub, you can always count on us to uphold these values — consistently and without compromise.</p>
  <div class="pledge-points">
    <div class="pledge-point"><div class="check">✅</div><p>We will always be transparent about our fees, features, and policies.</p></div>
    <div class="pledge-point"><div class="check">✅</div><p>We will respond to support requests within 24 hours, every day.</p></div>
    <div class="pledge-point"><div class="check">✅</div><p>We will never sell your personal data to third parties.</p></div>
    <div class="pledge-point"><div class="check">✅</div><p>We will continuously improve our platform based on your feedback.</p></div>
  </div>
</section>

<section class="cta z reveal">
  <h3>These Values Drive <em>Everything We Do</em></h3>
  <p>Come experience a platform built with purpose and people in mind.</p>
  <a href="register.php">Join Housing Hub</a>
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