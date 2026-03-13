<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['save_settings'])) {

    $site_name          = mysqli_real_escape_string($conn, $_POST['site_name']);
    $email              = mysqli_real_escape_string($conn, $_POST['email']);
    $notification_email = mysqli_real_escape_string($conn, $_POST['notification_email']);
    $backup_frequency   = mysqli_real_escape_string($conn, $_POST['backup_frequency']);

    // Update the first settings row
    mysqli_query($conn, "
        UPDATE system_settings
        SET site_name='$site_name',
            email='$email',
            notification_email='$notification_email',
            backup_frequency='$backup_frequency'
        WHERE id=1
    ");

    header("Location: admin_dashboard.php?page=settings&success=1");
    exit();
}
?>