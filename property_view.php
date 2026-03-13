<?php
session_start();
include "db_connect.php";

/* ================= LOGGED IN USER ================= */
$user = null;
$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    $user_id = intval($user_id);
    $userQuery = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
    $user = mysqli_fetch_assoc($userQuery);
}

/* ================= PROPERTY ================= */
$property_id = intval($_GET['id']);
$property = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM properties WHERE id = $property_id"));
if (!$property) die("Property does not exist");

/* ================= PROPERTY OWNER ================= */
$owner = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = {$property['owner_id']}"));

/* ================= IMAGE UPLOAD ================= */
if(isset($_POST['upload_images']) && $user_id == $property['owner_id']) {
    $upload_dir = "property_media/";
    if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    foreach($_FILES['property_images']['tmp_name'] as $key => $tmp_name) {
        $file_name = basename($_FILES['property_images']['name'][$key]);
        $target_file = $upload_dir . time() . '_' . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if(in_array($file_type, ['jpg','jpeg','png','gif'])) {
            if(move_uploaded_file($tmp_name, $target_file)) {
                mysqli_query($conn, "INSERT INTO property_images (property_id, image_path) VALUES ($property_id, '$target_file')");
            }
        }
    }
    $msg = "Images uploaded successfully!";
}

/* ================= INSPECTION BOOKING ================= */
if (isset($_POST['book_visit']) && $user_id) {
    $visit_date = mysqli_real_escape_string($conn, $_POST['visit_date']);
    $visit_time = mysqli_real_escape_string($conn, $_POST['visit_time']);
    mysqli_query($conn, "INSERT INTO property_visits (property_id, user_id, visit_date, visit_time) VALUES ($property_id, $user_id, '$visit_date', '$visit_time')");
    $msg = "Inspection request submitted!";
}

/* ================= REVIEW SUBMISSION ================= */
if (isset($_POST['submit_review']) && $user_id) {
    $cleanliness = intval($_POST['rating_cleanliness']);
    $security    = intval($_POST['rating_security']);
    $value       = intval($_POST['rating_value']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    mysqli_query($conn, "INSERT INTO property_reviews (property_id, user_id, rating_cleanliness, rating_security, rating_value, comment) VALUES ($property_id, $user_id, $cleanliness, $security, $value, '$comment')");
    $msg = "Review submitted successfully!";
}

/* ================= FETCH DATA ================= */
$imgs    = mysqli_query($conn, "SELECT * FROM property_images WHERE property_id=$property_id");
$amen    = mysqli_query($conn, "SELECT a.name, a.cost_type FROM amenities a JOIN property_amenities pa ON a.id=pa.amenity_id WHERE pa.property_id=$property_id");
$price   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM price_breakdown WHERE property_id=$property_id"));
$reviews = mysqli_query($conn, "SELECT r.*, u.fullname FROM property_reviews r JOIN users u ON r.user_id = u.id WHERE r.property_id = $property_id ORDER BY r.created_at DESC");
$fullURL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$purposeClass = strtolower($property['purpose']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($property['property_name']) ?> – HousingHub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.07);--gb:rgba(200,164,60,.25)}

/* CURSOR */
body{cursor:none;font-family:'Outfit',sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden}
#cur-dot{width:8px;height:8px;background:var(--gold);border-radius:50%;position:fixed;z-index:99999;pointer-events:none;transform:translate(-50%,-50%);transition:width .25s,height .25s;mix-blend-mode:difference}
#cur-ring{width:40px;height:40px;border:1.5px solid rgba(200,164,60,.7);border-radius:50%;position:fixed;z-index:99998;pointer-events:none;transform:translate(-50%,-50%);transition:width .45s cubic-bezier(.23,1,.32,1),height .45s cubic-bezier(.23,1,.32,1)}
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
.z{position:relative;z-index:10}
.reveal{opacity:0;transform:translateY(24px);transition:opacity .7s ease,transform .7s ease}
.reveal.visible{opacity:1;transform:translateY(0)}

/* NAV */
.top-nav{position:sticky;top:0;z-index:9000;display:flex;justify-content:space-between;align-items:center;padding:18px 60px;background:rgba(4,9,26,.96);border-bottom:1px solid var(--border);backdrop-filter:blur(12px)}
.nav-logo{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gold);text-decoration:none}
.nav-logo span{color:var(--muted)}
.nav-links{display:flex;align-items:center;gap:16px}
.nav-links a{font-size:11px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);text-decoration:none;transition:color .3s}
.nav-links a:hover{color:var(--gold)}
.nav-links a.btn-gold{color:var(--gold);border:1px solid rgba(200,164,60,.35);padding:8px 18px;border-radius:2px;transition:all .3s}
.nav-links a.btn-gold:hover{background:var(--gold);color:var(--ink)}

/* HERO / GALLERY */
.prop-hero{position:relative;z-index:10;border-bottom:1px solid var(--border)}
.gallery-grid{display:grid;gap:4px}
.gallery-grid.has-many{grid-template-columns:2fr 1fr;grid-template-rows:320px 200px}
.gallery-grid.single{grid-template-columns:1fr;grid-template-rows:420px}
.gallery-img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .5s ease}
.gallery-img:hover{transform:scale(1.02)}
.gallery-slot{overflow:hidden;background:rgba(255,255,255,.03)}
.gallery-slot:first-child{grid-row:span 2}
.gallery-more-overlay{position:relative;cursor:pointer}
.gallery-more-overlay::after{content:'+ More Photos';position:absolute;inset:0;background:rgba(4,9,26,.65);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:600;letter-spacing:2px;color:var(--gold);text-transform:uppercase}
.no-images{height:260px;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:12px;color:var(--muted);font-size:14px;background:rgba(255,255,255,.02);border-bottom:1px solid var(--border)}
.no-images-icon{font-size:48px;opacity:.3}

/* PAGE LAYOUT */
.prop-layout{position:relative;z-index:10;display:grid;grid-template-columns:1fr 360px;gap:0;max-width:1400px;margin:0 auto;padding:0 60px 80px;margin-top:40px}
.prop-main{padding-right:48px}
.prop-sidebar{position:sticky;top:100px;height:fit-content}

/* PROPERTY TITLE BLOCK */
.prop-eyebrow{font-size:11px;font-weight:500;letter-spacing:3px;text-transform:uppercase;color:var(--gold);display:flex;align-items:center;gap:10px;margin-bottom:14px}
.prop-eyebrow::before{content:'';width:28px;height:1px;background:var(--gold)}
.prop-title{font-family:'Cormorant Garamond',serif;font-size:clamp(36px,4vw,56px);font-weight:700;color:var(--white);line-height:1.1;margin-bottom:16px}
.prop-address{font-size:14px;color:var(--muted);display:flex;align-items:center;gap:8px;margin-bottom:24px}
.prop-address::before{content:'📍';font-size:13px}
.prop-badges{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:32px}
.badge{font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;padding:6px 14px;border-radius:20px;border:1px solid transparent}
.badge.type{background:rgba(200,164,60,.1);color:var(--gold);border-color:rgba(200,164,60,.2)}
.badge.rent{background:rgba(34,197,94,.1);color:#86efac;border-color:rgba(34,197,94,.2)}
.badge.buy{background:rgba(59,130,246,.1);color:#93c5fd;border-color:rgba(59,130,246,.2)}
.badge.lease{background:rgba(245,158,11,.1);color:#fcd34d;border-color:rgba(245,158,11,.2)}

/* SECTION BLOCKS */
.prop-section{margin-bottom:40px;padding-bottom:40px;border-bottom:1px solid var(--border)}
.prop-section:last-child{border-bottom:none}
.section-title{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:var(--white);margin-bottom:20px;display:flex;align-items:center;gap:12px}
.section-title em{color:var(--gold);font-style:italic}

/* META GRID */
.meta-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:10px}
.meta-item{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:16px 18px}
.meta-label{font-size:10px;font-weight:500;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:6px}
.meta-value{font-size:16px;font-weight:500;color:var(--white)}

/* AMENITIES */
.amenity-list{display:flex;flex-wrap:wrap;gap:10px}
.amenity-tag{font-size:12px;padding:7px 14px;border-radius:20px;border:1px solid var(--border);background:rgba(255,255,255,.03);display:flex;align-items:center;gap:6px}
.amenity-tag.free{color:#86efac;border-color:rgba(34,197,94,.2);background:rgba(34,197,94,.06)}
.amenity-tag.paid{color:#fca5a5;border-color:rgba(239,68,68,.2);background:rgba(239,68,68,.06)}

/* PRICE BREAKDOWN */
.price-rows{display:flex;flex-direction:column;gap:10px}
.price-row{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px}
.price-row-label{font-size:13px;color:var(--muted)}
.price-row-value{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:700;color:var(--gold)}

/* MAP */
.map-frame{border-radius:12px;overflow:hidden;border:1px solid var(--border)}
.map-frame iframe{display:block;width:100%;height:320px;border:0}

/* REVIEWS */
.review-form-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:24px}
.rating-group label.rating-label{font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--gold);display:block;margin-bottom:10px}
.star-rating{direction:rtl;display:inline-flex;font-size:28px}
.star-rating input{display:none}
.star-rating label{color:rgba(255,255,255,.15);cursor:pointer;transition:.2s}
.star-rating input:checked ~ label,.star-rating label:hover,.star-rating label:hover ~ label{color:var(--gold)}
.review-textarea{width:100%;padding:14px 18px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:8px;color:var(--white);font-family:'Outfit',sans-serif;font-size:14px;resize:vertical;min-height:110px;transition:border-color .3s}
.review-textarea::placeholder{color:rgba(255,255,255,.22)}
.review-textarea:focus{outline:none;border-color:var(--gold)}
.review-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:22px 24px;margin-bottom:14px;transition:border-color .3s}
.review-card:hover{border-color:var(--gb)}
.review-author{display:flex;align-items:center;gap:12px;margin-bottom:14px}
.review-avatar{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.3),rgba(14,90,200,.3));border:1px solid var(--gb);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.review-name{font-size:14px;font-weight:600;color:var(--white)}
.review-date{font-size:11px;color:rgba(255,255,255,.3);margin-top:2px}
.review-avg{font-size:12px;font-weight:600;color:var(--gold);background:rgba(200,164,60,.1);border:1px solid rgba(200,164,60,.2);padding:3px 10px;border-radius:12px;margin-left:auto}
.review-stars-row{display:flex;gap:20px;margin-bottom:12px;flex-wrap:wrap}
.review-stars-item{font-size:12px;color:var(--muted)}
.review-stars-item strong{color:var(--white);margin-right:4px}
.review-stars-item .stars{color:var(--gold);font-size:13px}
.review-comment{font-family:'Cormorant Garamond',serif;font-size:16px;font-style:italic;color:rgba(255,255,255,.7);line-height:1.7}

/* SHARE */
.share-btns{display:flex;gap:12px;flex-wrap:wrap}
.share-btn{display:inline-flex;align-items:center;gap:8px;padding:12px 22px;border-radius:6px;font-size:12px;font-weight:600;letter-spacing:1px;text-transform:uppercase;text-decoration:none;border:none;cursor:pointer;font-family:'Outfit',sans-serif;transition:all .3s}
.share-btn.wa{background:rgba(34,197,94,.15);color:#86efac;border:1px solid rgba(34,197,94,.25)}
.share-btn.wa:hover{background:rgba(34,197,94,.25)}
.share-btn.fb{background:rgba(59,130,246,.15);color:#93c5fd;border:1px solid rgba(59,130,246,.25)}
.share-btn.fb:hover{background:rgba(59,130,246,.25)}
.share-btn.copy{background:rgba(200,164,60,.12);color:var(--gold);border:1px solid rgba(200,164,60,.25)}
.share-btn.copy:hover{background:rgba(200,164,60,.22)}

/* SIDEBAR CARD */
.sidebar-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;overflow:hidden;margin-bottom:20px}
.sidebar-price{padding:24px;border-bottom:1px solid var(--border);background:rgba(200,164,60,.04)}
.sidebar-price-label{font-size:10px;font-weight:500;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:6px}
.sidebar-price-value{font-family:'Cormorant Garamond',serif;font-size:36px;font-weight:700;color:var(--gold);line-height:1}
.sidebar-price-sub{font-size:12px;color:var(--muted);margin-top:4px}
.sidebar-actions{padding:20px;display:flex;flex-direction:column;gap:10px}
.sidebar-btn{display:block;text-align:center;padding:14px;border-radius:6px;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;transition:all .3s;border:none;cursor:pointer;font-family:'Outfit',sans-serif;width:100%}
.sidebar-btn.primary{background:var(--gold);color:var(--ink)}
.sidebar-btn.primary:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,164,60,.28)}
.sidebar-btn.secondary{background:rgba(255,255,255,.05);color:var(--white);border:1px solid var(--border)}
.sidebar-btn.secondary:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2)}
.sidebar-btn.save{background:rgba(200,164,60,.1);color:var(--gold);border:1px solid rgba(200,164,60,.2)}
.sidebar-btn.save:hover{background:rgba(200,164,60,.2)}
.sidebar-owner{padding:20px;border-top:1px solid var(--border)}
.sidebar-owner-title{font-size:10px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:14px}
.owner-info{display:flex;align-items:center;gap:12px}
.owner-avatar{width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.3),rgba(14,90,200,.3));border:1px solid var(--gb);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.owner-name{font-size:14px;font-weight:600;color:var(--white)}
.owner-label{font-size:11px;color:var(--muted)}

/* BOOK INSPECTION */
.inspect-form{display:flex;flex-direction:column;gap:12px}
.inspect-inputs{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-field{display:flex;flex-direction:column;gap:6px}
.form-field label{font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold)}
.form-field input{padding:12px 16px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:6px;color:var(--white);font-family:'Outfit',sans-serif;font-size:14px;transition:border-color .3s}
.form-field input:focus{outline:none;border-color:var(--gold)}
.form-field input::-webkit-calendar-picker-indicator{filter:invert(1) opacity(.4)}

/* BUTTONS */
.btn-submit{padding:13px 32px;background:var(--gold);color:var(--ink);border:none;border-radius:6px;font-family:'Outfit',sans-serif;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;transition:all .3s}
.btn-submit:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,164,60,.28)}

/* SUCCESS / INFO MESSAGES */
.msg{padding:14px 18px;border-radius:8px;font-size:14px;margin-bottom:20px;border:1px solid transparent}
.msg.success{background:rgba(34,197,94,.1);border-color:rgba(34,197,94,.2);color:#86efac}
.msg.info{background:rgba(200,164,60,.08);border-color:rgba(200,164,60,.2);color:var(--gold)}

/* LOGIN PROMPT */
.login-prompt{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:18px 22px;font-size:14px;color:var(--muted)}
.login-prompt a{color:var(--gold);text-decoration:none;font-weight:600}
.login-prompt a:hover{text-decoration:underline}

/* DOWNLOAD LEASE */
.lease-btn{display:inline-flex;align-items:center;gap:10px;padding:13px 28px;background:rgba(200,164,60,.1);border:1px solid rgba(200,164,60,.25);border-radius:6px;color:var(--gold);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;transition:all .3s}
.lease-btn:hover{background:rgba(200,164,60,.2)}

/* FOOTER */
footer{padding:28px 60px;border-top:1px solid var(--border);text-align:center;font-size:12px;letter-spacing:1.5px;color:rgba(255,255,255,.2);position:relative;z-index:10}

@keyframes fadeDown{from{opacity:0;transform:translateY(-14px)}to{opacity:1;transform:translateY(0)}}

@media(max-width:1100px){
  .prop-layout{grid-template-columns:1fr;padding:0 24px 60px}
  .prop-main{padding-right:0}
  .prop-sidebar{position:static}
  .meta-grid{grid-template-columns:1fr 1fr}
  .review-form-grid{grid-template-columns:1fr}
}
@media(max-width:768px){
  .top-nav{padding:16px 24px}
  .gallery-grid.has-many{grid-template-columns:1fr;grid-template-rows:260px 160px 160px}
  .gallery-slot:first-child{grid-row:span 1}
  .inspect-inputs{grid-template-columns:1fr}
  footer{padding:24px}
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

<!-- ══ TOP NAV ══════════════════════════════════════════════════ -->
<nav class="top-nav z" style="animation:fadeDown .8s ease both">
  <a href="properties.php" class="nav-logo">Housing<span>Hub</span></a>
  <div class="nav-links">
    <a href="properties.php?browse=1">All Properties</a>
    <a href="properties.php?type=<?= urlencode($property['property_type']) ?>"><?= htmlspecialchars($property['property_type']) ?></a>
    <?php if($user): ?>
      <a href="logout.php" class="btn-gold">Logout</a>
    <?php else: ?>
      <a href="login.php" class="btn-gold">Login</a>
    <?php endif; ?>
  </div>
</nav>

<!-- ══ PHOTO GALLERY ═════════════════════════════════════════════ -->
<div class="prop-hero">
  <?php
  $imgList = [];
  while($img = mysqli_fetch_assoc($imgs)) $imgList[] = $img;
  $imgCount = count($imgList);
  ?>
  <?php if($imgCount > 0): ?>
  <div class="gallery-grid <?= $imgCount > 1 ? 'has-many' : 'single' ?>">
    <?php foreach(array_slice($imgList,0,3) as $i => $img): ?>
    <div class="gallery-slot <?= ($i === 2 && $imgCount > 3) ? 'gallery-more-overlay' : '' ?>">
      <img class="gallery-img" src="<?= htmlspecialchars($img['image_path']) ?>" alt="Property Image">
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="no-images z">
    <div class="no-images-icon">📷</div>
    <span>No photos uploaded yet</span>
  </div>
  <?php endif; ?>
</div>

<!-- ══ MAIN LAYOUT ═══════════════════════════════════════════════ -->
<div class="prop-layout">

  <!-- LEFT: MAIN CONTENT -->
  <div class="prop-main">

    <?php if(isset($msg)): ?>
    <div class="msg success z"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- TITLE + BADGES -->
    <div class="prop-section z reveal">
      <div class="prop-eyebrow">HousingHub Listing</div>
      <h1 class="prop-title"><?= htmlspecialchars($property['property_name']) ?></h1>
      <div class="prop-address"><?= htmlspecialchars($property['address']) ?></div>
      <div class="prop-badges">
        <span class="badge type"><?= htmlspecialchars($property['property_type']) ?></span>
        <span class="badge <?= $purposeClass ?>"><?= strtoupper($property['purpose']) ?></span>
      </div>
      <div class="meta-grid">
        <div class="meta-item"><div class="meta-label">Size</div><div class="meta-value"><?= $property['size_sqft'] ?> sqft</div></div>
        <div class="meta-item"><div class="meta-label">Units</div><div class="meta-value"><?= $property['units'] ?? 'N/A' ?></div></div>
        <div class="meta-item"><div class="meta-label">Bedrooms</div><div class="meta-value"><?= $property['bedrooms'] ?? 'N/A' ?></div></div>
      </div>
    </div>

    <!-- AMENITIES -->
    <div class="prop-section z reveal">
      <div class="section-title">Amenities <em>&amp; Features</em></div>
      <?php
      $amenList = [];
      while($a = mysqli_fetch_assoc($amen)) $amenList[] = $a;
      ?>
      <?php if(!empty($amenList)): ?>
      <div class="amenity-list">
        <?php foreach($amenList as $a): ?>
        <span class="amenity-tag <?= $a['cost_type']==='Free'?'free':'paid' ?>">
          <?= $a['cost_type']==='Free' ? '✓' : '💲' ?>
          <?= htmlspecialchars($a['name']) ?>
          <span style="opacity:.6;font-size:10px">(<?= $a['cost_type'] ?>)</span>
        </span>
        <?php endforeach; ?>
      </div>
      <?php else: ?><p style="color:var(--muted);font-size:14px">No amenities listed for this property.</p><?php endif; ?>
    </div>

    <!-- PRICE BREAKDOWN -->
    <div class="prop-section z reveal">
      <div class="section-title">Price <em>Breakdown</em></div>
      <?php if($price): ?>
      <div class="price-rows">
        <div class="price-row"><span class="price-row-label">Monthly Rent</span><span class="price-row-value">UGX <?= number_format($price['monthly_rent']) ?></span></div>
        <div class="price-row"><span class="price-row-label">Security Deposit</span><span class="price-row-value">UGX <?= number_format($price['deposit']) ?></span></div>
        <div class="price-row"><span class="price-row-label">Service Charge</span><span class="price-row-value">UGX <?= number_format($price['service_charge']) ?></span></div>
      </div>
      <?php else: ?><p style="color:var(--muted);font-size:14px">No price breakdown available.</p><?php endif; ?>
    </div>

    <!-- LOCATION MAP -->
    <div class="prop-section z reveal">
      <div class="section-title">Location <em>Map</em></div>
      <div class="map-frame">
        <iframe src="https://www.google.com/maps?q=<?= urlencode($property['address']) ?>&output=embed" allowfullscreen></iframe>
      </div>
    </div>

    <!-- BOOK INSPECTION -->
    <div class="prop-section z reveal">
      <div class="section-title">Book an <em>Inspection</em></div>
      <?php if($user_id): ?>
      <form method="POST" class="inspect-form">
        <div class="inspect-inputs">
          <div class="form-field"><label>Preferred Date</label><input type="date" name="visit_date" required></div>
          <div class="form-field"><label>Preferred Time</label><input type="time" name="visit_time" required></div>
        </div>
        <div><button name="book_visit" class="btn-submit">📝 Request Inspection</button></div>
      </form>
      <?php else: ?><div class="login-prompt">Please <a href="login.php">login</a> to book an inspection.</div><?php endif; ?>
    </div>

    <!-- LEASE DOWNLOAD -->
    <?php if(!empty($property['lease_file'])): ?>
    <div class="prop-section z reveal">
      <div class="section-title">Lease <em>Agreement</em></div>
      <a href="<?= htmlspecialchars($property['lease_file']) ?>" download class="lease-btn">📄 Download Lease Agreement</a>
    </div>
    <?php endif; ?>

    <!-- REVIEWS -->
    <div class="prop-section z reveal">
      <div class="section-title">Reviews <em>&amp; Ratings</em></div>

      <?php if($user_id): ?>
      <form method="POST" style="margin-bottom:32px">
        <div class="review-form-grid">
          <div class="rating-group">
            <label class="rating-label">Cleanliness</label>
            <div class="star-rating">
              <input type="radio" name="rating_cleanliness" value="5" id="c5" required><label for="c5">★</label>
              <input type="radio" name="rating_cleanliness" value="4" id="c4"><label for="c4">★</label>
              <input type="radio" name="rating_cleanliness" value="3" id="c3"><label for="c3">★</label>
              <input type="radio" name="rating_cleanliness" value="2" id="c2"><label for="c2">★</label>
              <input type="radio" name="rating_cleanliness" value="1" id="c1"><label for="c1">★</label>
            </div>
          </div>
          <div class="rating-group">
            <label class="rating-label">Security</label>
            <div class="star-rating">
              <input type="radio" name="rating_security" value="5" id="s5" required><label for="s5">★</label>
              <input type="radio" name="rating_security" value="4" id="s4"><label for="s4">★</label>
              <input type="radio" name="rating_security" value="3" id="s3"><label for="s3">★</label>
              <input type="radio" name="rating_security" value="2" id="s2"><label for="s2">★</label>
              <input type="radio" name="rating_security" value="1" id="s1"><label for="s1">★</label>
            </div>
          </div>
          <div class="rating-group">
            <label class="rating-label">Value for Money</label>
            <div class="star-rating">
              <input type="radio" name="rating_value" value="5" id="v5" required><label for="v5">★</label>
              <input type="radio" name="rating_value" value="4" id="v4"><label for="v4">★</label>
              <input type="radio" name="rating_value" value="3" id="v3"><label for="v3">★</label>
              <input type="radio" name="rating_value" value="2" id="v2"><label for="v2">★</label>
              <input type="radio" name="rating_value" value="1" id="v1"><label for="v1">★</label>
            </div>
          </div>
        </div>
        <textarea class="review-textarea" name="comment" placeholder="Share your experience with this property…" required></textarea>
        <div style="margin-top:14px"><button name="submit_review" class="btn-submit">Submit Review</button></div>
      </form>
      <?php else: ?><div class="login-prompt" style="margin-bottom:24px">Please <a href="login.php">login</a> to leave a review.</div><?php endif; ?>

      <!-- DISPLAY REVIEWS -->
      <?php if(mysqli_num_rows($reviews) > 0): ?>
        <?php while($r = mysqli_fetch_assoc($reviews)):
          $avg = round(($r['rating_cleanliness'] + $r['rating_security'] + $r['rating_value']) / 3, 1);
        ?>
        <div class="review-card">
          <div class="review-author">
            <div class="review-avatar">👤</div>
            <div>
              <div class="review-name"><?= htmlspecialchars($r['fullname']) ?></div>
              <div class="review-date"><?= date("d M Y", strtotime($r['created_at'])) ?></div>
            </div>
            <div class="review-avg">⭐ <?= $avg ?>/5</div>
          </div>
          <div class="review-stars-row">
            <div class="review-stars-item"><strong>Cleanliness</strong><span class="stars"><?= str_repeat('★',$r['rating_cleanliness']) ?><?= str_repeat('☆',5-$r['rating_cleanliness']) ?></span></div>
            <div class="review-stars-item"><strong>Security</strong><span class="stars"><?= str_repeat('★',$r['rating_security']) ?><?= str_repeat('☆',5-$r['rating_security']) ?></span></div>
            <div class="review-stars-item"><strong>Value</strong><span class="stars"><?= str_repeat('★',$r['rating_value']) ?><?= str_repeat('☆',5-$r['rating_value']) ?></span></div>
          </div>
          <div class="review-comment">"<?= htmlspecialchars($r['comment']) ?>"</div>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="color:var(--muted);font-size:14px;padding:20px 0">No reviews yet. Be the first to share your experience!</p>
      <?php endif; ?>
    </div>

    <!-- SHARE -->
    <div class="prop-section z reveal">
      <div class="section-title">Share <em>This Property</em></div>
      <div class="share-btns">
        <a href="https://wa.me/?text=Check%20out%20this%20property:%20<?= urlencode($fullURL) ?>" target="_blank" class="share-btn wa">📱 WhatsApp</a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($fullURL) ?>" target="_blank" class="share-btn fb">👍 Facebook</a>
        <button class="share-btn copy" onclick="navigator.clipboard.writeText('<?= $fullURL ?>').then(()=>{ this.textContent='✓ Copied!'; setTimeout(()=>this.textContent='🔗 Copy Link',2000) })">🔗 Copy Link</button>
      </div>
    </div>

  </div><!-- /prop-main -->

  <!-- RIGHT: SIDEBAR -->
  <div class="prop-sidebar z">
    <div class="sidebar-card">

      <!-- PRICE -->
      <div class="sidebar-price">
        <div class="sidebar-price-label">Listing Price</div>
        <div class="sidebar-price-value">UGX <?= number_format($property['rent_amount']) ?></div>
        <div class="sidebar-price-sub"><?= ucfirst(strtolower($property['purpose'])) ?> · <?= htmlspecialchars($property['property_type']) ?></div>
      </div>

      <!-- ACTION BUTTONS -->
      <div class="sidebar-actions">
        <?php if($property['purpose']==='Rent'): ?>
        <a href="payment_method.php?property_id=<?= $property['id'] ?>&action=rent" class="sidebar-btn primary">Rent This Property</a>
        <?php elseif($property['purpose']==='Buy'): ?>
        <a href="payment_method.php?property_id=<?= $property['id'] ?>&action=buy" class="sidebar-btn primary">Buy This Property</a>
        <?php elseif($property['purpose']==='Lease'): ?>
        <a href="payment_method.php?property_id=<?= $property['id'] ?>&action=lease" class="sidebar-btn primary">Lease This Property</a>
        <?php endif; ?>

        <?php if($user): ?>
        <a href="save_favorite.php?id=<?= $property['id'] ?>" class="sidebar-btn save">❤️ Save to Favourites</a>
        <?php else: ?>
        <a href="login.php" class="sidebar-btn secondary">Login to Save</a>
        <?php endif; ?>

        <a href="properties.php?browse=1" class="sidebar-btn secondary">← Back to Listings</a>
      </div>

      <!-- OWNER INFO -->
      <?php if($owner): ?>
      <div class="sidebar-owner">
        <div class="sidebar-owner-title">Listed By</div>
        <div class="owner-info">
          <div class="owner-avatar">🏠</div>
          <div>
            <div class="owner-name"><?= htmlspecialchars($owner['fullname'] ?? 'Property Owner') ?></div>
            <div class="owner-label">Property Owner</div>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div>

    <!-- QUICK STATS CARD -->
    <div class="sidebar-card" style="padding:20px">
      <div style="font-size:10px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:16px">Quick Facts</div>
      <div style="display:flex;flex-direction:column;gap:12px">
        <div style="display:flex;justify-content:space-between;font-size:13px"><span style="color:var(--muted)">Type</span><span style="color:var(--white);font-weight:500"><?= htmlspecialchars($property['property_type']) ?></span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px"><span style="color:var(--muted)">Purpose</span><span style="color:var(--white);font-weight:500"><?= htmlspecialchars($property['purpose']) ?></span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px"><span style="color:var(--muted)">Size</span><span style="color:var(--white);font-weight:500"><?= $property['size_sqft'] ?> sqft</span></div>
        <?php if(!empty($property['bedrooms'])): ?><div style="display:flex;justify-content:space-between;font-size:13px"><span style="color:var(--muted)">Rooms</span><span style="color:var(--white);font-weight:500"><?= $property['bedrooms'] ?></span></div><?php endif; ?>
        <?php if(!empty($property['units'])): ?><div style="display:flex;justify-content:space-between;font-size:13px"><span style="color:var(--muted)">Units</span><span style="color:var(--white);font-weight:500"><?= $property['units'] ?></span></div><?php endif; ?>
      </div>
    </div>

  </div><!-- /prop-sidebar -->

</div><!-- /prop-layout -->

<footer class="z">&copy; 2026 HousingHub | All Rights Reserved</footer>

<script>
// CURSOR
const dot=document.getElementById('cur-dot'),ring=document.getElementById('cur-ring'),trail=document.getElementById('cur-trail');
let mx=-200,my=-200,rx=-200,ry=-200,tx=-200,ty=-200;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;dot.style.left=mx+'px';dot.style.top=my+'px';});
(function anim(){rx+=(mx-rx)*.15;ry+=(my-ry)*.15;tx+=(mx-tx)*.06;ty+=(my-ty)*.06;ring.style.left=rx+'px';ring.style.top=ry+'px';trail.style.left=tx+'px';trail.style.top=ty+'px';requestAnimationFrame(anim);})();
document.querySelectorAll('a,button,input,.sidebar-btn,.share-btn,.review-card,.amenity-tag').forEach(el=>{
  el.addEventListener('mouseenter',()=>document.body.classList.add('cursor-hover'));
  el.addEventListener('mouseleave',()=>document.body.classList.remove('cursor-hover'));
});
document.addEventListener('mousedown',()=>document.body.classList.add('cursor-click'));
document.addEventListener('mouseup',()=>document.body.classList.remove('cursor-click'));

// SCROLL REVEAL
const ro=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');ro.unobserve(e.target);}});},{threshold:.08});
document.querySelectorAll('.reveal').forEach(el=>ro.observe(el));

// PARTICLES
for(let i=0;i<14;i++){
  const p=document.createElement('div');p.classList.add('ptcl');
  const sz=Math.random()*2.5+1;
  p.style.cssText=`width:${sz}px;height:${sz}px;left:${Math.random()*100}%;background:rgba(200,164,60,${(Math.random()*.4+.15).toFixed(2)});animation-duration:${Math.random()*20+12}s;animation-delay:${Math.random()*14}s;`;
  document.body.appendChild(p);
}
</script>
</body>
</html>