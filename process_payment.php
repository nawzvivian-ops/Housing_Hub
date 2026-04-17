<?php
session_start();
include "db_connect.php";
include "config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ── AUTH CHECK ────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

// ── INPUTS ────────────────────────────────────────────────────
$tenant_id   = intval($_SESSION['user_id']);
$property_id = intval($_POST['property_id'] ?? 0);
$method      = $_POST['method'] ?? '';
$action      = $_POST['action'] ?? 'rent';

if ($property_id <= 0) {
    die("Invalid property ID.");
}
if (!in_array($method, ['mobile_money', 'card', 'bank'])) {
    die("Invalid payment method.");
}

// ── FETCH PROPERTY ────────────────────────────────────────────
$stmt = $conn->prepare("SELECT rent_amount, property_name FROM properties WHERE id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();
if (!$property) {
    die("Property not found.");
}

// ── FETCH USER ────────────────────────────────────────────────
$stmt = $conn->prepare("SELECT fullname, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) {
    die("User not found.");
}

// ── SAFE USER FIELDS — bind_param cannot handle NULL ─────────
$fullname = (string)($user['fullname'] ?? '');
$email    = (string)($user['email']    ?? '');
$phone    = (string)($user['phone']    ?? '');

// ── INSERT PAYMENT RECORD ─────────────────────────────────────
$amount          = (float)$property['rent_amount'];
$transaction_ref = 'TXN' . time() . rand(1000, 9999);

$stmt = $conn->prepare("
    INSERT INTO payments
        (tenant_id, property_id, amount, payment_method, transaction_ref, status, date)
    VALUES (?, ?, ?, ?, ?, 'pending', NOW())
");
$stmt->bind_param("iidss", $tenant_id, $property_id, $amount, $method, $transaction_ref);
if (!$stmt->execute()) {
    die("Payment record creation failed: " . $conn->error);
}
$payment_id = $conn->insert_id;

// ── AUTO-TENANT CREATION ──────────────────────────────────────
$chk = $conn->prepare("SELECT id FROM tenants WHERE user_id = ? LIMIT 1");
$chk->bind_param("i", $tenant_id);
$chk->execute();
$existing_tenant = $chk->get_result()->fetch_assoc();

if (!$existing_tenant) {

    $lease_start = date('Y-m-d');

    if ($action === 'buy') {
        // lease_end = NULL — hardcoded in SQL so no null bind needed
        // 6 placeholders, 6 type chars: s s s i i s
        $ins = $conn->prepare("
            INSERT INTO tenants
                (fullname, email, phone, property_id, user_id,
                 lease_start, lease_end, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NULL, 'Active', NOW())
        ");
        $ins->bind_param("sssiis",
            $fullname, $email, $phone,
            $property_id, $tenant_id,
            $lease_start
        );

    } else {
        // lease_end is a real date string — safe to bind
        // 7 placeholders, 7 type chars: s s s i i s s
        $lease_end = ($action === 'lease')
            ? date('Y-m-d', strtotime('+1 year'))
            : date('Y-m-d', strtotime('+1 month'));

        $ins = $conn->prepare("
            INSERT INTO tenants
                (fullname, email, phone, property_id, user_id,
                 lease_start, lease_end, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Active', NOW())
        ");
        $ins->bind_param("sssiiss",
            $fullname, $email, $phone,
            $property_id, $tenant_id,
            $lease_start, $lease_end
        );
    }

    if (!$ins->execute()) {
        die("Tenant creation failed: " . $conn->error);
    }

    $_SESSION['new_tenant'] = true;

} else {
    $upd = $conn->prepare("UPDATE tenants SET property_id = ?, status = 'Active' WHERE user_id = ?");
    $upd->bind_param("ii", $property_id, $tenant_id);
    $upd->execute();
}

// ── UPDATE USER ROLE ──────────────────────────────────────────
$conn->query("
    UPDATE users SET role = 'tenant'
    WHERE id = $tenant_id
      AND role NOT IN ('admin','staff','broker','propertyowner')
");

// ── REDIRECT ──────────────────────────────────────────────────
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