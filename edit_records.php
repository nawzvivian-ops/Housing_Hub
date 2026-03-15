<?php
session_start();
include "db_connect.php";
 
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
 
// ── Read type and id from GET (link click) OR POST (form submit) ──
$type = $_GET['type'] ?? $_POST['type'] ?? '';
$id   = (int)($_GET['id']   ?? $_POST['id']   ?? 0);
 
if ($id <= 0 || empty($type)) {
    die("Invalid request — no ID or type provided.");
}
 
/* ==========================================
   HANDLE UPDATE SUBMISSION
========================================== */
if (isset($_POST['update'])) {
 
    /* ── PROPERTY ── */
    if ($type === "property") {
        $property_name = mysqli_real_escape_string($conn, $_POST['property_name']);
        $property_type = mysqli_real_escape_string($conn, $_POST['property_type']);
        $address       = mysqli_real_escape_string($conn, $_POST['address']);
        $units         = (int)$_POST['units'];
        $rent_amount   = (float)$_POST['rent_amount'];
        $status        = mysqli_real_escape_string($conn, $_POST['status'] ?? 'available');
        $description   = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
 
        mysqli_query($conn, "
            UPDATE properties
            SET property_name='$property_name',
                property_type='$property_type',
                address='$address',
                units=$units,
                rent_amount=$rent_amount,
                status='$status',
                description='$description'
            WHERE id=$id");
 
        header("Location: admin_dashboard.php?page=properties"); exit();
    }
 
    /* ── STAFF ── */
    if ($type === "staff") {
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $role     = mysqli_real_escape_string($conn, $_POST['role']);
        $salary   = (float)$_POST['salary'];
        $email    = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
        $phone    = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
 
        mysqli_query($conn, "
            UPDATE users
            SET fullname='$fullname', role='$role', salary=$salary,
                email='$email', phone='$phone'
            WHERE id=$id");
 
        header("Location: admin_dashboard.php?page=staff_roles"); exit();
    }
 
    /* ── TASK ── */
    if ($type === "task") {
        $title       = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $assigned_to = (int)$_POST['assigned_to'];
        $due_date    = mysqli_real_escape_string($conn, $_POST['due_date']);
        $priority    = mysqli_real_escape_string($conn, $_POST['priority']);
        $status      = mysqli_real_escape_string($conn, $_POST['status']);
 
        mysqli_query($conn, "
            UPDATE tasks
            SET title='$title', description='$description',
                assigned_to=$assigned_to, due_date='$due_date',
                priority='$priority', status='$status'
            WHERE id=$id");
 
        header("Location: admin_dashboard.php?page=staff_tasks"); exit();
    }
 
    /* ── TENANT ── */
    if ($type === "tenant") {
        $fullname    = mysqli_real_escape_string($conn, $_POST['fullname']);
        $phone       = mysqli_real_escape_string($conn, $_POST['phone']);
        $email       = mysqli_real_escape_string($conn, $_POST['email']);
        $property_id = (int)($_POST['property_id'] ?? 0) ?: 'NULL';
        $lease_start = mysqli_real_escape_string($conn, $_POST['lease_start'] ?? '');
        $lease_end   = mysqli_real_escape_string($conn, $_POST['lease_end']   ?? '');
        $status      = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Active');
        $occupation  = mysqli_real_escape_string($conn, $_POST['occupation']  ?? '');
        $gender      = mysqli_real_escape_string($conn, $_POST['gender']      ?? '');
        $national_id = mysqli_real_escape_string($conn, $_POST['national_id'] ?? '');
        $emergency_name  = mysqli_real_escape_string($conn, $_POST['emergency_name']  ?? '');
        $emergency_phone = mysqli_real_escape_string($conn, $_POST['emergency_phone'] ?? '');
 
        $prop_val = is_numeric($property_id) ? $property_id : 'NULL';
 
        mysqli_query($conn, "
            UPDATE tenants
            SET fullname='$fullname', phone='$phone', email='$email',
                property_id=$prop_val,
                lease_start=" . ($lease_start ? "'$lease_start'" : "NULL") . ",
                lease_end="   . ($lease_end   ? "'$lease_end'"   : "NULL") . ",
                status='$status', occupation='$occupation', gender='$gender',
                national_id='$national_id',
                emergency_name='$emergency_name', emergency_phone='$emergency_phone'
            WHERE id=$id");
 
        // Keep users table in sync if linked
        mysqli_query($conn, "
            UPDATE users SET fullname='$fullname', email='$email', phone='$phone'
            WHERE id = (SELECT user_id FROM tenants WHERE id=$id)");
 
        header("Location: admin_dashboard.php?page=tenants"); exit();
    }
 
    /* ── COMPLAINT ── */
    if ($type === "complaint") {
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        mysqli_query($conn, "UPDATE complaints SET status='$status' WHERE id=$id");
        header("Location: admin_dashboard.php?page=complaints"); exit();
    }
 
    /* ── MAINTENANCE ── */
    if ($type === "maintenance") {
        $status   = mysqli_real_escape_string($conn, $_POST['status']);
        $priority = mysqli_real_escape_string($conn, $_POST['priority']);
        mysqli_query($conn, "
            UPDATE maintenance_requests
            SET status='$status', priority='$priority'
            WHERE id=$id");
        header("Location: admin_dashboard.php?page=maintenance"); exit();
    }
 
    /* ── PAYMENT ── */
    if ($type === "payment") {
        $amount = (float)$_POST['amount'];
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        mysqli_query($conn, "UPDATE payments SET amount=$amount, status='$status' WHERE id=$id");
        header("Location: admin_dashboard.php?page=payments"); exit();
    }
 
    /* ── INSPECTION ── */
    if ($type === "inspection") {
        $status    = mysqli_real_escape_string($conn, $_POST['status']);
        $situation = mysqli_real_escape_string($conn, $_POST['situation']);
        $notes     = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');
        mysqli_query($conn, "
            UPDATE inspections
            SET status='$status', situation='$situation', notes='$notes'
            WHERE id=$id");
        header("Location: admin_dashboard.php?page=inspections"); exit();
    }
 
    /* ── PROPERTY OWNER ── */
    if ($type === "propertyowner") {
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $email    = mysqli_real_escape_string($conn, $_POST['email']);
        $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
        mysqli_query($conn, "
            UPDATE users SET fullname='$fullname', email='$email', phone='$phone'
            WHERE id=$id");
        header("Location: admin_dashboard.php?page=propertyowners"); exit();
    }
 
    /* ── DOCUMENT ── */
    if ($type === "document") {
        $document_name = mysqli_real_escape_string($conn, $_POST['document_name']);
        mysqli_query($conn, "
            UPDATE tenant_documents SET document_name='$document_name' WHERE id=$id");
        header("Location: admin_dashboard.php?page=tenant_documents"); exit();
    }
}
 
/* ==========================================
   FETCH RECORD FOR DISPLAY
========================================== */
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
 
// Extra data for dropdowns
$properties_list = mysqli_query($conn, "SELECT id, property_name FROM properties ORDER BY property_name ASC");
$staff_list      = mysqli_query($conn, "SELECT id, fullname FROM users WHERE role='staff' ORDER BY fullname ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit <?= ucfirst($type) ?> | HousingHub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:"Segoe UI",sans-serif;background:#f0f4f8;padding:30px 20px;min-height:100vh}
.wrap{background:white;padding:32px;width:100%;max-width:520px;margin:auto;border-radius:14px;box-shadow:0 6px 24px rgba(0,0,0,.1)}
h2{text-align:center;color:#0ea5e9;margin-bottom:6px;font-size:22px;text-transform:uppercase;letter-spacing:1px}
.sub{text-align:center;font-size:12px;color:#aaa;margin-bottom:24px}
label{display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px}
input,select,textarea{width:100%;padding:11px 13px;margin-bottom:16px;border:1px solid #ddd;border-radius:8px;font-size:14px;font-family:"Segoe UI",sans-serif;outline:none;transition:border .2s}
input:focus,select:focus,textarea:focus{border-color:#0ea5e9;box-shadow:0 0 0 3px rgba(14,165,233,.1)}
textarea{resize:vertical;min-height:80px}
.btn{width:100%;padding:13px;background:#0ea5e9;border:none;color:white;font-size:15px;font-weight:bold;border-radius:8px;cursor:pointer;transition:.3s;margin-top:4px}
.btn:hover{background:#0284c7;transform:translateY(-2px)}
.back{display:block;text-align:center;margin-top:14px;text-decoration:none;color:#0ea5e9;font-size:13px;font-weight:600}
.back:hover{text-decoration:underline}
.record-info{background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:10px 14px;margin-bottom:20px;font-size:12px;color:#0369a1}
</style>
</head>
<body>
<div class="wrap">
  <h2>Edit <?= ucfirst($type) ?></h2>
  <div class="sub">HousingHub Admin Panel · ID: <?= $id ?></div>
 
  <div class="record-info">
    Editing record <strong>#<?= $id ?></strong> from <strong><?= $table ?></strong>
  </div>
 
  <form method="POST">
    <!-- Always pass type and id as hidden fields so POST update works -->
    <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
    <input type="hidden" name="id"   value="<?= $id ?>">
 
    <?php /* ── PROPERTY ── */ if ($type === "property"): ?>
      <label>Property Name</label>
      <input type="text" name="property_name" value="<?= htmlspecialchars($data['property_name'] ?? '') ?>" required>
      <label>Property Type</label>
      <input type="text" name="property_type" value="<?= htmlspecialchars($data['property_type'] ?? '') ?>">
      <label>Address</label>
      <input type="text" name="address" value="<?= htmlspecialchars($data['address'] ?? '') ?>">
      <label>Units</label>
      <input type="number" name="units" value="<?= (int)($data['units'] ?? 0) ?>">
      <label>Rent Amount (UGX)</label>
      <input type="number" step="0.01" name="rent_amount" value="<?= $data['rent_amount'] ?? 0 ?>">
      <label>Status</label>
      <select name="status">
        <option value="available" <?= ($data['status']??'')==='available'?'selected':'' ?>>Available</option>
        <option value="occupied"  <?= ($data['status']??'')==='occupied' ?'selected':'' ?>>Occupied</option>
        <option value="archived"  <?= ($data['status']??'')==='archived' ?'selected':'' ?>>Archived</option>
      </select>
      <label>Description</label>
      <textarea name="description"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
 
    <?php /* ── STAFF ── */ elseif ($type === "staff"): ?>
      <label>Full Name</label>
      <input type="text" name="fullname" value="<?= htmlspecialchars($data['fullname'] ?? '') ?>" required>
      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>">
      <label>Phone</label>
      <input type="tel" name="phone" value="<?= htmlspecialchars($data['phone'] ?? '') ?>">
      <label>Role</label>
      <select name="role">
        <option value="staff"  <?= ($data['role']??'')==='staff'  ?'selected':'' ?>>Staff</option>
        <option value="broker" <?= ($data['role']??'')==='broker' ?'selected':'' ?>>Broker</option>
        <option value="admin"  <?= ($data['role']??'')==='admin'  ?'selected':'' ?>>Admin</option>
      </select>
      <label>Salary (UGX)</label>
      <input type="number" name="salary" value="<?= $data['salary'] ?? 0 ?>">
 
    <?php /* ── TASK ── */ elseif ($type === "task"): ?>
      <label>Task Title</label>
      <input type="text" name="title" value="<?= htmlspecialchars($data['title'] ?? '') ?>" required>
      <label>Description</label>
      <textarea name="description"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
      <label>Assigned To</label>
      <select name="assigned_to">
        <option value="">— Select Staff —</option>
        <?php while ($s = mysqli_fetch_assoc($staff_list)): ?>
        <option value="<?= $s['id'] ?>" <?= $s['id']==$data['assigned_to']?'selected':'' ?>><?= htmlspecialchars($s['fullname']) ?></option>
        <?php endwhile; ?>
      </select>
      <label>Due Date</label>
      <input type="date" name="due_date" value="<?= htmlspecialchars($data['due_date'] ?? '') ?>">
      <label>Priority</label>
      <select name="priority">
        <option value="Low"    <?= ($data['priority']??'')==='Low'    ?'selected':'' ?>>Low</option>
        <option value="Medium" <?= ($data['priority']??'')==='Medium' ?'selected':'' ?>>Medium</option>
        <option value="High"   <?= ($data['priority']??'')==='High'   ?'selected':'' ?>>High</option>
      </select>
      <label>Status</label>
      <select name="status">
        <option value="Pending"     <?= ($data['status']??'')==='Pending'     ?'selected':'' ?>>Pending</option>
        <option value="In Progress" <?= ($data['status']??'')==='In Progress' ?'selected':'' ?>>In Progress</option>
        <option value="Completed"   <?= ($data['status']??'')==='Completed'   ?'selected':'' ?>>Completed</option>
      </select>
 
    <?php /* ── TENANT ── */ elseif ($type === "tenant"): ?>
      <label>Full Name</label>
      <input type="text" name="fullname" value="<?= htmlspecialchars($data['fullname'] ?? '') ?>" required>
      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>">
      <label>Phone</label>
      <input type="tel" name="phone" value="<?= htmlspecialchars($data['phone'] ?? '') ?>">
      <label>Gender</label>
      <select name="gender">
        <option value=""       <?= empty($data['gender'])?'selected':'' ?>>— Select —</option>
        <option value="Male"   <?= ($data['gender']??'')==='Male'  ?'selected':'' ?>>Male</option>
        <option value="Female" <?= ($data['gender']??'')==='Female'?'selected':'' ?>>Female</option>
        <option value="Other"  <?= ($data['gender']??'')==='Other' ?'selected':'' ?>>Other</option>
      </select>
      <label>National ID</label>
      <input type="text" name="national_id" value="<?= htmlspecialchars($data['national_id'] ?? '') ?>">
      <label>Occupation</label>
      <input type="text" name="occupation" value="<?= htmlspecialchars($data['occupation'] ?? '') ?>">
      <label>Property</label>
      <select name="property_id">
        <option value="">— Not assigned —</option>
        <?php while ($p = mysqli_fetch_assoc($properties_list)): ?>
        <option value="<?= $p['id'] ?>" <?= $p['id']==$data['property_id']?'selected':'' ?>><?= htmlspecialchars($p['property_name']) ?></option>
        <?php endwhile; ?>
      </select>
      <label>Lease Start</label>
      <input type="date" name="lease_start" value="<?= htmlspecialchars($data['lease_start'] ?? '') ?>">
      <label>Lease End</label>
      <input type="date" name="lease_end" value="<?= htmlspecialchars($data['lease_end'] ?? '') ?>">
      <label>Emergency Contact Name</label>
      <input type="text" name="emergency_name" value="<?= htmlspecialchars($data['emergency_name'] ?? '') ?>">
      <label>Emergency Contact Phone</label>
      <input type="tel" name="emergency_phone" value="<?= htmlspecialchars($data['emergency_phone'] ?? '') ?>">
      <label>Status</label>
      <select name="status">
        <option value="Active"     <?= ($data['status']??'')==='Active'     ?'selected':'' ?>>Active</option>
        <option value="Inactive"   <?= ($data['status']??'')==='Inactive'   ?'selected':'' ?>>Inactive</option>
        <option value="Suspended"  <?= ($data['status']??'')==='Suspended'  ?'selected':'' ?>>Suspended</option>
        <option value="Terminated" <?= ($data['status']??'')==='Terminated' ?'selected':'' ?>>Terminated</option>
      </select>
 
    <?php /* ── COMPLAINT ── */ elseif ($type === "complaint"): ?>
      <label>Category</label>
      <input type="text" value="<?= htmlspecialchars($data['category'] ?? '') ?>" disabled style="background:#f5f5f5">
      <label>Message</label>
      <textarea disabled style="background:#f5f5f5"><?= htmlspecialchars($data['message'] ?? '') ?></textarea>
      <label>Status</label>
      <select name="status">
        <option value="pending"  <?= ($data['status']??'')==='pending'  ?'selected':'' ?>>Pending</option>
        <option value="resolved" <?= ($data['status']??'')==='resolved' ?'selected':'' ?>>Resolved</option>
      </select>
 
    <?php /* ── MAINTENANCE ── */ elseif ($type === "maintenance"): ?>
      <label>Issue</label>
      <textarea disabled style="background:#f5f5f5"><?= htmlspecialchars($data['issue'] ?? '') ?></textarea>
      <label>Priority</label>
      <select name="priority">
        <option value="low"    <?= ($data['priority']??'')==='low'    ?'selected':'' ?>>Low</option>
        <option value="medium" <?= ($data['priority']??'')==='medium' ?'selected':'' ?>>Medium</option>
        <option value="high"   <?= ($data['priority']??'')==='high'   ?'selected':'' ?>>High</option>
      </select>
      <label>Status</label>
      <select name="status">
        <option value="open"        <?= ($data['status']??'')==='open'        ?'selected':'' ?>>Open</option>
        <option value="in_progress" <?= ($data['status']??'')==='in_progress' ?'selected':'' ?>>In Progress</option>
        <option value="resolved"    <?= ($data['status']??'')==='resolved'    ?'selected':'' ?>>Resolved</option>
        <option value="closed"      <?= ($data['status']??'')==='closed'      ?'selected':'' ?>>Closed</option>
      </select>
 
    <?php /* ── PAYMENT ── */ elseif ($type === "payment"): ?>
      <label>Amount (UGX)</label>
      <input type="number" step="0.01" name="amount" value="<?= $data['amount'] ?? 0 ?>" required>
      <label>Status</label>
      <select name="status">
        <option value="pending"   <?= ($data['status']??'')==='pending'   ?'selected':'' ?>>Pending</option>
        <option value="paid"      <?= ($data['status']??'')==='paid'      ?'selected':'' ?>>Paid</option>
        <option value="failed"    <?= ($data['status']??'')==='failed'    ?'selected':'' ?>>Failed</option>
        <option value="cancelled" <?= ($data['status']??'')==='cancelled' ?'selected':'' ?>>Cancelled</option>
      </select>
 
    <?php /* ── INSPECTION ── */ elseif ($type === "inspection"): ?>
      <label>Situation / Findings</label>
      <input type="text" name="situation" value="<?= htmlspecialchars($data['situation'] ?? '') ?>" required>
      <label>Status</label>
      <select name="status">
        <option value="Pending"   <?= ($data['status']??'')==='Pending'   ?'selected':'' ?>>Pending</option>
        <option value="Completed" <?= ($data['status']??'')==='Completed' ?'selected':'' ?>>Completed</option>
      </select>
      <label>Notes</label>
      <textarea name="notes"><?= htmlspecialchars($data['notes'] ?? '') ?></textarea>
 
    <?php /* ── PROPERTY OWNER ── */ elseif ($type === "propertyowner"): ?>
      <label>Full Name</label>
      <input type="text" name="fullname" value="<?= htmlspecialchars($data['fullname'] ?? '') ?>" required>
      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>">
      <label>Phone</label>
      <input type="tel" name="phone" value="<?= htmlspecialchars($data['phone'] ?? '') ?>">
 
    <?php /* ── DOCUMENT ── */ elseif ($type === "document"): ?>
      <label>Document Name</label>
      <input type="text" name="document_name" value="<?= htmlspecialchars($data['document_name'] ?? '') ?>" required>
      <label>File Path (read only)</label>
      <input type="text" value="<?= htmlspecialchars($data['file_path'] ?? '') ?>" disabled style="background:#f5f5f5">
 
    <?php else: ?>
      <p style="color:red;text-align:center">Unknown type: <?= htmlspecialchars($type) ?></p>
    <?php endif; ?>
 
    <button type="submit" name="update" class="btn">💾 Save Changes</button>
  </form>
 
  <a href="admin_dashboard.php" class="back">⬅ Back to Admin Dashboard</a>
</div>
</body>
</html>