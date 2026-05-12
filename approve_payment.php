<?php
include "db_connect.php";
require_once "payment_receipt_helper.php";

$id = intval($_GET['id']);

// Update status to 'paid'
$stmt = $conn->prepare("UPDATE payments SET status = 'paid' WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    send_payment_receipt_email($conn, $id);
    header("Location: admin_verify.php?msg=Verified");
}
?>
