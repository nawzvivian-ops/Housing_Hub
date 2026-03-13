<?php
session_start();
include "db_connect.php";

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['user_id'];

// Fetch payments along with property info
$result = mysqli_query($conn, "
    SELECT p.*, pr.property_name 
    FROM payments p
    LEFT JOIN properties pr ON p.property_id = pr.id
    WHERE p.tenant_id = '$tenant_id'
    ORDER BY p.date DESC
");

$payments = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment History</title>
<style>
body {font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding:20px; background:lightblue;}
h2 {color:#4f46e5; margin-bottom:20px;}
table {width:100%; border-collapse: collapse;}
th, td {padding:10px; border:1px solid #ddd; text-align:left;}
th {background:#4f46e5; color:#fff;}
tr:hover {background:#e0e7ff;}
.action-btn {padding:6px 12px; background:#4f46e5; color:#fff; border:none; border-radius:5px; text-decoration:none;}
</style>
</head>
<body>

<h2>Payment History</h2>

<?php if(count($payments) > 0): ?>
<table>
<tr>
    <th>Date</th>
    <th>Property</th>
    <th>Amount (UGX)</th>
    <th>Payment Method</th>
    <th>Status</th>
    <th>Reference / Receipt</th>
</tr>
<?php foreach($payments as $p): ?>
<tr>
    <td><?= htmlspecialchars($p['date']) ?></td>
    <td><?= htmlspecialchars($p['property_name'] ?? 'N/A') ?></td>
    <td><?= number_format($p['amount'],2) ?></td>
    <td><?= ucfirst(str_replace('_',' ',$p['payment_method'])) ?></td>
    <td><?= ucfirst($p['status']) ?></td>
    <td>
        <?php if($p['status']=='paid'): ?>
            <a href="download_payment.php?id=<?= $p['id'] ?>" class="action-btn">Download Receipt</a>
        <?php else: ?>
            N/A
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p>No payments found.</p>
<?php endif; ?>

<a href="tenant.php" class="action-btn">← Back to Dashboard</a>

</body>
</html>