<?php
session_start();
include 'db_connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (isset($_SESSION['verified']) && $_SESSION['verified'] === true) {
    header('Location: broker_dashboard.php');
    exit();
}

function uploadFile($fileInputName, $targetDir = 'uploads/')
{
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    if (!is_writable($targetDir)) {
        return false;
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
    try {
        if (isset($_POST['fullname'])) {
            // Individual verification
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

            $stmt = $conn->prepare("INSERT INTO verification_requests
                (type, full_name, id_type, id_doc_path, phone, email, status, submitted_at)
                VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");

            $type = 'individual';
            $stmt->bind_param("ssssss", $type, $fullname, $id_type, $id_doc_path, $phone, $email);
            $stmt->execute();

            $_SESSION['message'] = 'Your personal verification request has been submitted successfully.';
        }

        elseif (isset($_POST['bname'])) {
            // Business verification
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

            $stmt = $conn->prepare("INSERT INTO verification_requests
                (type, business_name, b_reg_path, owner_id_path, duration_years, additional_doc_path, email, status, submitted_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");

            $type = 'business';
            $stmt->bind_param("ssssiss", $type, $bname, $b_reg_path, $b_owner_id_path, $b_duration, $b_doc_path, $b_email);
            $stmt->execute();

            $_SESSION['message'] = 'Your business verification request has been submitted successfully.';
        }

    } catch (Exception $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    }

    header('Location: join.php#verification');
    exit();
}
?>
