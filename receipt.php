<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$payment_id = intval($_POST['payment_id'] ?? 0);

// Fetch payment details
$stmt = $conn->prepare("
    SELECT p.*, u.username, u.email, u.phone, pr.property_name, pr.address 
    FROM payments p
    JOIN users u ON p.tenant_id = u.id
    JOIN properties pr ON p.property_id = pr.id
    WHERE p.id = ? AND p.tenant_id = ?
");
$stmt->bind_param("ii", $payment_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

if (!$payment) {
    die("Receipt not found.");
}

$status_color = [
    'paid' => '#22c55e',
    'pending' => '#f59e0b',
    'failed' => '#ef4444'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            padding: 20px;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .receipt-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 40px;
            text-align: center;
        }
        .receipt-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .receipt-body {
            padding: 40px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            margin: 20px 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
        }
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }
        .amount-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin: 30px 0;
        }
        .amount-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .amount-value {
            font-size: 48px;
            color: #667eea;
            font-weight: bold;
        }
        .divider {
            height: 1px;
            background: #e0e0e0;
            margin: 30px 0;
        }
        .buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }
        .btn-primary {
            background: #667eea;
            color: #fff;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-secondary {
            background: #fff;
            color: #667eea;
            border: 2px solid #667eea;
        }
        .btn-secondary:hover {
            background: #f8f9fa;
        }
        .footer {
            text-align: center;
            padding: 30px;
            color: #666;
            font-size: 14px;
            border-top: 1px solid #e0e0e0;
        }
        @media print {
            body { background: #fff; }
            .buttons { display: none; }
            .receipt-container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>🏠 HousingHub</h1>
            <p>Payment Receipt</p>
        </div>
        
        <div class="receipt-body">
            <div style="text-align: center;">
                <span class="status-badge" style="background: <?= $status_color[$payment['status']] ?>; color: #fff;">
                    <?= strtoupper($payment['status']) ?>
                </span>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Transaction Reference</div>
                    <div class="info-value"><?= htmlspecialchars($payment['transaction_ref']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Payment Date</div>
                    <div class="info-value"><?= date('M d, Y h:i A', strtotime($payment['date'])) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tenant Name</div>
                    <div class="info-value"><?= htmlspecialchars($payment['username']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Payment Method</div>
                    <div class="info-value"><?= ucwords(str_replace('_', ' ', $payment['payment_method'])) ?></div>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <div class="info-item" style="margin-bottom: 20px;">
                <div class="info-label">Property Details</div>
                <div class="info-value"><?= htmlspecialchars($payment['property_name']) ?></div>
                <div style="color: #666; font-size: 14px; margin-top: 5px;">
                    <?= htmlspecialchars($payment['address']) ?>
                </div>
            </div>
            
            <div class="amount-section">
                <div class="amount-label">Amount Paid</div>
                <div class="amount-value">UGX <?= number_format($payment['amount']) ?></div>
            </div>
            
            <div class="divider"></div>
            
            <div class="buttons">
                <button class="btn btn-primary" onclick="window.print()">
                    🖨️ Print Receipt
                </button>
                <a href="dashboard.php" class="btn btn-secondary">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>Thank you for your payment!</strong></p>
            <p>For any queries, contact support@housinghub.com</p>
            <p style="margin-top: 10px; font-size: 12px;">
                This is a computer-generated receipt and does not require a signature.
            </p>
        </div>
    </div>
</body>
</html>