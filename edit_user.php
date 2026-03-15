<?php
session_start();
include "db_connect.php";
 
// ── Check admin ──
$session_id = (int)($_SESSION['user_id'] ?? 0);
$admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE id='$session_id' LIMIT 1"));
if (!$admin || strtolower(trim($admin['role'])) !== 'admin') {
    header("Location: dashboard.php"); exit();
}
 
// ── Get the user ID ──
// On first load it comes via GET (?id=123)
// On form submit it comes via POST (hidden field)
$edit_id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
 
if ($edit_id <= 0) {
    echo "No user ID provided."; exit();
}
 
// ── Fetch user to edit ──
$edit_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$edit_id' LIMIT 1"));
 
if (!$edit_user) {
    echo "User not found (ID: $edit_id)."; exit();
}
 
// ── Handle form submission ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullname'])) {
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $role     = mysqli_real_escape_string($conn, trim($_POST['role']));
    $phone    = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
 
    mysqli_query($conn,
        "UPDATE users SET fullname='$fullname', email='$email', role='$role', phone='$phone'
         WHERE id='$edit_id'");
 
    // If role is tenant, also update the tenants table if linked
    if ($role === 'tenant') {
        mysqli_query($conn,
            "UPDATE tenants SET fullname='$fullname', email='$email', phone='$phone'
             WHERE user_id='$edit_id'");
    }
 
    header("Location: admin_dashboard.php?page=users&msg=updated");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User | HousingHub Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:"Segoe UI",sans-serif;background:lightblue;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px}
.card{background:linear-gradient(145deg,#0c0c0c,#0ea5e9);padding:40px;border-radius:20px;box-shadow:0 10px 25px rgba(0,0,0,.3);color:white;width:100%;max-width:460px}
.card h2{text-align:center;margin-bottom:8px;font-size:24px;text-transform:uppercase;letter-spacing:1px}
.card .sub{text-align:center;font-size:12px;color:rgba(255,255,255,.6);margin-bottom:28px}
label{display:block;margin-bottom:6px;font-size:13px;font-weight:600;letter-spacing:.5px;opacity:.85}
input,select{width:100%;padding:12px 14px;margin-bottom:18px;border-radius:8px;border:none;font-size:14px;outline:none;font-family:"Segoe UI",sans-serif}
input:focus,select:focus{box-shadow:0 0 0 3px rgba(255,255,255,.3)}
select option{background:#0c0c0c;color:white}
.btn{width:100%;padding:13px;background:#080808;border:none;border-radius:10px;font-size:15px;color:white;font-weight:bold;cursor:pointer;transition:.3s;margin-top:4px}
.btn:hover{background:#053896;transform:translateY(-2px)}
.back{display:block;text-align:center;margin-top:16px;text-decoration:none;color:rgba(255,255,255,.8);font-size:13px;font-weight:600}
.back:hover{color:white}
.user-info{background:rgba(255,255,255,.08);border-radius:8px;padding:12px 16px;margin-bottom:22px;font-size:12px;color:rgba(255,255,255,.7)}
.user-info strong{color:white}
</style>
</head>
<body>
<div class="card">
  <h2>Edit User</h2>
  <div class="sub">HousingHub Admin Panel</div>
 
  <div class="user-info">
    Editing: <strong><?= htmlspecialchars($edit_user['fullname']) ?></strong>
    &nbsp;·&nbsp; ID: <strong><?= $edit_id ?></strong>
    &nbsp;·&nbsp; Role: <strong><?= htmlspecialchars($edit_user['role']) ?></strong>
  </div>
 
  <form method="POST">
    <!-- Pass the ID through the form so it's available on POST -->
    <input type="hidden" name="id" value="<?= $edit_id ?>">
 
    <label>Full Name</label>
    <input type="text" name="fullname"
           value="<?= htmlspecialchars($edit_user['fullname']) ?>" required>
 
    <label>Email Address</label>
    <input type="email" name="email"
           value="<?= htmlspecialchars($edit_user['email'] ?? '') ?>" required>
 
    <label>Phone Number</label>
    <input type="tel" name="phone"
           value="<?= htmlspecialchars($edit_user['phone'] ?? '') ?>"
           placeholder="+256 700 000000">
 
    <label>Role</label>
    <select name="role" required>
      <option value="admin"         <?= $edit_user['role']==='admin'         ?'selected':'' ?>>Admin</option>
      <option value="staff"         <?= $edit_user['role']==='staff'         ?'selected':'' ?>>Staff</option>
      <option value="tenant"        <?= $edit_user['role']==='tenant'        ?'selected':'' ?>>Tenant</option>
      <option value="broker"        <?= $edit_user['role']==='broker'        ?'selected':'' ?>>Broker</option>
      <option value="propertyowner" <?= $edit_user['role']==='propertyowner' ?'selected':'' ?>>Property Owner</option>
      <option value="guest"         <?= $edit_user['role']==='guest'         ?'selected':'' ?>>Guest</option>
    </select>
 
    <button type="submit" class="btn">💾 Save Changes</button>
  </form>
 
  <a href="admin_dashboard.php?page=users" class="back">⬅ Back to Users</a>
</div>
</body>
</html>