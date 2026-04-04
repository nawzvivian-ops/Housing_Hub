<?php
session_start();
include "db_connect.php";

/* ================= REGISTER ================= */
if (isset($_POST['register'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = mysqli_real_escape_string($conn, $_POST['role'] ?? 'tenant');

    if ($role === 'admin') {
        if (($_POST['admin_secret'] ?? '') !== "admin12345") {
            $_SESSION['error'] = "Unauthorized admin attempt!";
            header("Location: register.php"); exit();
        }
    }

    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "Email already registered!";
        header("Location: register.php"); exit();
    }

    $insert = mysqli_query($conn, "INSERT INTO users (fullname, email, password, role) VALUES ('$fullname', '$email', '$password', '$role')");
    
    if ($insert) {
        $new_user_id = mysqli_insert_id($conn);
        
        if ($role === 'broker') {
            $v_check = mysqli_query($conn, "SELECT status FROM verification_requests WHERE email='$email' AND status='verified' LIMIT 1");
            
            if (mysqli_num_rows($v_check) > 0) {
                $_SESSION['user_id']  = $new_user_id;
                $_SESSION['role']     = 'broker';
                $_SESSION['fullname'] = $fullname;
                $_SESSION['email']    = $email; // Set this for dashboard
                $_SESSION['verified'] = true;   // Standardized key
                header("Location: broker_dashboard.php");
                exit();
            } else {
                $_SESSION['success'] = "Account created! Please wait for Admin verification.";
                header("Location: login.php"); exit();
            }
        }

        $_SESSION['user_id']  = $new_user_id;
        $_SESSION['role']     = $role;
        $_SESSION['fullname'] = $fullname;
        $_SESSION['email']    = $email;
        header("Location: index.php");
        exit();
    }
}

/* ================= LOGIN ================= */
if (isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' LIMIT 1");
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $role = strtolower($user['role']);

        if ($role === 'broker') {
            $v_check = mysqli_query($conn, "SELECT status FROM verification_requests WHERE email='$email' AND status='verified' LIMIT 1");
            
            if (mysqli_num_rows($v_check) > 0) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['role']     = 'broker';
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['email']    = $user['email']; // Set this for dashboard
                $_SESSION['verified'] = true;   // Standardized key
                header("Location: broker_dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Access Denied: Your broker documents are not yet verified.";
                header("Location: login.php"); exit();
            }
        }

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['role']     = $user['role'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['email']    = $user['email'];
        
        if ($user['role'] === 'admin') { header("Location: admin_dashboard.php"); }
        else { header("Location: index.php"); }
        exit();
    }

    $_SESSION['error'] = "Invalid email or password";
    header("Location: login.php");
    exit();
}
?>