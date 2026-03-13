<?php
session_start();
include "db_connect.php";

$payment_id = intval($_GET['payment_id'] ?? 0);
$stmt = $conn->prepare("
    SELECT p.*, pr.property_name 
    FROM payments p
    JOIN properties pr ON p.property_id = pr.id
    WHERE p.id = ? AND p.tenant_id = ?
");
$stmt->bind_param("ii", $payment_id, $_SESSION['user_id']);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bank Transfer &ndash; HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07)}
body{cursor:none;font-family:'Outfit',sans-serif;background:var(--ink);color:var(--white);min-height:100vh;display:flex;flex-direction:column;overflow-x:hidden}
#cur-dot{width:8px;height:8px;background:var(--gold);border-radius:50%;position:fixed;z-index:99999;pointer-events:none;transform:translate(-50%,-50%);mix-blend-mode:difference}
#cur-ring{width:40px;height:40px;border:1.5px solid rgba(200,164,60,.7);border-radius:50%;position:fixed;z-index:99998;pointer-events:none;transform:translate(-50%,-50%);transition:width .45s cubic-bezier(.23,1,.32,1),height .45s}
#cur-trail{width:80px;height:80px;border:1px solid rgba(200,164,60,.15);border-radius:50%;position:fixed;z-index:99997;pointer-events:none;transform:translate(-50%,-50%);transition:width .7s,height .7s}
body.cursor-hover #cur-dot{width:14px;height:14px;background:#fff}
body.cursor-hover #cur-ring{width:60px;height:60px;border-color:var(--gold);background:rgba(200,164,60,.06)}
body.cursor-click #cur-dot{width:5px;height:5px}
body.cursor-click #cur-ring{width:28px;height:28px}
.page-bg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(14,90,200,.18) 0%,transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(200,164,60,.1) 0%,transparent 50%),var(--ink);animation:atmo 14s ease-in-out infinite alternate}
@keyframes atmo{0%{filter:brightness(1)}100%{filter:brightness(1.1) hue-rotate(6deg)}}
.page-grid{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:72px 72px}
.ptcl{position:fixed;border-radius:50%;pointer-events:none;z-index:1;animation:pdrift linear infinite}
@keyframes pdrift{0%{transform:translateY(100vh) scale(0);opacity:0}5%{opacity:1}95%{opacity:.5}100%{transform:translateY(-10vh) translateX(50px) scale(1.4);opacity:0}}

/* NAV */
.top-nav{position:sticky;top:0;z-index:9000;display:flex;justify-content:space-between;align-items:center;padding:18px 60px;background:rgba(4,9,26,.96);border-bottom:1px solid var(--border);backdrop-filter:blur(12px);position:relative;z-index:10;animation:fadeDown .8s ease both}
@keyframes fadeDown{from{opacity:0;transform:translateY(-14px)}to{opacity:1;transform:translateY(0)}}
.nav-logo{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gold);text-decoration:none}
.nav-logo span{color:var(--muted)}
.nav-back{font-size:11px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);text-decoration:none;transition:color .3s}
.nav-back:hover{color:var(--gold)}

/* PAGE */
.page-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:60px 24px;position:relative;z-index:10}
.payment-card{width:100%;max-width:560px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:16px;overflow:hidden;animation:fadeUp .8s ease .1s both}
@keyframes fadeUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}

/* HEADER */
.card-header{padding:30px 36px;border-bottom:1px solid var(--border);background:rgba(200,164,60,.04);display:flex;align-items:center;gap:16px}
.card-header-icon{width:52px;height:52px;background:rgba(200,164,60,.12);border:1px solid rgba(200,164,60,.25);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0}
.card-eyebrow{font-size:10px;font-weight:500;letter-spacing:3px;text-transform:uppercase;color:rgba(200,164,60,.7);margin-bottom:6px}
.card-title{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:var(--white);line-height:1.1}
.card-title em{color:var(--gold);font-style:italic}

/* SECTIONS */
.section{padding:24px 36px;border-bottom:1px solid var(--border)}
.section-label{font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:16px}

/* BANK DETAIL ROWS */
.bank-row{display:flex;justify-content:space-between;align-items:center;padding:11px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.bank-row:last-child{border-bottom:none}
.bank-key{font-size:11px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.3)}
.bank-val{font-size:14px;font-weight:600;color:var(--white);text-align:right;display:flex;align-items:center;gap:8px}
.copy-btn{background:rgba(200,164,60,.12);border:1px solid rgba(200,164,60,.2);color:var(--gold);font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:4px 10px;border-radius:4px;cursor:pointer;font-family:'Outfit',sans-serif;transition:all .3s}
.copy-btn:hover{background:rgba(200,164,60,.25)}

/* PAYMENT SUMMARY */
.summary-row{display:flex;justify-content:space-between;align-items:center;padding:11px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.summary-row:last-child{border-bottom:none}
.summary-label{font-size:11px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.3)}
.summary-value{font-size:14px;font-weight:600;color:var(--white)}
.summary-value.amount{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:var(--gold)}
.summary-value.ref{font-family:monospace;font-size:12px;color:var(--gold);letter-spacing:1px}

/* WARNING */
.warning-block{padding:20px 36px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;gap:14px;background:rgba(245,158,11,.04)}
.warning-icon{font-size:20px;flex-shrink:0;margin-top:2px}
.warning-text{font-size:13px;color:rgba(253,230,138,.8);line-height:1.6}
.warning-text strong{color:#fcd34d;display:block;margin-bottom:3px}

/* FOOTER */
.card-footer{padding:24px 36px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}
.dashboard-btn{padding:13px 28px;background:var(--gold);color:var(--ink);border:none;border-radius:8px;font-family:'Outfit',sans-serif;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;transition:all .3s;display:inline-block}
.dashboard-btn:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,164,60,.28)}
.back-link{font-size:12px;color:var(--muted);text-decoration:none;letter-spacing:1px;transition:color .3s}
.back-link:hover{color:var(--gold)}

footer{padding:24px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}

@media(max-width:600px){
  .top-nav{padding:16px 24px}
  .card-header,.section,.warning-block,.card-footer{padding-left:20px;padding-right:20px}
  body{cursor:auto}
  #cur-dot,#cur-ring,#cur-trail{display:none}
}
</style>
</head>
<body>

<div id="cur-dot"></div>
<div id="cur-ring"></div>
<div id="cur-trail"></div>
<div class="page-bg"></div>
<div class="page-grid"></div>

<nav class="top-nav">
  <a href="properties.php" class="nav-logo">Housing<span>Hub</span></a>
  <a href="javascript:history.back()" class="nav-back">&larr; Back</a>
</nav>

<div class="page-wrap">
  <div class="payment-card">

    <div class="card-header">
      <div class="card-header-icon">&#127981;</div>
      <div>
        <div class="card-eyebrow">Payment Instructions</div>
        <div class="card-title">Bank <em>Transfer</em></div>
      </div>
    </div>

    <!-- BANK DETAILS -->
    <div class="section">
      <div class="section-label">Bank Details</div>
      <div class="bank-row">
        <span class="bank-key">Bank Name</span>
        <span class="bank-val">Stanbic Bank Uganda</span>
      </div>
      <div class="bank-row">
        <span class="bank-key">Account Name</span>
        <span class="bank-val">HousingHub Ltd</span>
      </div>
      <div class="bank-row">
        <span class="bank-key">Account Number</span>
        <span class="bank-val">
          9030008123456
          <button class="copy-btn" onclick="copyText('9030008123456', this)">Copy</button>
        </span>
      </div>
      <div class="bank-row">
        <span class="bank-key">Branch</span>
        <span class="bank-val">Kampala Road</span>
      </div>
      <div class="bank-row">
        <span class="bank-key">Swift Code</span>
        <span class="bank-val">
          SBICUGKX
          <button class="copy-btn" onclick="copyText('SBICUGKX', this)">Copy</button>
        </span>
      </div>
    </div>

    <!-- PAYMENT SUMMARY -->
    <div class="section">
      <div class="section-label">Payment Summary</div>
      <div class="summary-row">
        <span class="summary-label">Property</span>
        <span class="summary-value"><?php echo htmlspecialchars($payment['property_name']); ?></span>
      </div>
      <div class="summary-row">
        <span class="summary-label">Amount to Pay</span>
        <span class="summary-value amount">UGX <?php echo number_format($payment['amount']); ?></span>
      </div>
      <div class="summary-row">
        <span class="summary-label">Reference</span>
        <span class="summary-value">
          <span class="ref"><?php echo htmlspecialchars($payment['transaction_ref']); ?></span>
          <button class="copy-btn" style="margin-left:8px" onclick="copyText('<?php echo htmlspecialchars($payment['transaction_ref']); ?>', this)">Copy</button>
        </span>
      </div>
    </div>

    <!-- WARNING -->
    <div class="warning-block">
      <span class="warning-icon">&#9888;&#65039;</span>
      <div class="warning-text">
        <strong>Important &mdash; Please Read</strong>
        Use the exact reference number when making your transfer. Your payment will be verified and confirmed within 24 hours of receipt.
      </div>
    </div>

    <!-- FOOTER -->
    <div class="card-footer">
      <a href="index.html" class="dashboard-btn">Back to Dashboard</a>
      <a href="payment_method.php?property_id=<?php echo $payment['property_id']; ?>&action=rent" class="back-link">&larr; Choose Different Method</a>
    </div>

  </div>
</div>

<footer>&copy; 2026 HousingHub | All Rights Reserved</footer>

<script>
function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.textContent;
        btn.textContent = 'Copied!';
        btn.style.color = '#86efac';
        btn.style.borderColor = 'rgba(134,239,172,.3)';
        setTimeout(() => {
            btn.textContent = orig;
            btn.style.color = '';
            btn.style.borderColor = '';
        }, 2000);
    });
}

const dot=document.getElementById('cur-dot'),ring=document.getElementById('cur-ring'),trail=document.getElementById('cur-trail');
let mx=-200,my=-200,rx=-200,ry=-200,tx=-200,ty=-200;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;dot.style.left=mx+'px';dot.style.top=my+'px';});
(function anim(){rx+=(mx-rx)*.15;ry+=(my-ry)*.15;tx+=(mx-tx)*.06;ty+=(my-ty)*.06;ring.style.left=rx+'px';ring.style.top=ry+'px';trail.style.left=tx+'px';trail.style.top=ty+'px';requestAnimationFrame(anim);})();
document.querySelectorAll('a,button').forEach(el=>{
    el.addEventListener('mouseenter',()=>document.body.classList.add('cursor-hover'));
    el.addEventListener('mouseleave',()=>document.body.classList.remove('cursor-hover'));
});
document.addEventListener('mousedown',()=>document.body.classList.add('cursor-click'));
document.addEventListener('mouseup',()=>document.body.classList.remove('cursor-click'));
for(let i=0;i<12;i++){const p=document.createElement('div');p.classList.add('ptcl');const sz=Math.random()*2.5+1;p.style.cssText=`width:${sz}px;height:${sz}px;left:${Math.random()*100}%;background:rgba(200,164,60,${(Math.random()*.4+.15).toFixed(2)});animation-duration:${Math.random()*20+12}s;animation-delay:${Math.random()*14}s;`;document.body.appendChild(p);}
</script>
</body>
</html>