<?php
session_start();
include "db_connect.php";

// Check admin
$user_id = $_SESSION['user_id'] ?? 0;
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'"));
if(strtolower(trim($user['role'])) !== 'admin') {
    header("Location: dashboard.php"); exit();
}

// Delete user
$del_id = intval($_GET['id'] ?? 0);

// Prevent admin from deleting themselves
if($del_id !== $user_id){
    mysqli_query($conn, "DELETE FROM users WHERE id='$del_id'");
}

header("Location: admin_dashboard.php?page=users");
exit();

?>