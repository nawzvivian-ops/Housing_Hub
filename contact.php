<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Contact Us – Housing Hub</title>
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
.logo-text{font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:900;letter-spacing:2px;text-transform:uppercase;color:white;line-height:1}
.logo-slogan{font-size:14px;color:darkblue;font-style:italic;display:block;margin-top:3px}
nav{display:flex;align-items:center;gap:4px;overflow:visible;position:relative;z-index:9001}
nav>a{font-size:12px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:white;text-decoration:none;padding:8px 14px;border-radius:2px;transition:color .3s}
nav>a:hover{opacity:.8}
.dropdown{position:relative;overflow:visible;z-index:9002}
.dd-btn{font-family:'Outfit',sans-serif;font-size:12px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:darkblue;background:none;border:none;padding:8px 14px;white-space:nowrap;cursor:pointer;transition:color .3s}
.dd-btn:hover,.dd-btn.open{color:white}
.dd-menu{display:none;position:absolute;top:calc(100% + 8px);left:0;min-width:230px;z-index:999999;background:rgba(4,9,26,.99);border:1px solid var(--gb);border-radius:5px;padding:6px 0;box-shadow:0 24px 60px rgba(0,0,0,.85)}
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
.about-hero p{font-size:17px;font-weight:300;color:var(--muted);max-width:580px;margin:24px auto 0;line-height:1.8;opacity:0;animation:fadeUp .9s ease .6s both}

/* CONTACT WRAPPER */
.contact-wrapper{position:relative;z-index:10;display:grid;grid-template-columns:1fr 1.5fr;border-bottom:1px solid var(--border)}

/* LEFT INFO */
.contact-info{background:rgba(200,164,60,.06);border-right:1px solid var(--border);padding:70px 50px}
.contact-info h2{font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:700;color:var(--white);margin-bottom:12px}
.contact-info h2 em{color:var(--gold);font-style:italic}
.contact-info>p{font-size:14px;font-weight:300;color:var(--muted);margin-bottom:48px;line-height:1.8}
.info-item{display:flex;align-items:flex-start;gap:18px;margin-bottom:32px}
.info-icon{font-size:22px;min-width:36px;margin-top:2px}
.info-text h4{font-size:13px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.info-text p{font-size:14px;color:var(--muted);line-height:1.7;margin:0}
.social-links{margin-top:48px;padding-top:32px;border-top:1px solid var(--border)}
.social-links h4{font-size:11px;font-weight:600;letter-spacing:3px;text-transform:uppercase;color:var(--gold);margin-bottom:16px}
.social-icons{display:flex;gap:10px}
.social-icons a{display:flex;align-items:center;justify-content:center;width:40px;height:40px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:50%;color:var(--muted);text-decoration:none;font-size:16px;transition:background .3s,border-color .3s,color .3s}
.social-icons a:hover{background:rgba(200,164,60,.15);border-color:var(--gold);color:var(--gold)}

/* RIGHT FORM */
.contact-form{padding:70px 60px;background:rgba(255,255,255,.015)}
.contact-form h2{font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:700;color:var(--white);margin-bottom:10px}
.contact-form h2 em{color:var(--gold);font-style:italic}
.contact-form>p{font-size:14px;font-weight:300;color:var(--muted);margin-bottom:36px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.form-group{display:flex;flex-direction:column;margin-bottom:22px}
.form-group label{font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:8px}
.form-group input,.form-group select,.form-group textarea{padding:13px 18px;border:1px solid var(--border);border-radius:6px;font-size:14px;font-family:'Outfit',sans-serif;color:var(--white);background:rgba(255,255,255,.04);transition:border-color .3s,background .3s}
.form-group input::placeholder,.form-group textarea::placeholder{color:rgba(255,255,255,.2)}
.form-group select{color:var(--muted);cursor:pointer}
.form-group select option{background:#04091a;color:var(--white)}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--gold);background:rgba(200,164,60,.05)}
.form-group textarea{resize:vertical;min-height:130px}
.submit-btn{display:inline-block;padding:16px 48px;background:var(--gold);color:var(--ink);border:none;border-radius:3px;font-family:'Outfit',sans-serif;font-size:12px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;cursor:pointer;transition:all .3s}
.submit-btn:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,164,60,.28)}
.submit-btn:disabled{opacity:.6;transform:none;cursor:not-allowed}
.success-msg{display:none;padding:16px 22px;border-radius:6px;margin-top:18px;font-size:14px;border:1px solid transparent;line-height:1.6}

/* OFFICES */
.offices{position:relative;z-index:10;padding:90px 8%;text-align:center;border-bottom:1px solid var(--border);background:rgba(200,164,60,.02)}
.offices h2{font-family:'Cormorant Garamond',serif;font-size:clamp(28px,3.5vw,44px);font-weight:700;color:var(--white);margin-bottom:56px}
.offices h2 em{color:var(--gold);font-style:italic}
.offices-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.office-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:36px 24px;border-top:2px solid var(--gold);transition:transform .3s,border-color .3s,background .3s;display:flex;flex-direction:column;align-items:center;text-align:center}
.office-card:hover{transform:translateY(-6px);background:rgba(200,164,60,.05);border-color:var(--gb)}
.office-card .flag{font-size:38px;margin-bottom:16px}
.office-card h4{font-family:'Cormorant Garamond',serif;color:var(--gold);font-size:20px;font-weight:700;margin-bottom:12px}
.office-card p{color:var(--muted);font-size:14px;line-height:1.8}

/* FAQ */
.contact-faq{position:relative;z-index:10;padding:90px 10%;border-bottom:1px solid var(--border)}
.contact-faq h2{font-family:'Cormorant Garamond',serif;font-size:clamp(28px,3.5vw,44px);font-weight:700;color:var(--white);text-align:center;margin-bottom:56px}
.contact-faq h2 em{color:var(--gold);font-style:italic}
.faq-item{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:26px 30px;margin-bottom:14px;transition:border-color .3s,background .3s}
.faq-item:hover{border-color:var(--gb);background:rgba(200,164,60,.04)}
.faq-item h4{font-family:'Cormorant Garamond',serif;color:var(--gold);font-size:18px;font-weight:700;margin-bottom:10px}
.faq-item p{color:var(--muted);font-size:14px;line-height:1.8}
.faq-item p strong{color:var(--white)}

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

@media(max-width:1000px){.offices-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:900px){
  header{padding:16px 24px;flex-wrap:wrap;gap:12px}
  nav{flex-wrap:wrap}
  .contact-wrapper{grid-template-columns:1fr}
  .contact-info{border-right:none;border-bottom:1px solid var(--border);padding:50px 30px}
  .contact-form{padding:50px 30px}
  .form-row{grid-template-columns:1fr}
  .offices,.contact-faq,.cta{padding:80px 6%}
  .quick-links{padding:60px 24px}
  .quick-container{grid-template-columns:1fr;gap:40px}
}
@media(max-width:600px){.offices-grid{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<div class="page-bg"></div>
<div class="page-grid"></div>

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
  <div class="eyebrow">Reach Out</div>
  <h1>Contact <em>Us</em></h1>
  <p>We're here to help. Reach out anytime — our team typically responds within 24 hours.</p>
</div>

<section class="contact-wrapper z reveal">
  <div class="contact-info">
    <h2>Get in <em>Touch</em></h2>
    <p>Whether you have a question, need support, or want to partner with us — we'd love to hear from you.</p>
    <div class="info-item">
      <div class="info-icon">📍</div>
      <div class="info-text"><h4>Head Office</h4><p>Plot 14, Kampala Road<br>Kampala, Uganda</p></div>
    </div>
    <div class="info-item">
      <div class="info-icon">📞</div>
      <div class="info-text"><h4>Phone / WhatsApp</h4><p>+256 700 000 000<br>+256 780 000 000</p></div>
    </div>
    <div class="info-item">
      <div class="info-icon">✉️</div>
      <div class="info-text"><h4>Email</h4><p>info@housinghub.ug<br>support@housinghub.ug</p></div>
    </div>
    <div class="info-item">
      <div class="info-icon">⏰</div>
      <div class="info-text"><h4>Working Hours</h4><p>Monday – Friday: 8:00 AM – 6:00 PM<br>Saturday: 9:00 AM – 1:00 PM</p></div>
    </div>
    <div class="social-links">
      <h4>Follow Us</h4>
      <div class="social-icons">
        <a href="#" title="Facebook">f</a>
        <a href="#" title="Twitter/X">𝕏</a>
        <a href="#" title="Instagram">📷</a>
        <a href="#" title="LinkedIn">in</a>
      </div>
    </div>
  </div>

  <div class="contact-form">
    <h2>Send Us a <em>Message</em></h2>
    <p>Fill in the form below and we'll get back to you as soon as possible.</p>
    <form id="contactForm" onsubmit="handleSubmit(event)">
      <div class="form-row">
        <div class="form-group"><label>First Name *</label><input type="text" name="first_name" placeholder="e.g. David" required></div>
        <div class="form-group"><label>Last Name *</label><input type="text" name="last_name" placeholder="e.g. Mugisha" required></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Email Address *</label><input type="email" name="email" placeholder="you@example.com" required></div>
        <div class="form-group"><label>Phone Number</label><input type="tel" name="phone" placeholder="+256 700 000 000"></div>
      </div>
      <div class="form-group">
        <label>I am a... *</label>
        <select name="role" required>
          <option value="">Select your role</option>
          <option>Tenant</option><option>Landlord</option>
          <option>Property Owner</option><option>Broker / Agent</option><option>Other</option>
        </select>
      </div>
      <div class="form-group"><label>Subject *</label><input type="text" name="subject" placeholder="What is your message about?" required></div>
      <div class="form-group"><label>Message *</label><textarea name="message" placeholder="Tell us how we can help you..." required></textarea></div>
      <button type="submit" class="submit-btn" id="submitBtn">Send Message ✈️</button>
      <div class="success-msg" id="successMsg"></div>
    </form>
  </div>
</section>

<section class="offices z reveal">
  <h2>Our <em>Locations</em></h2>
  <div class="offices-grid">
    <div class="office-card"><div class="flag">🏙️</div><h4>Kampala (HQ)</h4><p>Plot 14, Kampala Road<br>+256 700 000 000<br>kampala@housinghub.ug</p></div>
    <div class="office-card"><div class="flag">🏭</div><h4>Jinja Branch</h4><p>Main Street, Jinja<br>+256 700 111 000<br>jinja@housinghub.ug</p></div>
    <div class="office-card"><div class="flag">🌿</div><h4>Mukono Branch</h4><p>Mukono Town Centre<br>+256 700 222 000<br>mukono@housinghub.ug</p></div>
    <div class="office-card"><div class="flag">📧</div><h4>Online / Remote</h4><p>Available 24/7 via our platform<br>info@housinghub.ug<br>support@housinghub.ug</p></div>
  </div>
</section>

<section class="contact-faq z reveal">
  <h2>Frequently Asked <em>Questions</em></h2>
  <div class="faq-item"><h4>How long does it take to get a response?</h4><p>Our support team responds to all inquiries within 24 hours on business days. Urgent matters are typically addressed within 2–4 hours.</p></div>
  <div class="faq-item"><h4>Can I visit your office in person?</h4><p>Yes! Our Kampala headquarters is open Monday–Friday from 8:00 AM to 6:00 PM and on Saturdays from 9:00 AM to 1:00 PM. Walk-ins are welcome.</p></div>
  <div class="faq-item"><h4>I'm a landlord interested in listing my property — who do I contact?</h4><p>Send us an email at <strong>landlords@housinghub.ug</strong> or call our main line. You can also register directly on the platform and start listing immediately.</p></div>
  <div class="faq-item"><h4>Do you offer a demo of the platform?</h4><p>Absolutely! Contact us to schedule a free demo with one of our team members. We'll walk you through all the features relevant to your needs.</p></div>
</section>

<section class="cta z reveal">
  <h3>Ready to Get <em>Started?</em></h3>
  <p>Join thousands of users who manage smarter with Housing Hub.</p>
  <a href="register.php">Get Started Free</a>
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
for(var i=0; i<18; i++){
  var p = document.createElement('div');
  p.className = 'ptcl';
  var sz = Math.random()*3+1;
  p.style.cssText = 'width:'+sz+'px;height:'+sz+'px;left:'+(Math.random()*100)+'%;'
    +'background:rgba(200,164,60,'+(Math.random()*.5+.15).toFixed(2)+');'
    +'animation-duration:'+(Math.random()*22+10)+'s;'
    +'animation-delay:'+(Math.random()*18)+'s;';
  document.body.appendChild(p);
}

/* ── SCROLL REVEAL ───────────────────────────────────────────── */
var ro = new IntersectionObserver(function(entries, obs){
  entries.forEach(function(x){
    if(x.isIntersecting){ x.target.classList.add('visible'); obs.unobserve(x.target); }
  });
}, {threshold: .1});
document.querySelectorAll('.reveal').forEach(function(el){ ro.observe(el); });

/* ── CONTACT FORM SUBMIT ─────────────────────────────────────── */
function handleSubmit(e) {
  e.preventDefault();
  var form = document.getElementById('contactForm');
  var btn  = document.getElementById('submitBtn');
  var msg  = document.getElementById('successMsg');
  btn.disabled = true;
  btn.textContent = 'Sending...';
  msg.style.display = 'none';
  fetch('contact_submit.php', {
    method: 'POST',
    body: new FormData(form)
  })
  .then(function(res){
    return res.text().then(function(text){
      try { return JSON.parse(text); }
      catch(err){ return { success: false, message: text }; }
    });
  })
  .then(function(data){
    msg.style.display = 'block';
    if(data.success){
      msg.style.background = 'rgba(34,197,94,.12)';
      msg.style.borderColor = 'rgba(34,197,94,.3)';
      msg.style.color = '#86efac';
      msg.textContent = '✅ ' + (data.message || 'Message sent! We\'ll get back to you within 24 hours.');
      form.reset();
    } else {
      msg.style.background = 'rgba(239,68,68,.12)';
      msg.style.borderColor = 'rgba(239,68,68,.3)';
      msg.style.color = '#fca5a5';
      msg.textContent = '❌ ' + (data.message || 'Something went wrong. Please try again or email us directly.');
    }
    btn.disabled = false;
    btn.textContent = 'Send Message ✈️';
  })
  .catch(function(){
    msg.style.display = 'block';
    msg.style.background = 'rgba(239,68,68,.12)';
    msg.style.borderColor = 'rgba(239,68,68,.3)';
    msg.style.color = '#fca5a5';
    msg.textContent = '❌ Network error. Please check your connection and try again.';
    btn.disabled = false;
    btn.textContent = 'Send Message ✈️';
  });
}
</script>
</body>
</html>