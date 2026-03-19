
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
 
$error = '';
 
if (isset($_POST['add'])) {
    $fullname        = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $phone           = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $email           = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $property_id     = (int)$_POST['property_id'];
    $gender          = mysqli_real_escape_string($conn, $_POST['gender'] ?? '');
    $national_id     = mysqli_real_escape_string($conn, trim($_POST['national_id'] ?? ''));
    $occupation      = mysqli_real_escape_string($conn, trim($_POST['occupation'] ?? ''));
    $lease_start     = mysqli_real_escape_string($conn, $_POST['lease_start'] ?? '');
    $lease_end       = mysqli_real_escape_string($conn, $_POST['lease_end'] ?? '');
    $emergency_name  = mysqli_real_escape_string($conn, trim($_POST['emergency_name'] ?? ''));
    $emergency_phone = mysqli_real_escape_string($conn, trim($_POST['emergency_phone'] ?? ''));
 
    $prop_val    = $property_id > 0 ? $property_id : 'NULL';
    $ls_val      = $lease_start ? "'$lease_start'" : 'NULL';
    $le_val      = $lease_end   ? "'$lease_end'"   : 'NULL';
 
    $q = mysqli_query($conn, "INSERT INTO tenants (fullname, phone, email, property_id, gender, national_id, occupation, lease_start, lease_end, emergency_name, emergency_phone, status, created_at)
         VALUES ('$fullname','$phone','$email',$prop_val,'$gender','$national_id','$occupation',$ls_val,$le_val,'$emergency_name','$emergency_phone','Active',NOW())");
 
    if ($q) {
        header("Location: admin_dashboard.php?page=tenants"); exit();
    } else {
        $error = "Error adding tenant: " . mysqli_error($conn);
    }
}
 
$properties = mysqli_query($conn, "SELECT id, property_name FROM properties ORDER BY property_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add New Tenant | HousingHub Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25)}
html,body{min-height:100vh;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);display:flex;align-items:flex-start;justify-content:center;padding:40px 20px 60px}
body{background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%),var(--ink)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
.wrap{position:relative;z-index:10;width:100%;max-width:560px}
.back{display:inline-flex;align-items:center;gap:8px;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);text-decoration:none;margin-bottom:24px;transition:color .2s}
.back:hover{color:var(--gold)}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:14px;padding:36px;box-shadow:0 20px 60px rgba(0,0,0,.4)}
.card-eyebrow{font-size:10px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gold);text-align:center;margin-bottom:8px}
.card-title{font-family:"Cormorant Garamond",serif;font-size:30px;font-weight:700;color:var(--white);text-align:center;margin-bottom:6px}
.card-title em{color:var(--gold);font-style:italic}
.card-sub{font-size:12px;color:var(--muted);text-align:center;margin-bottom:28px}
.divider{height:1px;background:var(--border);margin-bottom:24px}
.section-label{font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.25);margin-bottom:14px;margin-top:8px;padding-bottom:6px;border-bottom:1px solid var(--border)}
.fl{margin-bottom:14px}
.fl label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.fl input,.fl select{width:100%;padding:11px 13px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .25s}
.fl input:focus,.fl select:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
.fl input::placeholder{color:var(--muted)}
.fl select option{background:var(--ink);color:var(--white)}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.alert{padding:12px 16px;border-radius:7px;font-size:13px;margin-bottom:18px}
.alert.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.btn{width:100%;padding:13px;background:var(--gold);border:none;color:var(--ink);font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;border-radius:7px;cursor:pointer;transition:all .3s;margin-top:8px}
.btn:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 8px 24px rgba(200,164,60,.3)}
@media(max-width:500px){.grid2{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="wrap">
  <a href="admin_dashboard.php?page=tenants" class="back">← Back to Tenants</a>
  <div class="card">
    <div class="card-eyebrow">HousingHub Admin</div>
    <div class="card-title">Add <em>New Tenant</em></div>
    <div class="card-sub">Fill in the tenant's details to register them in the system</div>
    <div class="divider"></div>
 
    <?php if($error): ?>
    <div class="alert error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
 
    <form method="POST">
 
      <div class="section-label">Personal Information</div>
      <div class="fl"><label>Full Name</label><input type="text" name="fullname" placeholder="e.g. Nakato Sandra" required></div>
      <div class="grid2">
        <div class="fl"><label>Phone Number</label><input type="tel" name="phone" placeholder="+256 700 000000"></div>
        <div class="fl"><label>Email Address</label><input type="email" name="email" placeholder="email@example.com"></div>
      </div>
      <div class="grid2">
        <div class="fl"><label>Gender</label>
          <select name="gender">
            <option value="">— Select —</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div class="fl"><label>National ID</label><input type="text" name="national_id" placeholder="e.g. CM90012345XXXXX"></div>
      </div>
      <div class="fl"><label>Occupation</label><input type="text" name="occupation" placeholder="e.g. Teacher, Engineer..."></div>
 
      <div class="section-label">Property & Lease</div>
      <div class="fl"><label>Assign Property</label>
        <select name="property_id">
          <option value="">— Not assigned yet —</option>
          <?php while($prop = mysqli_fetch_assoc($properties)): ?>
          <option value="<?= $prop['id'] ?>"><?= htmlspecialchars($prop['property_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="grid2">
        <div class="fl"><label>Lease Start</label><input type="date" name="lease_start"></div>
        <div class="fl"><label>Lease End</label><input type="date" name="lease_end"></div>
      </div>
 
      <div class="section-label">Emergency Contact</div>
      <div class="grid2">
        <div class="fl"><label>Contact Name</label><input type="text" name="emergency_name" placeholder="Emergency contact name"></div>
        <div class="fl"><label>Contact Phone</label><input type="tel" name="emergency_phone" placeholder="+256 700 000000"></div>
      </div>
 
      <button type="submit" name="add" class="btn">Add Tenant →</button>
    </form>
  </div>
</div>
</body>
</html>