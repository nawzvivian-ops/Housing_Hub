<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Visitor & Guest Management | HousingHub</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25)}
body{cursor:none;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden}
#cur-dot{width:8px;height:8px;background:var(--gold);border-radius:50%;position:fixed;z-index:99999;pointer-events:none;transform:translate(-50%,-50%);mix-blend-mode:difference}
#cur-ring{width:20px;height:20px;border:1.5px solid rgba(200,164,60,.7);border-radius:50%;position:fixed;z-index:99998;pointer-events:none;transform:translate(-50%,-50%);transition:width .45s cubic-bezier(.23,1,.32,1),height .45s}
#cur-trail{width:30px;height:30px;border:1px solid rgba(200,164,60,.15);border-radius:50%;position:fixed;z-index:99997;pointer-events:none;transform:translate(-50%,-50%);transition:width .7s,height .7s}
body.cursor-hover #cur-dot{width:8px;height:8px;background:#fff}
body.cursor-hover #cur-ring{width:20px;height:20px;border-color:var(--gold);background:rgba(200,164,60,.06)}
body.cursor-click #cur-dot{width:8px;height:8px}
body.cursor-click #cur-ring{width:20px;height:20px}
.page-bg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(14,90,200,.18) 0%,transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12) 0%,transparent 50%),var(--ink)}
.page-grid{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:72px 72px}
.ptcl{position:fixed;border-radius:50%;pointer-events:none;z-index:1;animation:pdrift linear infinite}
@keyframes pdrift{0%{transform:translateY(100vh) scale(0);opacity:0}5%{opacity:1}95%{opacity:.5}100%{transform:translateY(-10vh) translateX(50px) scale(1.4);opacity:0}}
.z{position:relative;z-index:10}
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

.dual-grid{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:24px;
}
.dual-card{
  background:rgba(255,255,255,.03);
  border:1px solid var(--border);
  border-radius:14px;
  padding:32px;
  transition:all .35s ease;
}
.dual-card:hover{
  border-color:var(--gb);
  transform:translateY(-4px);
  background:rgba(200,164,60,.05);
}
.dual-tag{
  display:inline-block;
  font-size:11px;
  letter-spacing:2px;
  text-transform:uppercase;
  color:var(--gold);
  border:1px solid rgba(200,164,60,.35);
  padding:8px 12px;
  border-radius:999px;
  margin-bottom:18px;
}
.dual-card h3{
  font-family:"Cormorant Garamond",serif;
  font-size:28px;
  color:var(--white);
  margin-bottom:14px;
}
.dual-card p{
  font-size:14px;
  line-height:1.7;
  color:var(--muted);
  margin-bottom:20px;
}
.dual-points{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
}
.dual-points span{
  font-size:12px;
  color:var(--white);
  background:rgba(255,255,255,.05);
  border:1px solid var(--border);
  padding:10px 12px;
  border-radius:999px;
}
html{
  scroll-behavior:smooth;
}

.dual-link{
  display:inline-block;
  text-decoration:none;
  font-size:12px;
  font-weight:700;
  letter-spacing:1px;
  text-transform:uppercase;
  color:var(--ink);
  background:var(--gold);
  padding:12px 16px;
  border-radius:999px;
  transition:all .3s ease;
}
.dual-link:hover{
  background:var(--gold-l);
  transform:translateY(-2px);
}
.dual-link.alt{
  background:transparent;
  color:var(--gold);
  border:1px solid rgba(200,164,60,.35);
}
.dual-link.alt:hover{
  background:rgba(200,164,60,.08);
}

.detail-grid{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:24px;
}
.detail-card{
  background:rgba(255,255,255,.03);
  border:1px solid var(--border);
  border-radius:14px;
  padding:32px;
}
.detail-card h3{
  font-family:"Cormorant Garamond",serif;
  font-size:28px;
  color:var(--white);
  margin-bottom:14px;
}
.detail-card p{
  font-size:14px;
  color:var(--muted);
  line-height:1.7;
  margin-bottom:20px;
}
.detail-list{
  display:grid;
  gap:12px;
}
.detail-list div{
  padding:12px 14px;
  border:1px solid var(--border);
  border-radius:10px;
  background:rgba(255,255,255,.025);
  font-size:13px;
  color:var(--white);
}

.form-wrap{
  background:rgba(255,255,255,.03);
  border:1px solid var(--border);
  border-radius:16px;
  padding:36px;
}
.form-wrap h3{
  font-family:"Cormorant Garamond",serif;
  font-size:30px;
  color:var(--white);
  margin-bottom:12px;
}
.form-wrap p{
  color:var(--muted);
  font-size:14px;
  line-height:1.7;
  margin-bottom:26px;
}
.form-grid{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:20px;
}
.field{
  display:flex;
  flex-direction:column;
  gap:8px;
}
.field.full{
  grid-column:1 / -1;
}
.field label{
  font-size:12px;
  color:var(--gold);
  letter-spacing:1px;
  text-transform:uppercase;
}
.field input,
.field select,
.field textarea{
  width:100%;
  background:rgba(255,255,255,.04);
  border:1px solid var(--border);
  border-radius:10px;
  padding:14px 16px;
  color:var(--white);
  font-family:"Outfit",sans-serif;
  font-size:14px;
  outline:none;
  transition:border-color .3s ease, background .3s ease;
}
.field input:focus,
.field select:focus,
.field textarea:focus{
  border-color:var(--gold);
  background:rgba(255,255,255,.06);
}
.field textarea{
  min-height:130px;
  resize:vertical;
}
.form-actions{
  margin-top:24px;
  display:flex;
  gap:14px;
  flex-wrap:wrap;
}

@media(max-width:900px){
  .detail-grid,
  .form-grid{
    grid-template-columns:1fr;
  }
}
/* â”€â”€ FIXED HEADER â€” cannot scroll with content â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
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
        <a href="index.html#welcome">Welcome</a><a href="works.php">How It Works</a>
        
    </div></div>
    <div class="dropdown"><button class="dd-btn">Features &#9660;</button><div class="dd-menu">
        <a href="virtual.php">Virtual Property Tours</a><a href="Visitor.php">Visitor/Guest Management</a>
        <a href="Applications.php">Online Tenant Applications</a><a href="Reporting.php">Rent/Buy Reporting</a>
        <a href="Lease.php">Online Lease</a><a href="Maintenance.php">Maintenance</a>
        <a href="rent_collection.php">Rent Collection</a><a href="notifications.php">Smart Notification Center</a>
        <a href="complaints.php">Complaints &amp; Feedback HUB</a><a href="Owner_portal.php">Owner Portal &amp; Reporting</a>
        <a href="policies.html">Policies</a>
    </div></div>
    <div class="dropdown"><button class="dd-btn">Use Cases &#9660;</button><div class="dd-menu">
        <a href="Tenant.php">Tenants</a>
        <a href="staff.php">Staff</a>
        <a href="Visitor.php">Guests</a>
        <a href="Propertyowners.php">Property Owners</a>
        <a href="Broker.php">Brokers</a><a href="Employment.php">Employment</a>
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
<h1>Manage <em>Property Viewers</em><br>&amp; <span class="stroke">Tenant Guests</span></h1>
<p class="hero-sub">
  HousingHub's Visitor & Guest Management system helps propertyowners, tenants, agents, and security teams
  digitally manage two important visitor categories: people coming to inspect available properties and
  guests visiting occupied units. Every visit is logged, approved, tracked, and recorded in real time.
</p>

     
    <div class="hero-btns">
      <a href="properties.php" class="btn-primary">Browse Properties</a>
    </div>
    
</section>

    <section class="z reveal">
  <div class="section-eyebrow">Two Visitor Types</div>
  <h2 class="section-title">Built for <em>Inspections</em> &amp; <em>Tenant Guests</em></h2>
  <p class="section-sub">
    HousingHub is designed to manage both business-related visits to available properties and personal visits
    to occupied units, giving propertyowners, tenants, and security full visibility across the entire property.
  </p>

  <div class="dual-grid">
    <div class="dual-card">
      <div class="dual-tag">Inspection Visitors</div>
      <h3>People Coming to View a Property</h3>
      <p>
        This includes prospective tenants, buyers, agents, company representatives, and other approved visitors
        who want to inspect a vacant or listed property before renting, buying, or making a decision.
      </p>
      <div class="dual-points">
  <a href="#inspection-form" class="dual-link">Open Inspection Form</a>
  <a href="#inspection-details" class="dual-link alt">View More Details</a>
</div>
    </div>

    <div class="dual-card">
      <div class="dual-tag">Tenant Guests</div>
      <h3>People Visiting a Tenant</h3>
      <p>
        This includes friends, family members, personal guests, short-stay visitors, domestic support workers,
        and service-related guests visiting a resident in an occupied unit.
      </p>
      <div class="dual-points">
  <a href="#guest-form" class="dual-link">Open Guest Form</a>
  <a href="#guest-details" class="dual-link alt">View More Details</a>
</div>
    </div>
  </div>
</section>

<section class="z reveal">
  <div class="section-eyebrow">Visitor Details</div>
  <h2 class="section-title">More Details for <em>Each Visitor Type</em></h2>
  <p class="section-sub">
    HousingHub captures different details depending on whether the person is coming to inspect a property
    or visiting a tenant, helping staff, landlords, and security teams handle every visit correctly.
  </p>

  <div class="detail-grid">
    <div class="detail-card" id="inspection-details">
      <h3>Property Inspection Visitors</h3>
      <p>
        This category is for prospective tenants, buyers, agents, company representatives, and other approved
        individuals who want to inspect a vacant or listed property.
      </p>
      <div class="detail-list">
        <div>Visitor full name and phone number</div>
        <div>Email address for follow-up communication</div>
        <div>Property or unit to be inspected</div>
        <div>Preferred date and time for inspection</div>
        <div>Purpose of visit or interest type</div>
        <div>Assigned landlord, staff member, or agent</div>
        <div>Arrival and departure tracking</div>
        <div>Notes after inspection or next action</div>
      </div>
    </div>

    <div class="detail-card" id="guest-details">
      <h3>Tenant Guests</h3>
      <p>
        This category is for friends, relatives, personal visitors, short-stay guests, support workers,
        or service visitors coming to an occupied unit.
      </p>
      <div class="detail-list">
        <div>Guest full name and contact number</div>
        <div>Tenant name and unit number</div>
        <div>Relationship to tenant</div>
        <div>Expected date and time of visit</div>
        <div>Expected departure time</div>
        <div>Tenant approval confirmation</div>
        <div>Security check-in and check-out log</div>
        <div>Flagging for suspicious or blocked guests</div>
      </div>
    </div>
  </div>
</section>

<section class="z reveal" id="inspection-form">
  <div class="section-eyebrow">Inspection Form</div>
  <h2 class="section-title">Property <em>Inspection Request</em></h2>
  <p class="section-sub">
    Visitors interested in viewing a property can complete this form to request an inspection and provide
    all necessary details before arrival.
  </p>

  <div class="form-wrap">
    <h3>Inspection Visitor Form</h3>
    <p>Fill in the details below to schedule a property inspection.</p>

    <form action="submit_inspection.php" method="POST">
      <div class="form-grid">
        <div class="field">
          <label for="inspect_name">Full Name</label>
          <input type="text" id="inspect_name" name="inspect_name" required>
        </div>

        <div class="field">
          <label for="inspect_phone">Phone Number</label>
          <input type="text" id="inspect_phone" name="inspect_phone" required>
        </div>

        <div class="field">
          <label for="inspect_email">Email Address</label>
          <input type="email" id="inspect_email" name="inspect_email">
        </div>

        <div class="field">
          <label for="inspect_property">Property / Unit</label>
          <input type="text" id="inspect_property" name="inspect_property" required>
        </div>

        <div class="field">
          <label for="inspect_date">Inspection Date</label>
          <input type="date" id="inspect_date" name="inspect_date" required>
        </div>

        <div class="field">
          <label for="inspect_time">Preferred Time</label>
          <input type="time" id="inspect_time" name="inspect_time" required>
        </div>

        <div class="field">
          <label for="inspect_type">Visitor Type</label>
          <select id="inspect_type" name="inspect_type" required>
            <option value="">Select one</option>
            <option value="Prospective Tenant">Prospective Tenant</option>
            <option value="Buyer">Buyer</option>
            <option value="Agent">Agent</option>
            <option value="Company Representative">Company Representative</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="field">
          <label for="inspect_host">Assigned Staff / Agent</label>
          <input type="text" id="inspect_host" name="inspect_host">
        </div>

        <div class="field full">
          <label for="inspect_purpose">Purpose / Notes</label>
          <textarea id="inspect_purpose" name="inspect_purpose" placeholder="State what property you want to inspect and any other useful details"></textarea>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary">Submit Inspection Request</button>
        <a href="#inspection-details" class="btn-secondary">View Inspection Details</a>
      </div>
    </form>
  </div>
</section>

<section class="z reveal" id="guest-form">
  <div class="section-eyebrow">Guest Form</div>
  <h2 class="section-title">Tenant <em>Guest Registration</em></h2>
  <p class="section-sub">
    Tenants can pre-register expected guests so security teams can verify them quickly and maintain
    a proper digital record for every occupied unit.
  </p>

  <div class="form-wrap">
    <h3>Tenant Guest Form</h3>
    <p>Fill in the guest information below before the visitor arrives.</p>

    <form action="submit_guest.php" method="POST">
      <div class="form-grid">
        <div class="field">
          <label for="guest_name">Guest Full Name</label>
          <input type="text" id="guest_name" name="guest_name" required>
        </div>
          
        <div class="field">
          <label for="guest_email">Guest Email Address</label>
          <input type="email" id="guest_email" name="guest_email">
        </div> 

        <div class="field">
          <label for="guest_phone">Guest Phone Number</label>
          <input type="text" id="guest_phone" name="guest_phone" required>
        </div>

        <div class="field">
          <label for="tenant_name">Tenant Name</label>
          <input type="text" id="tenant_name" name="tenant_name" required>
        </div>

        <div class="field">
          <label for="unit_number">Unit Number</label>
          <input type="text" id="unit_number" name="unit_number" required>
        </div>

        <div class="field">
          <label for="guest_relationship">Relationship to Tenant</label>
          <select id="guest_relationship" name="guest_relationship" required>
            <option value="">Select one</option>
            <option value="Friend">Friend</option>
            <option value="Family">Family</option>
            <option value="Partner">Partner</option>
            <option value="Domestic Staff">Domestic Staff</option>
            <option value="Service Visitor">Service Visitor</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="field">
          <label for="guest_date">Visit Date</label>
          <input type="date" id="guest_date" name="guest_date" required>
        </div>

        <div class="field">
          <label for="guest_time">Expected Arrival Time</label>
          <input type="time" id="guest_time" name="guest_time" required>
        </div>

        <div class="field">
          <label for="guest_departure">Expected Departure Time</label>
          <input type="time" id="guest_departure" name="guest_departure">
        </div>

        <div class="field full">
          <label for="guest_notes">Additional Notes</label>
          <textarea id="guest_notes" name="guest_notes" placeholder="Add any special instructions for security or management"></textarea>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary">Register Guest</button>
        <a href="#guest-details" class="btn-secondary">View Guest Details</a>
      </div>
    </form>
  </div>
</section>

<section class="z reveal">
  <div class="section-eyebrow">The Problem &amp; The Fix</div>
  <h2 class="section-title">Before &amp; <em>After HousingHub</em></h2>
  <p class="section-sub">See how HousingHub transforms the experience for everyone involved.</p>
  <div class="pain-grid">
    <div class="pain-col before"><div class="pain-col-label">&#128683; Before HousingHub</div><div class="pain-item"><span class="pain-icon">&#128683;</span>Security guards writing visitor names in torn exercise books</div><div class="pain-item"><span class="pain-icon">&#128683;</span>No record of who visited which unit or when they left</div><div class="pain-item"><span class="pain-icon">&#128683;</span>Tenants unable to pre-approve guests before they arrive</div><div class="pain-item"><span class="pain-icon">&#128683;</span>Landlords with no visibility of visitor activity on their properties</div><div class="pain-item"><span class="pain-icon">&#128683;</span>No way to flag suspicious or unauthorised repeat visitors</div></div>
    <div class="pain-col after"><div class="pain-col-label">&#10003; With HousingHub</div><div class="pain-item"><span class="pain-icon">&#10004;</span>Digital visitor logbook accessible from any device in real time</div><div class="pain-item"><span class="pain-icon">&#10004;</span>Tenants pre-register expected guests before they arrive</div><div class="pain-item"><span class="pain-icon">&#10004;</span>Security receives instant digital notifications for approved visitors</div><div class="pain-item"><span class="pain-icon">&#10004;</span>Full timestamped entry and exit records per unit and per property</div><div class="pain-item"><span class="pain-icon">&#10004;</span>Flag and block unauthorised or suspicious visitors with one click</div></div>
  </div>
</section>

  
  
<section class="z reveal">
  <div class="section-eyebrow">What You Get</div>
  <h2 class="section-title">Key <em>Features</em></h2>
  <p class="section-sub">Everything you need, built into one powerful platform.</p>
  <div class="features-grid"><div class="feat-card"><div class="feat-icon">&#128274;</div><h3 class="feat-title">Pre-Approved Entry</h3><p class="feat-desc">Tenants invite guests in advance so security can verify them quickly.</p></div><div class="feat-card"><div class="feat-icon">&#128203;</div><h3 class="feat-title">Digital Logbook</h3><p class="feat-desc">Every visitor is logged with name, time, unit visited, and purpose.</p></div><div class="feat-card"><div class="feat-icon">&#128276;</div><h3 class="feat-title">Instant Alerts</h3><p class="feat-desc">Landlords and tenants get notified of visitor arrivals in real time.</p></div><div class="feat-card"><div class="feat-icon">&#128064;</div><h3 class="feat-title">Visitor History</h3><p class="feat-desc">Full searchable history of all visits per property and unit.</p></div><div class="feat-card"><div class="feat-icon">&#128683;</div><h3 class="feat-title">Block Visitors</h3><p class="feat-desc">Flag and block unauthorised individuals from re-entering the property.</p></div><div class="feat-card"><div class="feat-icon">&#128241;</div><h3 class="feat-title">Mobile Access</h3><p class="feat-desc">Security and management access the visitor log from any smartphone.</p></div></div>
</section>
<section class="z reveal"><div class="stats-strip"><div><div class="stat-num">100%</div><div class="stat-label">Digital Records</div></div><div><div class="stat-num">Real-Time</div><div class="stat-label">Notifications</div></div><div><div class="stat-num">Full</div><div class="stat-label">Audit Trail</div></div><div><div class="stat-num">24/7</div><div class="stat-label">Monitoring</div></div></div></section>
<section class="z reveal" style="padding-top:40px">
  <div class="cta-block">
    <h2>Secure Your Property <em>Today.</em></h2>
    <p>Set up digital visitor management on HousingHub and know exactly who is on your property at all times.</p>
    <a href="contact.php" class="btn-primary">Find us</a>
  </div>
</section>

<!-- QUICK LINKS -->
<section class="quick-links z reveal">
  <div class="quick-container">
 
    <div class="quick-col">
      <h3>Home</h3>
      <a href="index.html">Welcome</a>
      <a href="works.php">How It Works</a>
    </div>
 
    <div class="quick-col">
      <h3>Features</h3>
      <a href="virtual.php">Virtual Property Tours</a>
      <a href="Visitor.php">Visitor/Guest Management</a>
      <a href="Applications.php">Online Tenant Applications</a>
      <a href="Reporting.php">Rent/Buy Reporting</a>
      <a href="Lease.php">Online Lease</a>
      <a href="Maintenance.php">Maintenance</a>
      <a href="rent_collection.php">Rent Collection</a>
      <a href="notifications.php">Smart Notification Center</a>
      <a href="complaints.php">Complaints &amp; Feedback HUB</a>
      <a href="Owner_portal.php">Owner Portal &amp; Reporting</a>
      <a href="policies.html">Policies</a>
    </div>
 
    <div class="quick-col">
      <h3>Use Cases</h3>
      <a href="Tenant.php">Tenants</a>
      <a href="staff.php">Staff</a>
      <a href="Visitor.php">Guests</a>
      <a href="Propertyowners.php">Property Owners</a>
      <a href="Broker.php">Brokers</a>
      <a href="Employment.php">Employment</a>
    </div>
 
    <div class="quick-col">
      <h3>Properties</h3>
      <a href="properties.php">All Properties</a>
      <a href="properties.php?type=Commercial">Commercial</a>
      <a href="properties.php?type=Residential">Residential</a>
      <a href="properties.php?type=Industrial">Industrial</a>
      <a href="properties.php?type=Agricultural">Agricultural</a>
      <a href="properties.php?type=Special+Purpose">Special Purpose</a>
      <a href="properties.php?type=Land">Land</a>
    </div>
 
    <div class="quick-col">
      <h3>Account</h3>
      <a href="index.php">Login</a>
      <a href="register.php">Register</a>
    </div>
 
    <div class="quick-col">
      <h3>About HousingHub</h3>
      <a href="who.php">Who We Are</a>
      <a href="what.php">What We Do</a>
      <a href="vision.php">Our Vision</a>
      <a href="values.php">Core Values</a>
      <a href="contact.php">Contact Us</a>
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
document.querySelectorAll('a,button,.feat-card,.step-card').forEach(el=>{el.addEventListener('mouseenter',()=>document.body.classList.add('cursor-hover'));el.addEventListener('mouseleave',()=>document.body.classList.remove('cursor-hover'));});
document.addEventListener('mousedown',()=>document.body.classList.add('cursor-click'));
document.addEventListener('mouseup',()=>document.body.classList.remove('cursor-click'));
for(let i=0;i<18;i++){const p=document.createElement('div');p.classList.add('ptcl');const sz=Math.random()*3+1;p.style.cssText=`width:${sz}px;height:${sz}px;left:${Math.random()*100}%;background:rgba(200,164,60,${(Math.random()*.5+.15).toFixed(2)});animation-duration:${Math.random()*22+10}s;animation-delay:${Math.random()*18}s;`;document.body.appendChild(p);}
const ro=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');ro.unobserve(e.target);}});},{threshold:.08});
document.querySelectorAll('.reveal').forEach(el=>ro.observe(el));
</script>
</body></html>