<?php
session_start();
include "db_connect.php";

// --- 1. Check staff login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// --- 2. Handle new inspection
if (isset($_POST['add_inspection'])) {
    $property_id = intval($_POST['property_id']);
    $tenant_id   = !empty($_POST['tenant_id']) ? intval($_POST['tenant_id']) : 'NULL';
    $inspector   = mysqli_real_escape_string($conn, $_POST['inspector_name']);
    $inspection_date = $_POST['inspection_date'];
    $situation   = mysqli_real_escape_string($conn, $_POST['situation']);
    $status      = $_POST['status'];

    mysqli_query($conn, "
        INSERT INTO inspections 
        (property_id, tenant_id, inspector_name, inspection_date, `condition`, status)
        VALUES 
        ($property_id, $tenant_id, '$inspector', '$inspection_date', '$situation', '$status')
    ");

    header("Location: staff_inspections.php");
    exit();
}

// --- 3. Fetch inspections
$inspections = mysqli_query($conn, "
    SELECT i.*, p.property_name, t.fullname AS tenant_name 
    FROM inspections i
    LEFT JOIN properties p ON i.property_id = p.id
    LEFT JOIN tenants t ON i.tenant_id = t.id
    ORDER BY i.inspection_date DESC
");

// --- 4. Fetch properties & tenants for dropdowns
$properties = mysqli_query($conn, "SELECT * FROM properties ORDER BY property_name ASC");
$tenants    = mysqli_query($conn, "SELECT * FROM tenants ORDER BY fullname ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Property Inspections</title>
<style>
body { font-family: Arial, sans-serif; background:lightblue; margin:20px; }
h1, h3 { color: #333; }
form { background:#fff; padding:15px; border-radius:20px; border:3px solid #0e0d0d;margin-bottom:20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
label { display:block; margin-top:10px; }
input, select, button { padding:7px; width:99%; margin-top:5px; }
button { background:#4CAF50; color:#fff; border:none; cursor:pointer; font-weight:bold; }
button:hover { background:#45a049; }
table { width:100%; border-collapse:collapse; background:#fff; box-shadow:0 2px 5px rgba(0,0,0,0.1);}
th, td { border:1px solid #0a0a0a; padding:8px; text-align:center; }
th { background:#4CAF50; color:#fff; }
tr:nth-child(even){ background:#f2f2f2; }
.badge { padding:5px 10px; border-radius:3px; color:#fff; font-weight:bold; }
.badge.Pending { background:#FF9800; }
.badge.Completed { background:#4CAF50; }
.badge.Overdue { background:#f44336; }
</style>
</head>
<body>

<h1>PROPERTY INSPECTIONS</h1>
<br>
<h3>Add Inspection</h3>
<form method="POST">
    <label>Property:</label>
    <select name="property_id" required>
        <option value="">--Select Property--</option>
        <?php while($p = mysqli_fetch_assoc($properties)): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['property_name']) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Tenant (optional):</label>
    <select name="tenant_id">
        <option value="">--Select Tenant--</option>
        <?php while($t = mysqli_fetch_assoc($tenants)): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['fullname']) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Inspector Name:</label>
    <input type="text" name="inspector_name" required>

    <label>Inspection Date:</label>
    <input type="date" name="inspection_date" required>

    <label>Situation:</label>
    <input type="text" name="situation" required>

    <label>Status:</label>
    <select name="status">
        <option value="Pending">Pending</option>
        <option value="Completed">Completed</option>
    </select>

    <button type="submit" name="add_inspection">Add Inspection</button>
</form>

<h3>All Inspections</h3>
<table>
    <tr>
        <th>#</th>
        <th>Property</th>
        <th>Tenant</th>
        <th>Inspector</th>
        <th>Date</th>
        <th>Situation</th>
        <th>Status</th>
    </tr>
    <?php $i=1; while($ins = mysqli_fetch_assoc($inspections)):
        $status_class = $ins['status'];
        // Mark overdue inspections
        if($ins['status']=='Pending' && $ins['inspection_date'] < date('Y-m-d')){
            $status_class = 'Overdue';
        }
    ?>
    <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($ins['property_name']) ?></td>
        <td><?= htmlspecialchars($ins['tenant_name'] ?? '-') ?></td>
        <td><?= htmlspecialchars($ins['inspector_name']) ?></td>
        <td><?= htmlspecialchars($ins['inspection_date']) ?></td>
        <td><?= htmlspecialchars($ins['condition']) ?></td>
        <td><span class="badge <?= $status_class ?>"><?= $status_class ?></span></td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>

