<?php
session_start();

$payment_id = intval($_GET['payment_id'] ?? $_SESSION['card_payment_id'] ?? 0);
if ($payment_id <= 0) {
    die("Invalid payment request.");
}

header("Location: pesapal_payment.php?payment_id=" . $payment_id);
exit();
?>
