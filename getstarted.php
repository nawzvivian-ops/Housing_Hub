
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Get Started | HousingHub Property Management</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25)}
body{font-family:'Outfit',sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden;cursor:none}
body::before{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(14,90,200,.18) 0%,transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12) 0%,transparent 50%),var(--ink)}
.page-grid{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:72px 72px}
.ptcl{position:fixed;border-radius:50%;pointer-events:none;z-index:1;animation:pdrift linear infinite}
@keyframes pdrift{0%{transform:translateY(100vh) scale(0);opacity:0}5%{opacity:1}95%{opacity:.5}100%{transform:translateY(-10vh) translateX(50px) scale(1.4);opacity:0}}
#cur-dot{width:8px;height:8px;background:var(--gold);border-radius:50%;position:fixed;z-index:99999;pointer-events:none;transform:translate(-50%,-50%);mix-blend-mode:difference}
#cur-ring{width:20px;height:20px;border:1.5px solid rgba(200,164,60,.7);border-radius:50%;position:fixed;z-index:99998;pointer-events:none;transform:translate(-50%,-50%);transition:width .45s cubic-bezier(.23,1,.32,1),height .45s}
#cur-trail{width:30px;height:30px;border:1px solid rgba(200,164,60,.15);border-radius:50%;position:fixed;z-index:99997;pointer-events:none;transform:translate(-50%,-50%);transition:width .7s,height .7s}
body.cursor-hover #cur-dot{background:#fff}
body.cursor-hover #cur-ring{border-color:var(--gold);background:rgba(200,164,60,.06)}
body.cursor-click #cur-dot{width:5px;height:5px}
body.cursor-click #cur-ring{width:28px;height:28px}
@media(max-width:600px){body{cursor:auto!important}#cur-dot,#cur-ring,#cur-trail{display:none}}
 
/* HEADER */
body{padding-top:100px}
header{position:fixed;top:0;left:0;right:0;z-index:9999;display:flex;justify-content:space-between;align-items:center;padding:16px 60px;background:var(--gold);box-shadow:0 2px 28px rgba(0,0,0,.28)}
.header-logo{display:flex;align-items:center;gap:14px}
.logo-circle{width:55px;height:55px;border-radius:50%;object-fit:cover;border:2px solid var(--gb)}
.logo-text{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:900;letter-spacing:2px;text-transform:uppercase;color:var(--white);line-height:1}
.logo-slogan{font-size:12px;color:darkblue;font-style:italic;display:block;margin-top:2px}
.header-links{display:flex;gap:16px;align-items:center}
.header-links a{font-size:12px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--ink);text-decoration:none;opacity:.7;transition:opacity .2s}
.header-links a:hover{opacity:1}
 
/* HERO */
.hero{position:relative;z-index:10;padding:80px 8% 60px;text-align:center;border-bottom:1px solid var(--border)}
.eyebrow{display:inline-flex;align-items:center;gap:12px;font-size:11px;font-weight:500;letter-spacing:3px;text-transform:uppercase;color:var(--gold);margin-bottom:20px}
.eyebrow::before{content:'';width:32px;height:1px;background:var(--gold)}
.hero h1{font-family:'Cormorant Garamond',serif;font-size:clamp(40px,6vw,72px);font-weight:700;color:var(--white);margin-bottom:18px;line-height:1.05}
.hero h1 em{color:var(--gold);font-style:italic}
.hero-sub{font-size:16px;color:var(--muted);max-width:620px;margin:0 auto 40px;line-height:1.8}
 
/* WHAT YOU GET */
.benefits{position:relative;z-index:10;padding:70px 8%;border-bottom:1px solid var(--border)}
.benefits h2{font-family:'Cormorant Garamond',serif;font-size:clamp(28px,3.5vw,42px);font-weight:700;color:var(--white);text-align:center;margin-bottom:48px}
.benefits h2 em{color:var(--gold);font-style:italic}
.benefits-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.benefit-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:28px;border-top:2px solid var(--gold);transition:all .3s}
.benefit-card:hover{border-color:var(--gb);background:rgba(200,164,60,.05);transform:translateY(-4px)}
.benefit-icon{font-size:32px;margin-bottom:14px}
.benefit-card h3{font-family:'Cormorant Garamond',serif;font-size:19px;font-weight:700;color:var(--white);margin-bottom:8px}
.benefit-card p{font-size:13px;color:var(--muted);line-height:1.7}
 
/* HOW IT WORKS STEPS */
.steps-section{position:relative;z-index:10;padding:70px 8%;background:rgba(200,164,60,.02);border-bottom:1px solid var(--border);text-align:center}
.steps-section h2{font-family:'Cormorant Garamond',serif;font-size:clamp(28px,3.5vw,42px);font-weight:700;color:var(--white);margin-bottom:48px}
.steps-section h2 em{color:var(--gold);font-style:italic}
.steps-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.step-box{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:28px 20px;transition:all .3s}
.step-box:hover{border-color:var(--gb);transform:translateY(-4px)}
.step-num{width:48px;height:48px;border-radius:50%;background:rgba(200,164,60,.15);border:1.5px solid var(--gold);color:var(--gold);font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 16px}
.step-box h4{font-family:'Cormorant Garamond',serif;font-size:17px;font-weight:700;color:var(--white);margin-bottom:8px}
.step-box p{font-size:12px;color:var(--muted);line-height:1.6}
 
/* FORM */
.form-section{position:relative;z-index:10;padding:70px 8%;border-bottom:1px solid var(--border)}
.form-wrap{max-width:860px;margin:0 auto}
.form-section h2{font-family:'Cormorant Garamond',serif;font-size:clamp(28px,3.5vw,42px);font-weight:700;color:var(--white);text-align:center;margin-bottom:10px}
.form-section h2 em{color:var(--gold);font-style:italic}
.form-sub{text-align:center;font-size:14px;color:var(--muted);margin-bottom:40px}
.form-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:16px;padding:40px;position:relative;overflow:hidden}
.form-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,var(--gold),transparent)}
.section-label{font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:700;color:var(--white);margin-bottom:18px;padding-bottom:10px;border-bottom:1px solid var(--border)}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}
.fl{margin-bottom:16px}
.fl label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.fl input,.fl select,.fl textarea{width:100%;padding:12px 14px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:8px;color:var(--white);font-family:'Outfit',sans-serif;font-size:13px;outline:none;transition:border-color .25s}
.fl input:focus,.fl select:focus,.fl textarea:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
.fl input::placeholder,.fl textarea::placeholder{color:rgba(255,255,255,.2)}
.fl select option{background:#04091a;color:#fff}
.fl textarea{resize:vertical;min-height:100px}
.form-sep{border:none;border-top:1px solid var(--border);margin:28px 0}
.btn-submit{width:100%;padding:16px;background:var(--gold);border:none;border-radius:8px;color:var(--ink);font-family:'Outfit',sans-serif;font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;transition:all .3s;margin-top:8px}
.btn-submit:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,164,60,.3)}
.btn-submit:disabled{opacity:.6;cursor:not-allowed;transform:none}
.alert{padding:14px 18px;border-radius:8px;font-size:13px;margin-bottom:20px;font-weight:500;display:none}
.alert.success{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.alert.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
 
/* STATS STRIP */
.stats-strip{position:relative;z-index:10;padding:48px 8%;background:rgba(200,164,60,.04);border-bottom:1px solid var(--border);display:grid;grid-template-columns:repeat(4,1fr);gap:24px;text-align:center}
.stat-n{font-family:'Cormorant Garamond',serif;font-size:44px;font-weight:700;color:var(--gold)}
.stat-l{font-size:11px;color:var(--muted);letter-spacing:1px;margin-top:4px}
 
footer{padding:28px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}
 
@media(max-width:900px){
  header{padding:14px 24px}
  .hero,.benefits,.steps-section,.form-section,.stats-strip{padding-left:6%;padding-right:6%}
  .benefits-grid{grid-template-columns:1fr 1fr}
  .steps-row{grid-template-columns:1fr 1fr}
  .stats-strip{grid-template-columns:1fr 1fr}
  .grid2,.grid3{grid-template-columns:1fr}
  .form-card{padding:24px}
}
@media(max-width:600px){
  .benefits-grid,.steps-row{grid-template-columns:1fr}
}
</style>
</head>
<body>
<div id="cur-dot"></div><div id="cur-ring"></div><div id="cur-trail"></div>
<div class="page-grid"></div>
 
<!-- HEADER -->
<header>
  <div class="header-logo">
    <img src="image/hub.jpg" alt="Logo" class="logo-circle">
    <div>
      <div class="logo-text">Housing Hub</div>
      <span class="logo-slogan">"Your Property, Our Priority"</span>
    </div>
  </div>
  <div class="header-links">
    <a href="properties.php">Properties</a>
    <a href="works.php">How It Works</a>
    <a href="contact.php">Contact</a>
    <a href="index.php">Login</a>
  </div>
</header>
 
<!-- HERO -->
<section class="hero">
  <div class="eyebrow">Property Management Made Simple</div>
  <h1>Let HousingHub Manage<br>Your <em>Properties</em> For You</h1>
  <p class="hero-sub">Stop chasing rent, handling maintenance calls, and managing paperwork. HousingHub's professional team takes over — so you earn more and stress less.</p>
</section>
 
<!-- WHAT YOU GET -->
<section class="benefits">
  <h2>What You <em>Get</em> With HousingHub</h2>
  <div class="benefits-grid">
    <div class="benefit-card"><div class="benefit-icon">💳</div><h3>Automated Rent Collection</h3><p>Rent is collected digitally every month. No chasing tenants, no cash handling — just automatic payments and instant receipts.</p></div>
    <div class="benefit-card"><div class="benefit-icon">👥</div><h3>Tenant Screening & Placement</h3><p>We screen, verify, and place quality tenants in your property. Applications collected digitally with full background details.</p></div>
    <div class="benefit-card"><div class="benefit-icon">🔧</div><h3>Maintenance Management</h3><p>All maintenance requests handled by our team. You get notified and updated — without lifting a finger.</p></div>
    <div class="benefit-card"><div class="benefit-icon">📊</div><h3>Live Owner Dashboard</h3><p>Log in anytime to see your revenue, occupancy, tenant status, and maintenance — all in real time.</p></div>
    <div class="benefit-card"><div class="benefit-icon">📄</div><h3>Digital Lease Management</h3><p>All lease agreements prepared, signed, and stored digitally. Renewals tracked automatically with advance notice.</p></div>
    <div class="benefit-card"><div class="benefit-icon">🛡️</div><h3>Full Accountability</h3><p>Every payment, complaint, and maintenance request is documented. You always have a full record of your portfolio.</p></div>
  </div>
</section>
 
<!-- STATS -->
<div class="stats-strip">
  <div><div class="stat-n">500+</div><div class="stat-l">Properties Managed</div></div>
  <div><div class="stat-n">1,000+</div><div class="stat-l">Happy Tenants</div></div>
  <div><div class="stat-n">95%</div><div class="stat-l">Rent Collected On Time</div></div>
  <div><div class="stat-n">3</div><div class="stat-l">Cities Active</div></div>
</div>
 
<!-- HOW IT WORKS -->
<section class="steps-section">
  <h2>How It <em>Works</em></h2>
  <div class="steps-row">
    <div class="step-box"><div class="step-num">1</div><h4>Submit Application</h4><p>Fill in the form below with your property details and contact information.</p></div>
    <div class="step-box"><div class="step-num">2</div><h4>We Contact You</h4><p>Our team calls you within 24 hours to discuss your property and management needs.</p></div>
    <div class="step-box"><div class="step-num">3</div><h4>Property is Listed</h4><p>We add your property to the HousingHub platform with photos, pricing, and details.</p></div>
    <div class="step-box"><div class="step-num">4</div><h4>We Handle Everything</h4><p>From tenant placement to rent collection — your property is fully managed by us.</p></div>
  </div>
</section>
 
<!-- APPLICATION FORM -->
<section class="form-section">
  <div class="form-wrap">
    <h2>Apply to <em>List Your Property</em></h2>
    <p class="form-sub">Fill in the details below and our team will reach out within 24 hours to get you started.</p>
 
    <div id="alert-box" class="alert"></div>
 
    <div class="form-card">
      <form id="getStartedForm" onsubmit="submitForm(event)">
 
        <!-- OWNER DETAILS -->
        <div class="section-label">👤 Your Details</div>
        <div class="grid2">
          <div class="fl"><label>Full Name *</label><input type="text" name="fullname" placeholder="e.g. David Mugisha" required></div>
          <div class="fl"><label>Phone / WhatsApp *</label><input type="tel" name="phone" placeholder="+256 700 000 000" required></div>
        </div>
        <div class="grid2">
          <div class="fl"><label>Email Address *</label><input type="email" name="email" placeholder="you@example.com" required></div>
          <div class="fl"><label>Occupation / Business</label><input type="text" name="occupation" placeholder="e.g. Business Owner, Civil Servant"></div>
        </div>
        <div class="fl"><label>Your Location / Area</label><input type="text" name="owner_location" placeholder="e.g. Kampala, Jinja, Mukono"></div>
 
        <hr class="form-sep">
 
        <!-- PROPERTY DETAILS -->
        <div class="section-label">🏠 Property Details</div>
        <div class="grid2">
          <div class="fl"><label>Property Name / Reference *</label><input type="text" name="property_name" placeholder="e.g. Sunrise Apartments, Plot 14 Ntinda" required></div>
          <div class="fl">
            <label>Property Type *</label>
            <select name="property_type" required>
              <option value="">— Select type —</option>
              <option>Residential</option>
              <option>Commercial</option>
              <option>Industrial</option>
              <option>Agricultural</option>
              <option>Land</option>
              <option>Special Purpose</option>
            </select>
          </div>
        </div>
        <div class="fl"><label>Property Address *</label><input type="text" name="property_address" placeholder="e.g. Plot 14, Ntinda, Kampala" required></div>
        <div class="grid3">
          <div class="fl"><label>Number of Units *</label><input type="number" name="units" min="1" placeholder="e.g. 4" required></div>
          <div class="fl"><label>Rent per Unit (UGX)</label><input type="number" name="rent_amount" min="0" placeholder="e.g. 500000"></div>
          <div class="fl"><label>Number of Bedrooms</label><input type="number" name="bedrooms" min="0" placeholder="e.g. 2"></div>
        </div>
        <div class="fl">
          <label>Current Status of Property</label>
          <select name="property_status">
            <option value="">— Select —</option>
            <option>Vacant — ready for tenants</option>
            <option>Partially occupied</option>
            <option>Fully occupied — switching management</option>
            <option>Under construction</option>
          </select>
        </div>
        <div class="fl"><label>Available Amenities</label><input type="text" name="amenities" placeholder="e.g. Water, Electricity, WiFi, Parking, Security, Borehole"></div>
        <div class="fl"><label>Brief Description of the Property</label><textarea name="description" placeholder="Tell us more about the property — location, features, condition, any special notes..."></textarea></div>
 
        <hr class="form-sep">
 
        <!-- MANAGEMENT PREFERENCES -->
        <div class="section-label">📋 Management Preferences</div>
        <div class="grid2">
          <div class="fl">
            <label>Services Needed</label>
            <select name="services_needed">
              <option value="">— Select —</option>
              <option>Full Management (rent, tenants, maintenance, all)</option>
              <option>Tenant Placement Only</option>
              <option>Rent Collection Only</option>
              <option>Maintenance Management Only</option>
              <option>Not sure — I need advice</option>
            </select>
          </div>
          <div class="fl">
            <label>When Do You Want to Start?</label>
            <select name="start_timeline">
              <option value="">— Select —</option>
              <option>Immediately</option>
              <option>Within 1 month</option>
              <option>1–3 months</option>
              <option>Just exploring for now</option>
            </select>
          </div>
        </div>
        <div class="fl"><label>How Did You Hear About HousingHub?</label>
          <select name="referral_source">
            <option value="">— Select —</option>
            <option>Google / Internet Search</option>
            <option>Friend or Family Referral</option>
            <option>Social Media</option>
            <option>Existing HousingHub Tenant</option>
            <option>Other</option>
          </select>
        </div>
        <div class="fl"><label>Any Questions or Special Requirements?</label><textarea name="questions" placeholder="Anything specific you'd like to ask or mention..."></textarea></div>
 
        <button type="submit" class="btn-submit" id="submit-btn">🏠 Submit My Property Application</button>
      </form>
    </div>
  </div>
</section>
 
<footer>&copy; 2026 HousingHub | All Rights Reserved</footer>
 
<script>
async function submitForm(e) {
  e.preventDefault();
  const btn   = document.getElementById('submit-btn');
  const alert = document.getElementById('alert-box');
  const form  = document.getElementById('getStartedForm');
 
  btn.disabled = true;
  btn.textContent = 'Submitting...';
  alert.style.display = 'none';
 
  try {
    const res  = await fetch('submit_get_started.php', {
      method: 'POST',
      body: new FormData(form)
    });
    const data = await res.json();
 
    alert.style.display = 'block';
    alert.className = 'alert ' + (data.success ? 'success' : 'error');
    alert.textContent = data.message;
 
    if (data.success) {
      form.reset();
      window.scrollTo({top: 0, behavior: 'smooth'});
    }
  } catch {
    alert.style.display = 'block';
    alert.className = 'alert error';
    alert.textContent = '❌ Network error. Please email us at info@housinghub.ug';
  }
 
  btn.disabled = false;
  btn.textContent = '🏠 Submit My Property Application';
}
 
// Particles
for(let i=0;i<16;i++){const p=document.createElement('div');p.className='ptcl';const sz=Math.random()*3+1;p.style.cssText=`width:${sz}px;height:${sz}px;left:${Math.random()*100}%;background:rgba(200,164,60,${(Math.random()*.5+.15).toFixed(2)});animation-duration:${Math.random()*22+10}s;animation-delay:${Math.random()*18}s;`;document.body.appendChild(p);}
 
// Cursor
const dot=document.getElementById('cur-dot'),ring=document.getElementById('cur-ring'),trail=document.getElementById('cur-trail');
let mx=-200,my=-200,rx=-200,ry=-200,tx=-200,ty=-200;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;dot.style.left=mx+'px';dot.style.top=my+'px';});
(function anim(){rx+=(mx-rx)*.15;ry+=(my-ry)*.15;tx+=(mx-tx)*.06;ty+=(my-ty)*.06;ring.style.left=rx+'px';ring.style.top=ry+'px';trail.style.left=tx+'px';trail.style.top=ty+'px';requestAnimationFrame(anim);})();
document.querySelectorAll('a,button,.benefit-card,.step-box').forEach(el=>{
  el.addEventListener('mouseenter',()=>document.body.classList.add('cursor-hover'));
  el.addEventListener('mouseleave',()=>document.body.classList.remove('cursor-hover'));
});
document.addEventListener('mousedown',()=>document.body.classList.add('cursor-click'));
document.addEventListener('mouseup',()=>document.body.classList.remove('cursor-click'));
</script>
</body>
</html>