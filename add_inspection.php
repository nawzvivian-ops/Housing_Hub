<?php
session_start();
include "db_connect.php";

if (isset($_POST['add_inspection'])) {

    $property_id     = intval($_POST['property_id']);
    $tenant_id       = !empty($_POST['tenant_id']) ? intval($_POST['tenant_id']) : NULL;

    $inspector_name  = mysqli_real_escape_string($conn, $_POST['inspector_name']);
    $inspection_date = mysqli_real_escape_string($conn, $_POST['inspection_date']);
    $situation       = mysqli_real_escape_string($conn, $_POST['situation']);
    $notes           = mysqli_real_escape_string($conn, $_POST['notes']);

    mysqli_query($conn, "
        INSERT INTO inspections 
        (property_id, tenant_id, inspector_name, inspection_date, situation, notes, status, notified)
        VALUES 
        ($property_id, ".($tenant_id ? $tenant_id : "NULL").",
         '$inspector_name', '$inspection_date', '$situation', '$notes',
         'Pending', 0)
    ");

    header("Location: admin_dashboard.php?page=inspections");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Schedule Inspection</title>
<style>
body{font-family:Segoe UI;background:lightblue;padding:30px;}
form{width:550px;margin:auto;  border: 3px solid blue; background: linear-gradient(135deg, #495757, #0ea5e9);padding:25px;
border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.15);}
input,select,textarea{width:98%;padding:10px;margin:10px 0;}
button{width:100%;padding:12px;background:black;border:none;
color:white;font-size:16px;border-radius:6px;}
</style>
</head>
<body>

<form method="POST">
<h2 style="text-align:center;">Schedule Inspection</h2>

<!-- Property -->
<select name="property_id" required>
<option value="">-- Select Property --</option>
<?php
$properties = mysqli_query($conn, "SELECT id, property_name FROM properties");
while($p = mysqli_fetch_assoc($properties)){
    echo "<option value='{$p['id']}'>".htmlspecialchars($p['property_name'])."</option>";
}
?>
</select>

<!-- Tenant (Optional) -->
<select name="tenant_id">
<option value="">-- Optional Tenant --</option>
<?php
$tenants = mysqli_query($conn, "SELECT id, fullname FROM tenants");
while($t = mysqli_fetch_assoc($tenants)){
    echo "<option value='{$t['id']}'>".htmlspecialchars($t['fullname'])."</option>";
}
?>
</select>

<!-- Inspector Name -->
<input type="text" name="inspector_name" placeholder="Inspector Name" required>

<!-- Date -->
<input type="date" name="inspection_date" required>

<!-- Situation -->
<input type="text" name="situation" placeholder="Situation (Good, Damaged, Needs Repair)" required>

<!-- Notes -->
<textarea name="notes" placeholder="Inspection notes..."></textarea>

<button type="submit" name="add_inspection">Save Inspection</button>
</form>

</body>
</html>