<?php
session_start();
include "db_connect.php";

// --- 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// --- 2. Check if user is staff
$user_id = intval($_SESSION['user_id']);
$userQ = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($userQ);
if (!$user || strtolower($user['role']) !== 'staff') {
    echo "<h2 style='color:red;text-align:center;'>Access Denied!</h2>";
    exit();
}

// --- 3. Handle Add / Update Tenant
$edit_id = $_POST['edit_id'] ?? null;

if (isset($_POST['save_tenant'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $phone    = mysqli_real_escape_string($conn['phone'] ?? '', $_POST['phone']);
    $whatsapp = mysqli_real_escape_string($conn, $_POST['whatsapp'] ?? '');
    $property_id = intval($_POST['property_id']);
    $status   = mysqli_real_escape_string($conn, $_POST['status']);

    if ($edit_id) {
        // --- Update tenant
        mysqli_query($conn, "
            UPDATE tenants SET
                fullname='$fullname',
                email='$email',
                phone='$phone',
                whatsapp='$whatsapp',
                property_id='$property_id',
                status='$status'
            WHERE id='$edit_id'
        ");
    } else {
        // --- Add new tenant
        mysqli_query($conn, "
            INSERT INTO tenants (fullname,email,phone,whatsapp,property_id,status)
            VALUES ('$fullname','$email','$phone','$whatsapp','$property_id','$status')
        ");
    }
    header("Location: staff_tenants.php");
    exit();
}

// --- 4. Handle Delete
if (isset($_GET['delete'])) {
    $tenant_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM tenants WHERE id='$tenant_id'");
    header("Location: staff_tenants.php");
    exit();
}

// --- 5. Handle Edit (load data into form)
$edit_id = $_POST['edit'] ?? null;
$tenantData = null;
if ($edit_id) {
    $tenantData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tenants WHERE id='$edit_id'"));
}

// --- 6. Filter / Search
$filter_property = $_POST['property_id'] ?? '';
$filter_status   = $_POST['status'] ?? '';
$search          = $_POST['search'] ?? '';

$where = "WHERE 1=1";
if ($filter_property) $where .= " AND property_id='$filter_property'";
if ($filter_status)   $where .= " AND status='$filter_status'";
if ($search)          $where .= " AND (fullname LIKE '%$search%' OR email LIKE '%$search%')";

// --- 7. Fetch tenants
$tenantsQ = mysqli_query($conn, "SELECT t.*, p.property_name 
                                 FROM tenants t 
                                 LEFT JOIN properties p ON t.property_id = p.id 
                                 $where
                                 ORDER BY t.id DESC");

// --- 8. Quick stats
$totalQ    = mysqli_query($conn, "SELECT COUNT(*) as total FROM tenants");
$activeQ   = mysqli_query($conn, "SELECT COUNT(*) as active FROM tenants WHERE status='Active'");
$inactiveQ = mysqli_query($conn, "SELECT COUNT(*) as inactive FROM tenants WHERE status='Inactive'");

$total    = mysqli_fetch_assoc($totalQ)['total'];
$active   = mysqli_fetch_assoc($activeQ)['active'];
$inactive = mysqli_fetch_assoc($inactiveQ)['inactive'];

// --- 9. Fetch properties for dropdown
$propertiesQ = mysqli_query($conn, "SELECT * FROM properties ORDER BY property_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tenant Profiles</title>
    <style>
        body { font-family:Segoe UI; background:lightblue; padding:30px; }
        h1 { color:black; }
        form { background:white; padding:20px;  border:3px solid blue; margin-bottom:20px; }
        input, select { width:100%; padding:10px; margin:10px 0; border-radius:5px; border:1px solid #ccc; }
        button { padding:10px 20px; border:none; background:#2563eb; color:white; border-radius:5px; cursor:pointer; }
        button:hover { background:#1d4ed8; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { border:1px solid #1b1a1a; padding:10px; text-align:left; }
        th { background:#2563eb; color:white; }
        a { text-decoration:none; color:#2563eb; }
        a:hover { text-decoration:underline; }
        .stats { display:flex; gap:20px; margin-bottom:50px; }
        .stats div { flex:1; background:black; color:white; padding:15px; border-radius:40px; text-align:center; }
    </style>
</head>
<body>

   <div >
    <h1>Tenant Profiles</h1>
    <a href="staff_dashboard.php">← Back to Staff Dashboard</a><br>
</div>
<br>
<!-- Quick Stats -->
<div class="stats">
    <div>Total Tenants: <?php echo $total; ?></div>
    <div>Active: <?php echo $active; ?></div>
    <div>Inactive: <?php echo $inactive; ?></div>
</div>

<!-- Filter / Search -->
<form method="GET" action="">
    <h3>Filter / Search Tenants</h3>
    <select name="property_id">
        <option value="">All Properties</option>
        <?php
        $propertiesQ2 = mysqli_query($conn, "SELECT * FROM properties ORDER BY property_name ASC");
        while($p = mysqli_fetch_assoc($propertiesQ2)):
        ?>
            <option value="<?php echo $p['id']; ?>" <?php if($filter_property==$p['id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($p['property_name']); ?>
            </option>
        <?php endwhile; ?>
    </select>
    <select name="status">
        <option value="">All Status</option>
        <option value="Active" <?php if($filter_status=='Active') echo 'selected'; ?>>Active</option>
        <option value="Inactive" <?php if($filter_status=='Inactive') echo 'selected'; ?>>Inactive</option>
    </select>
    <input type="text" name="search" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Apply</button>
</form>

<!-- Add / Edit Tenant Form -->
<form method="POST" action="">
    <h3><?php echo $tenantData ? "Edit Tenant" : "Add New Tenant"; ?></h3>
    <input type="hidden" name="edit_id" value="<?php echo $tenantData['id'] ?? ''; ?>">
    <input type="text" name="fullname" placeholder="Full Name" required value="<?php echo htmlspecialchars($tenantData['fullname'] ?? ''); ?>">
    <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($tenantData['email'] ?? ''); ?>">
    <input type="text" name="phone" placeholder="Phone" value="<?php echo htmlspecialchars($tenantData['phone'] ?? ''); ?>">
    <input type="text" name="whatsapp" placeholder="WhatsApp" value="<?php echo htmlspecialchars($tenantData['whatsapp'] ?? ''); ?>">
    <select name="property_id" required>
        <option value="">Select Property</option>
        <?php
        $propertiesQ2 = mysqli_query($conn, "SELECT * FROM properties ORDER BY property_name ASC");
        while($p = mysqli_fetch_assoc($propertiesQ2)):
        ?>
            <option value="<?php echo $p['id']; ?>" <?php if(($tenantData['property_id'] ?? '')==$p['id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($p['property_name']); ?>
            </option>
        <?php endwhile; ?>
    </select>
    <select name="status" required>
        <option value="Active" <?php if(($tenantData['status'] ?? '')=='Active') echo 'selected'; ?>>Active</option>
        <option value="Inactive" <?php if(($tenantData['status'] ?? '')=='Inactive') echo 'selected'; ?>>Inactive</option>
    </select>
    <button type="submit" name="save_tenant"><?php echo $tenantData ? "Update Tenant" : "Add Tenant"; ?></button>
    <?php if($tenantData): ?>
        <a href="staff_tenants.php" style="margin-left:15px;">Cancel Edit</a>
    <?php endif; ?>
</form>

<!-- Tenant List -->
<table>
    <tr>
        <th>#</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>WhatsApp</th>
        <th>Property</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php $i=1; while($tenant = mysqli_fetch_assoc($tenantsQ)): ?>
    <tr>
        <td><?php echo $i++; ?></td>
        <td><?php echo htmlspecialchars($tenant['fullname']); ?></td>
        <td><?php echo htmlspecialchars($tenant['email']); ?></td>
        <td><?php echo htmlspecialchars($tenant['phone'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($tenant['whatsapp'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($tenant['property_name']); ?></td>
        <td><?php echo htmlspecialchars($tenant['status']); ?></td>
        <td>
            <a href="?edit=<?php echo $tenant['id']; ?>">Edit</a> |
            <a href="?delete=<?php echo $tenant['id']; ?>" onclick="return confirm('Delete this tenant?')">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>