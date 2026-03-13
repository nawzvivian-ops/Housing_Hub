<?php
session_start();
include "db_connect.php";
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$payment_id = intval($_POST['payment_id'] ?? 0);

// Handle manual confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_payment'])) {
    $stmt = $conn->prepare("UPDATE payments SET status = 'paid', updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    
    // Get payment and user details for SMS
    $stmt = $conn->prepare("
        SELECT p.*, u.username, u.phone, u.email, pr.property_name 
        FROM payments p
        JOIN users u ON p.tenant_id = u.id
        JOIN properties pr ON p.property_id = pr.id
        WHERE p.id = ?
    ");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $payment_data = $stmt->get_result()->fetch_assoc();
    
    // Send SMS confirmation
    if (!empty($payment_data['phone'])) {
        sendSMS(
            $payment_data['phone'], 
            "Payment Confirmed! Your rent of UGX " . number_format($payment_data['amount']) . 
            " for " . $payment_data['property_name'] . " has been received. Ref: " . $payment_data['transaction_ref']
        );
    }
    
    header("Location: receipt.php?payment_id=" . $payment_id);
    exit();
}

// Handle cancellation
if (isset($_POST['cancel'])) {
    $stmt = $conn->prepare("UPDATE payments SET status = 'failed' WHERE id = ?");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    
    header("Location: payment_method.php?property_id=" . $payment['property_id'] . "&action=rent&error=cancelled");
    exit();
}

$stmt = $conn->prepare("
    SELECT p.*, pr.property_name 
    FROM payments p
    JOIN properties pr ON p.property_id = pr.id
    WHERE p.id = ? AND p.tenant_id = ?
");
$stmt->bind_param("ii", $payment_id, $_SESSION['user_id']);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    die("Payment not found.");
}

// Function to send SMS
function sendSMS($phone, $message) {
    $username = AFRICASTALKING_USERNAME;
    $apiKey = AFRICASTALKING_API_KEY;
    
    // Use sandbox for testing
    $url = 'https://api.sandbox.africastalking.com/version1/messaging';
    
    $data = [
        'username' => $username,
        'to' => $phone,
        'message' => $message,
        'from' => 'HousingHub' // Optional shortcode
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apiKey: ' . $apiKey,
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log the response for debugging
    error_log("SMS Response: " . $response . " | HTTP Code: " . $httpCode);
    
    return json_decode($response, true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Payment</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .box {
            max-width: 500px;
            width: 100%;
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 {
            color: #333;
            margin: 20px 0 10px;
            font-size: 24px;
        }
        p {
            color: #666;
            margin: 10px 0;
            line-height: 1.6;
        }
        .amount-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .amount-box strong {
            color: #667eea;
            font-size: 28px;
        }
        .btn {
            padding: 15px 30px;
            background: #22c55e;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin: 10px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #16a34a;
            transform: translateY(-2px);
        }
        .btn-cancel {
            background: #ef4444;
        }
        .btn-cancel:hover {
            background: #dc2626;
        }
        .instructions {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: left;
        }
        .instructions h3 {
            color: #92400e;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .instructions ol {
            color: #92400e;
            font-size: 14px;
            padding-left: 20px;
        }
        .instructions li {
            margin: 8px 0;
        }
        .note {
            font-size: 12px;
            color: #999;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        .property-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        .property-info p {
            margin: 8px 0;
            color: #555;
            font-size: 14px;
        }
        .property-info strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="box">
        <div class="spinner"></div>
        <h2>⏳ Waiting for Payment...</h2>
        <p>Check your phone for the payment prompt</p>
        
        <div class="property-info">
            <p><strong>Property:</strong> <?= htmlspecialchars($payment['property_name']) ?></p>
            <p><strong>Transaction Ref:</strong> <?= htmlspecialchars($payment['transaction_ref']) ?></p>
        </div>
        
        <div class="amount-box">
            <p style="color: #666; font-size: 14px; margin-bottom: 5px;">Amount to Pay</p>
            <strong>UGX <?= number_format($payment['amount']) ?></strong>
        </div>
        
        <div class="instructions">
            <h3>📱 Complete Payment on Your Phone:</h3>
            <ol>
                <li>Check for a payment prompt on your mobile phone</li>
                <li>Verify the amount (UGX <?= number_format($payment['amount']) ?>)</li>
                <li>Enter your Mobile Money PIN</li>
                <li>Wait for confirmation SMS</li>
                <li>Click "I've Completed Payment" below</li>
            </ol>
        </div>
        
        <form method="POST">
            <button type="submit" name="confirm_payment" class="btn">
                ✓ I've Completed Payment
            </button>
        </form>
        
        <a href="?payment_id=<?= $payment_id ?>&cancel=1" style="text-decoration: none;">
            <button type="button" class="btn btn-cancel">
                ✗ Cancel Payment
            </button>
        </a>
        
        <div class="note">
            <p><strong>Note:</strong> This is a demo environment. In production, payment verification would happen automatically via API callback from the payment provider.</p>
            <p style="margin-top: 10px;">If you didn't receive a payment prompt, please try again or contact support.</p>
        </div>
    </div>
    
    <script>
        // Auto-refresh every 10 seconds to check payment status (optional)
        // Uncomment for production with webhook integration
        /*
        setTimeout(function() {
            window.location.reload();
        }, 10000);
        */
    </script>
</body>
</html>