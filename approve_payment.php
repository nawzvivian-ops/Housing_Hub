<?php
include "db_connect.php";

$id = intval($_GET['id']);

// Update status to 'paid'
$stmt = $conn->prepare("UPDATE payments SET status = 'paid' WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Optional: Send Africa's Talking SMS here to notify the user!
    header("Location: admin_verify.php?msg=Verified");
}
?>