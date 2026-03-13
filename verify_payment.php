<?php
session_start();
include "db_connect.php";
include "config.php";

$transaction_id = $_GET['transaction_id'] ?? '';
$payment_id = intval($_GET['payment_id'] ?? 0);

if (!$transaction_id || !$payment_id) {
    die("Invalid verification request");
}

// Verify payment with Flutterwave
$url = "https://api.flutterwave.com/v3/transactions/{$transaction_id}/verify";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . FLUTTERWAVE_SECRET_KEY
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

// Check if payment was successful
if ($result['status'] == 'success' && $result['data']['status'] == 'successful') {
    
    // Update payment status in database
    $stmt = $conn->prepare("
        UPDATE payments 
        SET status = 'paid', 
            payment_response = ?,
            updated_at = NOW() 
        WHERE id = ?
    ");
    $payment_response = json_encode($result['data']);
    $stmt->bind_param("si", $payment_response, $payment_id);
    $stmt->execute();
    
    // Get payment and user details
    $stmt = $conn->prepare("
        SELECT p.*, u.username, u.phone, u.email, pr.property_name 
        FROM payments p
        JOIN users u ON p.tenant_id = u.id
        JOIN properties pr ON p.property_id = pr.id
        WHERE p.id = ?
    ");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    
    // Send SMS notification
    sendSMS($payment['phone'], 
        "Payment Successful! Your rent of UGX " . number_format($payment['amount']) . 
        " for " . $payment['property_name'] . " has been received. Ref: " . $payment['transaction_ref']
    );
    
    // Redirect to receipt page
    header("Location: receipt.php?payment_id=" . $payment_id);
    exit();
    
} else {
    // Payment failed
    $stmt = $conn->prepare("UPDATE payments SET status = 'failed' WHERE id = ?");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    
    die("Payment verification failed. Please contact support.");
}

// Function to send SMS
function sendSMS($phone, $message) {
    $username = AFRICASTALKING_USERNAME;
    $apiKey = AFRICASTALKING_API_KEY;
    $url = 'https://api.africastalking.com/version1/messaging';
    
    $data = [
        'username' => $username,
        'to' => $phone,
        'message' => $message
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apiKey: ' . $apiKey,
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}
?>