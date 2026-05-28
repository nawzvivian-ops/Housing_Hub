<?php
session_start();

// Compatibility endpoint for older payment links. New live payments verify through
// pesapal_callback.php and pesapal_ipn.php.
$payment_id = intval($_GET['payment_id'] ?? 0);
$order_tracking_id = $_GET['OrderTrackingId'] ?? $_GET['orderTrackingId'] ?? '';
$merchant_reference = $_GET['OrderMerchantReference'] ?? $_GET['orderMerchantReference'] ?? '';

$query = [];
if ($payment_id > 0) {
    $query['payment_id'] = $payment_id;
}
if ($order_tracking_id !== '') {
    $query['OrderTrackingId'] = $order_tracking_id;
}
if ($merchant_reference !== '') {
    $query['OrderMerchantReference'] = $merchant_reference;
}

if (empty($query)) {
    die("Invalid verification request.");
}

header("Location: pesapal_callback.php?" . http_build_query($query));
exit();
?>
