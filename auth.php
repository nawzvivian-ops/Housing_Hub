<?php
session_start();
include "db_connect.php";

function dashboard_for_role($role) {
    $role = strtolower(trim((string)$role));
    if ($role === 'admin') return 'admin_dashboard.php';
    if ($role === 'staff') return 'staff_dashboard.php';
    if ($role === 'broker') return 'broker_dashboard.php';
    if ($role === 'guest') return 'guests.php';
    if ($role === 'propertyowner' || $role === 'owner') return 'propertyowner_dashboard.php';
    return 'dashboard.php';
}

function broker_can_access($conn, $user_id, $email) {
    $user_id = (int)$user_id;
    $email_safe = mysqli_real_escape_string($conn, $email);

    $status_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'status'");
    if ($status_check && mysqli_num_rows($status_check) > 0) {
        $user_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM users WHERE id=$user_id LIMIT 1"));
        if (strtolower(trim($user_status['status'] ?? 'active')) === 'suspended') {
            return false;
        }
    }

    $verified = mysqli_query($conn, "SELECT id FROM verification_requests WHERE email='$email_safe' AND status='verified' LIMIT 1");
    if ($verified && mysqli_num_rows($verified) > 0) return true;

    $broker_col = mysqli_query($conn, "SHOW COLUMNS FROM properties LIKE 'broker_id'");
    if ($broker_col && mysqli_num_rows($broker_col) > 0) {
        $assigned = mysqli_query($conn, "SELECT id FROM properties WHERE broker_id=$user_id LIMIT 1");
        if ($assigned && mysqli_num_rows($assigned) > 0) return true;
    }

    return false;
}

/* ================= REGISTER ================= */
if (isset($_POST['register'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = strtolower(trim(mysqli_real_escape_string($conn, $_POST['role'] ?? 'tenant')));

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
            if (broker_can_access($conn, $new_user_id, $email)) {
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
        header("Location: " . dashboard_for_role($role));
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

        if ($role === 'broker' && !broker_can_access($conn, $user['id'], $user['email'])) {
            $_SESSION['error'] = "Access Denied: Your broker account is not verified or assigned to any property yet.";
            header("Location: login.php"); exit();
        }

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['role']     = $role;
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['email']    = $user['email'];
        if ($role === 'broker') $_SESSION['verified'] = true;

        header("Location: " . dashboard_for_role($role));
        exit();
    }

    $_SESSION['error'] = "Invalid email or password";
    header("Location: login.php");
    exit();
}
?>
