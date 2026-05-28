<?php
include "db_connect.php";
require_once "payment_receipt_helper.php";
require_once "tenant_activation_helper.php";

$id = intval($_GET['id']);

// Update status to 'paid'
$stmt = $conn->prepare("UPDATE payments SET status = 'paid' WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    hh_activate_tenant_for_paid_payment($conn, $id);
    send_payment_receipt_email($conn, $id);
    header("Location: admin_verify.php?msg=Verified");
}
?>
