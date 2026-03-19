
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
 
$type = $_GET['type'] ?? $_POST['type'] ?? '';
$id   = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
 
if ($id <= 0 || empty($type)) die("Invalid request — no ID or type provided.");
 
/* ── HANDLE UPDATE ── */
if (isset($_POST['update'])) {
 
    if ($type === "property") {
        $property_name = mysqli_real_escape_string($conn, $_POST['property_name']);
        $property_type = mysqli_real_escape_string($conn, $_POST['property_type']);
        $address       = mysqli_real_escape_string($conn, $_POST['address']);
        $units         = (int)$_POST['units'];
        $rent_amount   = (float)$_POST['rent_amount'];
        $status        = mysqli_real_escape_string($conn, $_POST['status'] ?? 'available');
        $description   = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
        mysqli_query($conn, "UPDATE properties SET property_name='$property_name',property_type='$property_type',address='$address',units=$units,rent_amount=$rent_amount,status='$status',description='$description' WHERE id=$id");
        header("Location: admin_dashboard.php?page=properties"); exit();
    }
    if ($type === "staff") {
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $role     = mysqli_real_escape_string($conn, $_POST['role']);
        $salary   = (float)$_POST['salary'];
        $email    = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
        $phone    = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
        mysqli_query($conn, "UPDATE users SET fullname='$fullname',role='$role',salary=$salary,email='$email',phone='$phone' WHERE id=$id");
        header("Location: admin_dashboard.php?page=staff_roles"); exit();
    }
    if ($type === "task") {
        $title       = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $assigned_to = (int)$_POST['assigned_to'];
        $due_date    = mysqli_real_escape_string($conn, $_POST['due_date']);
        $priority    = mysqli_real_escape_string($conn, $_POST['priority']);
        $status      = mysqli_real_escape_string($conn, $_POST['status']);
        mysqli_query($conn, "UPDATE tasks SET title='$title',description='$description',assigned_to=$assigned_to,due_date='$due_date',priority='$priority',status='$status' WHERE id=$id");
        header("Location: admin_dashboard.php?page=staff_tasks"); exit();
    }
    if ($type === "tenant") {
        $fullname        = mysqli_real_escape_string($conn, $_POST['fullname']);
        $phone           = mysqli_real_escape_string($conn, $_POST['phone']);
        $email           = mysqli_real_escape_string($conn, $_POST['email']);
        $property_id     = (int)($_POST['property_id'] ?? 0) ?: 'NULL';
        $lease_start     = mysqli_real_escape_string($conn, $_POST['lease_start'] ?? '');
        $lease_end       = mysqli_real_escape_string($conn, $_POST['lease_end']   ?? '');
        $status          = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Active');
        $occupation      = mysqli_real_escape_string($conn, $_POST['occupation']  ?? '');
        $gender          = mysqli_real_escape_string($conn, $_POST['gender']      ?? '');
        $national_id     = mysqli_real_escape_string($conn, $_POST['national_id'] ?? '');
        $emergency_name  = mysqli_real_escape_string($conn, $_POST['emergency_name']  ?? '');
        $emergency_phone = mysqli_real_escape_string($conn, $_POST['emergency_phone'] ?? '');
        $prop_val = is_numeric($property_id) ? $property_id : 'NULL';
        mysqli_query($conn, "UPDATE tenants SET fullname='$fullname',phone='$phone',email='$email',property_id=$prop_val,lease_start=".($lease_start?"'$lease_start'":'NULL').",lease_end=".($lease_end?"'$lease_end'":'NULL').",status='$status',occupation='$occupation',gender='$gender',national_id='$national_id',emergency_name='$emergency_name',emergency_phone='$emergency_phone' WHERE id=$id");
        mysqli_query($conn, "UPDATE users SET fullname='$fullname',email='$email',phone='$phone' WHERE id=(SELECT user_id FROM tenants WHERE id=$id)");
        header("Location: admin_dashboard.php?page=tenants"); exit();
    }
    if ($type === "complaint") {
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        mysqli_query($conn, "UPDATE complaints SET status='$status' WHERE id=$id");
        header("Location: admin_dashboard.php?page=complaints"); exit();
    }
    if ($type === "maintenance") {
        $status   = mysqli_real_escape_string($conn, $_POST['status']);
        $priority = mysqli_real_escape_string($conn, $_POST['priority']);
        mysqli_query($conn, "UPDATE maintenance_requests SET status='$status',priority='$priority' WHERE id=$id");
        header("Location: admin_dashboard.php?page=maintenance"); exit();
    }
    if ($type === "payment") {
        $amount = (float)$_POST['amount'];
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        mysqli_query($conn, "UPDATE payments SET amount=$amount,status='$status' WHERE id=$id");
        header("Location: admin_dashboard.php?page=payments"); exit();
    }
    if ($type === "inspection") {
        $status    = mysqli_real_escape_string($conn, $_POST['status']);
        $situation = mysqli_real_escape_string($conn, $_POST['situation']);
        $notes     = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');
        mysqli_query($conn, "UPDATE inspections SET status='$status',`condition`='$situation',notes='$notes' WHERE id=$id");
        header("Location: admin_dashboard.php?page=inspections"); exit();
    }
    if ($type === "propertyowner") {
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $email    = mysqli_real_escape_string($conn, $_POST['email']);
        $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
        mysqli_query($conn, "UPDATE users SET fullname='$fullname',email='$email',phone='$phone' WHERE id=$id");
        header("Location: admin_dashboard.php?page=propertyowners"); exit();
    }
    if ($type === "document") {
        $document_name = mysqli_real_escape_string($conn, $_POST['document_name']);
        mysqli_query($conn, "UPDATE tenant_documents SET document_name='$document_name' WHERE id=$id");
        header("Location: admin_dashboard.php?page=tenant_documents"); exit();
    }
}
 
/* ── FETCH RECORD ── */
$table_map = [
    'property'      => 'properties',
    'staff'         => 'users',
    'task'          => 'tasks',
    'tenant'        => 'tenants',
    'complaint'     => 'complaints',
    'maintenance'   => 'maintenance_requests',
    'payment'       => 'payments',
    'inspection'    => 'inspections',
    'propertyowner' => 'users',
    'document'      => 'tenant_documents',
];
$table = $table_map[$type] ?? null;
if (!$table) die("Unknown record type: " . htmlspecialchars($type));
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$table` WHERE id=$id LIMIT 1"));
if (!$data) die("Record not found in '$table' with ID $id.");
 
$properties_list = mysqli_query($conn, "SELECT id, property_name FROM properties ORDER BY property_name ASC");
$staff_list      = mysqli_query($conn, "SELECT id, fullname FROM users WHERE role='staff' ORDER BY fullname ASC");
 
// Friendly labels
$labels = [
    'property'=>'Property','staff'=>'Staff Member','task'=>'Task','tenant'=>'Tenant',
    'complaint'=>'Complaint','maintenance'=>'Maintenance Request','payment'=>'Payment',
    'inspection'=>'Inspection','propertyowner'=>'Property Owner','document'=>'Document'
];
$back_pages = [
    'property'=>'properties','staff'=>'staff_roles','task'=>'staff_tasks','tenant'=>'tenants',
    'complaint'=>'complaints','maintenance'=>'maintenance','payment'=>'payments',
    'inspection'=>'inspections','propertyowner'=>'propertyowners','document'=>'tenant_documents'
];
$label     = $labels[$type]     ?? ucfirst($type);
$back_page = $back_pages[$type] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit <?= $label ?> | HousingHub</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25)}
html,body{min-height:100vh;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white)}
body{background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%),var(--ink);display:flex;align-items:flex-start;justify-content:center;padding:40px 20px 60px}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
.wrap{position:relative;z-index:10;width:100%;max-width:540px}
.back{display:inline-flex;align-items:center;gap:8px;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);text-decoration:none;margin-bottom:24px;transition:color .2s}
.back:hover{color:var(--gold)}
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:14px;padding:32px;box-shadow:0 20px 60px rgba(0,0,0,.4)}
.card-head{text-align:center;margin-bottom:28px}
.card-eyebrow{font-size:10px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gold);margin-bottom:8px}
.card-title{font-family:"Cormorant Garamond",serif;font-size:28px;font-weight:700;color:var(--white);line-height:1.1}
.card-title em{color:var(--gold);font-style:italic}
.card-id{font-size:11px;color:var(--muted);margin-top:6px;letter-spacing:1px}
.divider{height:1px;background:var(--border);margin-bottom:24px}
.fl{margin-bottom:16px}
.fl label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.fl input,.fl select,.fl textarea{width:100%;padding:11px 13px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .25s}
.fl input:focus,.fl select:focus,.fl textarea:focus{border-color:var(--gb);background:rgba(200,164,60,.04)}
.fl input::placeholder,.fl textarea::placeholder{color:var(--muted)}
.fl input[disabled],.fl textarea[disabled]{opacity:.45;cursor:not-allowed}
.fl select option{background:var(--ink)}
.fl textarea{resize:vertical;min-height:80px}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.btn{width:100%;padding:13px;background:var(--gold);border:none;color:var(--ink);font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;border-radius:7px;cursor:pointer;transition:all .3s;margin-top:8px}
.btn:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 8px 24px rgba(200,164,60,.3)}
@media(max-width:600px){.grid2{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="wrap">
 
  <a href="admin_dashboard.php?page=<?= $back_page ?>" class="back">← Back to <?= $label ?>s</a>
 
  <div class="card">
    <div class="card-head">
      <div class="card-eyebrow">HousingHub Admin</div>
      <div class="card-title">Edit <em><?= $label ?></em></div>
      <div class="card-id">Record ID: #<?= $id ?> &nbsp;·&nbsp; Table: <?= $table ?></div>
    </div>
    <div class="divider"></div>
 
    <form method="POST">
      <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
      <input type="hidden" name="id"   value="<?= $id ?>">
 
      <?php if($type === 'property'): ?>
        <div class="fl"><label>Property Name</label><input type="text" name="property_name" value="<?= htmlspecialchars($data['property_name']??'') ?>" required></div>
        <div class="grid2">
          <div class="fl"><label>Property Type</label><input type="text" name="property_type" value="<?= htmlspecialchars($data['property_type']??'') ?>"></div>
          <div class="fl"><label>Units</label><input type="number" name="units" value="<?= (int)($data['units']??0) ?>"></div>
        </div>
        <div class="fl"><label>Address</label><input type="text" name="address" value="<?= htmlspecialchars($data['address']??'') ?>"></div>
        <div class="grid2">
          <div class="fl"><label>Rent Amount (UGX)</label><input type="number" step="0.01" name="rent_amount" value="<?= $data['rent_amount']??0 ?>"></div>
          <div class="fl"><label>Status</label><select name="status"><option value="available" <?= ($data['status']??'')==='available'?'selected':'' ?>>Available</option><option value="occupied" <?= ($data['status']??'')==='occupied'?'selected':'' ?>>Occupied</option><option value="archived" <?= ($data['status']??'')==='archived'?'selected':'' ?>>Archived</option></select></div>
        </div>
        <div class="fl"><label>Description</label><textarea name="description"><?= htmlspecialchars($data['description']??'') ?></textarea></div>
 
      <?php elseif($type === 'staff'): ?>
        <div class="fl"><label>Full Name</label><input type="text" name="fullname" value="<?= htmlspecialchars($data['fullname']??'') ?>" required></div>
        <div class="grid2">
          <div class="fl"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($data['email']??'') ?>"></div>
          <div class="fl"><label>Phone</label><input type="tel" name="phone" value="<?= htmlspecialchars($data['phone']??'') ?>"></div>
        </div>
        <div class="grid2">
          <div class="fl"><label>Role</label><select name="role"><option value="staff" <?= ($data['role']??'')==='staff'?'selected':'' ?>>Staff</option><option value="broker" <?= ($data['role']??'')==='broker'?'selected':'' ?>>Broker</option><option value="admin" <?= ($data['role']??'')==='admin'?'selected':'' ?>>Admin</option></select></div>
          <div class="fl"><label>Salary (UGX)</label><input type="number" name="salary" value="<?= $data['salary']??0 ?>"></div>
        </div>
 
      <?php elseif($type === 'task'): ?>
        <div class="fl"><label>Task Title</label><input type="text" name="title" value="<?= htmlspecialchars($data['title']??'') ?>" required></div>
        <div class="fl"><label>Description</label><textarea name="description"><?= htmlspecialchars($data['description']??'') ?></textarea></div>
        <div class="fl"><label>Assigned To</label><select name="assigned_to"><option value="">— Select Staff —</option><?php while($s=mysqli_fetch_assoc($staff_list)): ?><option value="<?= $s['id'] ?>" <?= $s['id']==$data['assigned_to']?'selected':'' ?>><?= htmlspecialchars($s['fullname']) ?></option><?php endwhile; ?></select></div>
        <div class="grid2">
          <div class="fl"><label>Due Date</label><input type="date" name="due_date" value="<?= htmlspecialchars($data['due_date']??'') ?>"></div>
          <div class="fl"><label>Priority</label><select name="priority"><option value="Low" <?= ($data['priority']??'')==='Low'?'selected':'' ?>>Low</option><option value="Medium" <?= ($data['priority']??'')==='Medium'?'selected':'' ?>>Medium</option><option value="High" <?= ($data['priority']??'')==='High'?'selected':'' ?>>High</option></select></div>
        </div>
        <div class="fl"><label>Status</label><select name="status"><option value="Pending" <?= ($data['status']??'')==='Pending'?'selected':'' ?>>Pending</option><option value="In Progress" <?= ($data['status']??'')==='In Progress'?'selected':'' ?>>In Progress</option><option value="Completed" <?= ($data['status']??'')==='Completed'?'selected':'' ?>>Completed</option></select></div>
 
      <?php elseif($type === 'tenant'): ?>
        <div class="fl"><label>Full Name</label><input type="text" name="fullname" value="<?= htmlspecialchars($data['fullname']??'') ?>" required></div>
        <div class="grid2">
          <div class="fl"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($data['email']??'') ?>"></div>
          <div class="fl"><label>Phone</label><input type="tel" name="phone" value="<?= htmlspecialchars($data['phone']??'') ?>"></div>
        </div>
        <div class="grid2">
          <div class="fl"><label>Gender</label><select name="gender"><option value="">— Select —</option><option value="Male" <?= ($data['gender']??'')==='Male'?'selected':'' ?>>Male</option><option value="Female" <?= ($data['gender']??'')==='Female'?'selected':'' ?>>Female</option><option value="Other" <?= ($data['gender']??'')==='Other'?'selected':'' ?>>Other</option></select></div>
          <div class="fl"><label>National ID</label><input type="text" name="national_id" value="<?= htmlspecialchars($data['national_id']??'') ?>"></div>
        </div>
        <div class="grid2">
          <div class="fl"><label>Occupation</label><input type="text" name="occupation" value="<?= htmlspecialchars($data['occupation']??'') ?>"></div>
          <div class="fl"><label>Status</label><select name="status"><option value="Active" <?= ($data['status']??'')==='Active'?'selected':'' ?>>Active</option><option value="Inactive" <?= ($data['status']??'')==='Inactive'?'selected':'' ?>>Inactive</option><option value="Suspended" <?= ($data['status']??'')==='Suspended'?'selected':'' ?>>Suspended</option><option value="Terminated" <?= ($data['status']??'')==='Terminated'?'selected':'' ?>>Terminated</option></select></div>
        </div>
        <div class="fl"><label>Property</label><select name="property_id"><option value="">— Not assigned —</option><?php while($p=mysqli_fetch_assoc($properties_list)): ?><option value="<?= $p['id'] ?>" <?= $p['id']==$data['property_id']?'selected':'' ?>><?= htmlspecialchars($p['property_name']) ?></option><?php endwhile; ?></select></div>
        <div class="grid2">
          <div class="fl"><label>Lease Start</label><input type="date" name="lease_start" value="<?= htmlspecialchars($data['lease_start']??'') ?>"></div>
          <div class="fl"><label>Lease End</label><input type="date" name="lease_end" value="<?= htmlspecialchars($data['lease_end']??'') ?>"></div>
        </div>
        <div class="grid2">
          <div class="fl"><label>Emergency Contact Name</label><input type="text" name="emergency_name" value="<?= htmlspecialchars($data['emergency_name']??'') ?>"></div>
          <div class="fl"><label>Emergency Phone</label><input type="tel" name="emergency_phone" value="<?= htmlspecialchars($data['emergency_phone']??'') ?>"></div>
        </div>
 
      <?php elseif($type === 'complaint'): ?>
        <div class="fl"><label>Category</label><input type="text" value="<?= htmlspecialchars($data['category']??'') ?>" disabled></div>
        <div class="fl"><label>Message</label><textarea disabled><?= htmlspecialchars($data['message']??'') ?></textarea></div>
        <div class="fl"><label>Status</label><select name="status"><option value="pending" <?= ($data['status']??'')==='pending'?'selected':'' ?>>Pending</option><option value="resolved" <?= ($data['status']??'')==='resolved'?'selected':'' ?>>Resolved</option></select></div>
 
      <?php elseif($type === 'maintenance'): ?>
        <div class="fl"><label>Issue</label><textarea disabled><?= htmlspecialchars($data['issue']??'') ?></textarea></div>
        <div class="grid2">
          <div class="fl"><label>Priority</label><select name="priority"><option value="low" <?= ($data['priority']??'')==='low'?'selected':'' ?>>Low</option><option value="medium" <?= ($data['priority']??'')==='medium'?'selected':'' ?>>Medium</option><option value="high" <?= ($data['priority']??'')==='high'?'selected':'' ?>>High</option></select></div>
          <div class="fl"><label>Status</label><select name="status"><option value="open" <?= ($data['status']??'')==='open'?'selected':'' ?>>Open</option><option value="in_progress" <?= ($data['status']??'')==='in_progress'?'selected':'' ?>>In Progress</option><option value="resolved" <?= ($data['status']??'')==='resolved'?'selected':'' ?>>Resolved</option><option value="closed" <?= ($data['status']??'')==='closed'?'selected':'' ?>>Closed</option></select></div>
        </div>
 
      <?php elseif($type === 'payment'): ?>
        <div class="grid2">
          <div class="fl"><label>Amount (UGX)</label><input type="number" step="0.01" name="amount" value="<?= $data['amount']??0 ?>" required></div>
          <div class="fl"><label>Status</label><select name="status"><option value="pending" <?= ($data['status']??'')==='pending'?'selected':'' ?>>Pending</option><option value="paid" <?= ($data['status']??'')==='paid'?'selected':'' ?>>Paid</option><option value="failed" <?= ($data['status']??'')==='failed'?'selected':'' ?>>Failed</option><option value="cancelled" <?= ($data['status']??'')==='cancelled'?'selected':'' ?>>Cancelled</option></select></div>
        </div>
 
      <?php elseif($type === 'inspection'): ?>
        <div class="fl"><label>Situation / Findings</label><input type="text" name="situation" value="<?= htmlspecialchars($data['condition']??$data['situation']??'') ?>" required></div>
        <div class="fl"><label>Status</label><select name="status"><option value="Pending" <?= ($data['status']??'')==='Pending'?'selected':'' ?>>Pending</option><option value="Completed" <?= ($data['status']??'')==='Completed'?'selected':'' ?>>Completed</option></select></div>
        <div class="fl"><label>Notes</label><textarea name="notes"><?= htmlspecialchars($data['notes']??'') ?></textarea></div>
 
      <?php elseif($type === 'propertyowner'): ?>
        <div class="fl"><label>Full Name</label><input type="text" name="fullname" value="<?= htmlspecialchars($data['fullname']??'') ?>" required></div>
        <div class="grid2">
          <div class="fl"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($data['email']??'') ?>"></div>
          <div class="fl"><label>Phone</label><input type="tel" name="phone" value="<?= htmlspecialchars($data['phone']??'') ?>"></div>
        </div>
 
      <?php elseif($type === 'document'): ?>
        <div class="fl"><label>Document Name</label><input type="text" name="document_name" value="<?= htmlspecialchars($data['document_name']??'') ?>" required></div>
        <div class="fl"><label>File Path (read only)</label><input type="text" value="<?= htmlspecialchars($data['file_path']??'') ?>" disabled></div>
 
      <?php else: ?>
        <p style="color:#fca5a5;text-align:center">Unknown type: <?= htmlspecialchars($type) ?></p>
      <?php endif; ?>
 
      <button type="submit" name="update" class="btn">💾 Save Changes</button>
    </form>
  </div>
 
</div>
</body>
</html>