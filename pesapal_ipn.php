<?php

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/pesapal_helper.php';

header('Content-Type: application/json');

$input = pesapal_notification_input();
$order_tracking_id = $input['OrderTrackingId'] ?? $input['orderTrackingId'] ?? '';
$merchant_reference = $input['OrderMerchantReference'] ?? $input['orderMerchantReference'] ?? '';

$response = [
    'orderNotificationType' => $input['OrderNotificationType'] ?? $input['orderNotificationType'] ?? 'IPNCHANGE',
    'orderTrackingId' => $order_tracking_id,
    'orderMerchantReference' => $merchant_reference,
    'status' => 500,
];

try {
    if ($order_tracking_id === '' || $merchant_reference === '') {
        throw new Exception('Missing Pesapal IPN parameters.');
    }

    $payment = pesapal_payment_by_reference($conn, $merchant_reference);
    if (!$payment) {
        throw new Exception('Payment record not found.');
    }

    $token = pesapal_get_token();
    $status_response = pesapal_get_transaction_status($token, $order_tracking_id);
    pesapal_apply_status_to_payment($conn, intval($payment['id']), $order_tracking_id, $status_response);

    $response['status'] = 200;
} catch (Exception $e) {
    error_log('Pesapal IPN failed: ' . $e->getMessage());
}

echo json_encode($response);
?>
