<?php
session_start();
include "db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle form submission
if (isset($_POST['add'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $property_id = intval($_POST['property_id']);

    // Insert tenant
    $query = "INSERT INTO tenants (fullname, phone, email, property_id) 
              VALUES ('$fullname', '$phone', '$email', $property_id)";

    if (mysqli_query($conn, $query)) {
        header("Location: admin_dashboard.php?page=tenants");
        exit();
    } else {
        $error = "Error adding tenant: " . mysqli_error($conn);
    }
}

// Fetch properties for dropdown
$properties = mysqli_query($conn, "SELECT id, property_name FROM properties ORDER BY property_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Tenant</title>
    <style>
        body { font-family: Segoe UI; background:lightblue; padding: 30px; }
        form { border:3px solid blue; padding: 25px;background: linear-gradient(135deg, #495757, #0ea5e9); width: 480px; margin: auto; border-radius: 12px; box-shadow: 0 5px 15px rgba(19, 17, 17, 0.97);}
        h2 { text-align:center; color:black; }
        input, select { width:100%; padding:10px; margin:10px 0; border:1px solid #ccc; border-radius:6px; }
        button { width:100%; padding:12px; background:black; border:none; color:white; font-size:16px; border-radius:6px; cursor:pointer; }
        button:hover { background:#0284c7; }
        .error { color:red; text-align:center; margin-bottom:15px; }
    </style>
</head>
<body>

<form method="POST">
    <h2>Add New Tenant</h2>

    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

    <input type="text" name="fullname" placeholder="Full Name" required>
    <input type="text" name="phone" placeholder="Phone Number">
    <input type="email" name="email" placeholder="Email Address">

    <select name="property_id" required>
        <option value="">Select Property</option>
        <?php while($prop = mysqli_fetch_assoc($properties)): ?>
            <option value="<?= $prop['id'] ?>"><?= htmlspecialchars($prop['property_name']) ?></option>
        <?php endwhile; ?>
    </select>

    <button type="submit" name="add">Add Tenant</button>
</form>

</body>
</html>