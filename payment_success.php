<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Using POST from your payment processor or GET if redirecting
$payment_id = intval($_REQUEST['payment_id'] ?? 0);
$method = htmlspecialchars($_REQUEST['method'] ?? 'mobile_money');

$stmt = $conn->prepare("
    SELECT p.*, pr.property_name, pr.address 
    FROM payments p
    JOIN properties pr ON p.property_id = pr.id
    WHERE p.id = ? AND p.tenant_id = ?
");
$stmt->bind_param("ii", $payment_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

if (!$payment) {
    die("Payment record not found. Please contact support if your money was deducted.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Received | HousingHub</title>
    <style>
        :root {
            --primary: #1e293b;
            --accent: #d4af37; /* Gold */
            --success: #10b981;
            --warning: #f59e0b;
            --bg: #f1f5f9;
        }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background-color: var(--bg);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .receipt-card {
            background: white;
            width: 100%;
            max-width: 450px;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }
        .status-header {
            background: var(--primary);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .icon-circle {
            width: 70px;
            height: 70px;
            background: rgba(16, 185, 129, 0.2);
            border: 2px solid var(--success);
            color: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 15px;
        }
        .receipt-body {
            padding: 30px;
        }
        .amount-display {
            text-align: center;
            margin-bottom: 25px;
        }
        .amount-display h1 {
            margin: 0;
            font-size: 28px;
            color: var(--primary);
        }
        .amount-display p {
            margin: 5px 0 0;
            color: #64748b;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }
        .info-grid {
            border-top: 1px dashed #e2e8f0;
            padding-top: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }
        .info-label { color: #94a3b8; }
        .info-value { color: var(--primary); font-weight: 600; text-align: right; }
        
        .verification-notice {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            padding: 15px;
            border-radius: 12px;
            margin-top: 20px;
            display: flex;
            gap: 12px;
        }
        .notice-icon { font-size: 20px; }
        .notice-text { font-size: 13px; color: #92400e; line-height: 1.5; }

        .actions {
            padding: 20px 30px 30px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
            text-align: center;
            padding: 14px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-primary:hover { opacity: 0.9; }
        .btn-outline {
            text-align: center;
            padding: 12px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="receipt-card">
    <div class="status-header">
        <div class="icon-circle">✓</div>
        <h2 style="margin:0">Payment Received</h2>
        <p style="opacity:0.8; font-size: 14px; margin-top:5px">Transaction is now being verified</p>
    </div>

    <div class="receipt-body">
        <div class="amount-display">
            <p>Total Amount Paid</p>
            <h1>UGX <?= number_format($payment['amount']) ?></h1>
        </div>

        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Reference ID</span>
                <span class="info-value"><?= htmlspecialchars($payment['transaction_ref']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Property</span>
                <span class="info-value"><?= htmlspecialchars($payment['property_name']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Mode</span>
                <span class="info-value"><?= ucwords(str_replace('_', ' ', $method)) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Date & Time</span>
                <span class="info-value"><?= date('d M Y, h:i A', strtotime($payment['date'])) ?></span>
            </div>
        </div>

        <div class="verification-notice">
            <span class="notice-icon">⏳</span>
            <div class="notice-text">
                <strong>Awaiting Verification:</strong> Our administrators are currently matching this transaction with the mobile money network records. Your status will update to "Paid" once confirmed.
            </div>
        </div>
    </div>

    <div class="actions">
        <a href="dashboard.php" class="btn-primary">Return to Dashboard</a>
        <a href="javascript:window.print()" class="btn-outline">Download PDF Receipt</a>
    </div>
</div>

</body>
</html>