<?php
session_start();
include "db_connect.php";

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}



// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $tenant_id = intval($_POST['tenant_id'] ?? 0) ?: null;
    $property_id = intval($_POST['property_id'] ?? 0) ?: null;
    $check_in = $_POST['check_in'] ?? null;
    $check_out = $_POST['check_out'] ?? null;
    $status = $_POST['status'] ?? 'Pending';
    $visit_type = $_POST['visit_type'] ?? 'property';

    $query = "INSERT INTO guests (fullname, email, phone, tenant_id, property_id, check_in, check_out, status, visit_type) 
              VALUES ('$fullname', '$email', '$phone', " . ($tenant_id ? $tenant_id : "NULL") . ", " . ($property_id ? $property_id : "NULL") . ",
                      " . ($check_in ? "'$check_in'" : "NULL") . ", " . ($check_out ? "'$check_out'" : "NULL") . ", '$status', '$visit_type')";

    if (mysqli_query($conn, $query)) {
        $success = "Guest added successfully!";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Fetch tenants and properties for dropdowns
$tenants = mysqli_query($conn, "SELECT id, fullname FROM tenants ORDER BY fullname ASC");
$properties = mysqli_query($conn, "SELECT id, property_name FROM properties ORDER BY property_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Guest | HousingHub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body { font-family: "Segoe UI", sans-serif; padding: 30px; background: lightblue; }
form { max-width: 600px; margin: auto; border: 3px solid #1b1a1a;background: linear-gradient(135deg, #495757, #0ea5e9);  padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
form input, form select { width: 100%; padding: 10px; margin: 10px 0; border-radius: 6px; border: 1px solid #ccc; }
form button { padding: 12px 25px; background: #0f0f0f; color: white; border: none; border-radius: 6px; cursor: pointer; transition: 0.3s; }
form button:hover { background: #0d0d0e; }
.success { color: green; text-align: center; }
.error { color: red; text-align: center; }
</style>
</head>
<body>

<h2 style="text-align:center;">Add New Guest / Visitor</h2>

<?php if(!empty($success)) echo "<p class='success'>$success</p>"; ?>
<?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>

<form action="" method="POST">
    <label>Full Name:</label>
    <input type="text" name="fullname" required>

    <label>Email:</label>
    <input type="email" name="email">

    <label>Phone:</label>
    <input type="text" name="phone">

    <label>Linked Tenant (optional):</label>
    <select name="tenant_id">
        <option value="">-- Select Tenant --</option>
        <?php while($t = mysqli_fetch_assoc($tenants)): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['fullname']) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Property (optional):</label>
    <select name="property_id">
        <option value="">-- Select Property --</option>
        <?php while($p = mysqli_fetch_assoc($properties)): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['property_name']) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Check-in Date & Time:</label>
    <input type="datetime-local" name="check_in">

    <label>Check-out Date & Time:</label>
    <input type="datetime-local" name="check_out">

    <label>Status:</label>
    <select name="status">
        <option value="Pending">Pending</option>
        <option value="Approved">Approved</option>
        <option value="Rejected">Rejected</option>
    </select>

    <label>Visit Type:</label>
    <select name="visit_type">
        <option value="property">Property</option>
        <option value="tenant">Tenant</option>
    </select>

    <button type="submit">Add Guest</button>
</form>

</body>
</html>