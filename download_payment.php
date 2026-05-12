<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$payment_id = intval($_GET['id'] ?? 0);
if ($payment_id <= 0) {
    die("Invalid payment.");
}

$stmt = $conn->prepare("
    SELECT p.*, u.fullname, u.email, pr.property_name, pr.address
    FROM payments p
    LEFT JOIN users u ON p.tenant_id = u.id
    LEFT JOIN properties pr ON p.property_id = pr.id
    WHERE p.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    die("Receipt not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 24px; color: #172033; }
        .receipt { max-width: 760px; margin: 0 auto; background: #fff; border: 1px solid #dde3ee; border-radius: 10px; overflow: hidden; }
        .head { background: #0b145a; color: #fff; padding: 28px; text-align: center; }
        .head h1 { margin: 0 0 6px; }
        .body { padding: 28px; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .item { background: #f8fafc; border: 1px solid #edf1f7; border-radius: 8px; padding: 14px; }
        .label { font-size: 12px; text-transform: uppercase; color: #667085; margin-bottom: 6px; }
        .value { font-weight: 700; }
        .amount { margin: 24px 0; padding: 20px; text-align: center; background: #fff7db; border-radius: 8px; }
        .amount strong { display: block; font-size: 34px; color: #0b145a; margin-top: 6px; }
        .actions { display: flex; gap: 12px; margin-top: 24px; }
        .btn { flex: 1; padding: 12px 16px; border-radius: 8px; text-align: center; text-decoration: none; font-weight: 700; border: 0; cursor: pointer; }
        .primary { background: #cdaa3b; color: #071127; }
        .secondary { background: #eef2f7; color: #172033; }
        @media print { .actions { display: none; } body { background: #fff; } .receipt { border: 0; } }
    </style>
</head>
<body>
    <main class="receipt">
        <section class="head">
            <h1>HousingHub</h1>
            <div>Payment Receipt</div>
        </section>
        <section class="body">
            <div class="grid">
                <div class="item"><div class="label">Receipt No.</div><div class="value">#<?= htmlspecialchars($payment['id']) ?></div></div>
                <div class="item"><div class="label">Status</div><div class="value"><?= htmlspecialchars(ucfirst($payment['status'])) ?></div></div>
                <div class="item"><div class="label">Tenant</div><div class="value"><?= htmlspecialchars($payment['fullname'] ?? 'N/A') ?></div></div>
                <div class="item"><div class="label">Email</div><div class="value"><?= htmlspecialchars($payment['email'] ?? 'N/A') ?></div></div>
                <div class="item"><div class="label">Property</div><div class="value"><?= htmlspecialchars($payment['property_name'] ?? 'N/A') ?></div></div>
                <div class="item"><div class="label">Date</div><div class="value"><?= htmlspecialchars($payment['date']) ?></div></div>
                <div class="item"><div class="label">Method</div><div class="value"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $payment['payment_method'] ?? 'N/A'))) ?></div></div>
                <div class="item"><div class="label">Reference</div><div class="value"><?= htmlspecialchars($payment['transaction_ref'] ?? 'N/A') ?></div></div>
            </div>
            <div class="amount">
                Amount Paid
                <strong>UGX <?= number_format((float)$payment['amount'], 2) ?></strong>
            </div>
            <div class="actions">
                <button class="btn primary" onclick="window.print()">Print / Save PDF</button>
                <a class="btn secondary" href="payment_history.php">Back</a>
            </div>
        </section>
    </main>
</body>
</html>
