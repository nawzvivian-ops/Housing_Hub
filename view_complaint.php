<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id'] ?? 0);
$complaint = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT c.*, t.fullname as tenant_name
    FROM complaints c
    LEFT JOIN tenants t ON c.tenant_id = t.id
    WHERE c.id = $id
"));

if (!$complaint) {
    die("Complaint not found");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Complaint</title>
<style>
body { font-family:"Segoe UI",sans-serif; padding:20px; background:#f3f4f6; }
h2 { text-align:center; color:#366d21; margin-bottom:20px; }
.details { max-width:600px; margin:0 auto; background:white; padding:20px; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.1); }
.details p { margin-bottom:10px; }
a { display:block; margin-top:20px; text-align:center; text-decoration:none; color:#0ea5e9; }
</style>
</head>
<body>

<h2>Complaint Details</h2>

<div class="details">
    <p><strong>Tenant:</strong> <?= htmlspecialchars($complaint['tenant_name'] ?? 'N/A') ?></p>
    <p><strong>Category:</strong> <?= htmlspecialchars($complaint['category'] ?? 'N/A') ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($complaint['status'] ?? 'Pending') ?></p>
    <p><strong>Date:</strong> <?= htmlspecialchars($complaint['created_at'] ?? '-') ?></p>
    <p><strong>Message:</strong></p>
    <p><?= nl2br(htmlspecialchars($complaint['message'] ?? '')) ?></p>
</div>

<a href="admin_dashboard.php?page=complaints">← Back to Complaints</a>

</body>
</html>