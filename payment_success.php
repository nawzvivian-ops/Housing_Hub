<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$payment_id = intval($_POST['payment_id'] ?? 0);
$method = htmlspecialchars($_POST['method'] ?? 'N/A');

// ✅ Fixed: Using pr.address instead of pr.location
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
    die("Payment not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .success-box {
            max-width: 500px;
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #22c55e;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: #fff;
        }
        h2 { color: #22c55e; margin-bottom: 10px; }
        p { color: #666; }
        .details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 25px 0;
            text-align: left;
        }
        .details p {
            margin: 10px 0;
            color: #555;
            font-size: 15px;
        }
        .details strong { 
            color: #333;
            display: inline-block;
            min-width: 150px;
        }
        .status-pending {
            color: #f59e0b;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
            transition: background 0.3s;
            font-weight: 600;
        }
        .btn:hover { 
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .note {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin-top: 20px;
            border-radius: 8px;
            text-align: left;
        }
        .note p {
            margin: 5px 0;
            font-size: 14px;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="success-box">
        <div class="checkmark">✓</div>
        <h2>Payment Initiated!</h2>
        <p>Your payment has been recorded and is pending confirmation.</p>
        
        <div class="details">
            <p><strong>Transaction Ref:</strong> <?= htmlspecialchars($payment['transaction_ref']) ?></p>
            <p><strong>Property:</strong> <?= htmlspecialchars($payment['property_name']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($payment['address']) ?></p>
            <p><strong>Amount:</strong> UGX <?= number_format($payment['amount']) ?></p>
            <p><strong>Payment Method:</strong> <?= ucwords(str_replace('_', ' ', $method)) ?></p>
            <p><strong>Status:</strong> <span class="status-pending">Pending</span></p>
            <p><strong>Date:</strong> <?= date('M d, Y h:i A', strtotime($payment['date'])) ?></p>
        </div>
        
        <div class="note">
            <p><strong>⚠️ Next Steps:</strong></p>
            <p>
                <?php if ($method == 'mobile_money'): ?>
                    • You will receive a prompt on your phone to complete the payment<br>
                    • Enter your PIN to authorize the transaction<br>
                    • You'll receive a confirmation SMS once payment is successful
                <?php elseif ($method == 'card'): ?>
                    • You will be redirected to the payment gateway<br>
                    • Enter your card details securely<br>
                    • Payment confirmation will be sent to your email
                <?php else: ?>
                    • Bank details will be sent to your email<br>
                    • Complete the transfer using your banking app<br>
                    • Upload the payment receipt for verification
                <?php endif; ?>
            </p>
        </div>
        
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>
</body>
</html>