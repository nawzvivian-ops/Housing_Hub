<?php
session_start();
include "db_connect.php";

// Ensure user is logged in and the form was actually submitted
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$payment_id     = intval($_POST['payment_id']);
$network        = $_POST['network'] ?? '';
$transaction_id = $_POST['transaction_id'] ?? ''; // New field from the manual form

// 1. Fetch payment and verify ownership
$stmt = $conn->prepare("SELECT * FROM payments WHERE id = ? AND tenant_id = ?");
$stmt->bind_param("ii", $payment_id, $_SESSION['user_id']);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    die("Payment record not found.");
}

// 2. Validation: Ensure Transaction ID isn't empty
if (empty($transaction_id)) {
    die("Please provide the Transaction ID from your SMS.");
}

// 3. Update the payment record for manual admin verification.
$status = 'pending_verification';

$stmt = $conn->prepare("UPDATE payments SET transaction_ref = ?, status = ?, payment_response = ? WHERE id = ?");

// We still keep the JSON response for your logs/history
$response_data = json_encode([
    'network'        => $network,
    'transaction_id' => $transaction_id,
    'submitted_at'   => date("Y-m-d H:i:s")
]);

$stmt->bind_param("sssi", $transaction_id, $status, $response_data, $payment_id);

if ($stmt->execute()) {
    // 4. Redirect to a success page that tells the user to wait for admin approval
    header("Location: payment_success.php?payment_id=" . $payment_id . "&method=mobile_money");
    exit();
} else {
    echo "Error updating record: " . $conn->error;
}
?>
