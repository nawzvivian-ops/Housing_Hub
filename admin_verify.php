<?php
session_start();
include "db_connect.php";

// Basic security: Ensure only you (the admin) can see this
// if ($_SESSION['role'] !== 'admin') { die("Access Denied"); }

$sql = "SELECT p.id, p.amount, p.transaction_ref, p.date, 
               u.full_name, u.phone as user_phone, 
               pr.property_name 
        FROM payments p
        JOIN users u ON p.tenant_id = u.id
        JOIN properties pr ON p.property_id = pr.id
        WHERE p.status = 'pending_verification'
        ORDER BY p.date DESC";

$result = $conn->query($sql);
?>

<h2>HousingHub: Pending Approvals</h2>
<table border="1" cellpadding="10">
    <tr>
        <th>User (Tenant)</th>
        <th>Property</th>
        <th>Amount</th>
        <th>SMS ID to check</th>
        <th>Action</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td>
            <strong><?php echo $row['full_name']; ?></strong><br>
            <small><?php echo $row['user_phone']; ?></small>
        </td>
        <td><?php echo $row['property_name']; ?></td>
        <td>UGX <?php echo number_format($row['amount']); ?></td>
        <td style="color: blue; font-weight: bold;"><?php echo $row['transaction_ref']; ?></td>
        <td>
            <a href="approve_payment.php?id=<?php echo $row['id']; ?>" 
               onclick="return confirm('Did you see this ID on your phone?')">
               ✅ Approve
            </a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>