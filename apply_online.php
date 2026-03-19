<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
$success = $error = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname    = mysqli_real_escape_string($conn, trim($_POST['fullname'] ?? ''));
    $email       = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $phone       = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $national_id = mysqli_real_escape_string($conn, trim($_POST['national_id'] ?? ''));
    $occupation  = mysqli_real_escape_string($conn, trim($_POST['occupation'] ?? ''));
    $employer    = mysqli_real_escape_string($conn, trim($_POST['employer'] ?? ''));
    $income      = mysqli_real_escape_string($conn, trim($_POST['income'] ?? ''));
    $property_id = (int)($_POST['property_id'] ?? 0);
    $move_in     = mysqli_real_escape_string($conn, $_POST['move_in'] ?? '');
    $duration    = mysqli_real_escape_string($conn, $_POST['duration'] ?? '');
    $occupants   = (int)($_POST['occupants'] ?? 1);
    $prev_addr   = mysqli_real_escape_string($conn, trim($_POST['prev_address'] ?? ''));
    $reason      = mysqli_real_escape_string($conn, trim($_POST['reason'] ?? ''));
    $ref_name    = mysqli_real_escape_string($conn, trim($_POST['ref_name'] ?? ''));
    $ref_phone   = mysqli_real_escape_string($conn, trim($_POST['ref_phone'] ?? ''));
    $notes       = mysqli_real_escape_string($conn, trim($_POST['notes'] ?? ''));
 
    if (!$fullname || !$email || !$phone || !$property_id) {
        $error = "Please fill in all required fields and select a property.";
    } else {
        // Check if tenant_applications table exists, create if not
        mysqli_query($conn, "CREATE TABLE IF NOT EXISTS tenant_applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            fullname VARCHAR(200) NOT NULL,
            email VARCHAR(200),
            phone VARCHAR(50),
            national_id VARCHAR(100),
            occupation VARCHAR(200),
            employer VARCHAR(200),
            monthly_income VARCHAR(100),
            property_id INT,
            desired_move_in DATE,
            lease_duration VARCHAR(100),
            num_occupants INT DEFAULT 1,
            previous_address TEXT,
            reason_for_moving TEXT,
            reference_name VARCHAR(200),
            reference_phone VARCHAR(100),
            additional_notes TEXT,
            status VARCHAR(50) DEFAULT 'pending',
            created_at DATETIME DEFAULT NOW()
        )");
 
        $move_val = $move_in ? "'$move_in'" : 'NULL';
        $q = mysqli_query($conn, "INSERT INTO tenant_applications
            (fullname, email, phone, national_id, occupation, employer, monthly_income,
             property_id, desired_move_in, lease_duration, num_occupants,
             previous_address, reason_for_moving, reference_name, reference_phone,
             additional_notes, status, created_at)
            VALUES
            ('$fullname','$email','$phone','$national_id','$occupation','$employer','$income',
             $property_id, $move_val, '$duration', $occupants,
             '$prev_addr','$reason','$ref_name','$ref_phone',
             '$notes','pending',NOW())");
 
        if ($q) {
            // Notify admin
            $safe_name = mysqli_real_escape_string($conn, $fullname);
            mysqli_query($conn, "INSERT INTO notifications (user_id, tenant_id, title, message, status, date)
                VALUES (0, 0, 'New Tenant Application Received 📋',
                'A new online application has been submitted by $safe_name ($email) for property ID $property_id. Review it in the admin panel.',
                'unread', NOW())");
            $success = "Your application has been submitted successfully! We will review it and contact you within 24–48 hours.";
        } else {
            $error = "Something went wrong. Please try again. " . mysqli_error($conn);
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
<title>Apply Online | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25)}
html,body{min-height:100vh;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white)}
body{background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%),var(--ink)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
 
/* HEADER */
.site-header{position:fixed;top:0;left:0;right:0;z-index:999;display:flex;align-items:center;justify-content:space-between;padding:14px 40px;background:rgba(200,164,60,.97);box-shadow:0 2px 20px rgba(0,0,0,.3)}
.logo{display:flex;align-items:center;gap:12px;text-decoration:none}
.logo img{width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.3)}
.logo-text{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;letter-spacing:2px;color:var(--ink)}
.logo-sub{font-size:9px;color:rgba(4,9,26,.6);letter-spacing:1.5px}
.header-links{display:flex;gap:6px;align-items:center}
.header-links a{font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--ink);text-decoration:none;padding:8px 14px;border-radius:2px;transition:background .2s}
.header-links a:hover{background:rgba(4,9,26,.1)}
.header-links .hl-login{background:rgba(4,9,26,.12)}
 
/* MAIN */
.main{position:relative;z-index:10;padding:120px 20px 60px;max-width:780px;margin:0 auto}
 
/* HERO BLOCK */
.app-hero{text-align:center;margin-bottom:40px}
.app-eyebrow{font-size:10px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gold);margin-bottom:10px}
.app-title{font-family:"Cormorant Garamond",serif;font-size:clamp(32px,5vw,56px);font-weight:700;color:var(--white);line-height:1.1;margin-bottom:12px}
.app-title em{color:var(--gold);font-style:italic}
.app-sub{font-size:14px;color:var(--muted);line-height:1.7;max-width:520px;margin:0 auto}
 
/* PROGRESS STEPS */
.progress{display:flex;gap:0;margin-bottom:36px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;overflow:hidden}
.prog-step{flex:1;padding:14px 10px;text-align:center;font-size:11px;font-weight:600;letter-spacing:.5px;color:var(--muted);border-right:1px solid var(--border);transition:all .3s;cursor:pointer}
.prog-step:last-child{border-right:none}
.prog-step.active{background:rgba(200,164,60,.1);color:var(--gold);border-bottom:2px solid var(--gold)}
.prog-step.done{color:#86efac}
.prog-num{display:block;font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;margin-bottom:2px}
 
/* FORM CARD */
.form-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:14px;padding:32px;margin-bottom:20px}
.fc-title{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--white);margin-bottom:4px}
.fc-sub{font-size:12px;color:var(--muted);margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid var(--border)}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.fl{margin-bottom:14px}
.fl label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.fl input,.fl select,.fl textarea{width:100%;padding:11px 13px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .25s}
.fl input:focus,.fl select:focus,.fl textarea:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
.fl input::placeholder,.fl textarea::placeholder{color:var(--muted)}
.fl select option{background:var(--ink);color:var(--white)}
.fl textarea{resize:vertical;min-height:80px}
.req{color:var(--gold)}
 
/* PROPERTY SELECTOR */
.prop-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;margin-bottom:6px}
.prop-opt{border:1px solid var(--border);border-radius:8px;padding:14px;cursor:pointer;transition:all .25s;position:relative}
.prop-opt:hover{border-color:var(--gb);background:rgba(200,164,60,.04)}
.prop-opt.selected{border-color:var(--gold);background:rgba(200,164,60,.08)}
.prop-opt.selected::after{content:"✓";position:absolute;top:10px;right:12px;color:var(--gold);font-weight:700;font-size:14px}
.prop-name{font-size:13px;font-weight:600;color:var(--white);margin-bottom:3px}
.prop-addr{font-size:11px;color:var(--muted);margin-bottom:6px}
.prop-rent{font-size:12px;font-weight:700;color:var(--gold)}
.prop-type{font-size:10px;color:var(--muted);letter-spacing:1px}
 
/* ALERT */
.alert{padding:14px 18px;border-radius:8px;font-size:13px;margin-bottom:20px;display:flex;align-items:flex-start;gap:10px}
.alert.success{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.alert.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
 
/* SUBMIT */
.submit-btn{width:100%;padding:14px;background:var(--gold);border:none;color:var(--ink);font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;border-radius:7px;cursor:pointer;transition:all .3s;margin-top:6px}
.submit-btn:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,164,60,.35)}
 
/* SUCCESS SCREEN */
.success-screen{text-align:center;padding:60px 20px}
.success-icon{font-size:64px;display:block;margin-bottom:20px;animation:pop .5s cubic-bezier(.23,1,.32,1)}
@keyframes pop{from{transform:scale(0)}to{transform:scale(1)}}
.success-title{font-family:"Cormorant Garamond",serif;font-size:36px;font-weight:700;color:var(--white);margin-bottom:10px}
.success-title em{color:var(--gold);font-style:italic}
.success-msg{font-size:14px;color:var(--muted);line-height:1.7;max-width:460px;margin:0 auto 28px}
.timeline-steps{display:flex;flex-direction:column;gap:12px;max-width:380px;margin:0 auto 32px;text-align:left}
.ts{display:flex;gap:14px;align-items:flex-start}
.tsn{width:28px;height:28px;border-radius:50%;background:rgba(200,164,60,.15);border:1px solid var(--gb);color:var(--gold);font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.tst{font-size:13px;color:var(--muted);line-height:1.5}
.back-link{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border:1px solid var(--gb);color:var(--gold);font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;text-decoration:none;border-radius:6px;transition:all .2s}
.back-link:hover{background:rgba(200,164,60,.1)}
 
@media(max-width:600px){.grid2{grid-template-columns:1fr}.prop-grid{grid-template-columns:1fr}.progress{flex-wrap:wrap}.prog-step{min-width:50%}.site-header{padding:12px 20px}}
</style>
</head>
<body>
 
<!-- HEADER -->
<div class="site-header">
  <a href="index.html" class="logo">
    <img src="image/hub.jpg" alt="HousingHub">
    <div><div class="logo-text">HOUSING HUB</div><div class="logo-sub">"Your Property, Our Priority"</div></div>
  </a>
  <div class="header-links">
    <a href="properties.php">Browse Properties</a>
    <a href="works.php">How It Works</a>
    <a href="index.php" class="hl-login">Login</a>
  </div>
</div>
 
<div class="main">
 
<?php if($success): ?>
  <!-- SUCCESS SCREEN -->
  <div class="form-card success-screen">
    <span class="success-icon">🎉</span>
    <div class="success-title">Application <em>Submitted!</em></div>
    <p class="success-msg"><?= htmlspecialchars($success) ?></p>
    <div class="timeline-steps">
      <div class="ts"><div class="tsn">✓</div><div class="tst"><strong style="color:var(--white)">Application Received</strong> — Your details are in our system.</div></div>
      <div class="ts"><div class="tsn">2</div><div class="tst"><strong style="color:var(--white)">Under Review</strong> — Our team will verify your information within 24–48 hours.</div></div>
      <div class="ts"><div class="tsn">3</div><div class="tst"><strong style="color:var(--white)">Decision Notification</strong> — You'll receive a call or email with the outcome.</div></div>
      <div class="ts"><div class="tsn">4</div><div class="tst"><strong style="color:var(--white)">Move In</strong> — If approved, lease is signed and you get your keys!</div></div>
    </div>
    <a href="properties.php" class="back-link">← Browse More Properties</a>
  </div>
 
<?php else: ?>
 
  <div class="app-hero">
    <div class="app-eyebrow">Online Application</div>
    <div class="app-title">Apply for Your Next <em>Home</em></div>
    <p class="app-sub">Fill in the form below to apply for a property on HousingHub. No paper, no queues — just submit and track your application online.</p>
  </div>
 
  <?php if($error): ?>
  <div class="alert error">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
 
  <form method="POST">
 
    <!-- SECTION 1: PERSONAL INFO -->
    <div class="form-card">
      <div class="fc-title">1. Personal Information</div>
      <div class="fc-sub">Tell us about yourself so we can process your application.</div>
      <div class="fl"><label>Full Name <span class="req">*</span></label><input type="text" name="fullname" placeholder="e.g. Nakato Sandra" required></div>
      <div class="grid2">
        <div class="fl"><label>Email Address <span class="req">*</span></label><input type="email" name="email" placeholder="your@email.com" required></div>
        <div class="fl"><label>Phone Number <span class="req">*</span></label><input type="tel" name="phone" placeholder="+256 700 000000" required></div>
      </div>
      <div class="grid2">
        <div class="fl"><label>National ID (NIN)</label><input type="text" name="national_id" placeholder="e.g. CM90012345XXXXX"></div>
        <div class="fl"><label>Occupation / Job Title</label><input type="text" name="occupation" placeholder="e.g. Teacher, Engineer"></div>
      </div>
      <div class="grid2">
        <div class="fl"><label>Employer / Company</label><input type="text" name="employer" placeholder="Name of employer"></div>
        <div class="fl"><label>Monthly Income (UGX)</label><input type="text" name="income" placeholder="e.g. 1,500,000"></div>
      </div>
    </div>
 
    <!-- SECTION 2: PROPERTY SELECTION -->
    <div class="form-card">
      <div class="fc-title">2. Choose a Property <span class="req">*</span></div>
      <div class="fc-sub">Select the property you would like to apply for.</div>
      <?php if(mysqli_num_rows($properties) > 0): ?>
      <div class="prop-grid" id="prop-grid">
        <?php mysqli_data_seek($properties, 0); while($p = mysqli_fetch_assoc($properties)): ?>
        <div class="prop-opt" onclick="selectProp(<?= $p['id'] ?>, this)">
          <div class="prop-name"><?= htmlspecialchars($p['property_name']) ?></div>
          <div class="prop-addr">📍 <?= htmlspecialchars($p['address']??'Uganda') ?></div>
          <div class="prop-rent">UGX <?= number_format($p['rent_amount']??0) ?>/mo</div>
          <div class="prop-type"><?= htmlspecialchars($p['property_type']??'Residential') ?></div>
        </div>
        <?php endwhile; ?>
      </div>
      <input type="hidden" name="property_id" id="property_id" value="0">
      <div id="prop-error" style="font-size:12px;color:#fca5a5;margin-top:6px;display:none">⚠️ Please select a property above.</div>
      <?php else: ?>
        <p style="font-size:13px;color:var(--muted);padding:20px;text-align:center;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px">No available properties at the moment. <a href="properties.php" style="color:var(--gold)">Browse all listings →</a></p>
        <input type="hidden" name="property_id" value="0">
      <?php endif; ?>
    </div>
 
    <!-- SECTION 3: TENANCY DETAILS -->
    <div class="form-card">
      <div class="fc-title">3. Tenancy Details</div>
      <div class="fc-sub">Help us understand your rental needs.</div>
      <div class="grid2">
        <div class="fl"><label>Desired Move-in Date</label><input type="date" name="move_in"></div>
        <div class="fl"><label>Preferred Lease Duration</label>
          <select name="duration">
            <option value="">— Select —</option>
            <option value="6 months">6 Months</option>
            <option value="1 year">1 Year</option>
            <option value="2 years">2 Years</option>
            <option value="Month to month">Month to Month</option>
          </select>
        </div>
      </div>
      <div class="grid2">
        <div class="fl"><label>Number of Occupants</label><input type="number" name="occupants" min="1" max="20" value="1" placeholder="1"></div>
        <div class="fl"><label>Previous Address</label><input type="text" name="prev_address" placeholder="Your current/previous address"></div>
      </div>
      <div class="fl"><label>Reason for Moving</label><textarea name="reason" placeholder="Why are you looking for a new place?"></textarea></div>
    </div>
 
    <!-- SECTION 4: REFERENCE -->
    <div class="form-card">
      <div class="fc-title">4. Reference Contact</div>
      <div class="fc-sub">Provide someone we can contact as a reference (employer, landlord, colleague).</div>
      <div class="grid2">
        <div class="fl"><label>Reference Full Name</label><input type="text" name="ref_name" placeholder="Full name of reference"></div>
        <div class="fl"><label>Reference Phone</label><input type="tel" name="ref_phone" placeholder="+256 700 000000"></div>
      </div>
      <div class="fl"><label>Additional Notes (optional)</label><textarea name="notes" placeholder="Anything else you'd like us to know..."></textarea></div>
    </div>
 
    <button type="submit" class="submit-btn" onclick="return validateProp()">Submit Application →</button>
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
function validateProp() {
  const val = document.getElementById('property_id')?.value;
  if (!val || val === '0') {
    document.getElementById('prop-error').style.display = 'block';
    document.getElementById('prop-grid')?.scrollIntoView({behavior:'smooth', block:'center'});
    return false;
  }
  return true;
}
</script>
</body>
</html>