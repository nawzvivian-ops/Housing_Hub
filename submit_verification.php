<?php
session_start();
include 'db_connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (isset($_SESSION['verified']) && $_SESSION['verified'] === true) {
    header('Location: broker_dashboard.php');
    exit();
}

// Auto-update table if column doesn't exist
try {
    $conn->query("ALTER TABLE verification_requests ADD COLUMN IF NOT EXISTS terms_agreed TINYINT(1) DEFAULT 0 AFTER email");
} catch (Exception $e) {
    // Column might already exist or DB doesn't support IF NOT EXISTS on ALTER
}

function uploadFile($fileInputName, $targetDir = 'uploads/')
{
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $originalName = basename($_FILES[$fileInputName]['name']);
    $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $originalName);
    $targetFilePath = $targetDir . uniqid() . '_' . $safeName;

    if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetFilePath)) {
        return $targetFilePath;
    }

    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture the agreement status from the checkbox
    $terms_agreed = isset($_POST['terms_agreed']) ? 1 : 0;

    // Strict validation: Don't allow processing if they didn't check the box
    if ($terms_agreed === 0) {
        $_SESSION['error'] = '⚠️ You must read and agree to the Brokerage Master Agreement to proceed.';
        header('Location: join.php#verification');
        exit();
    }

    try {
        if (isset($_POST['fullname'])) {
            // --- Individual verification ---
            $fullname = trim($_POST['fullname']);
            $id_type  = trim($_POST['id_type']);
            $phone    = trim($_POST['phone']);
            $email    = trim($_POST['email']);

            $id_doc_path = uploadFile('id_doc');

            if (!$id_doc_path) {
                $_SESSION['error'] = 'Failed to upload ID document.';
                header('Location: join.php#verification');
                exit();
            }

            // Updated query to include terms_agreed
            $stmt = $conn->prepare("INSERT INTO verification_requests 
                (type, full_name, id_type, id_doc_path, phone, email, terms_agreed, status, submitted_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");

            $type = 'individual';
            $stmt->bind_param("ssssssi", $type, $fullname, $id_type, $id_doc_path, $phone, $email, $terms_agreed);
            $stmt->execute();

            $_SESSION['message'] = 'Your personal verification request and agreement have been submitted successfully.';
        }

        elseif (isset($_POST['bname'])) {
            // --- Business verification ---
            $bname      = trim($_POST['bname']);
            $b_duration = (int)$_POST['b_duration'];
            $b_email    = trim($_POST['email']);

            $b_reg_path      = uploadFile('b_reg');
            $b_owner_id_path = uploadFile('b_owner_id');
            $b_doc_path      = uploadFile('b_doc'); // optional

            if (!$b_reg_path || !$b_owner_id_path) {
                $_SESSION['error'] = 'Failed to upload required business documents.';
                header('Location: join.php#verification');
                exit();
            }

            // Updated query to include terms_agreed
            $stmt = $conn->prepare("INSERT INTO verification_requests 
                (type, business_name, b_reg_path, owner_id_path, duration_years, additional_doc_path, email, terms_agreed, status, submitted_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");

            $type = 'business';
            $stmt->bind_param("ssssissi", $type, $bname, $b_reg_path, $b_owner_id_path, $b_duration, $b_doc_path, $b_email, $terms_agreed);
            $stmt->execute();

            $_SESSION['message'] = 'Your business verification request and agreement have been submitted successfully.';
        }

    } catch (Exception $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    }

    header('Location: join.php#verification');
    exit();
}
?>