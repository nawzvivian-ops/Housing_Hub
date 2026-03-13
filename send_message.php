<?php
session_start();
include "db_connect.php";

// Check if tenant is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get tenant ID from session
$tenant_id = $_SESSION['user_id'];

// Validate POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($subject) || empty($message)) {
        die("Subject and message cannot be empty.");
    }

    // Insert message into tenant_messages table
    $stmt = $conn->prepare("INSERT INTO tenant_messages (tenant_id, subject, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $tenant_id, $subject, $message);

    if ($stmt->execute()) {
        // Redirect back to dashboard with success message
        header("Location: tenants.php?msg=Message+sent+successfully");
        exit();
    } else {
        die("Error sending message: " . $conn->error);
    }
} else {
    die("Invalid request.");
}
?>