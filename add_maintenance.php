<?php
session_start();
include "db_connect.php";

if (isset($_POST['add_request'])) {

    $property_id = intval($_POST['property_id']);
    $tenant_id   = intval($_POST['tenant_id']);
    $issue       = mysqli_real_escape_string($conn, $_POST['issue']);
    $priority    = mysqli_real_escape_string($conn, $_POST['priority']);

    mysqli_query($conn, "
        INSERT INTO maintenance_requests
        (property_id, tenant_id, issue, priority, status)
        VALUES
        ($property_id, $tenant_id, '$issue', '$priority', 'Pending')
    ");

    header("Location: admin_dashboard.php?page=maintenance");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Maintenance Request</title>
<style>
body{font-family:Segoe UI;background:#f4f7fb;padding:30px;}
form{width:550px;margin:auto;background:white;padding:25px;
border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.15);}
input,select,textarea{width:100%;padding:10px;margin:10px 0;}
button{width:100%;padding:12px;background:#0ea5e9;border:none;
color:white;font-size:16px;border-radius:6px;}
</style>
</head>
<body>

<form method="POST">
<h2 style="text-align:center;">Add Maintenance Request</h2>

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

<!-- Tenant -->
<select name="tenant_id" required>
<option value="">-- Select Tenant --</option>
<?php
$tenants = mysqli_query($conn, "SELECT id, fullname FROM tenants");
while($t = mysqli_fetch_assoc($tenants)){
    echo "<option value='{$t['id']}'>".htmlspecialchars($t['fullname'])."</option>";
}
?>
</select>

<a href="assign_staff.php?id=<?= $row['id'] ?>" class="btn">
    Assign Staff
</a>

<!-- Issue -->
<textarea name="issue" placeholder="Describe the issue..." required></textarea>

<!-- Priority -->
<select name="priority">
<option value="Low">Low</option>
<option value="Medium" selected>Medium</option>
<option value="High">High</option>
</select>

<button type="submit" name="add_request">Save Request</button>
</form>

</body>
</html>