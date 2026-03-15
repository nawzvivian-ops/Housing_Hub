
<?php
session_start();
include "db_connect.php";
include "config.php";
 
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
$action      = $_POST['action'] ?? 'rent'; // rent | buy | lease
 
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
$property = $stmt->get_result()->fetch_assoc();
if (!$property) {
    die("Property not found.");
}
 
// Fetch user details
$stmt = $conn->prepare("SELECT fullname, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) {
    die("User not found.");
}
 
$amount          = $property['rent_amount'];
$transaction_ref = 'TXN' . time() . rand(1000, 9999);
 
// ── INSERT PAYMENT RECORD ──
$stmt = $conn->prepare("
    INSERT INTO payments (tenant_id, property_id, amount, payment_method, transaction_ref, status, date)
    VALUES (?, ?, ?, ?, ?, 'pending', NOW())
");
$stmt->bind_param("iidss", $tenant_id, $property_id, $amount, $method, $transaction_ref);
if (!$stmt->execute()) {
    die("Payment record creation failed: " . $conn->error);
}
$payment_id = $conn->insert_id;
 
// ════════════════════════════════════════════════════════════
//  AUTO-TENANT CREATION
//  When a user pays to rent/buy/lease a property, they
//  automatically become a managed tenant and get dashboard
//  access — no admin linking required.
// ════════════════════════════════════════════════════════════
 
// Check if this user already has a tenant record
$chk = $conn->prepare("SELECT id FROM tenants WHERE user_id = ? LIMIT 1");
$chk->bind_param("i", $tenant_id);
$chk->execute();
$existing_tenant = $chk->get_result()->fetch_assoc();
 
if (!$existing_tenant) {
    // No tenant record yet — create one now and link their account
 
    // Work out lease dates based on action type
    $lease_start = date('Y-m-d');                          // today
    $lease_end   = match($action) {
        'buy'   => null,                                   // purchase — no end date
        'lease' => date('Y-m-d', strtotime('+1 year')),   // lease — 1 year default
        default => date('Y-m-d', strtotime('+1 month')),  // rent  — 1 month default
    };
 
    $ins = $conn->prepare("
        INSERT INTO tenants (
            fullname, email, phone,
            property_id, user_id,
            lease_start, lease_end,
            status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Active', NOW())
    ");
    $ins->bind_param(
        "sssiiiss",
        $user['fullname'],
        $user['email'],
        $user['phone'],
        $property_id,
        $tenant_id,
        $lease_start,
        $lease_end
    );
    $ins->execute();
 
    // Store in session so dashboard knows it's a fresh tenant
    $_SESSION['new_tenant'] = true;
 
} else {
    // Tenant record exists — update property if they moved / new deal
    $upd = $conn->prepare("
        UPDATE tenants
        SET property_id = ?,
            status      = 'Active'
        WHERE user_id = ?
    ");
    $upd->bind_param("ii", $property_id, $tenant_id);
    $upd->execute();
}
 
// Also update the users role to 'tenant' if it isn't already
// so Gate 2 on the dashboard passes correctly
$conn->query("
    UPDATE users SET role = 'tenant'
    WHERE id = '$tenant_id' AND role NOT IN ('admin','staff')
");
 
// ════════════════════════════════════════════════════════════
//  REDIRECT TO PAYMENT PROCESSOR
// ════════════════════════════════════════════════════════════
 
if ($method === 'mobile_money') {
    header("Location: mobile_money_payment.php?payment_id=" . $payment_id);
    exit();
 
} elseif ($method === 'card') {
    $_SESSION['card_payment_id'] = $payment_id;
    header("Location: flutterwave_payment.php?payment_id=" . $payment_id);
    exit();
 
} elseif ($method === 'bank') {
    header("Location: bank_transfer.php?payment_id=" . $payment_id);
    exit();
}
?>