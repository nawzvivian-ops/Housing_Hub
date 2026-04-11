<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$payment_id = intval($_GET['payment_id'] ?? 0);

// Fetch payment details
$stmt = $conn->prepare("
    SELECT p.*, pr.property_name 
    FROM payments p
    JOIN properties pr ON p.property_id = pr.id
    WHERE p.id = ? AND p.tenant_id = ?
");
$stmt->bind_param("ii", $payment_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

if (!$payment) {
    die("Payment not found.");
}

// These should ideally come from your database or a config file
$mtn_receiver = "256764700087"; 
$airtel_receiver = "256741035928";

// Note: Removed the UPDATE query from here because it should typically 
// happen in 'process_mobile_money.php' after the form is submitted.
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mobile Money Payment &ndash; HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--green:#22c55e;--green-d:#16a34a}

body{cursor:none;font-family:'Outfit',sans-serif;background:var(--ink);color:var(--white);min-height:100vh;display:flex;flex-direction:column;overflow-x:hidden}

/* FIX: Optimized Cursor Styles */
#cur-dot, #cur-ring, #cur-trail {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 99999;
    pointer-events: none;
    will-change: transform;
}
#cur-dot{width:8px;height:8px;background:var(--gold);border-radius:50%;mix-blend-mode:difference}
#cur-ring{width:20px;height:20px;border:1.5px solid rgba(200,164,60,.7);border-radius:50%;}
#cur-trail{width:30px;height:30px;border:1px solid rgba(200,164,60,.15);border-radius:50%;}

body.cursor-hover #cur-dot{width:8px;height:8px;background:#fff}
body.cursor-hover #cur-ring{width:20px;height:20px;border-color:var(--gold);background:rgba(200,164,60,.06)}

.page-bg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(14,90,200,.18) 0%,transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(34,197,94,.08) 0%,transparent 50%),var(--ink);}
.page-grid{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:72px 72px}
.ptcl{position:fixed;border-radius:50%;pointer-events:none;z-index:1;animation:pdrift linear infinite}
@keyframes pdrift{0%{transform:translateY(100vh) scale(0);opacity:0}5%{opacity:1}95%{opacity:.5}100%{transform:translateY(-10vh) translateX(50px) scale(1.4);opacity:0}}

.top-nav{position:sticky;top:0;z-index:9000;display:flex;justify-content:space-between;align-items:center;padding:18px 60px;background:rgba(4,9,26,.96);border-bottom:1px solid var(--border);backdrop-filter:blur(12px);animation:fadeDown .8s ease both}
@keyframes fadeDown{from{opacity:0;transform:translateY(-14px)}to{opacity:1;transform:translateY(0)}}
.nav-logo{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gold);text-decoration:none}
.nav-logo span{color:var(--muted)}
.nav-back{font-size:11px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);text-decoration:none;transition:color .3s}

.page-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:60px 24px;position:relative;z-index:10}
.payment-card{width:100%;max-width:500px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:16px;overflow:hidden;animation:fadeUp .8s ease .1s both}
@keyframes fadeUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}

.card-header{padding:30px 36px;border-bottom:1px solid var(--border);background:rgba(34,197,94,.04);display:flex;align-items:center;gap:16px}
.card-header-icon{width:52px;height:52px;background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.25);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0}
.card-eyebrow{font-size:10px;font-weight:500;letter-spacing:3px;text-transform:uppercase;color:rgba(134,239,172,.7);margin-bottom:6px}
.card-title{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:var(--white);line-height:1.1}
.card-title em{color:#86efac;font-style:italic}

.details{padding:22px 36px;border-bottom:1px solid var(--border);background:rgba(255,255,255,.02)}
.detail-row{display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.detail-row:last-child{border-bottom:none}
.detail-label{font-size:11px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.3)}
.detail-value{font-size:14px;font-weight:500;color:var(--white);text-align:right}
.detail-value.amount{font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:700;color:#86efac}
.detail-value.ref{font-family:monospace;font-size:12px;color:var(--gold);letter-spacing:1px}

.form-wrap{padding:28px 36px;border-bottom:1px solid var(--border)}
.form-group{margin-bottom:20px}
.form-label{font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--gold);display:block;margin-bottom:10px}

.network-opts{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:4px}
.network-opt{display:none}
.network-opt + label{display:flex;align-items:center;gap:10px;padding:13px 16px;border:1px solid var(--border);border-radius:8px;background:rgba(255,255,255,.03);cursor:pointer;transition:all .3s;font-size:13px;font-weight:600;color:var(--muted)}
.network-opt:checked + label{border-color:var(--gold);background:rgba(200,164,60,.08);color:var(--gold)}

.form-input{width:100%;padding:13px 16px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:8px;color:var(--white);font-family:'Outfit',sans-serif;font-size:14px;}
.form-input:focus{outline:none;border-color:var(--gold);background:rgba(200,164,60,.04)}

.submit-btn{width:100%;padding:15px;background:var(--green);color:#fff;border:none;border-radius:8px;font-family:'Outfit',sans-serif;font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;transition:all .3s;display:flex;align-items:center;justify-content:center;gap:10px}
.submit-btn:hover{background:var(--green-d);transform:translateY(-2px);box-shadow:0 10px 28px rgba(34,197,94,.28)}

.instructions{padding:24px 36px}
.instr-title{font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:16px}
.step{display:flex;align-items:flex-start;gap:14px;font-size:13px;color:var(--muted);line-height:1.5;margin-bottom:12px}
.step-num{width:24px;height:24px;border-radius:50%;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);color:#86efac;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}

footer{padding:24px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}

@media(max-width:600px){
  .top-nav{padding:16px 24px}
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
      <div class="card-header-icon">&#128241;</div>
      <div>
        <div class="card-eyebrow">Secure Payment</div>
        <div class="card-title">Mobile <em>Money</em></div>
      </div>
    </div>

    <div class="details">
      <div class="detail-row">
        <span class="detail-label">Property</span>
        <span class="detail-value"><?php echo htmlspecialchars($payment['property_name']); ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Amount</span>
        <span class="detail-value amount">UGX <?php echo number_format($payment['amount']); ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Reference</span>
        <span class="detail-value ref"><?php echo htmlspecialchars($payment['transaction_ref']); ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Send To</span>
        <span class="detail-value ref" id="receiver-display">Select network</span>
      </div>
    </div>

    <div class="form-wrap">
      <form method="POST" action="process_mobile_money.php">
        <input type="hidden" name="payment_id" value="<?php echo $payment_id; ?>">
        <input type="hidden" name="receiver_number" id="receiver_number" value="">

        <div class="form-group">
          <label class="form-label">Select Network</label>
          <div class="network-opts">
            <input type="radio" name="network" value="mtn" id="net_mtn" class="network-opt" required>
            <label for="net_mtn"><span class="net-icon">&#127951;</span> MTN MoMo</label>
            <input type="radio" name="network" value="airtel" id="net_airtel" class="network-opt">
            <label for="net_airtel"><span class="net-icon">&#128225;</span> Airtel Money</label>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="phone">Your Mobile Number</label>
          <input type="tel" name="phone" id="phone" class="form-input" placeholder="256700000000" required pattern="[0-9]{12}">
          <div class="form-hint">Format: 256xxxxxxxxx</div>
        </div>

        <button type="submit" class="submit-btn">&#128241; Send Payment Request</button>
      </form>
    </div>

    <div class="instructions">
      <div class="instr-title">&#128204; Instructions</div>
      <div class="step"><div class="step-num">1</div><span>Select network and enter your number.</span></div>
      <div class="step"><div class="step-num">2</div><span>Click "Send Payment Request".</span></div>
      <div class="step"><div class="step-num">3</div><span>Authorize the prompt on your phone with your PIN.</span></div>
    </div>
  </div>
</div>

<footer>&copy; 2026 HousingHub</footer>

<script>
// PHP variables to JS
const mtnReceiver = "<?php echo $mtn_receiver; ?>";
const airtelReceiver = "<?php echo $airtel_receiver; ?>";

const receiverDisplay = document.getElementById('receiver-display');
const receiverInput   = document.getElementById('receiver_number');

// Network Selection Logic
document.querySelectorAll('input[name="network"]').forEach(radio => {
  radio.addEventListener('change', function () {
    const val = this.value === 'mtn' ? mtnReceiver : airtelReceiver;
    receiverDisplay.textContent = val;
    receiverInput.value = val;
  });
});

// FIX: Cursor Logic (Uses requestAnimationFrame and translate3d for precision)
const dot = document.getElementById('cur-dot');
const ring = document.getElementById('cur-ring');
const trail = document.getElementById('cur-trail');

let mx = -100, my = -100; // Mouse Pos
let rx = -100, ry = -100; // Ring Pos
let tx = -100, ty = -100; // Trail Pos

window.addEventListener('mousemove', e => {
    mx = e.clientX;
    my = e.clientY;
    // Dot follows mouse instantly
    dot.style.transform = `translate3d(${mx}px, ${my}px, 0) translate(-50%, -50%)`;
});

function animateCursor() {
    // Smooth easing
    rx += (mx - rx) * 0.15;
    ry += (my - ry) * 0.15;
    tx += (mx - tx) * 0.08;
    ty += (my - ty) * 0.08;

    ring.style.transform = `translate3d(${rx}px, ${ry}px, 0) translate(-50%, -50%)`;
    trail.style.transform = `translate3d(${tx}px, ${ty}px, 0) translate(-50%, -50%)`;

    requestAnimationFrame(animateCursor);
}
animateCursor();

// Hover interactions
document.querySelectorAll('a, button, input, label').forEach(el => {
    el.addEventListener('mouseenter', () => document.body.classList.add('cursor-hover'));
    el.addEventListener('mouseleave', () => document.body.classList.remove('cursor-hover'));
});

// Particles
for(let i=0;i<12;i++){
    const p=document.createElement('div');
    p.classList.add('ptcl');
    const sz=Math.random()*2.5+1;
    p.style.cssText=`width:${sz}px;height:${sz}px;left:${Math.random()*100}%;background:rgba(200,164,60,${(Math.random()*.4+.15).toFixed(2)});animation-duration:${Math.random()*20+12}s;animation-delay:${Math.random()*14}s;`;
    document.body.appendChild(p);
}
</script>
</body>
</html>