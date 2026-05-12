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

// Your actual Mobile Money numbers
$mtn_receiver = "256764700087"; 
$airtel_receiver = "256741035928";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manual Payment &ndash; HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
/* ... (Keep your existing CSS for cursors, animations, and root variables) ... */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--green:#22c55e;--green-d:#16a34a}
body{cursor:none;font-family:'Outfit',sans-serif;background:var(--ink);color:var(--white);min-height:100vh;display:flex;flex-direction:column;overflow-x:hidden}
#cur-dot, #cur-ring, #cur-trail {position: fixed;top: 0;left: 0;z-index: 99999;pointer-events: none;will-change: transform;}
#cur-dot{width:8px;height:8px;background:var(--gold);border-radius:50%;mix-blend-mode:difference}
#cur-ring{width:20px;height:20px;border:1.5px solid rgba(200,164,60,.7);border-radius:50%;}
#cur-trail{width:30px;height:30px;border:1px solid rgba(200,164,60,.15);border-radius:50%;}
.page-bg{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 100% 60% at 80% 10%,rgba(14,90,200,.18) 0%,transparent 55%),radial-gradient(ellipse 50% 70% at 10% 90%,rgba(34,197,94,.08) 0%,transparent 50%),var(--ink);}
.page-grid{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:72px 72px}
.ptcl{position:fixed;border-radius:50%;pointer-events:none;z-index:1;animation:pdrift linear infinite}
@keyframes pdrift{0%{transform:translateY(100vh) scale(0);opacity:0}5%{opacity:1}95%{opacity:.5}100%{transform:translateY(-10vh) translateX(50px) scale(1.4);opacity:0}}
.top-nav{position:sticky;top:0;z-index:9000;display:flex;justify-content:space-between;align-items:center;padding:18px 60px;background:rgba(4,9,26,.96);border-bottom:1px solid var(--border);backdrop-filter:blur(12px)}
.nav-logo{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gold);text-decoration:none}
.nav-logo span{color:var(--muted)}
.nav-back{font-size:11px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);text-decoration:none}
.page-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:60px 24px;position:relative;z-index:10}
.payment-card{width:100%;max-width:500px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:16px;overflow:hidden;}
.card-header{padding:30px 36px;border-bottom:1px solid var(--border);background:rgba(200,164,60,0.05);display:flex;align-items:center;gap:16px}
.card-header-icon{width:52px;height:52px;background:rgba(200,164,60,.12);border:1px solid rgba(200,164,60,.25);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px}
.card-title{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:var(--white);}
.details{padding:22px 36px;background:rgba(255,255,255,.01)}
.detail-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.detail-label{font-size:11px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.3)}
.detail-value.amount{font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:700;color:var(--gold)}
.form-wrap{padding:28px 36px;background:rgba(255,255,255,.02)}
.form-label{font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--gold);display:block;margin-bottom:10px}
.network-opts{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px}
.network-opt{display:none}
.network-opt + label{display:flex;align-items:center;justify-content:center;padding:13px;border:1px solid var(--border);border-radius:8px;background:rgba(255,255,255,.03);cursor:pointer;font-size:12px;color:var(--muted)}
.network-opt:checked + label{border-color:var(--gold);background:rgba(200,164,60,.1);color:var(--gold)}
.form-input{width:100%;padding:14px;background:rgba(255,255,255,0.05);border:1px solid var(--border);border-radius:8px;color:white;font-family:inherit;margin-bottom:15px}
.submit-btn{width:100%;padding:16px;background:var(--gold);color:var(--ink);border:none;border-radius:8px;font-weight:700;text-transform:uppercase;letter-spacing:2px;cursor:pointer;transition: 0.3s;}
.submit-btn:hover{background:var(--gold-l);transform:translateY(-2px)}
.instructions{padding:24px 36px;border-top:1px solid var(--border)}
.instr-title{font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:15px}
.step{display:flex;gap:12px;font-size:13px;color:var(--muted);margin-bottom:10px;line-height:1.4}
.step-num{width:20px;height:20px;background:rgba(200,164,60,0.1);color:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;flex-shrink:0}
footer{padding:24px;text-align:center;font-size:12px;color:rgba(255,255,255,0.2)}
</style>
</head>
<body>

<div id="cur-dot"></div><div id="cur-ring"></div><div id="cur-trail"></div>
<div class="page-bg"></div><div class="page-grid"></div>

<nav class="top-nav">
    <a href="properties.php" class="nav-logo">Housing<span>Hub</span></a>
    <a href="javascript:history.back()" class="nav-back">&larr; Back</a>
</nav>

<div class="page-wrap">
    <div class="payment-card">
        <div class="card-header">
            <div class="card-header-icon">&#128184;</div>
            <div>
                <div class="card-title">Mobile Money<em>Payment</em></div>
            </div>
        </div>

        <div class="details">
            <div class="detail-row">
                <span class="detail-label">Property</span>
                <span class="detail-value"><?php echo htmlspecialchars($payment['property_name']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Amount Due</span>
                <span class="detail-value amount">UGX <?php echo number_format($payment['amount']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Transfer To</span>
                <span class="detail-value" id="receiver-display" style="color:var(--gold); font-weight:bold;">Select network below</span>
            </div>
        </div>

        <div class="form-wrap">
            <form method="POST" action="process_manual_payment.php">
                <input type="hidden" name="payment_id" value="<?php echo $payment_id; ?>">
                
                <div class="form-group">
                    <label class="form-label">1. Choose Network</label>
                    <div class="network-opts">
                        <input type="radio" name="network" value="mtn" id="net_mtn" class="network-opt" required>
                        <label for="net_mtn">MTN MoMo</label>
                        <input type="radio" name="network" value="airtel" id="net_airtel" class="network-opt">
                        <label for="net_airtel">Airtel Money</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">2. Transaction ID / ID Confirmation</label>
                    <input type="text" name="transaction_id" class="form-input" placeholder="e.g. 1928374655" required>
                    <p style="font-size:10px; color:rgba(255,255,255,0.3); margin-top:-10px; margin-bottom:15px;">Paste the ID from your confirmation SMS</p>
                </div>

                <button type="submit" class="submit-btn">Submit Proof of Payment</button>
            </form>
        </div>

        <div class="instructions">
            <div class="instr-title">How to pay</div>
            <div class="step">
                <div class="step-num">1</div>
                <span>Dial <b>*165#</b> (MTN) or <b>*185#</b> (Airtel) on your phone.</span>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <span>Select <b>Send Money</b> to the number shown above.</span>
            </div><div class="step">
                <div class="step-num">3</div>
                <span>Enter the exact amount: <b>UGX <?php echo number_format($payment['amount']); ?></b></span>
            </div>
            <div class="step">
                <div class="step-num">4</div>
                <span>Enter the reference: using the propertyname + name.</span>
            <div class="step">
                <div class="step-num">5</div>
                <span>Once paid, copy the <b>Transaction ID</b> from the SMS and paste it here.</span>
            </div>
        </div>
    </div>
</div>

<footer>&copy; 2026 HousingHub &bull; Manual Verification System</footer>

<script>
const mtnReceiver = "<?php echo $mtn_receiver; ?>";
const airtelReceiver = "<?php echo $airtel_receiver; ?>";
const receiverDisplay = document.getElementById('receiver-display');

document.querySelectorAll('input[name="network"]').forEach(radio => {
    radio.addEventListener('change', function () {
        receiverDisplay.textContent = this.value === 'mtn' ? mtnReceiver : airtelReceiver;
    });
});

// Cursor Animation (Keeping your particle and cursor logic)
const dot = document.getElementById('cur-dot');
const ring = document.getElementById('cur-ring');
const trail = document.getElementById('cur-trail');
let mx = -100, my = -100, rx = -100, ry = -100, tx = -100, ty = -100;

window.addEventListener('mousemove', e => { mx = e.clientX; my = e.clientY; dot.style.transform = `translate3d(${mx}px, ${my}px, 0) translate(-50%, -50%)`; });
function animateCursor() {
    rx += (mx - rx) * 0.15; ry += (my - ry) * 0.15;
    tx += (mx - tx) * 0.08; ty += (my - ty) * 0.08;
    ring.style.transform = `translate3d(${rx}px, ${ry}px, 0) translate(-50%, -50%)`;
    trail.style.transform = `translate3d(${tx}px, ${ty}px, 0) translate(-50%, -50%)`;
    requestAnimationFrame(animateCursor);
}
animateCursor();

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