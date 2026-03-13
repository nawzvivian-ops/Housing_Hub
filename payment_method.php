<?php
session_start();
include "db_connect.php";

$property_id = intval($_GET['property_id'] ?? 0);
$action = $_GET['action'] ?? '';

if ($property_id <= 0 || !in_array($action, ['rent', 'buy', 'lease'])) {
    die("Invalid request.");
}

$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();

if (!$property) {
    die("Property not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Select Payment Method &ndash; HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25)}
body{cursor:none;font-family:'Outfit',sans-serif;background:var(--ink);color:var(--white);min-height:100vh;display:flex;flex-direction:column;overflow-x:hidden}
#cur-dot{width:8px;height:8px;background:var(--gold);border-radius:50%;position:fixed;z-index:99999;pointer-events:none;transform:translate(-50%,-50%);mix-blend-mode:difference}
#cur-ring{width:40px;height:40px;border:1.5px solid rgba(200,164,60,.7);border-radius:50%;position:fixed;z-index:99998;pointer-events:none;transform:translate(-50%,-50%);transition:width .45s cubic-bezier(.23,1,.32,1),height .45s}
#cur-trail{width:80px;height:80px;border:1px solid rgba(200,164,60,.15);border-radius:50%;position:fixed;z-index:99997;pointer-events:none;transform:translate(-50%,-50%);transition:width .7s,height .7s}
body.cursor-hover #cur-dot{width:14px;height:14px;background:#fff}
body.cursor-hover #cur-ring{width:60px;height:60px;border-color:var(--gold);background:rgba(200,164,60,.06)}
body.cursor-click #cur-dot{width:5px;height:5px}
body.cursor-click #cur-ring{width:28px;height:28px}

/* BACKGROUND */
.page-bg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(14,90,200,.18) 0%,transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(180,140,40,.12) 0%,transparent 50%),var(--ink);animation:atmo 14s ease-in-out infinite alternate}
@keyframes atmo{0%{filter:brightness(1)}100%{filter:brightness(1.1) hue-rotate(6deg)}}
.page-grid{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:72px 72px}
.ptcl{position:fixed;border-radius:50%;pointer-events:none;z-index:1;animation:pdrift linear infinite}
@keyframes pdrift{0%{transform:translateY(100vh) scale(0);opacity:0}5%{opacity:1}95%{opacity:.5}100%{transform:translateY(-10vh) translateX(50px) scale(1.4);opacity:0}}

/* NAV */
.top-nav{position:sticky;top:0;z-index:9000;display:flex;justify-content:space-between;align-items:center;padding:18px 60px;background:rgba(4,9,26,.96);border-bottom:1px solid var(--border);backdrop-filter:blur(12px);position:relative;z-index:10;animation:fadeDown .8s ease both}
@keyframes fadeDown{from{opacity:0;transform:translateY(-14px)}to{opacity:1;transform:translateY(0)}}
.nav-logo{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gold);text-decoration:none}
.nav-logo span{color:var(--muted)}
.nav-back{font-size:11px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);text-decoration:none;transition:color .3s;display:flex;align-items:center;gap:8px}
.nav-back:hover{color:var(--gold)}

/* PAGE */
.page-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:60px 24px;position:relative;z-index:10}
.payment-card{width:100%;max-width:520px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:16px;overflow:hidden;animation:fadeUp .8s ease .1s both}
@keyframes fadeUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}

/* CARD HEADER */
.card-header{padding:32px 36px;border-bottom:1px solid var(--border);background:rgba(200,164,60,.04)}
.card-eyebrow{font-size:11px;font-weight:500;letter-spacing:3px;text-transform:uppercase;color:var(--gold);display:flex;align-items:center;gap:10px;margin-bottom:12px}
.card-eyebrow::before{content:'';width:24px;height:1px;background:var(--gold)}
.card-title{font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:700;color:var(--white);line-height:1.1}
.card-title em{color:var(--gold);font-style:italic}

/* PROPERTY INFO */
.prop-info{padding:24px 36px;border-bottom:1px solid var(--border);background:rgba(255,255,255,.02)}
.prop-info-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.prop-info-row:last-child{border-bottom:none}
.prop-info-label{font-size:11px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.3)}
.prop-info-value{font-size:14px;font-weight:500;color:var(--white);text-align:right;max-width:280px}
.prop-info-value.price{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:var(--gold)}
.action-badge{font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:4px 12px;border-radius:20px;border:1px solid transparent}
.action-badge.rent{background:rgba(34,197,94,.1);color:#86efac;border-color:rgba(34,197,94,.2)}
.action-badge.buy{background:rgba(59,130,246,.1);color:#93c5fd;border-color:rgba(59,130,246,.2)}
.action-badge.lease{background:rgba(245,158,11,.1);color:#fcd34d;border-color:rgba(245,158,11,.2)}

/* PAYMENT METHODS */
.methods-wrap{padding:28px 36px}
.methods-label{font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:18px}
.method-btn{width:100%;padding:16px 22px;margin-bottom:12px;border:1px solid var(--border);border-radius:10px;background:rgba(255,255,255,.03);color:var(--white);font-family:'Outfit',sans-serif;font-size:14px;font-weight:600;cursor:pointer;transition:all .3s;display:flex;align-items:center;gap:14px;text-align:left}
.method-btn:last-of-type{margin-bottom:0}
.method-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.3)}
.method-btn .method-icon{font-size:22px;flex-shrink:0;width:36px;text-align:center}
.method-btn .method-info{flex:1}
.method-btn .method-name{font-size:14px;font-weight:600;color:var(--white)}
.method-btn .method-sub{font-size:11px;color:var(--muted);margin-top:2px}
.method-btn .method-arr{color:var(--gold);opacity:0;transform:translateX(-6px);transition:all .3s;font-size:16px}
.method-btn:hover .method-arr{opacity:1;transform:translateX(0)}
.method-btn.mobile{border-color:rgba(34,197,94,.2);background:rgba(34,197,94,.06)}
.method-btn.mobile:hover{border-color:rgba(34,197,94,.4);background:rgba(34,197,94,.12)}
.method-btn.mobile .method-name{color:#86efac}
.method-btn.card-pay{border-color:rgba(59,130,246,.2);background:rgba(59,130,246,.06)}
.method-btn.card-pay:hover{border-color:rgba(59,130,246,.4);background:rgba(59,130,246,.12)}
.method-btn.card-pay .method-name{color:#93c5fd}
.method-btn.bank{border-color:rgba(200,164,60,.2);background:rgba(200,164,60,.06)}
.method-btn.bank:hover{border-color:rgba(200,164,60,.4);background:rgba(200,164,60,.12)}
.method-btn.bank .method-name{color:var(--gold)}

/* BACK LINK */
.card-footer{padding:20px 36px;border-top:1px solid var(--border);text-align:center}
.back-link{font-size:12px;color:var(--muted);text-decoration:none;letter-spacing:1px;transition:color .3s}
.back-link:hover{color:var(--gold)}

footer{padding:24px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}

@media(max-width:600px){
  .top-nav{padding:16px 24px}
  .card-header,.prop-info,.methods-wrap,.card-footer{padding-left:24px;padding-right:24px}
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

<!-- NAV -->
<nav class="top-nav">
  <a href="properties.php" class="nav-logo">Housing<span>Hub</span></a>
  <a href="property_view.php?id=<?php echo $property_id; ?>" class="nav-back">&larr; Back to Property</a>
</nav>

<!-- PAGE -->
<div class="page-wrap">
  <div class="payment-card">

    <!-- HEADER -->
    <div class="card-header">
      <div class="card-eyebrow">Secure Checkout</div>
      <div class="card-title"><?php echo ucfirst(htmlspecialchars($action)); ?> <em>Payment</em></div>
    </div>

    <!-- PROPERTY INFO -->
    <div class="prop-info">
      <div class="prop-info-row">
        <span class="prop-info-label">Property</span>
        <span class="prop-info-value"><?php echo htmlspecialchars($property['property_name']); ?></span>
      </div>
      <div class="prop-info-row">
        <span class="prop-info-label">Address</span>
        <span class="prop-info-value"><?php echo htmlspecialchars($property['address'] ?? 'N/A'); ?></span>
      </div>
      <div class="prop-info-row">
        <span class="prop-info-label">Action</span>
        <span class="prop-info-value"><span class="action-badge <?php echo $action; ?>"><?php echo ucfirst($action); ?></span></span>
      </div>
      <div class="prop-info-row">
        <span class="prop-info-label">Amount Due</span>
        <span class="prop-info-value price">UGX <?php echo number_format($property['rent_amount'] ?? 0); ?></span>
      </div>
    </div>

    <!-- PAYMENT METHODS -->
    <div class="methods-wrap">
      <div class="methods-label">Choose Payment Method</div>
      <form method="POST" action="process_payment.php">
        <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
        <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">

        <button type="submit" name="method" value="mobile_money" class="method-btn mobile">
          <span class="method-icon">&#128241;</span>
          <div class="method-info">
            <div class="method-name">Mobile Money</div>
            <div class="method-sub">MTN MoMo &middot; Airtel Money</div>
          </div>
          <span class="method-arr">&rarr;</span>
        </button>

        <button type="submit" name="method" value="card" class="method-btn card-pay">
          <span class="method-icon">&#128179;</span>
          <div class="method-info">
            <div class="method-name">Debit / Credit Card</div>
            <div class="method-sub">Visa &middot; Mastercard &middot; Verve</div>
          </div>
          <span class="method-arr">&rarr;</span>
        </button>

        <button type="submit" name="method" value="bank" class="method-btn bank">
          <span class="method-icon">&#127981;</span>
          <div class="method-info">
            <div class="method-name">Bank Transfer</div>
            <div class="method-sub">Direct transfer from your bank</div>
          </div>
          <span class="method-arr">&rarr;</span>
        </button>

      </form>
    </div>

    <!-- FOOTER -->
    <div class="card-footer">
      <a href="index.html" class="back-link">&larr; Back to Dashboard</a>
    </div>

  </div>
</div>

<footer>&copy; 2026 HousingHub | All Rights Reserved</footer>

<script>
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