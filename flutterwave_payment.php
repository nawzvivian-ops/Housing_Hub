<?php
session_start();
include "db_connect.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!file_exists("config.php")) {
    die("Error: config.php file not found. Please create it with your API keys.");
}
include "config.php";

if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in. <a href='login.php'>Login here</a>");
}

$payment_id = 0;
if (isset($_GET['payment_id'])) {
    $payment_id = intval($_GET['payment_id']);
} elseif (isset($_SESSION['card_payment_id'])) {
    $payment_id = intval($_SESSION['card_payment_id']);
}

if ($payment_id == 0) {
    die("Error: No payment ID provided. <a href='dashboard.php'>Go to Dashboard</a>");
}

$users_columns = [];
$columns_query = mysqli_query($conn, "SHOW COLUMNS FROM users");
while($col = mysqli_fetch_assoc($columns_query)) { $users_columns[] = $col['Field']; }

$properties_columns = [];
$prop_columns_query = mysqli_query($conn, "SHOW COLUMNS FROM properties");
while($col = mysqli_fetch_assoc($prop_columns_query)) { $properties_columns[] = $col['Field']; }

$name_column = 'id';
if (in_array('username', $users_columns)) { $name_column = 'username'; }
elseif (in_array('name', $users_columns)) { $name_column = 'name'; }
elseif (in_array('full_name', $users_columns)) { $name_column = 'full_name'; }

$email_select = in_array('email', $users_columns) ? 'u.email' : "CONCAT('user', u.id, '@housinghub.local')";
$phone_select = in_array('phone', $users_columns) ? 'u.phone' : "'256700000000'";

$address_column = "'N/A'";
if (in_array('address', $properties_columns)) { $address_column = 'pr.address'; }
elseif (in_array('location', $properties_columns)) { $address_column = 'pr.location'; }

$sql = "
    SELECT p.*, u.id as user_id, u.{$name_column} as username,
           {$email_select} as email, {$phone_select} as phone,
           pr.property_name, {$address_column} as address
    FROM payments p
    JOIN users u ON p.tenant_id = u.id
    JOIN properties pr ON p.property_id = pr.id
    WHERE p.id = ? AND p.tenant_id = ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) { die("Database error: " . $conn->error); }

$stmt->bind_param("ii", $payment_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

if (!$payment) {
    echo "<h3>Debug Information:</h3>";
    echo "<p>Payment ID: " . $payment_id . "</p>";
    echo "<p>Your User ID: " . $_SESSION['user_id'] . "</p>";
    $check = mysqli_query($conn, "SELECT * FROM payments WHERE id = $payment_id");
    if ($check && mysqli_num_rows($check) > 0) {
        $p = mysqli_fetch_assoc($check);
        echo "<p style='color:red;'>Payment belongs to user ID: " . $p['tenant_id'] . " (Not you!)</p>";
    } else {
        echo "<p style='color:red;'>Payment record not found in database.</p>";
    }
    echo "<p><a href='dashboard.php'>← Back to Dashboard</a></p>";
    die();
}

$email = !empty($payment['email']) && $payment['email'] != 'user' . $payment['user_id'] . '@housinghub.local'
    ? $payment['email'] : 'user' . $payment['user_id'] . '@housinghub.local';
$phone = !empty($payment['phone']) && $payment['phone'] != '256700000000' ? $payment['phone'] : '256700000000';
$username = !empty($payment['username']) ? $payment['username'] : 'User ' . $payment['user_id'];

if (!defined('FLUTTERWAVE_PUBLIC_KEY') || empty(FLUTTERWAVE_PUBLIC_KEY)) {
    die("Error: Flutterwave API keys not configured. Please check config.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Card Payment &ndash; HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://checkout.flutterwave.com/v3.js"></script>
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
.nav-back{font-size:11px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);text-decoration:none;transition:color .3s}
.nav-back:hover{color:var(--gold)}

/* PAGE */
.page-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:60px 24px;position:relative;z-index:10}
.payment-card{width:100%;max-width:560px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:16px;overflow:hidden;animation:fadeUp .8s ease .1s both}
@keyframes fadeUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}

/* HEADER */
.card-header{padding:30px 36px;border-bottom:1px solid var(--border);background:rgba(59,130,246,.05);display:flex;align-items:center;gap:16px}
.card-header-icon{width:52px;height:52px;background:rgba(59,130,246,.12);border:1px solid rgba(59,130,246,.25);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0}
.card-eyebrow{font-size:10px;font-weight:500;letter-spacing:3px;text-transform:uppercase;color:rgba(147,197,253,.7);margin-bottom:6px}
.card-title{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:var(--white);line-height:1.1}
.card-title em{color:#93c5fd;font-style:italic}

/* PROPERTY */
.prop-info{padding:22px 36px;border-bottom:1px solid var(--border);background:rgba(255,255,255,.02)}
.detail-row{display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.detail-row:last-child{border-bottom:none}
.detail-label{font-size:11px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.3)}
.detail-value{font-size:14px;font-weight:500;color:var(--white);text-align:right;max-width:280px}
.detail-value.ref{font-family:monospace;font-size:11px;color:var(--gold);letter-spacing:1px}
.detail-value.hint{font-size:10px;color:rgba(255,255,255,.25);display:block;margin-top:2px}

/* AMOUNT */
.amount-block{padding:24px 36px;border-bottom:1px solid var(--border);background:rgba(59,130,246,.04);text-align:center}
.amount-label{font-size:11px;font-weight:500;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:8px}
.amount-value{font-family:'Cormorant Garamond',serif;font-size:46px;font-weight:700;color:#93c5fd;line-height:1}
.amount-currency{font-size:16px;font-weight:600;color:rgba(147,197,253,.6);margin-right:6px}

/* METHODS */
.methods-block{padding:20px 36px;border-bottom:1px solid var(--border)}
.methods-label{font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:14px}
.method-tags{display:flex;flex-wrap:wrap;gap:8px}
.method-tag{padding:7px 14px;border:1px solid var(--border);border-radius:6px;font-size:11px;font-weight:600;color:var(--muted);background:rgba(255,255,255,.03)}

/* ACTION */
.action-block{padding:28px 36px;border-bottom:1px solid var(--border)}
.pay-btn{width:100%;padding:16px;background:linear-gradient(135deg,#3b82f6,#6366f1);color:#fff;border:none;border-radius:10px;font-family:'Outfit',sans-serif;font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;transition:all .3s;display:flex;align-items:center;justify-content:center;gap:10px}
.pay-btn:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(59,130,246,.35)}
.pay-btn:disabled{background:#2a2a3a;color:rgba(255,255,255,.3);cursor:not-allowed;transform:none;box-shadow:none}
.loader{display:none;margin:20px auto;border:3px solid rgba(255,255,255,.08);border-top:3px solid #93c5fd;border-radius:50%;width:36px;height:36px;animation:spin 1s linear infinite}
@keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}

/* SECURITY */
.security-block{padding:20px 36px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;gap:14px}
.security-icon{font-size:22px;flex-shrink:0;margin-top:2px}
.security-text{font-size:12px;color:var(--muted);line-height:1.6}
.security-text strong{color:var(--white);display:block;margin-bottom:2px}

/* FOOTER */
.card-footer{padding:20px 36px;text-align:center}
.cancel-link{font-size:12px;color:var(--muted);text-decoration:none;letter-spacing:1px;transition:color .3s}
.cancel-link:hover{color:var(--gold)}

footer{padding:24px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}

@media(max-width:600px){
  .top-nav{padding:16px 24px}
  .card-header,.prop-info,.amount-block,.methods-block,.action-block,.security-block,.card-footer{padding-left:20px;padding-right:20px}
  .amount-value{font-size:34px}
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
      <div class="card-header-icon">&#128179;</div>
      <div>
        <div class="card-eyebrow">Secure Payment</div>
        <div class="card-title">Card <em>Payment</em></div>
      </div>
    </div>

    <div class="prop-info">
      <div class="detail-row">
        <span class="detail-label">Property</span>
        <span class="detail-value"><?php echo htmlspecialchars($payment['property_name']); ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Location</span>
        <span class="detail-value"><?php echo htmlspecialchars($payment['address']); ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Tenant</span>
        <span class="detail-value"><?php echo htmlspecialchars($username); ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Reference</span>
        <span class="detail-value">
          <span class="ref"><?php echo htmlspecialchars($payment['transaction_ref']); ?></span>
          <span class="hint">Keep this reference for your records</span>
        </span>
      </div>
    </div>

    <div class="amount-block">
      <div class="amount-label">Amount to Pay</div>
      <div class="amount-value"><span class="amount-currency">UGX</span><?php echo number_format($payment['amount']); ?></div>
    </div>

    <div class="methods-block">
      <div class="methods-label">Accepted Methods</div>
      <div class="method-tags">
        <div class="method-tag">&#128179; Visa</div>
        <div class="method-tag">&#128179; Mastercard</div>
        <div class="method-tag">&#128179; Verve</div>
        <div class="method-tag">&#128241; Mobile Money</div>
      </div>
    </div>

    <div class="action-block">
      <button class="pay-btn" onclick="initiatePayment()" id="payButton">
        &#128274; Pay UGX <?php echo number_format($payment['amount']); ?> Securely
      </button>
      <div class="loader" id="loader"></div>
    </div>

    <div class="security-block">
      <span class="security-icon">&#128274;</span>
      <div class="security-text">
        <strong>Your payment is encrypted &amp; secure</strong>
        We never store your card details. All transactions are processed through Flutterwave&rsquo;s PCI-DSS compliant gateway.
      </div>
    </div>

    <div class="card-footer">
      <a href="payment_method.php?property_id=<?php echo $payment['property_id']; ?>&action=rent" class="cancel-link">
        &larr; Choose a Different Payment Method
      </a>
    </div>

  </div>
</div>

<footer>&copy; 2026 HousingHub | All Rights Reserved</footer>

<script>
console.log("=== Flutterwave Payment Page Loaded ===");
console.log("Payment ID: <?php echo $payment_id; ?>");
console.log("Amount: UGX <?php echo number_format($payment['amount']); ?>");

function initiatePayment() {
    const button = document.getElementById('payButton');
    const loader = document.getElementById('loader');
    console.log("Initiating payment...");
    button.disabled = true;
    button.innerHTML = '&#9203; Loading Payment Gateway...';
    loader.style.display = 'block';
    setTimeout(makePayment, 500);
}

function makePayment() {
    console.log("Calling Flutterwave Checkout...");
    try {
        FlutterwaveCheckout({
            public_key: "<?php echo FLUTTERWAVE_PUBLIC_KEY; ?>",
            tx_ref: "<?php echo $payment['transaction_ref']; ?>",
            amount: <?php echo $payment['amount']; ?>,
            currency: "UGX",
            country: "UG",
            payment_options: "card,mobilemoney,ussd",
            customer: {
                email: "<?php echo addslashes($email); ?>",
                phone_number: "<?php echo addslashes($phone); ?>",
                name: "<?php echo addslashes($username); ?>",
            },
            customizations: {
                title: "HousingHub - Rent Payment",
                description: "Payment for <?php echo addslashes($payment['property_name']); ?>",
            },
            callback: function (data) {
                console.log("Payment callback:", data);
                if (data.status === "successful" || data.status === "completed") {
                    console.log("Payment successful!");
                    window.location.href = "verify_payment.php?transaction_id=" + data.transaction_id + "&tx_ref=" + data.tx_ref + "&payment_id=<?php echo $payment['id']; ?>";
                } else {
                    console.log("Payment failed:", data.status);
                    alert("Payment was not completed. Status: " + data.status);
                    resetButton();
                }
            },
            onclose: function() {
                console.log("Payment window closed");
                resetButton();
            },
        });
        console.log("Flutterwave initialized");
    } catch (error) {
        console.error("Flutterwave error:", error);
        alert("Failed to load payment gateway: " + error.message);
        resetButton();
    }
}

function resetButton() {
    const button = document.getElementById('payButton');
    const loader = document.getElementById('loader');
    button.disabled = false;
    button.innerHTML = '&#128274; Pay UGX <?php echo number_format($payment['amount']); ?> Securely';
    loader.style.display = 'none';
}

window.addEventListener('load', function() {
    if (typeof FlutterwaveCheckout === 'undefined') {
        console.error("Flutterwave script not loaded!");
        alert("Payment gateway unavailable. Please refresh the page.");
        document.getElementById('payButton').disabled = true;
    } else {
        console.log("Flutterwave ready");
    }
});

// CURSOR
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