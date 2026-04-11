<?php
session_start();
include "db_connect.php"; // your database connection
require_once "send_mail.php"; // your mail function

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $action = $_POST['action'];

    // Validate action
    if (!in_array($action, ['approve', 'reject'])) {
        $_SESSION['admin_error'] = "Invalid action.";
        header("Location: admin_dashboard.php?page=verification_requests");
        exit();
    }

    // Fetch the request
    $res = mysqli_query($conn, "SELECT * FROM verification_requests WHERE id=$id");
    if (mysqli_num_rows($res) === 0) {
        $_SESSION['admin_error'] = "Request not found.";
        header("Location: admin_dashboard.php?page=verification_requests");
        exit();
    }
    $req = mysqli_fetch_assoc($res);

    // Update status
    $new_status = ($action === 'approve') ? 'approved' : 'rejected';
    mysqli_query($conn, "UPDATE verification_requests SET status='$new_status' WHERE id=$id");

    // Optional: send email notification based on type
    if ($req['type'] === 'individual') {
        $name = $req['full_name'];
        $email = $req['email'];
        $subject = ucfirst($new_status) . " Verification Request";
        $message = "Dear $name, your verification request has been $new_status.";
        send_mail($email, $subject, $message);
    }
    // Redirect back
    $_SESSION['admin_success'] = "Verification request #$id has been $new_status.";
    header("Location: admin_dashboard.php?page=verification_requests");
    exit();
}
?>