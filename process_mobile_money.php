<?php
session_start();
include "db_connect.php";
// include "config.php"; // Uncomment if you have specific API keys here

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$payment_id = intval($_POST['payment_id']);
$network    = $_POST['network'] ?? '';
$phone      = $_POST['phone'] ?? '';

// 1. Fetch payment and verify ownership
$stmt = $conn->prepare("SELECT * FROM payments WHERE id = ? AND tenant_id = ?");
$stmt->bind_param("ii", $payment_id, $_SESSION['user_id']);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    die("Payment not found or access denied.");
}

// 2. Define the receivers 
$mtn_receiver    = "256764700087";
$airtel_receiver = "256741035928";
$active_receiver = ($network === 'mtn') ? $mtn_receiver : $airtel_receiver;

// 3. Update the payment record
// We store the user's phone, the network they used, and the receiver number they were assigned
$stmt = $conn->prepare("UPDATE payments SET payment_response = ?, status = 'Pending' WHERE id = ?");

$response_data = json_encode([
    'network'         => $network,
    'sender_phone'    => $phone,
    'receiver_number' => $active_receiver,
    'timestamp'       => date("Y-m-d H:i:s")
]);

$stmt->bind_param("si", $response_data, $payment_id);
$stmt->execute();

// 4. Redirect to the confirmation/verification page
header("Location: mobile_money_confirm.php?payment_id=" . $payment_id);
exit();
?>