<?php
session_start();
include "db_connect.php";

// Redirect if not admin
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE id='$user_id'"))['role'];
if (strtolower($role_check) !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $query = "INSERT INTO users (fullname, email, phone, password, role, created_at) 
              VALUES ('$fullname', '$email', '$phone', '$password', 'propertyowner', NOW())";
    if (mysqli_query($conn, $query)) {
        header("Location: admin_dashboard.php?page=propertyowners");
        exit();
    } else {
        $error = "Failed to add property owner: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Property Owner</title>
<link rel="stylesheet" href="index.css">
<style>
    form { max-width: 500px;border: 3px solid black; background: linear-gradient(135deg, #495757, #0ea5e9);margin: 50px auto; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(17, 16, 16, 0.97); }
    input { width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 6px; border: 1px solid #ccc; }
    button { background: #121213; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; }
    button:hover { background: #38bdf8; }
    .error { color: red; margin-bottom: 10px; }
</style>
</head>
<body style="background:lightblue;">

<form method="POST">
    <h2>Add New Property Owner</h2>
    <?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <input type="text" name="fullname" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="phone" placeholder="Phone">
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Add Property Owner</button>
</form>

</body>
</html>