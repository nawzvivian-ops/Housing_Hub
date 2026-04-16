<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Join the Elite | HousingHub Partner Verification</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
:root {
    --ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;
    --white:#fff;--muted:rgba(255,255,255,.45);
    --border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

/* ── CURSOR ──────────────────────────────────────────────────── */
@media(pointer:fine){ body { cursor: none; } }
@media(pointer:coarse){ #cur-dot,#cur-ring { display: none; } }

#cur-dot {
    width: 8px; height: 8px;
    background: var(--gold); border-radius: 50%;
    position: fixed; z-index: 99999; pointer-events: none;
    top: -40px; left: -40px;          /* start off-screen, not at 0,0 */
    transform: translate(-50%,-50%);
    mix-blend-mode: difference;
    will-change: left, top;
    transition: width .2s, height .2s, background .2s;
}
#cur-ring {
    width: 22px; height: 22px;
    border: 1.5px solid rgba(200,164,60,.75); border-radius: 50%;
    position: fixed; z-index: 99998; pointer-events: none;
    top: -40px; left: -40px;          /* start off-screen */
    transform: translate(-50%,-50%);
    transition: width .42s cubic-bezier(.23,1,.32,1),
                height .42s cubic-bezier(.23,1,.32,1),
                border-color .3s, background .3s;
    will-change: left, top;
}
body.cursor-hover #cur-dot  { width: 7px; height: 7px; background: #fff; }
body.cursor-hover #cur-ring { width: 28px; height: 28px; border-color: var(--gold); background: rgba(200,164,60,.07); }
body.cursor-click #cur-ring { width: 32px; height: 32px; }

/* ── BASE ────────────────────────────────────────────────────── */
body { font-family:"Outfit",sans-serif; background:var(--ink); color:var(--white); overflow-x:hidden; }
.page-bg { position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(200,164,60,.13) 0%,transparent 55%),var(--ink); }
.page-grid { position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:72px 72px; }
.ptcl { position:fixed;border-radius:50%;pointer-events:none;z-index:1;animation:pdrift linear infinite; }
@keyframes pdrift { 0%{transform:translateY(100vh) scale(0);opacity:0}5%{opacity:1}100%{transform:translateY(-10vh) scale(1.4);opacity:0} }
.z { position:relative;z-index:10; }
.reveal { opacity:0;transform:translateY(22px);transition:opacity .7s,transform .7s; }
.reveal.visible { opacity:1;transform:translateY(0); }

/* ── HEADER ──────────────────────────────────────────────────── */
header {
    position:sticky;top:0;z-index:9000;
    display:flex;justify-content:space-between;align-items:center;
    padding:15px 40px;
    background:rgba(4,9,26,.98);border-bottom:1px solid var(--border);
    backdrop-filter:blur(15px);
}
.logo-text {
    font-family:"Cormorant Garamond",serif;font-size:24px;font-weight:700;
    letter-spacing:4px;text-transform:uppercase;color:var(--gold);
    position:absolute;left:50%;transform:translateX(-50%);
}
.back-btn {
    font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;
    color:var(--gold);text-decoration:none;padding:10px 20px;
    border:1px solid var(--border);border-radius:4px;
    transition:.3s;display:flex;align-items:center;gap:10px;
    background:transparent;z-index:9001;
}
.back-btn:hover { background:var(--gb);border-color:var(--gold);color:var(--white);transform:translateX(-5px); }

/* ── HERO ────────────────────────────────────────────────────── */
.hero { padding:100px 60px 40px;text-align:center;position:relative;z-index:10; }
.hero h2 { font-family:"Cormorant Garamond",serif;font-size:clamp(40px,6vw,72px);margin-bottom:20px;color:var(--white);line-height:1.1; }
.hero h2 em { color:var(--gold);font-style:italic; }
.hero p { font-size:18px;color:var(--muted);max-width:800px;margin:0 auto 40px;line-height:1.7; }

/* ── ROADMAP ─────────────────────────────────────────────────── */
.roadmap { display:flex;justify-content:center;gap:20px;flex-wrap:wrap;margin-bottom:80px;padding:0 40px;position:relative;z-index:10; }
.step { background:rgba(255,255,255,.03);border:1px solid var(--border);padding:25px;border-radius:12px;width:260px;text-align:center;transition:.3s; }
.step:hover { border-color:var(--gold);background:rgba(200,164,60,.05);transform:translateY(-5px); }
.step-num { font-family:"Cormorant Garamond",serif;font-size:32px;color:var(--gold);font-weight:700;margin-bottom:10px;display:block; }

/* ── AGREEMENT ───────────────────────────────────────────────── */
.agreement-container { max-width:900px;margin:0 auto 20px;padding:40px;background:rgba(255,255,255,.02);border:1px solid var(--muted);border-radius:5px;position:relative;z-index:10; }
.terms-box { height:300px;overflow-y:auto;background:rgba(0,0,0,.2);padding:20px;border-radius:1px;font-size:14px;line-height:1.6;color:var(--muted);margin-bottom:25px;border:1px solid var(--border); }
.terms-box::-webkit-scrollbar { width:10px; }
.terms-box::-webkit-scrollbar-thumb { background:var(--gold);border-radius:10px; }
.checkbox-wrapper { display:flex;align-items:center;gap:15px;cursor:pointer;user-select:none; }
.checkbox-wrapper input[type="checkbox"] { width:20px;height:20px;accent-color:var(--gold);cursor:pointer; }

/* ── FORMS OVERLAY ───────────────────────────────────────────── */
#forms-overlay {
    transition:opacity .5s ease,filter .5s ease;
    opacity:.3;pointer-events:none;filter:blur(5px);
}
#forms-overlay.active { opacity:1;pointer-events:all;filter:blur(0); }

/* ── FORMS ───────────────────────────────────────────────────── */
#verification { padding:60px;position:relative;z-index:10; }
.forms-container { display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:40px;max-width:1200px;margin:auto; }
form { background:rgba(255,255,255,.02);border:1px solid var(--border);border-radius:20px;padding:45px;backdrop-filter:blur(15px);position:relative;transition:.4s; }
form:hover { border-color:var(--gb);box-shadow:0 20px 40px rgba(0,0,0,.4); }
form h4 { font-family:"Cormorant Garamond",serif;font-size:32px;color:var(--gold);margin-bottom:30px; }
label { display:block;font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:8px;margin-top:20px; }
input:not([type="checkbox"]),select { width:100%;background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--white);padding:14px;border-radius:6px;outline:none;transition:.3s;font-family:"Outfit",sans-serif;font-size:14px; }
input:not([type="checkbox"]):focus,select:focus { border-color:var(--gold); }
select option { background:var(--ink); }
.btn-primary { padding:18px 38px;background:var(--gold);color:var(--ink);font-size:13px;font-weight:800;letter-spacing:2px;text-transform:uppercase;border-radius:4px;transition:.3s;display:inline-block;border:none;cursor:pointer;width:100%;margin-top:35px;font-family:"Outfit",sans-serif; }
.btn-primary:hover { background:var(--gold-l);transform:translateY(-3px);box-shadow:0 10px 30px rgba(200,164,60,.4); }

/* ── ALERTS ──────────────────────────────────────────────────── */
.alert { padding:20px;border-radius:12px;text-align:center;margin:20px auto;max-width:800px;font-weight:600;z-index:100;position:relative; }
.success { background:rgba(22,163,74,.15);border:1px solid #16a34a;color:#86efac; }
.error   { background:rgba(239,68,68,.15);border:1px solid #ef4444;color:#fca5a5; }

.get-started-box { text-align:center;padding:100px 20px;position:relative;z-index:10; }

footer { text-align:center;padding:60px;color:rgba(255,255,255,.2);font-size:11px;letter-spacing:2px;position:relative;z-index:10; }

@media(max-width:768px){
    .logo-text { font-size:18px;position:relative;left:0;transform:none; }
    header { padding:15px 20px; }
    .forms-container { grid-template-columns:1fr; }
    .hero { padding:60px 24px 30px; }
    #verification { padding:24px; }
}
</style>
</head>
<body>

<div id="cur-dot"></div>
<div id="cur-ring"></div>
<div class="page-bg"></div>
<div class="page-grid"></div>

<header>
    <a href="Broker.php" class="back-btn"><span>←</span> Back</a>
    <div class="logo-text">HOUSING HUB BROKERS</div>
    <div style="width:80px"></div>
</header>

<main class="z">
    <?php if(isset($_SESSION['message'])): ?>
      <div class="alert success"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
      <div class="alert error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <section class="hero reveal">
        <span style="color:var(--gold);font-weight:700;letter-spacing:3px;text-transform:uppercase;font-size:12px;">Partner Program</span>
        <h2>Elevate Your <em>Brokerage</em> Career</h2>
        <p>Enter the vault of Uganda's most exclusive property network. Verified partners gain access to high-value listings, automated commission tracking, and a global client base.</p>
    </section>

    <!-- AGREEMENT -->
    <section class="agreement-container reveal">
        <h4 style="font-family:'Cormorant Garamond',serif;font-size:28px;color:var(--gold);margin-bottom:15px;">Brokerage Master Agreement</h4>
        <div class="terms-box">
            <p><strong>1. Scope & Eligibility:</strong> This agreement establishes a binding professional relationship between HousingHub and the Broker. Users must be 18+ and possess full legal authority to execute contracts in Uganda.</p><br>
            <p><strong>2. Truthfulness of Documentation:</strong> Partners guarantee all uploaded ID and incorporation documents are authentic, valid, and unaltered. Forgery results in an immediate permanent ban and 
            reporting to national regulatory authorities..</p><br>
            <p><strong>3. Professional Licensing:</strong> Partners must maintain active compliance with AREA Uganda or relevant national planning guidelines. HousingHub reserves the right to suspend accounts that fail to
             provide proof of valid licensure.</p><br>
            <p><strong>4. Listing Accuracy:</strong> All property uploads must strictly reflect the true price, size, and legal availability of the unit. "Bait and switch" tactics or misleading data are categorized as terminal breaches of conduct.</p><br>
            <p><strong>5. Intellectual Property:</strong>Partners grant the platform a royalty-free license to use uploaded media for global marketing. You warrant that you own the copyrights or have express permission for all submitted imagery. </p><br>
            <p><strong>6. Commission Structure:</strong>Partners must disclose final closing prices to ensure accurate fee processing and documentation. </p><br>
            <p><strong>7. Response Time Standards:</strong> Partners must acknowledge client inquiries within four business hours. Consistent communication delays will lead to a rating downgrade or suspension of the lead feed.</p><br>
            <p><strong>8. Ethical Conduct:</strong> Partners must adhere to high ethical standards, prohibiting the disparagement of colleagues or client poaching. "Deal-stealing" is
             a zero-tolerance offense that results in immediate expulsion from the network.</p><br>
            <p><strong>9. Data Confidentiality:</strong> All client contact information received via the dashboard is classified as strictly confidential data. Selling or leaking lead data to third-party firms is prohibited 
            without express written client consent.</p><br>
            <p><strong>10. Viewing Protocols:</strong> Partners act as the primary brand ambassadors during physical property tours and site visits. Any property damage caused by Partner 
            negligence is the sole financial responsibility of the Partner.</p><br>
            <p><strong>11. Service Fees:</strong>HousingHub may charge non-refundable access fees to maintain the CRM, SEO, and security of the Vault. These fees ensure the platform continues to provide high-value,
             exclusive global property leads </p><br>
            <p><strong>12. Termination Clause</strong> Terminated partners are barred from re-applying to the network for a minimum of 24 months.</p>
        </div>
        <label class="checkbox-wrapper">
            <input type="checkbox" id="agree-checkbox">
            <span style="font-size:15px;color:var(--white);text-transform:none;letter-spacing:1px;">
                I have read and agreed to the Broker Partner Terms &amp; Conditions
            </span><br><br>
            
            <span style="font-size:13px;color:var(--gold);text-transform:none;letter-spacing:1px;">
                ACCEPT TO  OPEN & FILL THE FORM TO ACCESS THE DASHBOARD 
            </span>
    
        </label>
    </section>

    <!-- ROADMAP -->
    <div class="roadmap reveal">
        <div class="step"><span class="step-num">01</span><h3>Verification</h3><p>Fill out the brokerage form below with your valid credentials.</p></div>
        <div class="step"><span class="step-num">02</span><h3>Review</h3><p>Our concierge team validates your documents within 24 hours.</p></div>
        <div class="step"><span class="step-num">03</span><h3>Approval</h3><p>Receive a "Verified Partner" confirmation via email &amp; SMS.</p></div>
        <div class="step"><span class="step-num">04</span><h3>Dashboard</h3><p>Login to your portal and start closing million-dollar deals.</p></div>
    </div>

    <!-- FORMS (locked until agreement checked) -->
    <div id="forms-overlay">
        <section id="verification">
            <div class="forms-container">
                <form enctype="multipart/form-data" method="post" action="submit_verification.php" class="reveal">
                    <input type="hidden" name="agreed_to_terms" value="1">
                    <h4>Individual Professional</h4>
                    <p style="font-size:13px;color:var(--muted);margin-bottom:20px;">For independent brokers and real estate agents.</p>
                    <label>Legal Full Name</label>
                    <input type="text" name="fullname" placeholder="As it appears on your ID" required>
                    <label>Professional Email</label>
                    <input type="email" name="email" placeholder="email@professional.com" required>
                    <label>ID Type</label>
                    <select name="id_type" required>
                        <option value="">Select Category</option>
                        <option value="NIN">National ID (NIN)</option>
                        <option value="Passport">International Passport</option>
                        <option value="DriversLicense">Driver's License</option>
                    </select>
                    <label>Upload Clear ID Scan</label>
                    <input type="file" name="id_doc" required>
                    <label>Phone Number (Mobile Money Linked)</label>
                    <input type="text" name="phone" placeholder="+256..." required>
                    <button type="submit" class="btn-primary">Submit for Verification</button>
                </form>

                <form enctype="multipart/form-data" method="post" action="submit_verification.php" class="reveal">
                    <input type="hidden" name="agreed_to_terms" value="1">
                    <h4>Agency &amp; Business</h4>
                    <p style="font-size:13px;color:var(--muted);margin-bottom:20px;">For registered real estate firms and companies.</p>
                    <label>Company Name</label>
                    <input type="text" name="bname" placeholder="Registered Entity Name" required>
                    <label>Company Email</label>
                    <input type="email" name="email" placeholder="admin@company.com" required>
                    <label>Certificate of Incorporation</label>
                    <input type="file" name="b_reg" required>
                    <label>Managing Director's ID</label>
                    <input type="file" name="b_owner_id" required>
                    <label>Years of Operation</label>
                    <input type="number" name="b_duration" placeholder="Years in industry" min="1" required>
                    <label>Tax Clearance / Trading License</label>
                    <input type="file" name="b_doc">
                    <button type="submit" class="btn-primary">Verify Agency</button>
                </form>
            </div>
        </section>
    </div>

    <div class="get-started-box reveal">
        <h2>Already <em>Verified?</em></h2>
        <a href="index.php" class="btn-primary" style="width:auto;padding:20px 60px;display:inline-block;text-decoration:none">Enter Dashboard</a>
    </div>
</main>

<footer>&copy; 2026 HOUSING HUB | THE STANDARD OF LUXURY REAL ESTATE</footer>

<script>
/* ── AGREEMENT TOGGLE ────────────────────────────────────────── */
// Single declaration — no duplicate
const agreeChk    = document.getElementById('agree-checkbox');
const formsOverlay = document.getElementById('forms-overlay');

agreeChk.addEventListener('change', function(){
    if(this.checked){
        formsOverlay.classList.add('active');
        setTimeout(()=>{
            formsOverlay.scrollIntoView({ behavior:'smooth', block:'start' });
        }, 300);
    } else {
        formsOverlay.classList.remove('active');
    }
});

/* ── CURSOR ──────────────────────────────────────────────────── */
(function(){
    const dot  = document.getElementById('cur-dot');
    const ring = document.getElementById('cur-ring');
    if(!dot || !ring) return;

    // rAF-based lag: dot moves instantly, ring interpolates
    let mx = -400, my = -400;   // start far off-screen
    let rx = -400, ry = -400;

    document.addEventListener('mousemove', function(e){
        mx = e.clientX; my = e.clientY;
        // Dot moves instantly
        dot.style.left = mx + 'px';
        dot.style.top  = my + 'px';
    });

    (function tick(){
        // Ring smoothly follows
        rx += (mx - rx) * 0.14;
        ry += (my - ry) * 0.14;
        ring.style.left = rx + 'px';
        ring.style.top  = ry + 'px';
        requestAnimationFrame(tick);
    })();

    // Hover/click states
    document.querySelectorAll('a, button, input, select, form, .step, .checkbox-wrapper').forEach(function(el){
        el.addEventListener('mouseenter', ()=> document.body.classList.add('cursor-hover'));
        el.addEventListener('mouseleave', ()=> document.body.classList.remove('cursor-hover'));
    });
    document.addEventListener('mousedown', ()=> document.body.classList.add('cursor-click'));
    document.addEventListener('mouseup',   ()=> document.body.classList.remove('cursor-click'));
})();

/* ── PARTICLES ───────────────────────────────────────────────── */
for(var i=0;i<20;i++){
    var p=document.createElement('div');
    p.classList.add('ptcl');
    var sz=Math.random()*3+1;
    p.style.cssText='width:'+sz+'px;height:'+sz+'px;left:'+(Math.random()*100)+'%;background:rgba(200,164,60,'+(Math.random()*.4+.1)+');animation-duration:'+(Math.random()*15+10)+'s;animation-delay:'+(Math.random()*10)+'s;';
    document.body.appendChild(p);
}

/* ── SCROLL REVEAL ───────────────────────────────────────────── */
var ro = new IntersectionObserver(function(entries, obs){
    entries.forEach(function(e){
        if(e.isIntersecting){ e.target.classList.add('visible'); obs.unobserve(e.target); }
    });
}, { threshold: .1 });
document.querySelectorAll('.reveal').forEach(function(el){ ro.observe(el); });
</script>
</body>
</html>