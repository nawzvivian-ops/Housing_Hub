<?php
session_start();
include "db_connect.php";
include "config.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$tenant_id   = intval($_SESSION['user_id']);
$property_id = intval($_POST['property_id'] ?? 0);
$method      = $_POST['method'] ?? '';
$action      = $_POST['action'] ?? 'rent';

// Validate inputs
if ($property_id <= 0) {
    die("Invalid property ID.");
}

if (!in_array($method, ['mobile_money', 'card', 'bank'])) {
    die("Invalid payment method.");
}

// Fetch property details
$stmt = $conn->prepare("SELECT rent_amount, property_name FROM properties WHERE id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();

if (!$property) {
    die("Property not found.");
}

// Fetch user details (for payment)
$stmt = $conn->prepare("SELECT fullname, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

$amount = $property['rent_amount'];
$transaction_ref = 'TXN' . time() . rand(1000, 9999);

// Insert payment record with the selected method
$stmt = $conn->prepare("
    INSERT INTO payments (tenant_id, property_id, amount, payment_method, transaction_ref, status, date)
    VALUES (?, ?, ?, ?, ?, 'pending', NOW())
");
$stmt->bind_param("iidss", $tenant_id, $property_id, $amount, $method, $transaction_ref);

if (!$stmt->execute()) {
    die("Payment record creation failed: " . $conn->error);
}

$payment_id = $conn->insert_id;

// Process payment based on method
if ($method == 'mobile_money') {
    // Redirect to mobile money payment page
    header("Location: mobile_money_payment.php?payment_id=" . $payment_id);
    exit();
    
} elseif ($method == 'card') {
    // ✅ FIXED: Store payment data in session and redirect to card payment
    $_SESSION['card_payment_id'] = $payment_id;
    
    header("Location: flutterwave_payment.php?payment_id=" . $payment_id);
    exit();
    
} elseif ($method == 'bank') {
    // Show bank transfer details
    header("Location: bank_transfer.php?payment_id=" . $payment_id);
    exit();
}
?>