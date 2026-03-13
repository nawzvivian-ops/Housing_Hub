<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

/* ===============================
   ADD STAFF FORM SUBMISSION
=================================*/
if (isset($_POST['add_staff'])) {

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $salary   = floatval($_POST['salary']);

    // Default staff role
    $role = "staff";

    // Password default (you can improve later)
    $password = password_hash("staff123", PASSWORD_DEFAULT);

    // Insert into users table
    mysqli_query($conn, "
        INSERT INTO users (fullname, email, role, salary, password)
        VALUES ('$fullname', '$email', '$role', $salary, '$password')
    ");

    header("Location: admin_dashboard.php?page=staff_roles");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Staff</title>
    <style>
        body {
            font-family: Segoe UI;
            background: lightblue;
            padding: 40px;
             
        }

        form {
            width: 450px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
             border: 3px solid blue;
              background: linear-gradient(135deg, #111111, #0ea5e9);
        }

        h2 {
            text-align: center;
            color: #0ea5e9;
        }

        input {
            width: 98%;
            padding: 12px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            background: #171718;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #0284c7;
        }

        .back {
            display: block;
            margin-top: 15px;
            text-align: center;
            text-decoration: none;
            color: #444;
        }
    </style>
</head>

<body>

<form method="POST">
    <h2>ADD NEW STAFF</h2>

    <input type="text" name="fullname" placeholder="Full Name" required>

    <input type="email" name="email" placeholder="Email Address" required>

    <input type="number" name="salary" placeholder="Salary (UGX)" required>

    <button type="submit" name="add_staff">ENTER</button>

    <a href="admin_dashboard.php?page=staff_roles" class="back">⬅PREVIOUS</a>
</form>

</body>
</html>