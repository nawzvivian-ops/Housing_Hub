<?php
session_start();
include "db_connect.php";
include "config.php";

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$payment_id = intval($_POST['payment_id']);
$network = $_POST['network'];
$phone = $_POST['phone'];

// Fetch payment
$stmt = $conn->prepare("SELECT * FROM payments WHERE id = ? AND tenant_id = ?");
$stmt->bind_param("ii", $payment_id, $_SESSION['user_id']);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    die("Payment not found");
}

// Here you would integrate with MTN/Airtel API
// For now, we'll simulate and redirect to manual confirmation

// Update payment with phone number
$stmt = $conn->prepare("UPDATE payments SET payment_response = ? WHERE id = ?");
$response_data = json_encode(['network' => $network, 'phone' => $phone]);
$stmt->bind_param("si", $response_data, $payment_id);
$stmt->execute();

// Redirect to confirmation page
header("Location: mobile_money_confirm.php?payment_id=" . $payment_id);
exit();
?>