<?php
session_start();

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$tenant_id = intval($_SESSION['user_id']);
$property_id = intval($_POST['property_id'] ?? 0);
$method = $_POST['method'] ?? '';
$action = $_POST['action'] ?? 'rent';

if ($property_id <= 0) {
    die("Invalid property ID.");
}

if (!in_array($method, ['mobile_money', 'card', 'bank'], true)) {
    die("Invalid payment method.");
}

if (!in_array($action, ['rent', 'buy', 'lease'], true)) {
    $action = 'rent';
}

$stmt = $conn->prepare("SELECT rent_amount, property_name FROM properties WHERE id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    die("Property not found.");
}

$amount = (float)$property['rent_amount'];
$transaction_ref = 'HH-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();
$payment_response = json_encode([
    'provider' => ($method === 'bank') ? 'bank_transfer' : 'pesapal',
    'action' => $action,
    'created_from' => 'checkout',
]);

$stmt = $conn->prepare("
    INSERT INTO payments
        (tenant_id, property_id, amount, payment_method, transaction_ref, payment_response, status, date)
    VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
");

$stmt->bind_param("iidsss", $tenant_id, $property_id, $amount, $method, $transaction_ref, $payment_response);
if (!$stmt->execute()) {
    die("Payment record creation failed: " . htmlspecialchars($conn->error));
}

$payment_id = $conn->insert_id;

if ($method === 'bank') {
    header("Location: bank_transfer.php?payment_id=" . $payment_id);
    exit();
}

header("Location: pesapal_payment.php?payment_id=" . $payment_id);
exit();
?>
