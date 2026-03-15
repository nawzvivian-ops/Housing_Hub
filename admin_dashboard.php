<?php
session_start();
include "db_connect.php";
 
// ── Prevent browser caching so all users always see fresh data ──
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
 
// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
 
$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($result);
 
$role = strtolower(trim($user['role']));
 
if ($role !== 'admin') {
    header("Location: dashboard.php");
    exit();
}
 
// ── Stats ──
$total_brokers       = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM users WHERE role='broker'"))['count'];
$total_owners        = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM users WHERE role='owner'"))['count'];
$total_guests        = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM guests"))['count'];
$total_complaints    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM complaints WHERE status='pending'"))['count'];
$total_notifications = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM notifications WHERE is_read=0"))['count'];
$pending_payments    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM payments WHERE status='pending'"))['count'];
$total_properties    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM properties"))['count'];
$total_tenants       = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM tenants"))['count'];
$total_staff         = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM users WHERE role='staff'"))['count'];
$pending_applications= mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM job_applications WHERE status='pending'"))['count'];
$pending_requests    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM maintenance_requests WHERE status='pending'"))['count'];
$revenue             = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as total FROM payments"))['total'];
 
// ── Count unlinked tenants (tenant rows with no user_id set) ──
$unlinked_count = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as count FROM tenants WHERE user_id IS NULL OR user_id = 0"
))['count'];
 
$page = $_GET['page'] ?? 'dashboard';
 
// ── Handle inline link form POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'link_account') {
    $tenant_id  = (int)$_POST['tenant_id'];
    $link_user_id = (int)$_POST['link_user_id'];
 
    if ($tenant_id > 0 && $link_user_id > 0) {
        // Check if this user_id is already linked to a different tenant
        $check = mysqli_query($conn,
            "SELECT id FROM tenants WHERE user_id = '$link_user_id' AND id != '$tenant_id' LIMIT 1");
 
        if (mysqli_num_rows($check) > 0) {
            $_SESSION['admin_error'] = "This user account is already linked to another tenant.";
        } else {
            mysqli_query($conn,
                "UPDATE tenants SET user_id = '$link_user_id' WHERE id = '$tenant_id'");
            $_SESSION['admin_success'] = "Account linked successfully! The tenant can now access their dashboard.";
        }
    }
    header("Location: admin_dashboard.php?page=tenants");
    exit();
}
 
// ── Handle unlink POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unlink_account') {
    $tenant_id = (int)$_POST['tenant_id'];
    if ($tenant_id > 0) {
        mysqli_query($conn, "UPDATE tenants SET user_id = NULL WHERE id = '$tenant_id'");
        $_SESSION['admin_success'] = "Account unlinked. The tenant will see the pending screen on next login.";
    }
    header("Location: admin_dashboard.php?page=tenants");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | HousingHub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25);--red:#ef4444;--green:#16a34a;--sw:260px}
html,body{height:100%;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden}
body::before{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
.sidebar{position:fixed;left:0;top:0;width:var(--sw);height:100%;background:rgba(4,9,26,.98);border-right:1px solid var(--border);color:var(--white);display:flex;flex-direction:column;overflow-y:auto;z-index:1000}
.sidebar::-webkit-scrollbar{width:3px}.sidebar::-webkit-scrollbar-thumb{background:var(--gb);border-radius:2px}
.sidebar h2{text-align:center;padding:24px 20px 20px;font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gold);border-bottom:1px solid var(--border)}
.sidebar a{color:var(--muted);padding:11px 22px;text-decoration:none;display:block;transition:all .2s;font-size:13px;font-weight:500;border-left:3px solid transparent}
.sidebar a:hover{color:var(--white);background:rgba(255,255,255,.04);border-left-color:var(--gb)}
.sidebar a.active{color:var(--gold);background:rgba(200,164,60,.08);border-left-color:var(--gold)}
.header{display:flex;justify-content:space-between;align-items:center;background:rgba(4,9,26,.95);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);color:var(--white);padding:16px 36px;position:sticky;top:0;z-index:100;margin-left:var(--sw);box-shadow:0 2px 20px rgba(0,0,0,.3)}
.header h1{font-family:"Cormorant Garamond",serif;font-size:22px;font-weight:700;color:var(--gold);letter-spacing:1px}
.logout-btn{color:var(--white);text-decoration:none;background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);padding:9px 20px;border-radius:6px;font-size:12px;font-weight:600;letter-spacing:1px;text-transform:uppercase;transition:all .3s}
.logout-btn:hover{background:rgba(239,68,68,.3)}
.main-content{margin-left:var(--sw);padding:32px 40px;min-height:calc(100vh - 60px);position:relative;z-index:10}
section h2{margin-bottom:24px;font-family:"Cormorant Garamond",serif;font-size:28px;font-weight:700;color:var(--white);border-bottom:2px solid var(--gb);padding-bottom:12px}
.overview-cards{display:flex;flex-wrap:wrap;gap:20px;justify-content:center;margin-bottom:40px}
.circular-card{width:150px;height:150px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.15),rgba(14,90,200,.15));border:2px solid var(--gb);color:var(--white);display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;box-shadow:0 8px 24px rgba(0,0,0,.4);transition:transform .3s,box-shadow .3s}
.circular-card:hover{transform:scale(1.06);box-shadow:0 12px 32px rgba(200,164,60,.2)}
.circular-card h3{margin:0 0 6px;font-size:12px;font-weight:500;letter-spacing:.5px;color:var(--muted);padding:0 10px;line-height:1.3}
.circular-card p{font-family:"Cormorant Garamond",serif;font-size:26px;font-weight:700;color:var(--gold);margin:0}
table{width:100%;border-collapse:collapse;margin-bottom:36px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.2)}
table th{background:rgba(200,164,60,.1);color:var(--gold);font-weight:600;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;padding:14px 16px;text-align:left;border-bottom:1px solid var(--border)}
table td{padding:13px 16px;font-size:13px;color:rgba(255,255,255,.8);border-bottom:1px solid rgba(255,255,255,.04)}
table tr:last-child td{border-bottom:none}
table tr:hover td{background:rgba(200,164,60,.04)}
.action-btn{display:inline-block;padding:7px 14px;border-radius:6px;text-decoration:none;color:var(--white);background:rgba(200,164,60,.2);border:1px solid var(--gb);transition:all .25s;margin-right:4px;font-size:12px;font-weight:600;cursor:pointer;font-family:"Outfit",sans-serif}
.action-btn:hover{background:rgba(200,164,60,.35);transform:translateY(-2px)}
.chart-container{margin-bottom:36px;background:rgba(255,255,255,.03);border:1px solid var(--border);padding:28px;border-radius:12px}
.alert{padding:14px 20px;border-radius:8px;margin-bottom:20px;font-size:13px;font-weight:500}
.alert.success{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.alert.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.link-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.5px}
.link-badge.linked{background:rgba(22,163,74,.1);color:#86efac;border:1px solid rgba(22,163,74,.3)}
.link-badge.unlinked{background:rgba(200,164,60,.1);color:var(--gold);border:1px solid var(--gb)}
.link-form{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-top:6px}
.link-form select{padding:6px 10px;border-radius:6px;border:1px solid var(--border);font-size:12px;background:rgba(255,255,255,.06);color:var(--white);min-width:180px;font-family:"Outfit",sans-serif}
.link-form select option{background:var(--ink)}
.link-form button{padding:7px 14px;border-radius:6px;border:none;cursor:pointer;font-size:12px;font-weight:700;color:var(--white);font-family:"Outfit",sans-serif}
.btn-link{background:rgba(14,90,200,.4)}
.btn-link:hover{background:rgba(14,90,200,.6)}
.btn-unlink{background:rgba(239,68,68,.2);color:#fca5a5}
.btn-unlink:hover{background:rgba(239,68,68,.35)}
.unlinked-banner{background:rgba(200,164,60,.08);border:1px solid var(--gb);border-radius:10px;padding:14px 20px;margin-bottom:24px;display:flex;align-items:center;gap:12px;font-size:13px;color:var(--gold)}
.unlinked-banner strong{color:var(--gold-l)}
input,select,textarea{background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--white);border-radius:6px;padding:10px 13px;font-family:"Outfit",sans-serif;font-size:13px;width:100%;outline:none;transition:border-color .25s;margin-bottom:12px}
input:focus,select:focus,textarea:focus{border-color:var(--gb)}
input::placeholder,textarea::placeholder{color:var(--muted)}
select option{background:var(--ink)}
label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
@media(max-width:900px){
  :root{--sw:0px}
  .sidebar{transform:translateX(-260px);width:260px}
  .main-content,.header{margin-left:0}
  table{font-size:12px}
  table th,table td{padding:10px}
  .main-content{padding:20px 16px}
}
</style>
</head>
<body>
 
<div class="sidebar">
    <h2>ADMIN PANEL</h2>
    <a href="admin_dashboard.php?page=dashboard" <?php echo ($page==='dashboard')?'class="active"':''; ?>>HOME</a>
    <a href="admin_dashboard.php?page=users" <?php echo ($page==='users')?'class="active"':''; ?>>Manage Users</a>
    <a href="admin_dashboard.php?page=properties" <?php echo ($page==='properties')?'class="active"':''; ?>>Manage Properties</a>
    <a href="admin_dashboard.php?page=staff_roles" <?php echo ($page==='staff_roles')?'class="active"':''; ?>>Staff Roles & Payroll</a>
    <a href="admin_dashboard.php?page=staff_tasks" <?php echo ($page==='staff_tasks')?'class="active"':''; ?>>Staff Tasks / Schedule</a>
    <a href="admin_dashboard.php?page=inspections" <?php echo ($page==='inspections')?'class="active"':''; ?>>Property Inspections</a>
    <a href="admin_dashboard.php?page=maintenance" <?php echo ($page==='maintenance')?'class="active"':''; ?>>Maintenance Requests</a>
    <a href="admin_dashboard.php?page=tenants" <?php echo ($page==='tenants')?'class="active"':''; ?>>
        Manage Tenants
        <?php if($unlinked_count > 0): ?>
            <span style="background:#ef4444;color:white;border-radius:10px;padding:2px 8px;font-size:11px;margin-left:6px"><?= $unlinked_count ?></span>
        <?php endif; ?>
    </a>
    <a href="admin_dashboard.php?page=tenant_documents" <?php echo ($page==='tenant_documents')?'class="active"':''; ?>>Tenant Documents</a>
    <a href="admin_dashboard.php?page=tenant_payments" <?php echo ($page==='tenant_payments')?'class="active"':''; ?>>Tenant Payments</a>
    <a href="admin_dashboard.php?page=complaints" <?php echo ($page==='complaints')?'class="active"':''; ?>>Complaints & Feedback</a>
    <a href="admin_dashboard.php?page=guests" <?php echo ($page==='guests')?'class="active"':''; ?>>Guest / Visitor Approvals</a>
    <a href="admin_dashboard.php?page=brokers" <?php echo ($page==='brokers')?'class="active"':''; ?>>Brokers / Agents</a>
    <a href="admin_dashboard.php?page=propertyowners" <?php echo ($page==='propertyowners')?'class="active"':''; ?>>Property Owners</a>
    <a href="admin_dashboard.php?page=jobs" <?php echo ($page==='jobs')?'class="active"':''; ?>>Employment Applications</a>
    <a href="admin_dashboard.php?page=employee_performance" <?php echo ($page==='employee_performance')?'class="active"':''; ?>>Employee Performance</a>
    <a href="admin_dashboard.php?page=payments" <?php echo ($page==='payments')?'class="active"':''; ?>>Payments / Rent Tracking</a>
    <a href="admin_dashboard.php?page=revenue_reports" <?php echo ($page==='revenue_reports')?'class="active"':''; ?>>Revenue Reports</a>
    <a href="admin_dashboard.php?page=settings" <?php echo ($page==='settings')?'class="active"':''; ?>>System Settings</a>
    <a href="admin_dashboard.php?page=notifications" <?php echo ($page==='notifications')?'class="active"':''; ?>>Notifications</a>
    <a href="admin_dashboard.php?page=backups" <?php echo ($page==='backups')?'class="active"':''; ?>>Backup / Export Data</a>
</div>
 
<div class="header">
    <h1>Welcome, <?php echo htmlspecialchars($user['fullname']); ?></h1>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>
 
<div class="main-content">
 
<?php
// ── Flash messages ──
if (isset($_SESSION['admin_success'])):
    echo '<div class="alert success">✅ '.htmlspecialchars($_SESSION['admin_success']).'</div>';
    unset($_SESSION['admin_success']);
endif;
if (isset($_SESSION['admin_error'])):
    echo '<div class="alert error">⚠️ '.htmlspecialchars($_SESSION['admin_error']).'</div>';
    unset($_SESSION['admin_error']);
endif;
?>
 
<?php if($page === 'dashboard'): ?>
<section id="dashboard">
    <h2 style="text-align:center;margin-bottom:30px;">OVERVIEW</h2>
    <div class="overview-cards">
        <div class="circular-card"><h3>Total Properties</h3><p><?= $total_properties ?></p></div>
        <div class="circular-card"><h3>Total Tenants</h3><p><?= $total_tenants ?></p></div>
        <div class="circular-card"><h3>Total Staff</h3><p><?= $total_staff ?></p></div>
        <div class="circular-card"><h3>Total Brokers</h3><p><?= $total_brokers ?></p></div>
        <div class="circular-card"><h3>Property Owners</h3><p><?= $total_owners ?></p></div>
        <div class="circular-card"><h3>Total Guests</h3><p><?= $total_guests ?></p></div>
        <div class="circular-card"><h3>Pending Complaints</h3><p><?= $total_complaints ?></p></div>
        <div class="circular-card"><h3>Unread Alerts</h3><p><?= $total_notifications ?></p></div>
        <div class="circular-card"><h3>Pending Payments</h3><p><?= $pending_payments ?></p></div>
        <div class="circular-card"><h3>Pending Applications</h3><p><?= $pending_applications ?></p></div>
        <div class="circular-card"><h3>Pending Maintenance</h3><p><?= $pending_requests ?></p></div>
        <div class="circular-card"><h3>Revenue Collected</h3><p>UGX <?= number_format($revenue ?? 0) ?></p></div>
    </div>
</section>
<hr style="border:none;border-top:1px solid var(--border);margin:20px 0">
 
<?php elseif($page === 'users'): ?>
<section id="users">
    <h2 style="text-align:center;margin-bottom:30px;color:var(--gold);">USER MANAGEMENT</h2>
    <div style="text-align:center;margin-bottom:20px;">
        <a href="add_user.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4);">+++ ADD NEW USER</a>
    </div>
    <table>
        <tr>
            <th>Full Name</th>
            <th>Role</th>
            <th>Email</th>
            <th>Tenant Link</th>
            <th>Actions</th>
        </tr>
        <?php
        $users_q = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
        while ($u = mysqli_fetch_assoc($users_q)):
            // Check if this user is linked to a tenant record
            $linked = null;
            if (strtolower($u['role']) === 'tenant') {
                $lq = mysqli_query($conn,
                    "SELECT id, fullname FROM tenants WHERE user_id = '".(int)$u['id']."' LIMIT 1");
                $linked = mysqli_fetch_assoc($lq);
            }
        ?>
        <tr>
            <td><?= htmlspecialchars($u['fullname']) ?></td>
            <td><?= htmlspecialchars($u['role']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td>
                <?php if (strtolower($u['role']) === 'tenant'): ?>
                    <?php if ($linked): ?>
                        <span class="link-badge linked">✓ Linked — <?= htmlspecialchars($linked['fullname']) ?></span>
                    <?php else: ?>
                        <span class="link-badge unlinked">⏳ Not linked</span>
                        <br><small style="color:#888;font-size:11px">Go to Manage Tenants → Link Account</small>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color:#aaa;font-size:12px">—</span>
                <?php endif; ?>
            </td>
            <td>
                <a href="edit_user.php?id=<?= $u['id'] ?>" class="action-btn">Edit</a>
                <a href="delete_user.php?id=<?= $u['id'] ?>" class="action-btn"
                   onclick="return confirm('Are you sure you want to delete this user?')"
                   style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</section>
 
<?php elseif($page === 'tenants'): ?>
<section id="tenants">
    <h2 style="text-align:center;margin-bottom:20px;color:var(--gold);">MANAGE TENANTS</h2>
 
    <?php if($unlinked_count > 0): ?>
    <div class="unlinked-banner">
        ⚠️ <div><strong><?= $unlinked_count ?> tenant<?= $unlinked_count>1?'s':'' ?> not linked to a user account.</strong>
        These tenants will see the "Account Pending" screen when they log in. Use the <em>Link Account</em> dropdown below to connect them.</div>
    </div>
    <?php endif; ?>
 
    <div style="text-align:center;margin-bottom:20px;">
        <a href="add_tenant.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4);">+++ ADD NEW TENANT</a>
    </div>
 
    <?php
    // Fetch all tenants with property name
    // Also fetch all tenant-role users who are NOT yet linked, for the dropdown
    $tenant_users = mysqli_query($conn,
        "SELECT u.id, u.fullname, u.email
         FROM users u
         WHERE u.role = 'tenant'
           AND (
               NOT EXISTS (SELECT 1 FROM tenants t WHERE t.user_id = u.id)
               OR u.id NOT IN (SELECT COALESCE(user_id,0) FROM tenants WHERE user_id IS NOT NULL)
           )
         ORDER BY u.fullname ASC");
    $available_users = [];
    while ($tu = mysqli_fetch_assoc($tenant_users)) $available_users[] = $tu;
 
    $tenants_q = mysqli_query($conn,
        "SELECT t.*, p.property_name, u.fullname AS linked_username, u.email AS linked_email
         FROM tenants t
         LEFT JOIN properties p ON t.property_id = p.id
         LEFT JOIN users u ON t.user_id = u.id
         ORDER BY t.created_at DESC");
    ?>
 
    <table>
        <tr>
            <th>Full Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Property</th>
            <th>Account Status</th>
            <th>Link User Account</th>
            <th>Actions</th>
        </tr>
        <?php while($t = mysqli_fetch_assoc($tenants_q)): ?>
        <tr>
            <td><?= htmlspecialchars($t['fullname']) ?></td>
            <td><?= htmlspecialchars($t['phone'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($t['email'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($t['property_name'] ?? 'Unassigned') ?></td>
 
            <!-- Account link status -->
            <td>
                <?php if (!empty($t['user_id']) && $t['user_id'] > 0): ?>
                    <span class="link-badge linked">✓ Linked</span>
                    <div style="font-size:11px;color:#555;margin-top:3px">
                        <?= htmlspecialchars($t['linked_username'] ?? '') ?>
                    </div>
                <?php else: ?>
                    <span class="link-badge unlinked">⏳ Pending</span>
                <?php endif; ?>
            </td>
 
            <!-- Link / Unlink form -->
            <td>
                <?php if (!empty($t['user_id']) && $t['user_id'] > 0): ?>
                    <!-- Already linked — show unlink option -->
                    <form method="POST" action="admin_dashboard.php?page=tenants"
                          onsubmit="return confirm('Remove account link? This tenant will see the pending screen.')">
                        <input type="hidden" name="action" value="unlink_account">
                        <input type="hidden" name="tenant_id" value="<?= $t['id'] ?>">
                        <button type="submit" class="btn-unlink action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;font-size:12px;padding:6px 12px;">
                            ✕ Unlink
                        </button>
                    </form>
                <?php else: ?>
                    <!-- Not linked — show dropdown of available users -->
                    <?php if (!empty($available_users)): ?>
                    <form method="POST" action="admin_dashboard.php?page=tenants" class="link-form">
                        <input type="hidden" name="action" value="link_account">
                        <input type="hidden" name="tenant_id" value="<?= $t['id'] ?>">
                        <select name="link_user_id" required>
                            <option value="">— Select user account —</option>
                            <?php foreach($available_users as $au): ?>
                            <option value="<?= $au['id'] ?>">
                                <?= htmlspecialchars($au['fullname']) ?> (<?= htmlspecialchars($au['email']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn-link action-btn" style="background:rgba(14,90,200,.3);border:1px solid rgba(14,90,200,.4);font-size:12px;padding:6px 12px;">
                            ✓ Link
                        </button>
                    </form>
                    <?php else: ?>
                        <span style="font-size:12px;color:#888">No unlinked tenant accounts available.<br>
                        <a href="add_user.php" style="color:#0ea5e9">+ Create account</a></span>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
 
            <td>
                <a href="edit_records.php?type=tenant&id=<?= $t['id'] ?>" class="action-btn">Edit</a>
                <a href="delete_record.php?table=tenants&id=<?= $t['id'] ?>"
                   class="action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;"
                   onclick="return confirm('Are you sure you want to delete this tenant?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
 
    <!-- QUICK HOW-TO -->
    <div style="background:rgba(255,255,255,.03);border:1px solid #e2e8f0;border-radius:10px;padding:20px;max-width:600px;margin-top:10px">
        <h4 style="color:var(--white);margin-bottom:10px;font-size:15px">How tenant account linking works</h4>
        <ol style="font-size:13px;color:#555;line-height:1.8;padding-left:18px">
            <li>A person registers on the site → their account appears in <strong>Manage Users</strong> with role <em>tenant</em></li>
            <li>You add their details to <strong>Manage Tenants</strong> (or they already exist)</li>
            <li>Use the <strong>Link Account</strong> dropdown to connect their user account to their tenant record</li>
            <li>They log in → pass all checks → see their personalised dashboard ✅</li>
        </ol>
    </div>
</section>
 
<?php elseif($page === 'properties'): ?>
<section id="properties">
    <h2>Manage Properties</h2>
    <table>
        <tr><th>Property Name</th><th>Type</th><th>Address</th><th>Units</th><th>Rent (UGX)</th><th>Owner / Broker</th><th>Created At</th><th>Actions</th></tr>
        <?php
        $properties = mysqli_query($conn,"SELECT p.*,u.fullname FROM properties p LEFT JOIN users u ON p.owner_id=u.id ORDER BY p.created_at DESC");
        while($prop=mysqli_fetch_assoc($properties)):?>
        <tr>
            <td><?=htmlspecialchars($prop['property_name'])?></td>
            <td><?=htmlspecialchars($prop['property_type']??'N/A')?></td>
            <td><?=htmlspecialchars($prop['address']??'N/A')?></td>
            <td><?=(int)$prop['units']?></td>
            <td><?=number_format($prop['rent_amount']??0)?></td>
            <td><?=htmlspecialchars($prop['fullname']??'Unassigned')?></td>
            <td><?=htmlspecialchars($prop['created_at'])?></td>
            <td>
                <a href="edit_records.php?type=property&id=<?=$prop['id']?>" class="action-btn">Edit</a>
                <a href="delete_record.php?table=properties&id=<?=$prop['id']?>" class="action-btn" onclick="return confirm('Delete this property?')" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;">Delete</a>
            </td>
        </tr>
        <?php endwhile;?>
    </table>
</section>
 
<?php elseif($page === 'staff_roles'): ?>
<section id="staff_roles">
    <h2>Staff Roles & Payroll</h2>
    <table>
        <tr><th>Full Name</th><th>Role</th><th>Salary (UGX)</th><th>Email</th><th>Created At</th><th>Actions</th></tr>
        <?php $staff=mysqli_query($conn,"SELECT * FROM users WHERE role='staff' ORDER BY created_at DESC");
        while($s=mysqli_fetch_assoc($staff)):?>
        <tr>
            <td><?=htmlspecialchars($s['fullname']??'N/A')?></td>
            <td><?=htmlspecialchars($s['role']??'Staff')?></td>
            <td><?=number_format($s['salary']??0)?></td>
            <td><?=htmlspecialchars($s['email']??'N/A')?></td>
            <td><?=htmlspecialchars($s['created_at']??'N/A')?></td>
            <td>
                <a href="edit_records.php?type=staff&id=<?=$s['id']?>" class="action-btn">Edit</a>
                <a href="delete_record.php?table=users&id=<?=$s['id']?>" class="action-btn" onclick="return confirm('Delete this staff member?')" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;">Delete</a>
            </td>
        </tr>
        <?php endwhile;?>
    </table>
    <div style="text-align:center;margin-top:20px"><a href="add_staff.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4);">+++ ADD NEW STAFF</a></div>
</section>
 
<?php elseif($page === 'inspections'): ?>
<section id="inspections">
    <h2 style="text-align:center;">Property Inspections</h2>
    <div style="text-align:center;margin-bottom:20px;"><a href="add_inspection.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4);">+ Schedule New Inspection</a></div>
    <table>
        <tr><th>Property</th><th>Tenant</th><th>Inspector</th><th>Date</th><th>Situation</th><th>Status</th><th>Notified</th><th>Actions</th></tr>
        <?php $inspections=mysqli_query($conn,"SELECT i.*,p.property_name,t.fullname AS tenant_name FROM inspections i LEFT JOIN properties p ON i.property_id=p.id LEFT JOIN tenants t ON i.tenant_id=t.id ORDER BY i.inspection_date DESC");
        while($i=mysqli_fetch_assoc($inspections)):?>
        <tr>
            <td><?=htmlspecialchars($i['property_name']??'N/A')?></td>
            <td><?=htmlspecialchars($i['tenant_name']??'None')?></td>
            <td><?=htmlspecialchars($i['inspector_name'])?></td>
            <td><?=htmlspecialchars($i['inspection_date'])?></td>
            <td><?=htmlspecialchars($i['situation'])?></td>
            <td><?=htmlspecialchars($i['status'])?></td>
            <td><?=($i['notified']==1)?"Yes":"No"?></td>
            <td>
                <a href="edit_records.php?type=inspection&id=<?=$i['id']?>" class="action-btn">Edit</a>
                <a href="delete_record.php?table=inspections&id=<?=$i['id']?>" class="action-btn" onclick="return confirm('Delete?')" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;">Delete</a>
                <?php if($i['status']!="Completed"):?><a href="mark_inspection_complete.php?id=<?=$i['id']?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);">Complete</a><?php endif;?>
            </td>
        </tr>
        <?php endwhile;?>
    </table>
</section>
 
<?php elseif($page === 'staff_tasks'): ?>
<section id="staff_tasks">
    <h2 style="text-align:center;">Staff Tasks & Schedule</h2>
    <div style="text-align:center;margin-bottom:20px;"><a href="add_task.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4);">+ Assign New Task</a></div>
    <table>
        <tr><th>Task Title</th><th>Staff Assigned</th><th>Due Date</th><th>Priority</th><th>Status</th><th>Assigned By</th><th>Actions</th></tr>
        <?php $tasks=mysqli_query($conn,"SELECT t.*,u.fullname AS staff_name FROM tasks t LEFT JOIN users u ON t.assigned_to=u.id ORDER BY t.due_date ASC");
        while($task=mysqli_fetch_assoc($tasks)):?>
        <tr>
            <td><?=htmlspecialchars($task['title'])?></td>
            <td><?=htmlspecialchars($task['staff_name']??'N/A')?></td>
            <td><?=htmlspecialchars($task['due_date']??'-')?></td>
            <td><?=htmlspecialchars($task['priority'])?></td>
            <td><?=htmlspecialchars($task['status'])?></td>
            <td><?=htmlspecialchars($task['assigned_by'])?></td>
            <td>
                <a href="edit_records.php?type=task&id=<?=$task['id']?>" class="action-btn">Edit</a>
                <a href="delete_record.php?table=tasks&id=<?=$task['id']?>" class="action-btn" onclick="return confirm('Delete?')" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;">Delete</a>
                <?php if($task['status']!='Completed'):?><a href="mark_task_complete.php?id=<?=$task['id']?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);">Complete</a><?php endif;?>
            </td>
        </tr>
        <?php endwhile;?>
    </table>
</section>
 
<?php elseif($page === 'tenant_documents'): ?>
<section id="tenant_documents">
    <h2 style="text-align:center;margin-bottom:30px;color:var(--gold);">TENANT DOCUMENTS</h2>
    <div style="text-align:center;margin-bottom:20px;"><a href="add_document.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4);">+++ ADD NEW DOCUMENT</a></div>
    <table>
        <tr><th>Tenant</th><th>Document Name</th><th>File</th><th>Uploaded At</th><th>Actions</th></tr>
        <?php $docs=mysqli_query($conn,"SELECT d.*,t.fullname AS tenant_name FROM tenant_documents d LEFT JOIN tenants t ON d.tenant_id=t.id ORDER BY d.uploaded_at DESC");
        while($doc=mysqli_fetch_assoc($docs)):?>
        <tr>
            <td><?=htmlspecialchars($doc['tenant_name']??'N/A')?></td>
            <td><?=htmlspecialchars($doc['document_name']??'Unnamed')?></td>
            <td><?php if(!empty($doc['file_path'])):?><a href="<?=htmlspecialchars($doc['file_path'])?>" target="_blank">View</a><?php else:?>N/A<?php endif;?></td>
            <td><?=htmlspecialchars($doc['uploaded_at']??'-')?></td>
            <td>
                <a href="edit_records.php?type=document&id=<?=$doc['id']?>" class="action-btn">Edit</a>
                <a href="delete_record.php?table=tenant_documents&id=<?=$doc['id']?>" class="action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;" onclick="return confirm('Delete?')">Delete</a>
            </td>
        </tr>
        <?php endwhile;?>
    </table>
</section>
 
<?php elseif($page === 'jobs'): ?>
<section id="jobs">
    <h2>Employment Applications</h2>
 
    <?php
    // ── Stats summary ──
    $total_apps    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM job_applications"))['c'];
    $pending_apps  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM job_applications WHERE status='pending'"))['c'];
    $approved_apps = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM job_applications WHERE status='approved'"))['c'];
    $rejected_apps = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM job_applications WHERE status='rejected'"))['c'];
    ?>
 
    <!-- Summary Cards -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:28px">
        <div style="background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:10px;padding:18px;text-align:center">
            <div style="font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:700;color:var(--white)"><?= $total_apps ?></div>
            <div style="font-size:11px;color:var(--muted);letter-spacing:1px;margin-top:4px">Total Applications</div>
        </div>
        <div style="background:rgba(200,164,60,.06);border:1px solid var(--gb);border-radius:10px;padding:18px;text-align:center">
            <div style="font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:700;color:var(--gold)"><?= $pending_apps ?></div>
            <div style="font-size:11px;color:var(--muted);letter-spacing:1px;margin-top:4px">Pending Review</div>
        </div>
        <div style="background:rgba(22,163,74,.06);border:1px solid rgba(22,163,74,.25);border-radius:10px;padding:18px;text-align:center">
            <div style="font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:700;color:#86efac"><?= $approved_apps ?></div>
            <div style="font-size:11px;color:var(--muted);letter-spacing:1px;margin-top:4px">Approved</div>
        </div>
        <div style="background:rgba(239,68,68,.06);border:1px solid rgba(239,68,68,.25);border-radius:10px;padding:18px;text-align:center">
            <div style="font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:700;color:#fca5a5"><?= $rejected_apps ?></div>
            <div style="font-size:11px;color:var(--muted);letter-spacing:1px;margin-top:4px">Rejected</div>
        </div>
    </div>
 
    <!-- Filter tabs -->
    <?php $filter = $_GET['filter'] ?? 'all'; ?>
    <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
        <?php
        $filters = ['all'=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'];
        foreach($filters as $k=>$label):
            $active = $filter===$k;
        ?>
        <a href="admin_dashboard.php?page=jobs&filter=<?=$k?>"
           style="padding:7px 16px;border-radius:6px;font-size:12px;font-weight:600;letter-spacing:.5px;text-decoration:none;transition:all .2s;
                  background:<?=$active?'rgba(200,164,60,.2)':'rgba(255,255,255,.04)'?>;
                  border:1px solid <?=$active?'var(--gb)':'var(--border)'?>;
                  color:<?=$active?'var(--gold)':'var(--muted)'?>">
            <?=$label?>
            <?php if($k!=='all'): ?>
            <?php
            $kcount = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT COUNT(*) as c FROM job_applications WHERE status='" . mysqli_real_escape_string($conn,$k) . "'"));
            ?>
            <span style="background:rgba(255,255,255,.1);border-radius:10px;padding:1px 6px;font-size:10px;margin-left:4px">
                <?= $kcount['c'] ?>
            </span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
 
    <!-- Applications Table -->
    <?php
    // Your table: job_applications(id, full_name, email, phone, position, resume, status, created_at)
    $where = $filter !== 'all' ? "WHERE status='".mysqli_real_escape_string($conn,$filter)."'" : '';
    $apps  = mysqli_query($conn,
        "SELECT * FROM job_applications $where ORDER BY created_at DESC");
    $app_count = $apps ? mysqli_num_rows($apps) : 0;
    ?>
 
    <?php if($app_count == 0): ?>
    <div style="text-align:center;padding:48px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;color:var(--muted)">
        No <?= $filter !== 'all' ? $filter : '' ?> applications found.
    </div>
    <?php else: ?>
    <table>
        <tr>
            <th>#</th>
            <th>Applicant</th>
            <th>Position Applied</th>
            <th>Phone</th>
            <th>Applied</th>
            <th>Resume</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php $i=1; while($app=mysqli_fetch_assoc($apps)):
            $st  = strtolower($app['status'] ?? 'pending');
            $sc  = match($st) { 'approved'=>'#86efac', 'rejected'=>'#fca5a5', default=>'var(--gold)' };
            $sbg = match($st) { 'approved'=>'rgba(22,163,74,.1)', 'rejected'=>'rgba(239,68,68,.1)', default=>'rgba(200,164,60,.1)' };
            $sbd = match($st) { 'approved'=>'rgba(22,163,74,.3)', 'rejected'=>'rgba(239,68,68,.3)', default=>'var(--gb)' };
        ?>
        <tr>
            <td style="color:var(--muted);font-size:12px"><?= $i++ ?></td>
            <td>
                <div style="font-weight:600;color:var(--white)"><?=htmlspecialchars($app['full_name'])?></div>
                <div style="font-size:11px;color:var(--muted)"><?=htmlspecialchars($app['email']??'—')?></div>
            </td>
            <td style="font-weight:500;font-size:13px"><?=htmlspecialchars($app['position']??'N/A')?></td>
            <td style="font-size:12px;color:var(--muted)"><?=htmlspecialchars($app['phone']??'—')?></td>
            <td style="font-size:12px;color:var(--muted);white-space:nowrap">
                <?= $app['created_at'] ? date('d M Y', strtotime($app['created_at'])) : 'N/A' ?>
            </td>
            <td>
                <?php if(!empty($app['resume'])): ?>
                    <a href="uploads/<?=htmlspecialchars($app['resume'])?>" target="_blank"
                       style="font-size:12px;color:var(--gold);text-decoration:none">📄 View</a>
                <?php else: ?>
                    <span style="font-size:11px;color:var(--muted)">None</span>
                <?php endif; ?>
            </td>
            <td>
                <span style="padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;background:<?=$sbg?>;color:<?=$sc?>;border:1px solid <?=$sbd?>">
                    <?= ucfirst($st) ?>
                </span>
            </td>
            <td style="white-space:nowrap">
                <!-- View full application -->
                <a href="view_application.php?id=<?=$app['id']?>"
                   class="action-btn" style="background:rgba(14,90,200,.3);border-color:rgba(14,90,200,.4)">
                   👁 View
                </a>
 
                <?php if($st === 'pending'): ?>
                <!-- Approve -->
                <a href="approve_application.php?id=<?=$app['id']?>"
                   class="action-btn"
                   style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac"
                   onclick="return confirm('Approve <?=htmlspecialchars(addslashes($app['full_name']))?> and send them a congratulations email?')">
                   ✓ Approve
                </a>
                <!-- Reject -->
                <a href="reject_application.php?id=<?=$app['id']?>"
                   class="action-btn"
                   style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5"
                   onclick="return confirm('Reject this application and send a decline email to <?=htmlspecialchars(addslashes($app['email']))?> ?')">
                   ✕ Reject
                </a>
                <?php elseif($st === 'approved'): ?>
                <span style="font-size:11px;color:#86efac">✓ Hired</span>
                <?php elseif($st === 'rejected'): ?>
                <span style="font-size:11px;color:#fca5a5">✕ Declined</span>
                <?php endif; ?>
 
                <!-- Delete -->
                <a href="delete_record.php?table=job_applications&id=<?=$app['id']?>"
                   class="action-btn"
                   style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.25);color:#fca5a5"
                   onclick="return confirm('Permanently delete this application?')">
                   🗑
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php endif; ?>
 
    <!-- HOW TO MANAGE note -->
    <div style="background:rgba(200,164,60,.06);border:1px solid var(--gb);border-radius:10px;padding:18px 22px;margin-top:10px;font-size:13px;color:var(--muted);line-height:1.8">
        <strong style="color:var(--gold)">How to manage applications:</strong><br>
        1. Click <strong style="color:var(--white)">👁 View</strong> to read the full application, experience details and resume.<br>
        2. Click <strong style="color:#86efac">✓ Approve</strong> to hire — a congratulations email is sent to the applicant automatically.<br>
        3. Click <strong style="color:#fca5a5">✕ Reject</strong> to decline — a polite rejection email is sent automatically.<br>
        4. Use the <strong style="color:var(--white)">filter tabs</strong> above to sort by Pending / Approved / Rejected.
    </div>
</section>
 
<?php elseif($page === 'maintenance'): ?>
<section id="maintenance">
    <h2>Maintenance Requests</h2>
    <table>
        <tr><th>Property</th><th>Issue</th><th>Priority</th><th>Assigned Staff</th><th>Status</th><th>Actions</th></tr>
        <?php $requests=mysqli_query($conn,"SELECT m.*,u.fullname as staff_name,p.property_name FROM maintenance_requests m LEFT JOIN users u ON m.assigned_staff=u.id LEFT JOIN properties p ON m.property_id=p.id ORDER BY m.created_at DESC");
        while($r=mysqli_fetch_assoc($requests)):?>
        <tr>
            <td><?=htmlspecialchars($r['property_name']??'N/A')?></td>
            <td><?=htmlspecialchars($r['issue'])?></td>
            <td><?=htmlspecialchars($r['priority']??'medium')?></td>
            <td><?=htmlspecialchars($r['staff_name']??'Unassigned')?></td>
            <td><?=htmlspecialchars($r['status'])?></td>
            <td>
                <a href="assign_staff.php?id=<?=$r['id']?>" class="action-btn">Assign</a>
                <a href="mark_complete.php?id=<?=$r['id']?>" class="action-btn">Complete</a>
            </td>
        </tr>
        <?php endwhile;?>
    </table>
</section>
 
<?php elseif($page === 'tenant_payments'): ?>
<section id="tenant_payments">
    <h2 style="text-align:center;margin-bottom:30px;color:var(--gold);">TENANT PAYMENTS</h2>
    <div style="text-align:center;margin-bottom:20px;"><a href="add_payment.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4);">+++ RECORD NEW PAYMENT</a></div>
    <table>
        <tr><th>Tenant</th><th>Property</th><th>Amount (UGX)</th><th>Date</th><th>Status</th><th>Actions</th></tr>
        <?php $payments=mysqli_query($conn,"SELECT pay.*,t.fullname as tenant_name,p.property_name FROM payments pay LEFT JOIN tenants t ON pay.tenant_id=t.id LEFT JOIN properties p ON pay.property_id=p.id ORDER BY pay.date DESC");
        while($pay=mysqli_fetch_assoc($payments)):?>
        <tr>
            <td><?=htmlspecialchars($pay['tenant_name']??'N/A')?></td>
            <td><?=htmlspecialchars($pay['property_name']??'N/A')?></td>
            <td><?=number_format($pay['amount'])?></td>
            <td><?=htmlspecialchars($pay['date'])?></td>
            <td><?=htmlspecialchars($pay['status']??'Pending')?></td>
            <td>
                <a href="edit_records.php?type=payment&id=<?=$pay['id']?>" class="action-btn">Edit</a>
                <a href="delete_record.php?table=payments&id=<?=$pay['id']?>" class="action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;" onclick="return confirm('Delete?')">Delete</a>
            </td>
        </tr>
        <?php endwhile;?>
    </table>
</section>
 
<?php elseif($page === 'complaints'): ?>
<section id="complaints">
    <h2 style="text-align:center;margin-bottom:30px;color:var(--gold);">COMPLAINTS & FEEDBACK</h2>
    <table>
        <tr><th>Tenant</th><th>Category</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr>
        <?php $complaints=mysqli_query($conn,"SELECT c.*,t.fullname as tenant_name FROM complaints c LEFT JOIN tenants t ON c.tenant_id=t.id ORDER BY c.created_at DESC");
        while($c=mysqli_fetch_assoc($complaints)):?>
        <tr>
            <td><?=htmlspecialchars($c['tenant_name']??'N/A')?></td>
            <td><?=htmlspecialchars($c['category']??'N/A')?></td>
            <td><?=htmlspecialchars(substr($c['message']??'',0,50))?>...</td>
            <td><?=htmlspecialchars($c['status']??'pending')?></td>
            <td><?=htmlspecialchars($c['created_at']??'N/A')?></td>
            <td>
                <a href="view_complaint.php?id=<?=$c['id']?>" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4);">View</a>
                <a href="resolve_complaint.php?id=<?=$c['id']?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);" onclick="return confirm('Mark resolved?')">Resolve</a>
                <a href="delete_record.php?table=complaints&id=<?=$c['id']?>" class="action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;" onclick="return confirm('Delete?')">Delete</a>
            </td>
        </tr>
        <?php endwhile;?>
    </table>
</section>
 
<?php elseif($page === 'guests'): ?>
<section id="guests">
    <h2 style="text-align:center;margin-bottom:30px;color:var(--gold);">GUEST / VISITOR APPROVALS</h2>
    <div style="text-align:center;margin-bottom:20px;"><a href="add_guest.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4);">+++ ADD NEW GUEST</a></div>
    <?php $guests=mysqli_query($conn,"SELECT g.*,t.fullname AS tenant_name,p.property_name FROM guests g LEFT JOIN tenants t ON g.tenant_id=t.id LEFT JOIN properties p ON g.property_id=p.id ORDER BY g.created_at DESC");?>
    <table>
        <tr><th>Guest Name</th><th>Email</th><th>Phone</th><th>Tenant</th><th>Property</th><th>Check-in</th><th>Check-out</th><th>Status</th><th>Actions</th></tr>
        <?php while($g=mysqli_fetch_assoc($guests)):?>
        <tr>
            <td><?=htmlspecialchars($g['fullname'])?></td>
            <td><?=htmlspecialchars($g['email']??'N/A')?></td>
            <td><?=htmlspecialchars($g['phone']??'N/A')?></td>
            <td><?=htmlspecialchars($g['tenant_name']??'N/A')?></td>
            <td><?=htmlspecialchars($g['property_name']??'N/A')?></td>
            <td><?=htmlspecialchars($g['check_in']??'-')?></td>
            <td><?=htmlspecialchars($g['check_out']??'-')?></td>
            <td><?=htmlspecialchars($g['status']??'Pending')?></td>
            <td>
                <a href="approve_guest.php?id=<?=$g['id']?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);">Approve</a>
                <a href="reject_guest.php?id=<?=$g['id']?>" class="action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;">Reject</a>
                <a href="delete_record.php?table=guests&id=<?=$g['id']?>" class="action-btn" style="background:rgba(239,68,68,.25);border:1px solid rgba(239,68,68,.4);color:#fca5a5;" onclick="return confirm('Delete?')">Delete</a>
            </td>
        </tr>
        <?php endwhile;?>
    </table>
</section>
 
<?php elseif($page === 'brokers'): ?>
<section id="brokers">
    <h2 style="text-align:center;margin-bottom:30px;color:var(--gold);">MANAGE BROKERS / AGENTS</h2>
    <div style="text-align:center;margin-bottom:20px;"><a href="add_user.php?role=broker" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4);">+++ ADD NEW BROKER</a></div>
    <table>
        <tr><th>Full Name</th><th>Email</th><th>Phone</th><th>Assigned Properties</th><th>Commission (UGX)</th><th>Actions</th></tr>
        <?php $brokers=mysqli_query($conn,"SELECT * FROM users WHERE role='broker' ORDER BY created_at DESC");
        while($b=mysqli_fetch_assoc($brokers)):
            $bid=$b['id'];
            $pc=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM properties WHERE broker_id='$bid'"))['count'];
            $ct=mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount*commission_percentage/100) AS total FROM payments p JOIN properties pr ON p.property_id=pr.id WHERE pr.broker_id='$bid'"))['total']??0;
        ?>
        <tr>
            <td><?=htmlspecialchars($b['fullname'])?></td>
            <td><?=htmlspecialchars($b['email']??'N/A')?></td>
            <td><?=htmlspecialchars($b['phone']??'N/A')?></td>
            <td><?=$pc?></td>
            <td><?=number_format($ct)?></td>
            <td>
                <a href="edit_user.php?id=<?=$bid?>" class="action-btn">Edit</a>
                <a href="delete_user.php?id=<?=$bid?>" class="action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;" onclick="return confirm('Delete broker?')">Delete</a>
            </td>
        </tr>
        <?php endwhile;?>
    </table>
</section>
 
<?php elseif($page === 'propertyowners'): ?>
<section id="propertyowners">
    <h2 style="text-align:center;margin-bottom:30px;color:var(--gold);">PROPERTY OWNERS</h2>
    <div style="text-align:center;margin-bottom:20px;"><a href="add_propertyowner.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4);">+++ ADD NEW PROPERTY OWNER</a></div>
    <table>
        <tr><th>Full Name</th><th>Email</th><th>Phone</th><th>Properties Owned</th><th>Actions</th></tr>
        <?php $owners=mysqli_query($conn,"SELECT u.*,COUNT(p.id) AS properties_count FROM users u LEFT JOIN properties p ON u.id=p.owner_id WHERE u.role='propertyowner' GROUP BY u.id ORDER BY u.created_at DESC");
        while($owner=mysqli_fetch_assoc($owners)):?>
        <tr>
            <td><?=htmlspecialchars($owner['fullname'])?></td>
            <td><?=htmlspecialchars($owner['email']??'N/A')?></td>
            <td><?=htmlspecialchars($owner['phone']??'N/A')?></td>
            <td><?=(int)$owner['properties_count']?></td>
            <td>
                <a href="edit_records.php?type=propertyowner&id=<?=$owner['id']?>" class="action-btn">Edit</a>
                <a href="delete_record.php?table=users&id=<?=$owner['id']?>" class="action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;" onclick="return confirm('Delete?')">Delete</a>
            </td>
        </tr>
        <?php endwhile;?>
    </table>
</section>
 
<?php elseif($page === 'payments'): ?>
<section id="payments">
    <h2>Payments / Rent Tracking</h2>
    <table>
        <tr><th>Tenant</th><th>Property</th><th>Amount (UGX)</th><th>Date</th><th>Status</th></tr>
        <?php $payments=mysqli_query($conn,"SELECT pay.*,t.fullname as tenant_name,p.property_name FROM payments pay LEFT JOIN tenants t ON pay.tenant_id=t.id LEFT JOIN properties p ON pay.property_id=p.id ORDER BY pay.date DESC");
        while($pay=mysqli_fetch_assoc($payments)):?>
        <tr>
            <td><?=htmlspecialchars($pay['tenant_name']??'N/A')?></td>
            <td><?=htmlspecialchars($pay['property_name']??'N/A')?></td>
            <td><?=number_format($pay['amount'])?></td>
            <td><?=htmlspecialchars($pay['date'])?></td>
            <td><?=htmlspecialchars($pay['status']??'pending')?></td>
        </tr>
        <?php endwhile;?>
    </table>
</section>
 
<?php elseif($page === 'employee_performance'): ?>
<section id="employee_performance">
    <h2>Employee Performance</h2>
    <table>
        <tr><th>Staff Member</th><th>Tasks Completed</th><th>Tasks Pending</th><th>Overall Status</th></tr>
        <?php $staff=mysqli_query($conn,"SELECT * FROM users WHERE role='staff'");
        while($s=mysqli_fetch_assoc($staff)):
            $sid=$s['id'];
            $done=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM tasks WHERE assigned_to='$sid' AND status='Completed'"))['count'];
            $pend=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM tasks WHERE assigned_to='$sid' AND status!='Completed'"))['count'];
            $overall=$pend==0?'Excellent':($done>=$pend?'Good':'Needs Improvement');
        ?>
        <tr>
            <td><?=htmlspecialchars($s['fullname'])?></td>
            <td><?=$done?></td>
            <td><?=$pend?></td>
            <td><?=$overall?></td>
        </tr>
        <?php endwhile;?>
    </table>
</section>
 
<?php elseif($page === 'revenue_reports'): ?>
<section id="revenue_reports">
    <h2 style="text-align:center;margin-bottom:30px;color:var(--gold);">REVENUE REPORTS</h2>
    <?php
    $total_rev=mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) AS total FROM payments"))['total']??0;
    $pending_rev=mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) AS total FROM payments WHERE status='pending'"))['total']??0;
    $collected=$total_rev-$pending_rev;
    ?>
    <div class="overview-cards" style="justify-content:center;margin-bottom:40px">
        <div class="circular-card"><h3>Total Revenue</h3><p>UGX <?=number_format($total_rev)?></p></div>
        <div class="circular-card"><h3>Collected</h3><p>UGX <?=number_format($collected)?></p></div>
        <div class="circular-card"><h3>Pending</h3><p>UGX <?=number_format($pending_rev)?></p></div>
    </div>
    <h3 style="margin-bottom:20px">Revenue by Property</h3>
    <table>
        <tr><th>Property Name</th><th>Total Paid (UGX)</th><th>Pending (UGX)</th></tr>
        <?php $props=mysqli_query($conn,"SELECT pr.property_name,SUM(CASE WHEN p.status='paid' THEN p.amount ELSE 0 END) AS paid,SUM(CASE WHEN p.status='pending' THEN p.amount ELSE 0 END) AS pending FROM properties pr LEFT JOIN payments p ON pr.id=p.property_id GROUP BY pr.id ORDER BY pr.property_name ASC");
        while($prop=mysqli_fetch_assoc($props)):?>
        <tr>
            <td><?=htmlspecialchars($prop['property_name'])?></td>
            <td><?=number_format($prop['paid']??0)?></td>
            <td><?=number_format($prop['pending']??0)?></td>
        </tr>
        <?php endwhile;?>
    </table>
</section>
 
<?php elseif($page === 'settings'): ?>
<section>
    <h2 style="text-align:center;">SYSTEM SETTINGS</h2>
    <?php
    $settingsQuery=mysqli_query($conn,"SELECT * FROM system_settings LIMIT 1");
    $settings=($settingsQuery&&mysqli_num_rows($settingsQuery)>0)?mysqli_fetch_assoc($settingsQuery):["site_name"=>"HousingHub","email"=>"","notification_email"=>"","backup_frequency"=>"weekly"];
    ?>
    <form method="POST" action="save_settings.php" style="max-width:500px;margin:auto;color:var(--white);border:4px solid #0ea5e9;padding:25px;border-radius:12px;background:rgba(255,255,255,.04);box-shadow:0 4px 15px rgba(0,0,0,.97)">
        <label><b>Site Name</b></label>
        <input type="text" name="site_name" value="<?=htmlspecialchars($settings['site_name'])?>" required style="width:100%;padding:10px;margin:10px 0">
        <label><b>System Email</b></label>
        <input type="email" name="email" value="<?=htmlspecialchars($settings['email']??'')?>" style="width:100%;padding:10px;margin:10px 0">
        <label><b>Notification Email</b></label>
        <input type="email" name="notification_email" value="<?=htmlspecialchars($settings['notification_email']??'')?>" style="width:100%;padding:10px;margin:10px 0">
        <label><b>Backup Frequency</b></label>
        <select name="backup_frequency" style="width:100%;padding:10px;margin:10px 0">
            <option value="daily" <?=($settings['backup_frequency']=="daily")?"selected":""?>>Daily</option>
            <option value="weekly" <?=($settings['backup_frequency']=="weekly")?"selected":""?>>Weekly</option>
            <option value="monthly" <?=($settings['backup_frequency']=="monthly")?"selected":""?>>Monthly</option>
        </select>
        <button type="submit" name="save_settings" class="action-btn" style="width:100%;background:black">SAVE</button>
    </form>
</section>
 
<?php elseif($page === 'notifications'): ?>
<section id="notifications">
    <h2 style="text-align:center;margin-bottom:30px;color:var(--gold);">NOTIFICATIONS</h2>
    <?php $notifs=mysqli_query($conn,"SELECT n.*,u.fullname AS sender_name,t.fullname AS tenant_name FROM notifications n LEFT JOIN users u ON n.user_id=u.id LEFT JOIN tenants t ON n.tenant_id=t.id ORDER BY n.date DESC");?>
    <table>
        <tr><th>Recipient</th><th>Tenant</th><th>Title</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr>
        <?php while($n=mysqli_fetch_assoc($notifs)):?>
        <tr>
            <td><?=htmlspecialchars($n['sender_name']??'System')?></td>
            <td><?=htmlspecialchars($n['tenant_name']??'-')?></td>
            <td><?=htmlspecialchars($n['title']??'-')?></td>
            <td><?=htmlspecialchars(substr($n['message'],0,50))?>...</td>
            <td><?=htmlspecialchars(ucfirst($n['status']??'unread'))?></td>
            <td><?=htmlspecialchars($n['date']??'-')?></td>
            <td>
                <?php if($n['status']==='unread'):?><a href="mark_notification_read.php?id=<?=$n['id']?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);">Mark Read</a><?php endif;?>
                <a href="delete_record.php?table=notifications&id=<?=$n['id']?>" class="action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;" onclick="return confirm('Delete?')">Delete</a>
            </td>
        </tr>
        <?php endwhile;?>
    </table>
</section>
 
<?php elseif($page === 'backups'): ?>
<section id="backups">
    <h2 style="text-align:center;margin-bottom:30px;color:var(--gold);">BACKUP / EXPORT DATA</h2>
    <div style="text-align:center;margin-bottom:20px">
        <a href="export_sql.php" class="action-btn" style="background:rgba(14,90,200,.3);border:1px solid rgba(14,90,200,.4);margin-bottom:10px">Export Full Database (SQL)</a><br>
        <a href="export_csv.php?table=users" class="action-btn" style="background:#38bdf8;margin-bottom:10px">Export Users (CSV)</a><br>
        <a href="export_csv.php?table=tenants" class="action-btn" style="background:#38bdf8;margin-bottom:10px">Export Tenants (CSV)</a><br>
        <a href="export_csv.php?table=properties" class="action-btn" style="background:#38bdf8;margin-bottom:10px">Export Properties (CSV)</a><br>
        <a href="export_csv.php?table=payments" class="action-btn" style="background:#38bdf8;margin-bottom:10px">Export Payments (CSV)</a><br>
        <a href="export_csv.php?table=complaints" class="action-btn" style="background:#38bdf8">Export Complaints (CSV)</a>
    </div>
    <div class="chart-container" style="text-align:center">
        <h3>Info</h3>
        <p>Exported SQL can be used to restore the database. CSV exports can be opened in Excel or Google Sheets.</p>
    </div>
</section>
 
<?php elseif($page === 'reports'): ?>
<section id="reports">
    <h2>Reports & Analytics</h2>
    <div class="chart-container"><h3>Coming Soon</h3><p>Charts and analytics will be displayed here.</p></div>
</section>
 
<?php endif; ?>
</div>
</body>
</html>