
<?php
session_start();
include "db_connect.php";
require_once __DIR__ . "/send_mail.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
$success = $error = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname    = mysqli_real_escape_string($conn, trim($_POST['fullname'] ?? ''));
    $email       = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $phone       = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $national_id = mysqli_real_escape_string($conn, trim($_POST['national_id'] ?? ''));
    $property_id = (int)($_POST['property_id'] ?? 0);
    $lease_start = mysqli_real_escape_string($conn, $_POST['lease_start'] ?? '');
    $lease_end   = mysqli_real_escape_string($conn, $_POST['lease_end'] ?? '');
    $duration    = mysqli_real_escape_string($conn, $_POST['duration'] ?? '');
    $occupants   = (int)($_POST['occupants'] ?? 1);
    $move_in     = mysqli_real_escape_string($conn, $_POST['move_in'] ?? '');
    $prev_addr   = mysqli_real_escape_string($conn, trim($_POST['prev_address'] ?? ''));
    $purpose     = mysqli_real_escape_string($conn, $_POST['purpose'] ?? '');
    $terms       = isset($_POST['terms']) ? 1 : 0;
    $signature   = mysqli_real_escape_string($conn, trim($_POST['signature'] ?? ''));
    $notes       = mysqli_real_escape_string($conn, trim($_POST['notes'] ?? ''));
 
    if (!$fullname || !$email || !$phone || !$property_id) {
        $error = "Please fill in all required fields and select a property.";
    } elseif (!$terms) {
        $error = "You must agree to the lease terms and conditions to proceed.";
    } elseif (!$signature) {
        $error = "Please type your full name as your digital signature.";
    } else {
        // Auto-create lease_applications table if missing
        mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `lease_applications` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `fullname` VARCHAR(200) NOT NULL,
            `email` VARCHAR(200) DEFAULT NULL,
            `phone` VARCHAR(50) DEFAULT NULL,
            `national_id` VARCHAR(100) DEFAULT NULL,
            `property_id` INT DEFAULT NULL,
            `lease_start` DATE DEFAULT NULL,
            `lease_end` DATE DEFAULT NULL,
            `lease_duration` VARCHAR(100) DEFAULT NULL,
            `num_occupants` INT DEFAULT 1,
            `desired_move_in` DATE DEFAULT NULL,
            `previous_address` TEXT DEFAULT NULL,
            `purpose_of_tenancy` VARCHAR(200) DEFAULT NULL,
            `digital_signature` VARCHAR(200) DEFAULT NULL,
            `terms_agreed` TINYINT DEFAULT 0,
            `additional_notes` TEXT DEFAULT NULL,
            `status` VARCHAR(50) DEFAULT 'pending',
            `admin_notes` TEXT DEFAULT NULL,
            `signed_at` DATETIME DEFAULT NULL,
            `created_at` DATETIME DEFAULT NOW()
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
 
        $ls_val  = $lease_start ? "'$lease_start'" : 'NULL';
        $le_val  = $lease_end   ? "'$lease_end'"   : 'NULL';
        $mi_val  = $move_in     ? "'$move_in'"     : 'NULL';
        $signed  = date('Y-m-d H:i:s');
 
        $q = mysqli_query($conn, "INSERT INTO lease_applications
            (fullname, email, phone, national_id, property_id, lease_start, lease_end,
             lease_duration, num_occupants, desired_move_in, previous_address,
             purpose_of_tenancy, digital_signature, terms_agreed, additional_notes,
             status, signed_at, created_at)
            VALUES
            ('$fullname','$email','$phone','$national_id',$property_id,
             $ls_val,$le_val,'$duration',$occupants,$mi_val,
             '$prev_addr','$purpose','$signature',$terms,'$notes',
             'pending','$signed',NOW())");
 
        if ($q) {
            // Get property name
            $prop = mysqli_fetch_assoc(mysqli_query($conn, "SELECT property_name, address, rent_amount FROM properties WHERE id=$property_id LIMIT 1"));
            $pname   = $prop['property_name'] ?? 'Selected Property';
            $paddr   = $prop['address'] ?? 'Uganda';
            $prent   = number_format($prop['rent_amount'] ?? 0);
 
            // ── Notify admin ──
            mysqli_query($conn, "INSERT INTO notifications (user_id, tenant_id, title, message, status, date)
                VALUES (0, 0, 'New Lease Application 📋',
                'A new lease application has been submitted by $fullname ($email) for $pname. Review it in the admin panel.',
                'unread', NOW())");
 
            // ── Email to applicant ──
            if (!empty($email)) {
                $subj = "Your Lease Application — HousingHub";
                $body = "Dear $fullname,\n\n"
                    . "Thank you for submitting your lease application on HousingHub.\n\n"
                    . "════════════════════════════════\n"
                    . "  LEASE APPLICATION RECEIVED\n"
                    . "════════════════════════════════\n"
                    . "Applicant      : $fullname\n"
                    . "Property       : $pname\n"
                    . "Address        : $paddr\n"
                    . "Monthly Rent   : UGX $prent\n"
                    . "Lease Duration : $duration\n"
                    . "Desired Move-in: $move_in\n"
                    . "Digital Signed : $signature\n"
                    . "Submitted On   : " . date('d M Y, H:i') . "\n"
                    . "Status         : ⏳ Pending Review\n"
                    . "════════════════════════════════\n\n"
                    . "WHAT HAPPENS NEXT:\n"
                    . "1. Our team will review your application within 24–48 hours.\n"
                    . "2. You will receive a call or email with the outcome.\n"
                    . "3. If approved, your official lease agreement will be sent for final signing.\n"
                    . "4. Once signed by both parties, you will receive move-in instructions.\n\n"
                    . "For questions, contact us at support@housinghuborg.ug\n\n"
                    . "Warm regards,\n"
                    . "HousingHub Team\n"
                    . "support@housinghuborg.ug";
                send_mail($email, $subj, $body);
            }
 
            // ── Email to admin ──
            send_mail('nawzvivian@gmail.com',
                "New Lease Application — $fullname",
                "A new lease application has been submitted.\n\nApplicant: $fullname\nEmail: $email\nPhone: $phone\nProperty: $pname\nDuration: $duration\nSigned: $signature\n\nLog in to the admin panel to review:\nhttp://localhost/housinghub/admin_dashboard.php?page=lease_applications"
            );
 
            $success = "Your lease application has been submitted and digitally signed. We will be in touch within 24–48 hours.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
 
$properties = mysqli_query($conn, "SELECT id, property_name, address, rent_amount, property_type FROM properties WHERE status='available' OR status='' ORDER BY property_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Apply for a Lease | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25)}
html,body{min-height:100vh;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white)}
body{background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%),var(--ink)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
.site-header{position:fixed;top:0;left:0;right:0;z-index:999;display:flex;align-items:center;justify-content:space-between;padding:14px 40px;background:rgba(200,164,60,.97);box-shadow:0 2px 20px rgba(0,0,0,.3)}
.logo{display:flex;align-items:center;gap:12px;text-decoration:none}
.logo img{width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.3)}
.logo-text{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;letter-spacing:2px;color:var(--ink)}
.logo-sub{font-size:9px;color:rgba(4,9,26,.6);letter-spacing:1.5px}
.header-links{display:flex;gap:6px}
.header-links a{font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--ink);text-decoration:none;padding:8px 14px;border-radius:2px;transition:background .2s}
.header-links a:hover{background:rgba(4,9,26,.1)}
.header-links .hl-login{background:rgba(4,9,26,.12)}
.main{position:relative;z-index:10;padding:120px 20px 60px;max-width:800px;margin:0 auto}
.app-hero{text-align:center;margin-bottom:40px}
.app-eyebrow{font-size:10px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gold);margin-bottom:10px}
.app-title{font-family:"Cormorant Garamond",serif;font-size:clamp(32px,5vw,56px);font-weight:700;color:var(--white);line-height:1.1;margin-bottom:12px}
.app-title em{color:var(--gold);font-style:italic}
.app-sub{font-size:14px;color:var(--muted);line-height:1.7;max-width:540px;margin:0 auto}
/* STEPS BAR */
.steps-bar{display:flex;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:32px}
.sb-step{flex:1;padding:14px 8px;text-align:center;font-size:11px;font-weight:600;color:var(--muted);border-right:1px solid var(--border);transition:all .3s}
.sb-step:last-child{border-right:none}
.sb-step.active{background:rgba(200,164,60,.1);color:var(--gold);border-bottom:2px solid var(--gold)}
.sb-step.done{color:#86efac}
.sb-num{display:block;font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;margin-bottom:2px}
/* FORM */
.form-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:14px;padding:32px;margin-bottom:20px}
.fc-title{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--white);margin-bottom:4px}
.fc-sub{font-size:12px;color:var(--muted);margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid var(--border)}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.fl{margin-bottom:14px}
.fl label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.fl input,.fl select,.fl textarea{width:100%;padding:11px 13px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .25s}
.fl input:focus,.fl select:focus,.fl textarea:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
.fl input::placeholder,.fl textarea::placeholder{color:var(--muted)}
.fl select option{background:var(--ink)}
.fl textarea{resize:vertical;min-height:80px}
.req{color:var(--gold)}
/* PROPERTY SELECTOR */
.prop-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;margin-bottom:6px}
.prop-opt{border:1px solid var(--border);border-radius:8px;padding:14px;cursor:pointer;transition:all .25s;position:relative}
.prop-opt:hover{border-color:var(--gb);background:rgba(200,164,60,.04)}
.prop-opt.selected{border-color:var(--gold);background:rgba(200,164,60,.08)}
.prop-opt.selected::after{content:"✓";position:absolute;top:10px;right:12px;color:var(--gold);font-weight:700}
.prop-name{font-size:13px;font-weight:600;color:var(--white);margin-bottom:3px}
.prop-addr{font-size:11px;color:var(--muted);margin-bottom:6px}
.prop-rent{font-size:12px;font-weight:700;color:var(--gold)}
.prop-type{font-size:10px;color:var(--muted);letter-spacing:.5px}
/* LEASE PREVIEW */
.lease-preview{background:rgba(255,255,255,.02);border:1px solid var(--border);border-radius:10px;padding:24px;margin-bottom:16px;font-size:13px;line-height:1.9;color:rgba(255,255,255,.75)}
.lease-preview h4{font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;color:var(--white);margin-bottom:4px}
.lease-preview .lp-sub{font-size:11px;color:var(--muted);margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)}
.lp-section{margin-bottom:14px}
.lp-section strong{color:var(--gold);font-size:11px;letter-spacing:1px;text-transform:uppercase;display:block;margin-bottom:4px}
/* SIGNATURE */
.sig-box{border:2px dashed var(--gb);border-radius:8px;padding:20px;text-align:center;margin-bottom:14px}
.sig-box input{border:none;border-bottom:2px solid var(--gold);border-radius:0;background:transparent;font-family:"Cormorant Garamond",serif;font-size:22px;font-style:italic;text-align:center;color:var(--gold);width:100%;max-width:320px;padding:8px}
.sig-box input:focus{outline:none;border-bottom-color:var(--gold-l)}
.sig-label{font-size:11px;color:var(--muted);margin-top:8px;letter-spacing:.5px}
/* TERMS */
.terms-box{background:rgba(200,164,60,.05);border:1px solid var(--gb);border-radius:8px;padding:16px;margin-bottom:16px}
.terms-check{display:flex;align-items:flex-start;gap:12px;font-size:13px;color:var(--muted);line-height:1.6;cursor:pointer}
.terms-check input[type=checkbox]{width:18px;height:18px;margin-top:2px;cursor:pointer;accent-color:var(--gold);flex-shrink:0}
/* ALERT */
.alert{padding:14px 18px;border-radius:8px;font-size:13px;margin-bottom:20px}
.alert.success{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.alert.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
/* SUBMIT */
.submit-btn{width:100%;padding:14px;background:var(--gold);border:none;color:var(--ink);font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;border-radius:7px;cursor:pointer;transition:all .3s;margin-top:6px}
.submit-btn:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,164,60,.35)}
/* SUCCESS */
.success-screen{text-align:center;padding:60px 20px}
.success-icon{font-size:64px;display:block;margin-bottom:20px;animation:pop .5s cubic-bezier(.23,1,.32,1)}
@keyframes pop{from{transform:scale(0)}to{transform:scale(1)}}
.success-title{font-family:"Cormorant Garamond",serif;font-size:36px;font-weight:700;color:var(--white);margin-bottom:10px}
.success-title em{color:var(--gold);font-style:italic}
.success-msg{font-size:14px;color:var(--muted);line-height:1.7;max-width:480px;margin:0 auto 28px}
.timeline{display:flex;flex-direction:column;gap:12px;max-width:400px;margin:0 auto 32px;text-align:left}
.tl{display:flex;gap:14px}
.tln{width:28px;height:28px;border-radius:50%;background:rgba(200,164,60,.15);border:1px solid var(--gb);color:var(--gold);font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.tlt{font-size:13px;color:var(--muted);line-height:1.5}
.back-link{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border:1px solid var(--gb);color:var(--gold);font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;text-decoration:none;border-radius:6px;transition:all .2s}
.back-link:hover{background:rgba(200,164,60,.1)}
@media(max-width:600px){.grid2{grid-template-columns:1fr}.prop-grid{grid-template-columns:1fr}.steps-bar{flex-wrap:wrap}.sb-step{min-width:50%}.site-header{padding:12px 20px}}
</style>
</head>
<body>
<div class="site-header">
  <a href="index.html" class="logo">
    <img src="image/hub.jpg" alt="HousingHub">
    <div><div class="logo-text">HOUSING HUB</div><div class="logo-sub">"Your Property, Our Priority"</div></div>
  </a>
  <div class="header-links">
    <a href="properties.php">Browse Properties</a>
    <a href="lease.php">About Leases</a>
    <a href="index.php" class="hl-login">Login</a>
  </div>
</div>
 
<div class="main">
 
<?php if($success): ?>
  <div class="form-card success-screen">
    <span class="success-icon">📄</span>
    <div class="success-title">Lease Application <em>Submitted!</em></div>
    <p class="success-msg"><?= htmlspecialchars($success) ?> A confirmation has been sent to your email.</p>
    <div class="timeline">
      <div class="tl"><div class="tln">✓</div><div class="tlt"><strong style="color:var(--white)">Application Received</strong> — Your lease application and digital signature are in our system.</div></div>
      <div class="tl"><div class="tln">2</div><div class="tlt"><strong style="color:var(--white)">Under Review</strong> — Our team reviews your details within 24–48 hours.</div></div>
      <div class="tl"><div class="tln">3</div><div class="tlt"><strong style="color:var(--white)">Official Lease Sent</strong> — If approved, the official signed lease is sent to your email.</div></div>
      <div class="tl"><div class="tln">4</div><div class="tlt"><strong style="color:var(--white)">Move In</strong> — Receive your keys and move-in instructions. Welcome home!</div></div>
    </div>
    <a href="properties.php" class="back-link">← Browse More Properties</a>
  </div>
 
<?php else: ?>
 
  <div class="app-hero">
    <div class="app-eyebrow">Online Lease Application</div>
    <div class="app-title">Apply for a <em>Lease</em> Online</div>
    <p class="app-sub">Fill in your details, select a property, review the lease terms, and sign digitally — all in one place. No printing, no queues.</p>
  </div>
 
  <div class="steps-bar">
    <div class="sb-step active"><span class="sb-num">1</span>Your Details</div>
    <div class="sb-step"><span class="sb-num">2</span>Choose Property</div>
    <div class="sb-step"><span class="sb-num">3</span>Lease Terms</div>
    <div class="sb-step"><span class="sb-num">4</span>Sign & Submit</div>
  </div>
 
  <?php if($error): ?><div class="alert error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
 
  <form method="POST" id="lease-form">
 
    <!-- SECTION 1: PERSONAL DETAILS -->
    <div class="form-card">
      <div class="fc-title">1. Your Personal Details</div>
      <div class="fc-sub">We need this to prepare your official lease agreement.</div>
      <div class="fl"><label>Full Legal Name <span class="req">*</span></label><input type="text" name="fullname" placeholder="As it appears on your National ID" required></div>
      <div class="grid2">
        <div class="fl"><label>Email Address <span class="req">*</span></label><input type="email" name="email" placeholder="your@email.com" required></div>
        <div class="fl"><label>Phone Number <span class="req">*</span></label><input type="tel" name="phone" placeholder="+256 700 000000" required></div>
      </div>
      <div class="fl"><label>National ID (NIN)</label><input type="text" name="national_id" placeholder="e.g. CM90012345XXXXX"></div>
      <div class="fl"><label>Previous / Current Address</label><input type="text" name="prev_address" placeholder="Where you currently live"></div>
    </div>
 
    <!-- SECTION 2: PROPERTY SELECTION -->
    <div class="form-card">
      <div class="fc-title">2. Select a Property <span class="req">*</span></div>
      <div class="fc-sub">Choose the property you want to lease.</div>
      <?php if(mysqli_num_rows($properties) > 0): ?>
      <div class="prop-grid" id="prop-grid">
        <?php while($p = mysqli_fetch_assoc($properties)): ?>
        <div class="prop-opt" onclick="selectProp(<?= $p['id'] ?>, this)">
          <div class="prop-name"><?= htmlspecialchars($p['property_name']) ?></div>
          <div class="prop-addr">📍 <?= htmlspecialchars($p['address'] ?? 'Uganda') ?></div>
          <div class="prop-rent">UGX <?= number_format($p['rent_amount'] ?? 0) ?>/mo</div>
          <div class="prop-type"><?= htmlspecialchars($p['property_type'] ?? 'Residential') ?></div>
        </div>
        <?php endwhile; ?>
      </div>
      <input type="hidden" name="property_id" id="property_id" value="0">
      <div id="prop-error" style="font-size:12px;color:#fca5a5;margin-top:6px;display:none">⚠️ Please select a property.</div>
      <?php else: ?>
        <p style="font-size:13px;color:var(--muted);padding:20px;text-align:center;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px">No available properties at the moment. <a href="properties.php" style="color:var(--gold)">Browse all listings →</a></p>
        <input type="hidden" name="property_id" value="0">
      <?php endif; ?>
    </div>
 
    <!-- SECTION 3: TENANCY DETAILS -->
    <div class="form-card">
      <div class="fc-title">3. Tenancy Details</div>
      <div class="fc-sub">Tell us about your planned tenancy period.</div>
      <div class="grid2">
        <div class="fl"><label>Preferred Lease Start</label><input type="date" name="lease_start" id="ls"></div>
        <div class="fl"><label>Preferred Lease End</label><input type="date" name="lease_end" id="le"></div>
      </div>
      <div class="grid2">
        <div class="fl"><label>Lease Duration</label>
          <select name="duration">
            <option value="">— Select —</option>
            <option value="6 months">6 Months</option>
            <option value="1 year">1 Year</option>
            <option value="2 years">2 Years</option>
            <option value="Month to month">Month to Month</option>
          </select>
        </div>
        <div class="fl"><label>Number of Occupants</label><input type="number" name="occupants" min="1" max="20" value="1"></div>
      </div>
      <div class="grid2">
        <div class="fl"><label>Desired Move-in Date</label><input type="date" name="move_in"></div>
        <div class="fl"><label>Purpose of Tenancy</label>
          <select name="purpose">
            <option value="Residential">Residential</option>
            <option value="Commercial">Commercial</option>
            <option value="Mixed Use">Mixed Use</option>
          </select>
        </div>
      </div>
      <div class="fl"><label>Additional Notes (optional)</label><textarea name="notes" placeholder="Any special requests or conditions you'd like to discuss..."></textarea></div>
    </div>
 
    <!-- SECTION 4: LEASE PREVIEW & SIGNATURE -->
    <div class="form-card">
      <div class="fc-title">4. Lease Preview &amp; Digital Signature</div>
      <div class="fc-sub">Review the standard lease terms below, then sign digitally to submit your application.</div>
 
      <div class="lease-preview">
        <h4>HOUSINGHUB TENANCY AGREEMENT</h4>
        <div class="lp-sub">Standard Residential / Commercial Lease — Uganda</div>
        <div class="lp-section"><strong>1. Parties</strong>This agreement is between HousingHub (acting on behalf of the Property Owner / Landlord) and the Tenant named in this application.</div>
        <div class="lp-section"><strong>2. Premises</strong>The Tenant agrees to lease the selected property for the purposes stated in this application and for residential or commercial use only as indicated.</div>
        <div class="lp-section"><strong>3. Rent &amp; Payment</strong>Monthly rent is as listed on the property. Rent is due on the 1st of each month and shall be paid via the HousingHub payment portal using MTN MoMo, Airtel Money, card, or bank transfer.</div>
        <div class="lp-section"><strong>4. Lease Term</strong>The tenancy begins and ends on the dates selected in this application. After expiry, the lease converts to month-to-month unless renewed in writing.</div>
        <div class="lp-section"><strong>5. Maintenance</strong>Structural repairs are the Landlord's responsibility. Tenants must report issues via the HousingHub maintenance portal promptly. Tenants are responsible for minor upkeep and keeping the property clean.</div>
        <div class="lp-section"><strong>6. Termination</strong>Either party may terminate this agreement with 30 days written notice via the HousingHub portal. Immediate termination may apply for breach of terms.</div>
        <div class="lp-section"><strong>7. Rules &amp; Conduct</strong>Tenants must not sublet the property, use it for illegal purposes, or cause nuisance to neighbours. Violations may result in immediate termination.</div>
        <div class="lp-section"><strong>8. Digital Acceptance</strong>By typing your full name below and checking the agreement box, you confirm you have read and agree to all terms of this lease. This constitutes a legally binding digital signature.</div>
      </div>
 
      <div class="terms-box">
        <label class="terms-check">
          <input type="checkbox" name="terms" id="terms">
          I have read and agree to all terms and conditions of this HousingHub Lease Agreement. I understand this is a legally binding digital document.
        </label>
      </div>
 
      <div class="sig-box">
        <div style="font-size:11px;color:var(--muted);letter-spacing:1px;text-transform:uppercase;margin-bottom:12px">Digital Signature — Type Your Full Legal Name</div>
        <input type="text" name="signature" id="signature" placeholder="e.g. Nakato Sandra" autocomplete="off">
        <div class="sig-label">By typing your name above you are digitally signing this lease application · <?= date('d M Y, H:i') ?></div>
      </div>
    </div>
 
    <button type="submit" class="submit-btn" onclick="return validateForm()">
      📄 Submit Lease Application &amp; Sign Digitally →
    </button>
  </form>
 
<?php endif; ?>
</div>
 
<script>
function selectProp(id, el) {
  document.querySelectorAll('.prop-opt').forEach(p => p.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('property_id').value = id;
  document.getElementById('prop-error').style.display = 'none';
}
 
function validateForm() {
  const pid = document.getElementById('property_id')?.value;
  if (!pid || pid === '0') {
    document.getElementById('prop-error').style.display = 'block';
    document.getElementById('prop-grid')?.scrollIntoView({behavior:'smooth', block:'center'});
    return false;
  }
  const terms = document.getElementById('terms');
  if (!terms?.checked) {
    alert('Please agree to the lease terms and conditions before submitting.');
    terms.scrollIntoView({behavior:'smooth', block:'center'});
    return false;
  }
  const sig = document.getElementById('signature')?.value.trim();
  if (!sig) {
    alert('Please type your full name as your digital signature.');
    document.getElementById('signature')?.focus();
    return false;
  }
  return true;
}
 
// Update steps bar as user fills form
document.addEventListener('scroll', () => {
  const cards = document.querySelectorAll('.form-card');
  const steps = document.querySelectorAll('.sb-step');
  cards.forEach((card, i) => {
    const rect = card.getBoundingClientRect();
    if (rect.top < window.innerHeight * 0.6 && i < steps.length) {
      steps.forEach(s => s.classList.remove('active'));
      steps[i].classList.add('active');
    }
  });
});
</script>
</body>
</html>