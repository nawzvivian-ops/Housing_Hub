<?php
session_start();
include "db_connect.php";

// 1. Safety Check: Is the user logged in?
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Process the Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get data from your mobile_money_payment.php form
    $payment_id = intval($_POST['payment_id'] ?? 0);
    $txn_id     = trim($_POST['transaction_id'] ?? '');
    $network    = $_POST['network'] ?? ''; 

    // Basic Validation
    if ($payment_id <= 0 || empty($txn_id)) {
        die("Error: Missing payment information or Transaction ID.");
    }

    /**
     * 3. THE DATABASE UPDATE
     * We update the 'payments' table. 
     * We replace the temporary 'TXN...' ID with the REAL ID from the SMS.
     * We set status to 'pending_verification' so you know it's ready for you.
     */
    $stmt = $conn->prepare("
        UPDATE payments 
        SET transaction_ref = ?, 
            status = 'pending_verification' 
        WHERE id = ? AND tenant_id = ?
    ");
    
    // Bind: s (string txn_id), i (int payment_id), i (int user_id)
    $stmt->bind_param("sii", $txn_id, $payment_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        /**
         * 4. COMPLETION
         * We send the user to the dashboard with a success message.
         */
        $_SESSION['success_msg'] = "Thank you! Proof of payment submitted. We are verifying ID: " . htmlspecialchars($txn_id);
        
        // Redirect to your tenant dashboard
        header("Location: dashboard.php?status=verifying");
        exit();
    } else {
        die("Database Error: Could not save proof of payment. " . $conn->error);
    }

} else {
    // If someone tries to access this file without clicking 'Submit'
    header("Location: properties.php");
    exit();
}
?>