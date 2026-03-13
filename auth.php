<?php
session_start();
include "db_connect.php";

/* ================= REGISTER ================= */
if (isset($_POST['register'])) {

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Get role from form, default to tenant
    $role = mysqli_real_escape_string($conn, $_POST['role'] ?? 'tenant');

    // Protect admin registration with a secret key
    if ($role === 'admin') {
        $admin_secret = $_POST['admin_secret'] ?? '';
        $expected_secret = "admin12345"; // <-- replace with your own secret

        if ($admin_secret !== $expected_secret) {
            $_SESSION['error'] = "Cannot register as admin!";
            header("Location: register.php");
            exit();
        }
    }

    // Check if email already exists
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "Email already registered!";
        header("Location: register.php");
        exit();
    }

    // Insert user into database
    $insert = mysqli_query($conn, "
        INSERT INTO users (fullname, email, password, role)
        VALUES ('$fullname', '$email', '$password', '$role')
    ");

    if ($insert) {
        $_SESSION['user_id']  = mysqli_insert_id($conn);
        $_SESSION['role']     = $role;
        $_SESSION['fullname'] = $fullname;

        // Redirect based on role
        if ($role === 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($role === 'staff') {
            header("Location: staff_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    }

    $_SESSION['error'] = "Registration failed!";
    header("Location: register.php");
    exit();
}

/* ================= LOGIN ================= */
if (isset($_POST['login'])) {

    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepare statement to prevent SQL injection
    $stmt = mysqli_prepare($conn, "SELECT id, password, role, fullname FROM users WHERE email=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $user = mysqli_fetch_assoc($result);

    if ($user && is_array($user)) {

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['fullname'] = $user['fullname'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['role'] === 'staff') {
                header("Location: staff_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        }
    }

    // If login fails
    $_SESSION['error'] = "Invalid email or password";
    header("Location: login.php");
    exit();
}
?>