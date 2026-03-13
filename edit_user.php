<?php
session_start();
include "db_connect.php";

// Check if admin
$user_id = $_SESSION['user_id'] ?? 0;
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'"));
if(strtolower(trim($user['role'])) !== 'admin') {
    header("Location: dashboard.php"); exit();
}

// Get the user to edit
$edit_id = intval($_POST['id'] ?? 0);
$edit_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$edit_id'"));

if(!$edit_user){
    echo "User not found"; exit();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    mysqli_query($conn, "UPDATE users SET fullname='$fullname', email='$email', role='$role' WHERE id='$edit_id'");
    header("Location: admin_dashboard.php?page=users");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User | Admin Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
    font-family: "Segoe UI", sans-serif;
    background: lightblue;
    margin:0;
    padding:0;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    border: 3px solid #141414;
}

.edit-card {
    background: linear-gradient(145deg, #0c0c0c, #0ea5e9);
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    color: white;
    width: 100%;
    max-width: 450px;
}

.edit-card h2 {
    text-align: center;
    margin-bottom: 30px;
    font-size: 26px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #fff;
}

.edit-card label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}

.edit-card input, .edit-card select {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 10px;
    border: none;
    font-size: 16px;
    outline: none;
}

.edit-card input:focus, .edit-card select:focus {
    box-shadow: 0 0 5px rgba(255,255,255,0.8);
}

.btn {
    width: 100%;
    padding: 12px;
    background: #080808;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    color: white;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.btn:hover {
    background: #053896;
    transform: scale(1.05);
}

.back-btn {
    display: block;
    text-align: center;
    margin-top: 15px;
    text-decoration: none;
    color: white;
    font-weight: bold;
}
</style>
</head>
<body>

<div class="edit-card">
    <h2>EDIT USER</h2>
    <form method="POST">
        <label>Fullname</label>
        <input type="text" name="fullname" value="<?= htmlspecialchars($edit_user['fullname']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($edit_user['email']) ?>" required>

        <label>Role</label>
        <select name="role" required>
            <option value="admin" <?= $edit_user['role']=='admin'?'selected':'' ?>>Admin</option>
            <option value="staff" <?= $edit_user['role']=='staff'?'selected':'' ?>>Staff</option>
            <option value="tenant" <?= $edit_user['role']=='tenant'?'selected':'' ?>>Tenant</option>
            <option value="guest" <?= $edit_user['role']=='guest'?'selected':'' ?>>Guest</option>
            <option value="broker" <?= $edit_user['role']=='broker'?'selected':'' ?>>Broker</option>
            <option value="propertyowner" <?= $edit_user['role']=='propertyowner'?'selected':'' ?>>Propertyowner</option>
        
        </select>

        <button type="submit" class="btn">Save Changes</button>
    </form>

    <a href="admin_dashboard.php?page=users" class="back-btn">⬅ Back to Users</a>
</div>

</body>
</html>