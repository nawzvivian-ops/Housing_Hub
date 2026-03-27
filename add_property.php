
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='{$_SESSION['user_id']}' LIMIT 1"));
if (!$admin || strtolower($admin['role']) !== 'admin') { header("Location: dashboard.php"); exit(); }
 
$error = $success = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_name = mysqli_real_escape_string($conn, trim($_POST['property_name'] ?? ''));
    $property_type = mysqli_real_escape_string($conn, trim($_POST['property_type'] ?? ''));
    $address       = mysqli_real_escape_string($conn, trim($_POST['address'] ?? ''));
    $units         = (int)($_POST['units'] ?? 1);
    $rent_amount   = (float)($_POST['rent_amount'] ?? 0);
    $bedrooms      = (int)($_POST['bedrooms'] ?? 0);
    $size_sqft     = (float)($_POST['size_sqft'] ?? 0);
    $purpose       = mysqli_real_escape_string($conn, trim($_POST['purpose'] ?? 'Rent'));
    $status        = mysqli_real_escape_string($conn, trim($_POST['status'] ?? 'Available'));
    $description   = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    $amenities     = mysqli_real_escape_string($conn, trim($_POST['amenities'] ?? ''));
    $owner_id      = (int)($_POST['owner_id'] ?? 0);
    $broker_id     = (int)($_POST['broker_id'] ?? 0);
 
    if (!$property_name || !$property_type || !$address) {
        $error = "Property name, type, and address are required.";
    } else {
        $owner_val  = $owner_id  > 0 ? $owner_id  : 'NULL';
        $broker_val = $broker_id > 0 ? $broker_id : 'NULL';
 
        $q = mysqli_query($conn, "INSERT INTO properties
            (property_name, property_type, address, units, rent_amount, bedrooms, size_sqft, purpose, status, description, amenities, owner_id, broker_id, created_at)
            VALUES
            ('$property_name','$property_type','$address',$units,$rent_amount,$bedrooms,$size_sqft,'$purpose','$status','$description','$amenities',$owner_val,$broker_val,NOW())");
 
        if ($q) {
            $new_id = mysqli_insert_id($conn);
            // Notify owner if assigned
            if ($owner_id > 0) {
                $pname_s = mysqli_real_escape_string($conn, $property_name);
                mysqli_query($conn, "INSERT INTO notifications (user_id, tenant_id, title, message, status, date)
                    VALUES ($owner_id, 0, 'New Property Assigned',
                    'The property \"$pname_s\" has been added and linked to your account.',
                    'unread', NOW())");
            }
            $_SESSION['admin_success'] = "✅ Property <strong>" . htmlspecialchars($property_name) . "</strong> added successfully.";
            header("Location: admin_dashboard.php?page=properties"); exit();
        } else {
            $error = "Database error: " . mysqli_error($conn);
        }
    }
}
 
// Fetch owners and brokers for dropdowns
$owners  = mysqli_query($conn, "SELECT id, fullname FROM users WHERE role='propertyowner' ORDER BY fullname ASC");
$brokers = mysqli_query($conn, "SELECT id, fullname FROM users WHERE role='broker' ORDER BY fullname ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Property | HousingHub Admin</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25);--sw:260px}
body{font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);min-height:100vh;padding:36px 40px;margin-left:var(--sw)}
body::before{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.12),transparent 55%)}
.wrap{position:relative;z-index:1;max-width:820px}
h2{font-family:"Cormorant Garamond",serif;font-size:28px;font-weight:700;color:var(--gold);margin-bottom:24px;padding-bottom:12px;border-bottom:2px solid var(--gb)}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:28px;margin-bottom:20px}
.card-title{font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;color:var(--white);margin-bottom:18px;padding-bottom:10px;border-bottom:1px solid var(--border)}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}
.fl{margin-bottom:16px}
label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
input,select,textarea{width:100%;padding:11px 14px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .25s}
input:focus,select:focus,textarea:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
input::placeholder,textarea::placeholder{color:rgba(255,255,255,.2)}
select option{background:#04091a;color:#fff}
textarea{resize:vertical;min-height:90px}
.btn-submit{padding:13px 32px;background:var(--gold);border:none;border-radius:7px;color:var(--ink);font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;transition:all .3s}
.btn-submit:hover{background:var(--gold-l);transform:translateY(-2px)}
.btn-back{padding:13px 24px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--muted);font-family:"Outfit",sans-serif;font-size:12px;font-weight:600;text-decoration:none;transition:all .3s}
.btn-back:hover{border-color:var(--gb);color:var(--white)}
.alert{padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:20px;font-weight:500}
.alert.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.alert.success{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
</style>
</head>
<body>
<div class="wrap">
  <h2>🏠 Add New Property</h2>
 
  <?php if($error): ?><div class="alert error">⚠️ <?= $error ?></div><?php endif; ?>
  <?php if($success): ?><div class="alert success">✅ <?= $success ?></div><?php endif; ?>
 
  <form method="POST">
 
    <!-- BASIC INFO -->
    <div class="card">
      <div class="card-title">Basic Information</div>
      <div class="grid2">
        <div class="fl"><label>Property Name *</label><input type="text" name="property_name" placeholder="e.g. Sunrise Apartments" required></div>
        <div class="fl">
          <label>Property Type *</label>
          <select name="property_type" required>
            <option value="">— Select type —</option>
            <option value="Residential">Residential</option>
            <option value="Commercial">Commercial</option>
            <option value="Industrial">Industrial</option>
            <option value="Agricultural">Agricultural</option>
            <option value="Land">Land</option>
            <option value="Special Purpose">Special Purpose</option>
          </select>
        </div>
      </div>
      <div class="fl"><label>Address *</label><input type="text" name="address" placeholder="e.g. Plot 14, Ntinda, Kampala" required></div>
      <div class="fl"><label>Description</label><textarea name="description" placeholder="Brief description of the property..."></textarea></div>
      <div class="fl"><label>Amenities</label><input type="text" name="amenities" placeholder="e.g. Water, Electricity, WiFi, Parking, Security"></div>
    </div>
 
    <!-- DETAILS -->
    <div class="card">
      <div class="card-title">Property Details</div>
      <div class="grid3">
        <div class="fl"><label>Number of Units</label><input type="number" name="units" min="1" value="1"></div>
        <div class="fl"><label>Rent Amount (UGX)</label><input type="number" name="rent_amount" min="0" placeholder="e.g. 500000"></div>
        <div class="fl"><label>Bedrooms</label><input type="number" name="bedrooms" min="0" value="0"></div>
      </div>
      <div class="grid3">
        <div class="fl"><label>Size (sq ft)</label><input type="number" name="size_sqft" min="0" placeholder="e.g. 1200"></div>
        <div class="fl">
          <label>Purpose</label>
          <select name="purpose">
            <option value="Rent">For Rent</option>
            <option value="Sale">For Sale</option>
            <option value="Both">Rent & Sale</option>
          </select>
        </div>
        <div class="fl">
          <label>Status</label>
          <select name="status">
            <option value="Available">Available</option>
            <option value="Occupied">Occupied</option>
            <option value="Archived">Archived</option>
          </select>
        </div>
      </div>
    </div>
 
    <!-- ASSIGNMENT -->
    <div class="card">
      <div class="card-title">Assign to Owner / Broker <span style="font-size:12px;font-weight:400;color:var(--muted)">(Optional — can be done later)</span></div>
      <div class="grid2">
        <div class="fl">
          <label>Property Owner</label>
          <select name="owner_id">
            <option value="">— No owner yet —</option>
            <?php while($o = mysqli_fetch_assoc($owners)): ?>
            <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['fullname']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="fl">
          <label>Broker / Agent</label>
          <select name="broker_id">
            <option value="">— No broker —</option>
            <?php while($b = mysqli_fetch_assoc($brokers)): ?>
            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['fullname']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
      <p style="font-size:12px;color:var(--muted);line-height:1.6">
        Assigning an owner here will activate their dashboard immediately.
        You can also assign or change the owner later from the Property Owners page.
      </p>
    </div>
 
    <div style="display:flex;gap:12px;align-items:center">
      <button type="submit" class="btn-submit"> Add Property</button>
      <a href="admin_dashboard.php?page=properties" class="btn-back">← Back to Properties</a>
    </div>
 
  </form>
</div>
</body>
</html>