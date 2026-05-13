<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id'] ?? 0);
mysqli_query($conn, "UPDATE guests SET status='approved' WHERE id=$id");

$guest = mysqli_fetch_assoc(mysqli_query($conn, "SELECT fullname, email, phone, password FROM guests WHERE id=$id LIMIT 1"));
if ($guest && !empty($guest['email'])) {
    $email = mysqli_real_escape_string($conn, trim($guest['email']));
    $name  = mysqli_real_escape_string($conn, trim($guest['fullname'] ?? 'Guest'));
    $phone = mysqli_real_escape_string($conn, trim($guest['phone'] ?? ''));
    $existing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE email='$email' LIMIT 1"));
    if ($existing) {
        $uid = (int)$existing['id'];
        mysqli_query($conn, "UPDATE users SET role='guest', status='active', fullname='$name', phone='$phone' WHERE id=$uid AND role<>'admin'");
    } else {
        $plain = !empty($guest['password']) ? $guest['password'] : ('guest@' . (strlen($guest['phone'] ?? '') >= 4 ? substr($guest['phone'], -4) : '1234'));
        $hash = password_hash($plain, PASSWORD_DEFAULT);
        $hash = mysqli_real_escape_string($conn, $hash);
        mysqli_query($conn, "INSERT INTO users (fullname, email, phone, password, role, status, created_at) VALUES ('$name', '$email', '$phone', '$hash', 'guest', 'active', NOW())");
        $_SESSION['admin_success'] = "Guest approved. Login account created for " . htmlspecialchars($guest['email']) . " with temporary password: " . htmlspecialchars($plain);
    }
}

header("Location: admin_dashboard.php?page=guests");
exit();

