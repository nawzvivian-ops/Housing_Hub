<?php
session_start();
include "db_connect.php";

// Ensure tenant is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get tenant info
$user_id = $_SESSION['user_id'];
$tenant_result = mysqli_query($conn, "SELECT * FROM tenants WHERE user_id='$user_id'");
$tenant = mysqli_fetch_assoc($tenant_result);

if (!$tenant) {
    die("Tenant information not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $issue = mysqli_real_escape_string($conn, $_POST['issue']);
    $priority = mysqli_real_escape_string($conn, $_POST['priority']);
    $property_id = $tenant['property_id'];

    if (!empty($issue)) {
        $insert = mysqli_query($conn, "INSERT INTO maintenance_requests (property_id, issue, priority) 
                                       VALUES ('$property_id', '$issue', '$priority')");
        if ($insert) {
            $success = "Maintenance request submitted successfully!";
        } else {
            $error = "Failed to submit request: " . mysqli_error($conn);
        }
    } else {
        $error = "Issue description is required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SUBMIT MAINTENANCE REQUESTS</title>
<style>
body { font-family: Arial,sans-serif; background:lightblue; padding:20px; }
.container { max-width:500px; margin:0 auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.1);}
h2 { color:#4f46e5; text-align:center; margin-bottom:20px; }
input, textarea, select { width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:6px; }
button { padding:12px; background:#4f46e5; color:#fff; border:none; border-radius:6px; width:100%; cursor:pointer; }
button:hover { background:#3b36e0; }
.success { color:green; margin-bottom:15px; }
.error { color:red; margin-bottom:15px; }
a { text-decoration:none; color:#4f46e5; display:block; margin-top:15px; text-align:center; }
</style>
</head>
<body>
<div class="container" style="border:3px solid #105ceb;">
    <h2>SUBMIT MAINTENANCE REQUESTS</h2>

    <?php if(!empty($success)) echo "<p class='success'>$success</p>"; ?>
    <?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <textarea name="issue" placeholder="Describe the issue" required></textarea>
        <select name="priority">
            <option value="low" >Low Priority</option>
            <option value="medium" selected>Medium Priority</option>
            <option value="high">High Priority</option>
        </select>
        <button type="submit">Submit Request</button>
    </form>

    <a href="tenant.php">← Back to Dashboard</a>
</div>
</body>
</html>