<?php
session_start();

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/pesapal_helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$payment_id = intval($_GET['payment_id'] ?? $_POST['payment_id'] ?? 0);
if ($payment_id <= 0) {
    die("Invalid payment request.");
}

$stmt = $conn->prepare("
    SELECT p.*, u.fullname, u.email, u.phone, pr.property_name, pr.address
    FROM payments p
    JOIN users u ON p.tenant_id = u.id
    JOIN properties pr ON p.property_id = pr.id
    WHERE p.id = ? AND p.tenant_id = ?
    LIMIT 1
");
if (!$stmt) {
    die("Database error: " . htmlspecialchars($conn->error));
}

$user_id = intval($_SESSION['user_id']);
$stmt->bind_param("ii", $payment_id, $user_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    die("Payment record not found.");
}

if (strtolower((string)$payment['status']) === 'paid') {
    header("Location: receipt.php?payment_id=" . $payment_id);
    exit();
}

try {
    $token = pesapal_get_token();
    $notification_id = pesapal_get_notification_id($token);

    $response_data = hh_decode_payment_response($payment['payment_response'] ?? '');
    $action = $response_data['action'] ?? 'rent';
    $name = trim((string)($payment['fullname'] ?? 'HousingHub Customer'));
    $name_parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
    $first_name = $name_parts[0] ?? 'Customer';
    $last_name = count($name_parts) > 1 ? end($name_parts) : '';

    $description = substr('HousingHub ' . ucfirst($action) . ' payment for ' . $payment['property_name'], 0, 100);
    $callback_url = hh_app_url('pesapal_callback.php', ['payment_id' => $payment_id]);
    $cancellation_url = hh_app_url('payment_method.php', [
        'property_id' => $payment['property_id'],
        'action' => $action,
    ]);

    $order = [
        'id' => $payment['transaction_ref'],
        'currency' => CURRENCY,
        'amount' => (float)$payment['amount'],
        'description' => $description,
        'callback_url' => $callback_url,
        'cancellation_url' => $cancellation_url,
        'redirect_mode' => 'TOP_WINDOW',
        'notification_id' => $notification_id,
        'branch' => SITE_NAME,
        'billing_address' => [
            'email_address' => (string)($payment['email'] ?? ''),
            'phone_number' => (string)($payment['phone'] ?? ''),
            'country_code' => 'UG',
            'first_name' => $first_name,
            'middle_name' => '',
            'last_name' => $last_name,
            'line_1' => (string)($payment['address'] ?? ''),
            'line_2' => '',
            'city' => 'Kampala',
            'state' => '',
            'postal_code' => '',
            'zip_code' => '',
        ],
    ];

    $pesapal_order = pesapal_submit_order($token, $order);

    hh_merge_payment_response($conn, $payment_id, [
        'provider' => 'pesapal',
        'action' => $action,
        'pesapal_order' => $pesapal_order,
        'pesapal_order_tracking_id' => $pesapal_order['order_tracking_id'],
        'submitted_at' => date('c'),
    ], 'pending');

    header("Location: " . $pesapal_order['redirect_url']);
    exit();
} catch (Exception $e) {
    http_response_code(500);
    $message = htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Setup Error | HousingHub</title>
<style>
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#111827;margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.box{max-width:560px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:28px;box-shadow:0 18px 45px rgba(15,23,42,.12)}
h1{font-size:22px;margin:0 0 12px;color:#991b1b}
p{line-height:1.5;color:#374151}
a{display:inline-block;margin-top:12px;color:#0f172a;font-weight:700}
</style>
</head>
<body>
<div class="box">
  <h1>Payment could not start</h1>
  <p><?php echo $message; ?></p>
  <p>Check your live Pesapal keys and public site URL in <strong>config.php</strong>.</p>
  <a href="payment_method.php?property_id=<?php echo intval($payment['property_id']); ?>&action=<?php echo urlencode($action ?? 'rent'); ?>">Back to payment methods</a>
</div>
</body>
</html>
