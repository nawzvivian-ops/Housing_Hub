
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$user_id = $_SESSION['user_id'];
$user    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'"));
if (!$user || strtolower(trim($user['role'])) !== 'admin') { header("Location: dashboard.php"); exit(); }
 
$success = $error = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenant_id     = (int)($_POST['tenant_id'] ?? 0);
    $document_name = trim($_POST['document_name'] ?? '');
 
    if ($tenant_id <= 0 || empty($document_name)) {
        $error = "Please select a tenant and enter a document name.";
    } elseif (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== 0) {
        $error = "Please upload a valid file.";
    } else {
        $uploads_dir = 'uploads/tenant_documents';
        if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0777, true);
 
        $file_name    = basename($_FILES['document_file']['name']);
        $file_ext     = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed      = ['pdf','doc','docx','jpg','jpeg','png'];
        if (!in_array($file_ext, $allowed)) {
            $error = "File type not allowed. Use PDF, DOC, DOCX, JPG or PNG.";
        } else {
            $new_name   = time() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $document_name) . '.' . $file_ext;
            $target     = $uploads_dir . '/' . $new_name;
            if (move_uploaded_file($_FILES['document_file']['tmp_name'], $target)) {
                $stmt = $conn->prepare("INSERT INTO tenant_documents (tenant_id, document_name, file_path, uploaded_at) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("iss", $tenant_id, $document_name, $target);
                if ($stmt->execute()) {
                    $success = "Document uploaded successfully!";
                } else {
                    $error = "Database error: Could not save document.";
                }
            } else {
                $error = "Failed to upload file. Check folder permissions.";
            }
        }
    }
}
 
$tenants_result = mysqli_query($conn, "SELECT id, fullname FROM tenants ORDER BY fullname ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add Tenant Document | HousingHub Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25)}
html,body{min-height:100vh;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);display:flex;align-items:center;justify-content:center;padding:40px 20px}
body{background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%),var(--ink)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
.wrap{position:relative;z-index:10;width:100%;max-width:500px}
.back{display:inline-flex;align-items:center;gap:8px;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);text-decoration:none;margin-bottom:24px;transition:color .2s}
.back:hover{color:var(--gold)}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:14px;padding:36px;box-shadow:0 20px 60px rgba(0,0,0,.4)}
.card-eyebrow{font-size:10px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gold);text-align:center;margin-bottom:8px}
.card-title{font-family:"Cormorant Garamond",serif;font-size:30px;font-weight:700;color:var(--white);text-align:center;margin-bottom:6px}
.card-title em{color:var(--gold);font-style:italic}
.card-sub{font-size:12px;color:var(--muted);text-align:center;margin-bottom:28px}
.divider{height:1px;background:var(--border);margin-bottom:24px}
.fl{margin-bottom:16px}
.fl label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.fl input,.fl select{width:100%;padding:11px 13px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .25s}
.fl input:focus,.fl select:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
.fl input::placeholder{color:var(--muted)}
.fl select option{background:var(--ink);color:var(--white)}
/* FILE UPLOAD */
.file-zone{border:2px dashed var(--border);border-radius:8px;padding:28px;text-align:center;cursor:pointer;transition:all .25s;position:relative}
.file-zone:hover{border-color:var(--gb);background:rgba(200,164,60,.03)}
.file-zone.has-file{border-color:rgba(22,163,74,.4);background:rgba(22,163,74,.04)}
.file-zone input[type="file"]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.file-icon{font-size:32px;margin-bottom:10px;display:block}
.file-text{font-size:13px;color:var(--muted)}
.file-text strong{color:var(--gold)}
.file-name{font-size:12px;color:#86efac;margin-top:8px;display:none}
.file-types{font-size:11px;color:rgba(255,255,255,.2);margin-top:6px}
.alert{padding:12px 16px;border-radius:7px;font-size:13px;margin-bottom:18px}
.alert.success{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.alert.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.btn{width:100%;padding:13px;background:var(--gold);border:none;color:var(--ink);font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;border-radius:7px;cursor:pointer;transition:all .3s;margin-top:8px}
.btn:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 8px 24px rgba(200,164,60,.3)}
</style>
</head>
<body>
<div class="wrap">
  <a href="admin_dashboard.php?page=tenant_documents" class="back">← Back to Documents</a>
  <div class="card">
    <div class="card-eyebrow">HousingHub Admin</div>
    <div class="card-title">Upload <em>Document</em></div>
    <div class="card-sub">Attach a document to a tenant's record</div>
    <div class="divider"></div>
 
    <?php if($success): ?>
    <div class="alert success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if($error): ?>
    <div class="alert error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
 
    <form method="POST" enctype="multipart/form-data">
      <div class="fl">
        <label>Select Tenant</label>
        <select name="tenant_id" required>
          <option value="">— Choose Tenant —</option>
          <?php while($t = mysqli_fetch_assoc($tenants_result)): ?>
          <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['fullname']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="fl">
        <label>Document Name</label>
        <input type="text" name="document_name" placeholder="e.g. National ID, Lease Agreement, Receipt..." required>
      </div>
      <div class="fl">
        <label>Upload File</label>
        <div class="file-zone" id="file-zone">
          <input type="file" name="document_file" id="document_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required onchange="updateFileName(this)">
          <span class="file-icon">📎</span>
          <div class="file-text">Click to browse or <strong>drag & drop</strong></div>
          <div class="file-name" id="file-name"></div>
          <div class="file-types">PDF, DOC, DOCX, JPG, PNG</div>
        </div>
      </div>
      <button type="submit" class="btn">Upload Document →</button>
    </form>
  </div>
</div>
 
<script>
function updateFileName(input) {
  const zone = document.getElementById('file-zone');
  const label = document.getElementById('file-name');
  if (input.files && input.files[0]) {
    label.textContent = '✓ ' + input.files[0].name;
    label.style.display = 'block';
    zone.classList.add('has-file');
  }
}
</script>
</body>
</html>