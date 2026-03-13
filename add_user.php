<?php
session_start();
include "db_connect.php";

// Check if admin
$user_id = $_SESSION['user_id'] ?? 0;
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'"));

if(strtolower(trim($user['role'])) !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $role     = mysqli_real_escape_string($conn, $_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insert into database
    mysqli_query($conn, "
        INSERT INTO users (fullname, email, role, password)
        VALUES ('$fullname', '$email', '$role', '$password')
    ");

    header("Location: admin_dashboard.php?page=users");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New User | Admin Panel</title>
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
    border: 3px solid black;
}

/* Card Container */
.add-card {
    background: linear-gradient(145deg, black, #38bdf8);
    padding: 45px;
    border-radius: 22px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.18);
    color: white;
    width: 90%;
    max-width: 480px;
    animation: fadeIn 0.8s ease-in-out;
    border: 3px solid #105ceb;
}

@keyframes fadeIn {
    from {opacity:0; transform:translateY(20px);}
    to {opacity:1; transform:translateY(0);}
}

.add-card h2 {
    text-align: center;
    margin-bottom: 30px;
    font-size: 28px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Labels */
.add-card label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}

/* Inputs */
.add-card input, 
.add-card select {
    width: 100%;
    padding: 13px 15px;
    margin-bottom: 18px;
    border-radius: 12px;
    border: none;
    font-size: 16px;
    outline: none;
}

.add-card input:focus,
.add-card select:focus {
    box-shadow: 0 0 8px rgba(255,255,255,0.9);
}

/* Button */
.btn {
    width: 100%;
    padding: 13px;
    background: #0f0f0f;
    border: none;
    border-radius: 12px;
    font-size: 17px;
    font-weight: bold;
    color: white;
    cursor: pointer;
    transition: 0.3s;
}

.btn:hover {
    background: #151616;
    transform: scale(1.05);
}

/* Back Button */
.back-btn {
    display: block;
    text-align: center;
    margin-top: 18px;
    text-decoration: none;
    color: white;
    font-weight: bold;
    font-size: 15px;
}

.back-btn:hover {
    text-decoration: underline;
}
</style>

</head>
<body>

<div class="add-card">
    <h2>Add New User</h2>

    <form method="POST">

        <label>Full Name</label>
        <input type="text" name="fullname" placeholder="Enter full name..." required>

        <label>Email Address</label>
        <input type="email" name="email" placeholder="Enter email..." required>

        <label>User Role</label>
        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="staff">Staff</option>
            <option value="guest">Guest</option>
            <option value="tenant">Tenant</option>
            <option value="broker">Broker</option>
            <option value="propertyowner">Propertyowner</option>
            <option value="admin">Admin</option>
        </select>

        <label>Temporary Password</label>
        <input type="password" name="password" placeholder="Enter password..." required>

        <button type="submit" class="btn">ENTER</button>

    </form>

    <a href="admin_dashboard.php?page=users" class="back-btn">
        ⬅ BACK TO USER MANAGEMENT
    </a>
</div>

</body>
</html>

