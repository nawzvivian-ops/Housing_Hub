<?php
session_start();
include "db_connect.php";

// Ensure the tenant is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch tenant info
$tenant_result = mysqli_query($conn, "SELECT * FROM tenants WHERE user_id='$user_id'");
$tenant = mysqli_fetch_assoc($tenant_result);

if (!$tenant) {
    die("Tenant information not found.");
}

// Handle form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $national_id = mysqli_real_escape_string($conn, $_POST['national_id']);
    $occupation = mysqli_real_escape_string($conn, $_POST['occupation']);
    $emergency_name = mysqli_real_escape_string($conn, $_POST['emergency_name']);
    $emergency_phone = mysqli_real_escape_string($conn, $_POST['emergency_phone']);

    // Basic validation (example)
    if (empty($fullname)) {
        $error = "Full name is required.";
    } else {
        $update_query = "
            UPDATE tenants SET
            fullname='$fullname',
            email='$email',
            phone='$phone',
            gender='$gender',
            national_id='$national_id',
            occupation='$occupation',
            emergency_name='$emergency_name',
            emergency_phone='$emergency_phone'
            WHERE user_id='$user_id'
        ";

        if (mysqli_query($conn, $update_query)) {
            $success = "Profile updated successfully!";
            // Refresh tenant data
            $tenant_result = mysqli_query($conn, "SELECT * FROM tenants WHERE user_id='$user_id'");
            $tenant = mysqli_fetch_assoc($tenant_result);
        } else {
            $error = "Error updating profile: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Tenant Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:lightblue; margin:0; color:#333;}
.container {max-width:700px; margin:40px auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 6px 15px rgba(0,0,0,0.1);}
h1 {text-align:center; color:#4f46e5; margin-bottom:20px;}
form {display:flex; flex-direction:column; gap:15px;}
label {font-weight:600;}
input, select {padding:10px; border-radius:6px; border:1px solid #ccc; font-size:16px;}
input[type="submit"] {background:#4f46e5; color:#fff; border:none; cursor:pointer; transition:0.3s;}
input[type="submit"]:hover {background:#3b36e0;}
.message {padding:10px; border-radius:6px;}
.success {background:#d1fae5; color:#065f46;}
.error {background:#fee2e2; color:#991b1b;}
.logout-btn {display:block; width:150px; margin:20px auto 0; padding:10px; background:BLACK; color:#fff; text-align:center; border:none; border-radius:8px; text-decoration:none;}
.logout-btn:hover {background:#dc2626;}
</style>
</head>
<body>

<div class="container"style="border:3px solid #105ceb;">
    <h1>EDIT PROFILE</h1>

    <?php if($success): ?>
        <div class="message success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="fullname">Full Name</label>
        <input type="text" name="fullname" id="fullname" value="<?php echo htmlspecialchars($tenant['fullname']); ?>" required>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($tenant['email']); ?>">

        <label for="phone">Phone</label>
        <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($tenant['phone']); ?>">

        <label for="gender">Gender</label>
        <select name="gender" id="gender">
            <option value="Male" <?php echo ($tenant['gender']=='Male')?'selected':''; ?>>Male</option>
            <option value="Female" <?php echo ($tenant['gender']=='Female')?'selected':''; ?>>Female</option>
            <option value="Other" <?php echo ($tenant['gender']=='Other')?'selected':''; ?>>Other</option>
        </select>

        <label for="national_id">National ID</label>
        <input type="text" name="national_id" id="national_id" value="<?php echo htmlspecialchars($tenant['national_id'] ?? ''); ?>"

        <label for="occupation">Occupation</label>
        <input type="text" name="occupation" id="occupation" value="<?php echo htmlspecialchars($tenant['occupation']); ?>">

        <label for="emergency_name">Emergency Contact Name</label>
        <input type="text" name="emergency_name" id="emergency_name" value="<?php echo htmlspecialchars($tenant['emergency_name']); ?>">

        <label for="emergency_phone">Emergency Contact Phone</label>
        <input type="text" name="emergency_phone" id="emergency_phone" value="<?php echo htmlspecialchars($tenant['emergency_phone']); ?>">

        <input type="submit" value="Save Changes">
    </form>

    <a href="tenants.php" class="logout-btn">Back to Dashboard</a>
</div>

</body>
</html>