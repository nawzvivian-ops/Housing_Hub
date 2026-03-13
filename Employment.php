<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Careers | HousingHub</title>
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
body.cursor-click #cur-dot{width:5px;height:5px}
body.cursor-click #cur-ring{width:28px;height:28px}
.page-bg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(14,90,200,.18) 0%,transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12) 0%,transparent 50%),var(--ink)}
.page-grid{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:72px 72px}
.ptcl{position:fixed;border-radius:50%;pointer-events:none;z-index:1;animation:pdrift linear infinite}
@keyframes pdrift{0%{transform:translateY(100vh) scale(0);opacity:0}5%{opacity:1}95%{opacity:.5}100%{transform:translateY(-10vh) translateX(50px) scale(1.4);opacity:0}}
.z{position:relative;z-index:10}
.reveal{opacity:0;transform:translateY(24px);transition:opacity .7s ease,transform .7s ease}
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
.dd-divider{height:1px;background:var(--border);margin:6px 0}
nav > a{font-size:12px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--ink);text-decoration:none;padding:10px 14px;border-radius:2px;transition:background .2s}
nav > a:hover{background:rgba(4,9,26,.1)}

/* HERO */
.hero{min-height:80vh;display:flex;align-items:center;padding:100px 60px 80px;position:relative;z-index:10}
.hero-content{max-width:680px}
.hero-eyebrow{font-size:11px;font-weight:500;letter-spacing:4px;text-transform:uppercase;color:var(--gold);display:flex;align-items:center;gap:12px;margin-bottom:24px}
.hero-eyebrow::before{content:"";width:36px;height:1px;background:var(--gold)}
.hero h1{font-family:"Cormorant Garamond",serif;font-size:clamp(48px,7vw,88px);font-weight:700;line-height:1.0;margin-bottom:24px;color:var(--white)}
.hero h1 em{color:var(--gold);font-style:italic}
.hero-sub{font-size:17px;line-height:1.7;color:var(--muted);max-width:520px;margin-bottom:40px}
.hero-btns{display:flex;gap:16px;flex-wrap:wrap}
.btn-primary{padding:15px 34px;background:var(--gold);color:var(--ink);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:2px;transition:all .3s;display:inline-block}
.btn-primary:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,164,60,.35)}
.btn-secondary{padding:15px 34px;border:1px solid rgba(200,164,60,.4);color:var(--gold);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:2px;transition:all .3s;display:inline-block}
.btn-secondary:hover{background:rgba(200,164,60,.08);transform:translateY(-2px)}

/* VALUES */
section{padding:80px 60px;position:relative;z-index:10}
.section-eyebrow{font-size:11px;font-weight:500;letter-spacing:4px;text-transform:uppercase;color:var(--gold);display:flex;align-items:center;gap:12px;margin-bottom:20px}
.section-eyebrow::before{content:"";width:28px;height:1px;background:var(--gold)}
.section-title{font-family:"Cormorant Garamond",serif;font-size:clamp(32px,4vw,52px);font-weight:700;color:var(--white);line-height:1.1;margin-bottom:16px}
.section-title em{color:var(--gold);font-style:italic}
.section-sub{font-size:16px;color:var(--muted);max-width:560px;line-height:1.7;margin-bottom:48px}
.values-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px;margin-bottom:0}
.val-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:24px;text-align:center}
.val-icon{font-size:32px;margin-bottom:12px}
.val-title{font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;color:var(--gold);margin-bottom:6px}
.val-desc{font-size:12px;color:var(--muted);line-height:1.6}

/* JOBS */
.jobs-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:24px}
.job-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:14px;padding:28px;transition:all .4s;position:relative}
.job-card:hover{border-color:var(--gb);background:rgba(200,164,60,.04);transform:translateY(-4px)}
.job-status-badge{position:absolute;top:20px;right:20px;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:4px 12px;border-radius:20px}
.badge-open{background:rgba(34,197,94,.1);color:#86efac;border:1px solid rgba(34,197,94,.2)}
.badge-filled{background:rgba(255,255,255,.05);color:rgba(255,255,255,.3);border:1px solid rgba(255,255,255,.08)}
.job-icon{font-size:28px;margin-bottom:14px}
.job-title{font-family:"Cormorant Garamond",serif;font-size:22px;font-weight:700;color:var(--white);margin-bottom:8px}
.job-desc{font-size:13px;color:var(--muted);line-height:1.6;margin-bottom:16px}
.job-meta{display:flex;flex-direction:column;gap:6px;margin-bottom:20px}
.job-meta-item{font-size:12px;color:rgba(255,255,255,.4);display:flex;gap:8px}
.job-meta-item strong{color:rgba(255,255,255,.6)}
.job-freebies{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:22px}
.freebie-tag{font-size:10px;padding:4px 10px;background:rgba(200,164,60,.08);border:1px solid rgba(200,164,60,.15);color:var(--gold);border-radius:4px;letter-spacing:.5px}
.apply-btn{display:block;text-align:center;padding:12px;background:var(--gold);color:var(--ink);font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:6px;transition:all .3s}
.apply-btn:hover{background:var(--gold-l);transform:translateY(-1px)}
.filled-btn{display:block;text-align:center;padding:12px;background:rgba(255,255,255,.04);color:rgba(255,255,255,.25);font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:6px;cursor:not-allowed;border:1px solid rgba(255,255,255,.06)}

/* SPECULATIVE */
.spec-block{background:rgba(200,164,60,.05);border:1px solid var(--border);border-radius:14px;padding:48px;text-align:center;max-width:680px;margin:0 auto}
.spec-block h3{font-family:"Cormorant Garamond",serif;font-size:32px;font-weight:700;color:var(--white);margin-bottom:12px}
.spec-block h3 em{color:var(--gold);font-style:italic}
.spec-block p{font-size:15px;color:var(--muted);line-height:1.7;margin-bottom:24px}
.spec-email{font-size:16px;font-weight:600;color:var(--gold);text-decoration:none;letter-spacing:1px}

/* PERKS */
.perks-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px}
.perk{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:20px;text-align:center}
.perk-icon{font-size:26px;margin-bottom:8px}
.perk-title{font-size:13px;font-weight:600;color:var(--white)}

footer{padding:32px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}
@media(max-width:900px){
  header,section,.hero,footer{padding-left:24px;padding-right:24px}
  .hero{padding-top:80px;padding-bottom:60px}
  body{cursor:auto}
  #cur-dot,#cur-ring,#cur-trail{display:none}
}
</style>
</head>
<body>
<div id="cur-dot"></div><div id="cur-ring"></div><div id="cur-trail"></div>
<div class="page-bg"></div><div class="page-grid"></div>

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
        <a href="contact.php">Contact Us</a>
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
        <a href="landlord.php">Landlord</a>
        <a href="propertyowners.php">Property Owners</a>
        <a href="broker.php">Broker</a>
        <a href="employment.php">Employment</a>
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

<!-- HERO -->
<section class="hero z">
  <div class="hero-content">
    <div class="hero-eyebrow">Careers at HousingHub</div>
    <h1>Join a Team<br>That <em>Builds</em><br>Uganda's Future.</h1>
    <p class="hero-sub">At HousingHub we believe that great work comes from great people. We value effort, attitude, and dedication over formal qualifications. If you are ready to grow with us &mdash; we are ready for you.</p>
    <div class="hero-btns">
      <a href="#open-roles" class="btn-primary">See Open Roles</a>
      <a href="contact.php" class="btn-secondary">Send Enquiry</a>
    </div>
  </div>
</section>

<!-- WHY WORK WITH US -->
<section class="z reveal">
  <div class="section-eyebrow">Why HousingHub</div>
  <h2 class="section-title">Why Work<br><em>With Us?</em></h2>
  <p class="section-sub">We are more than a company. We are a growing family building the future of property management in Uganda.</p>
  <div class="perks-grid">
    <div class="perk"><div class="perk-icon">&#127775;</div><div class="perk-title">Growth Opportunities</div></div>
    <div class="perk"><div class="perk-icon">&#127829;</div><div class="perk-title">Meals Provided</div></div>
    <div class="perk"><div class="perk-icon">&#128664;</div><div class="perk-title">Transport Allowance</div></div>
    <div class="perk"><div class="perk-icon">&#127979;</div><div class="perk-title">On-the-Job Training</div></div>
    <div class="perk"><div class="perk-icon">&#129309;</div><div class="perk-title">Friendly Team</div></div>
    <div class="perk"><div class="perk-icon">&#128176;</div><div class="perk-title">Stable Monthly Pay</div></div>
    <div class="perk"><div class="perk-icon">&#128084;</div><div class="perk-title">Uniforms Provided</div></div>
    <div class="perk"><div class="perk-icon">&#127919;</div><div class="perk-title">Clear Job Roles</div></div>
  </div>
</section>

<!-- VALUES -->
<section class="z reveal">
  <div class="section-eyebrow">Our Culture</div>
  <h2 class="section-title">What We <em>Value</em></h2>
  <p class="section-sub">No need to worry about formal qualifications &mdash; your attitude, effort, and commitment matter most to us.</p>
  <div class="values-grid">
    <div class="val-card"><div class="val-icon">&#128170;</div><div class="val-title">Hard Work</div><div class="val-desc">We reward dedication and consistent effort above all else.</div></div>
    <div class="val-card"><div class="val-icon">&#128101;</div><div class="val-title">Teamwork</div><div class="val-desc">We win together. Collaboration is at the heart of everything we do.</div></div>
    <div class="val-card"><div class="val-icon">&#127775;</div><div class="val-title">Growth</div><div class="val-desc">Every role at HousingHub is an opportunity to grow and advance.</div></div>
    <div class="val-card"><div class="val-icon">&#128172;</div><div class="val-title">Respect</div><div class="val-desc">Every person on our team is treated with dignity and respect.</div></div>
  </div>
</section>

<!-- OPEN ROLES -->
<section class="z reveal" id="open-roles">
  <div class="section-eyebrow">Open Positions</div>
  <h2 class="section-title">Current <em>Openings</em></h2>
  <p class="section-sub">We are actively hiring for the roles below. Apply now or send a speculative application if you don&rsquo;t see your role listed.</p>
  <div class="jobs-grid">

    <div class="job-card">
      <span class="job-status-badge badge-filled">Filled</span>
      <div class="job-icon">&#129529;</div>
      <h3 class="job-title">Cleaner / Janitor</h3>
      <p class="job-desc">Maintain cleanliness and hygiene of our offices and managed properties. No formal education required.</p>
      <div class="job-meta">
        <div class="job-meta-item"><strong>Location:</strong> Kampala, Uganda</div>
        <div class="job-meta-item"><strong>Type:</strong> Full-time / Part-time</div>
      </div>
      <div class="job-freebies">
        <span class="freebie-tag">Meals Provided</span>
        <span class="freebie-tag">On-the-Job Training</span>
        <span class="freebie-tag">Friendly Environment</span>
      </div>
      <span class="filled-btn">Position Filled</span>
    </div>

    <div class="job-card">
      <span class="job-status-badge badge-open">Open</span>
      <div class="job-icon">&#128737;</div>
      <h3 class="job-title">Security Guard</h3>
      <p class="job-desc">Ensure the safety and security of our properties and staff. Training provided, no formal education required.</p>
      <div class="job-meta">
        <div class="job-meta-item"><strong>Location:</strong> Kampala, Uganda</div>
        <div class="job-meta-item"><strong>Type:</strong> Full-time / Night Shift</div>
      </div>
      <div class="job-freebies">
        <span class="freebie-tag">Training Provided</span>
        <span class="freebie-tag">Uniform Provided</span>
        <span class="freebie-tag">Stable Monthly Pay</span>
      </div>
      <a href="apply.php?job=security-guard" class="apply-btn">Apply Now</a>
    </div>

    <div class="job-card">
      <span class="job-status-badge badge-open">Open</span>
      <div class="job-icon">&#128667;</div>
      <h3 class="job-title">Field / Delivery Assistant</h3>
      <p class="job-desc">Assist with deliveries, errands, and property site visits. Basic literacy preferred but not required.</p>
      <div class="job-meta">
        <div class="job-meta-item"><strong>Location:</strong> Kampala, Uganda</div>
        <div class="job-meta-item"><strong>Type:</strong> Full-time / Part-time</div>
      </div>
      <div class="job-freebies">
        <span class="freebie-tag">Transport Allowance</span>
        <span class="freebie-tag">On-the-Job Training</span>
        <span class="freebie-tag">Friendly Team</span>
      </div>
      <a href="apply.php?job=field-assistant" class="apply-btn">Apply Now</a>
    </div>

    <div class="job-card">
      <span class="job-status-badge badge-open">Open</span>
      <div class="job-icon">&#128222;</div>
      <h3 class="job-title">Receptionist / Office Assistant</h3>
      <p class="job-desc">Greet visitors, answer calls, manage correspondence, and support day-to-day office operations.</p>
      <div class="job-meta">
        <div class="job-meta-item"><strong>Location:</strong> Kampala, Uganda</div>
        <div class="job-meta-item"><strong>Type:</strong> Full-time / Part-time</div>
      </div>
      <div class="job-freebies">
        <span class="freebie-tag">Friendly Environment</span>
        <span class="freebie-tag">On-the-Job Training</span>
        <span class="freebie-tag">Growth Opportunity</span>
      </div>
      <a href="apply.php?job=receptionist" class="apply-btn">Apply Now</a>
    </div>

    <div class="job-card">
      <span class="job-status-badge badge-open">Open</span>
      <div class="job-icon">&#9889;</div>
      <h3 class="job-title">Electrician</h3>
      <p class="job-desc">Handle electrical installations, maintenance, and solutions for our managed properties.</p>
      <div class="job-meta">
        <div class="job-meta-item"><strong>Location:</strong> Jinja, Uganda</div>
        <div class="job-meta-item"><strong>Type:</strong> Full-time / Part-time</div>
      </div>
      <div class="job-freebies">
        <span class="freebie-tag">Transport Allowance</span>
        <span class="freebie-tag">Friendly Team</span>
      </div>
      <a href="apply.php?job=electricians" class="apply-btn">Apply Now</a>
    </div>

    <div class="job-card">
      <span class="job-status-badge badge-open">Open</span>
      <div class="job-icon">&#128167;</div>
      <h3 class="job-title">Plumber</h3>
      <p class="job-desc">Assist with water blockages, pipe installations, and general plumbing challenges across properties.</p>
      <div class="job-meta">
        <div class="job-meta-item"><strong>Location:</strong> Mukono, Uganda</div>
        <div class="job-meta-item"><strong>Type:</strong> Part-time</div>
      </div>
      <div class="job-freebies">
        <span class="freebie-tag">Transport Allowance</span>
      </div>
      <a href="apply.php?job=plumbers" class="apply-btn">Apply Now</a>
    </div>

  </div>
</section>

<!-- SPECULATIVE -->
<section class="z reveal" style="padding-top:20px">
  <div class="spec-block">
    <h3>Don&rsquo;t See Your <em>Role?</em></h3>
    <p>We are always looking for talented and motivated people. Send us a speculative application and we will get back to you when a suitable position opens up.</p>
    <a href="mailto:careers@housinghub.ug" class="spec-email">careers@housinghub.ug</a>
    <div style="margin-top:28px"><a href="contact.php" class="btn-primary">Send Application</a></div>
  </div>
</section>

<section class="z" style="padding:40px 60px 60px">
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:40px;border-top:1px solid var(--border);padding-top:48px">
    <div><h4 style="font-family:Cormorant Garamond,serif;color:var(--gold);font-size:18px;margin-bottom:16px">Use Cases</h4><a href="landlord.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:8px" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Landlord</a><a href="propertyowners.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:8px" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Property Owners</a><a href="broker.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:8px" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Broker</a><a href="employment.php" style="display:block;font-size:12px;color:var(--gold);text-decoration:none" onmouseover="this.style.color='#e0c06a'" onmouseout="this.style.color='#c8a43c'">Employment</a></div>
    <div><h4 style="font-family:Cormorant Garamond,serif;color:var(--gold);font-size:18px;margin-bottom:16px">Features</h4><a href="rent_collection.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:8px" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Rent Collection</a><a href="maintenance.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:8px" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Maintenance</a><a href="virtual.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:8px" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Virtual Tours</a><a href="notifications.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Notifications</a></div>
    <div><h4 style="font-family:Cormorant Garamond,serif;color:var(--gold);font-size:18px;margin-bottom:16px">Company</h4><a href="who.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:8px" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Who We Are</a><a href="contact.php" style="display:block;font-size:12px;color:var(--muted);text-decoration:none;margin-bottom:8px" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Contact</a><a href="policies.html" style="display:block;font-size:12px;color:var(--muted);text-decoration:none" onmouseover="this.style.color='#c8a43c'" onmouseout="this.style.color=''">Policies</a></div>
  </div>
</section>

<footer class="z">&copy; 2026 HousingHub | All Rights Reserved</footer>

<script>
function closeAllMenus(){document.querySelectorAll('.dd-menu.open').forEach(function(m){m.classList.remove('open')});document.querySelectorAll('.dd-btn.open').forEach(function(b){b.classList.remove('open')})}
document.querySelectorAll('.dropdown').forEach(function(dd){var btn=dd.querySelector('.dd-btn');var menu=dd.querySelector('.dd-menu');if(!btn||!menu)return;btn.addEventListener('click',function(e){e.stopPropagation();var isOpen=menu.classList.contains('open');closeAllMenus();if(!isOpen){menu.classList.add('open');btn.classList.add('open')}});menu.addEventListener('mousedown',function(e){e.stopPropagation()});menu.addEventListener('click',function(e){e.stopPropagation()})});
document.addEventListener('click',closeAllMenus);
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeAllMenus()});
const dot=document.getElementById('cur-dot'),ring=document.getElementById('cur-ring'),trail=document.getElementById('cur-trail');
let mx=-200,my=-200,rx=-200,ry=-200,tx=-200,ty=-200;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;dot.style.left=mx+'px';dot.style.top=my+'px';});
(function anim(){rx+=(mx-rx)*.15;ry+=(my-ry)*.15;tx+=(mx-tx)*.06;ty+=(my-ty)*.06;ring.style.left=rx+'px';ring.style.top=ry+'px';trail.style.left=tx+'px';trail.style.top=ty+'px';requestAnimationFrame(anim);})();
document.querySelectorAll('a,button,.job-card,.val-card,.perk').forEach(el=>{el.addEventListener('mouseenter',()=>document.body.classList.add('cursor-hover'));el.addEventListener('mouseleave',()=>document.body.classList.remove('cursor-hover'));});
document.addEventListener('mousedown',()=>document.body.classList.add('cursor-click'));
document.addEventListener('mouseup',()=>document.body.classList.remove('cursor-click'));
for(let i=0;i<18;i++){const p=document.createElement('div');p.classList.add('ptcl');const sz=Math.random()*3+1;p.style.cssText=`width:${sz}px;height:${sz}px;left:${Math.random()*100}%;background:rgba(200,164,60,${(Math.random()*.5+.15).toFixed(2)});animation-duration:${Math.random()*22+10}s;animation-delay:${Math.random()*18}s;`;document.body.appendChild(p);}
const ro=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');ro.unobserve(e.target);}});},{threshold:.08});
document.querySelectorAll('.reveal').forEach(el=>ro.observe(el));
</script>
</body>
</html>