<?php
session_start();

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/pesapal_helper.php';

$payment_id = intval($_GET['payment_id'] ?? 0);
$order_tracking_id = $_GET['OrderTrackingId'] ?? $_GET['orderTrackingId'] ?? '';
$merchant_reference = $_GET['OrderMerchantReference'] ?? $_GET['orderMerchantReference'] ?? '';

if ($order_tracking_id === '') {
    die("Missing Pesapal order tracking ID.");
}

if ($payment_id <= 0 && $merchant_reference !== '') {
    $payment = pesapal_payment_by_reference($conn, $merchant_reference);
    $payment_id = intval($payment['id'] ?? 0);
}

if ($payment_id <= 0) {
    die("Payment record not found.");
}

if (isset($_SESSION['user_id'])) {
    $payment = pesapal_payment_by_id($conn, $payment_id);
    if ($payment && intval($payment['tenant_id']) !== intval($_SESSION['user_id'])) {
        die("Payment record not found.");
    }
}

try {
    $token = pesapal_get_token();
    $status_response = pesapal_get_transaction_status($token, $order_tracking_id);
    $local_status = pesapal_apply_status_to_payment($conn, $payment_id, $order_tracking_id, $status_response);

    if ($local_status === 'paid') {
        $_SESSION['role'] = 'tenant';
        header("Location: receipt.php?payment_id=" . $payment_id);
        exit();
    }

    header("Location: payment_success.php?payment_id=" . $payment_id . "&method=pesapal");
    exit();
} catch (Exception $e) {
    http_response_code(500);
    die("Payment verification failed: " . htmlspecialchars($e->getMessage()));
}
?>
