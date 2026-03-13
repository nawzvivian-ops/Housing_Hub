<?php
session_start();
include "db_connect.php";

# --- Staff Login Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

$userQ = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($userQ);

if (!$user || $user['role'] !== 'staff') {
    echo "<h2 style='color:red;text-align:center;'>Access Denied!</h2>";
    exit();
}

# --- Recent Payments Report ---
$recentPayments = mysqli_query($conn, "
    SELECT p.*, t.fullname AS tenant_name
    FROM payments p
    LEFT JOIN tenants t ON p.tenant_id = t.id
    ORDER BY p.date DESC
    LIMIT 5
");

# --- Maintenance Summary Report ---
$maintenanceStats = mysqli_query($conn, "
    SELECT status, COUNT(*) AS total
    FROM maintenance_requests
    GROUP BY status
");

# --- Inspection Summary Report ---
$inspectionStats = mysqli_query($conn, "
    SELECT status, COUNT(*) AS total
    FROM inspections
    GROUP BY status
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Reports</title>
    <style>
        body{
            font-family:Segoe UI;
            background:lightblue;
            padding:30px;
        }
        h1{
            text-align:center;
            color:#1e293b;
        }
        .box{
            background:white;
            padding:20px;
            margin:20px auto;
            border-radius:15px;
            max-width:900px;
            box-shadow:0 4px 10px rgba(0,0,0,0.1);
        }
        table{
            width:100%;
            border-collapse:collapse;
            margin-top:15px;
        }
        th, td{
            padding:12px;
            border-bottom:1px solid #ddd;
            text-align:left;
        }
        th{
            background:#2563eb;
            color:white;
        }
        .badge{
            padding:6px 12px;
            border-radius:10px;
            font-size:13px;
            font-weight:bold;
            color:white;
        }
        .pending{background:orange;}
        .completed{background:green;}
        .in_progress{background:purple;}
        a.back{
            display:inline-block;
            margin-top:15px;
            text-decoration:none;
            padding:10px 15px;
            background:#2563eb;
            color:white;
            border-radius:10px;
        }
    </style>
</head>

<body>

<h1>Staff Reports & Activity Summary</h1>

<div class="box">
    <h2> Recent Payments</h2>

    <table>
        <tr>
            <th>Tenant</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Date Paid</th>
            <th>Due Date</th>
            <th>Status</th>
        </tr>

        <?php while($p = mysqli_fetch_assoc($recentPayments)): ?>
        <tr>
            <td><?= htmlspecialchars($p['tenant_name'] ?? '-') ?></td>
            <td>UGX <?= htmlspecialchars($p['amount']) ?></td>
            <td><?= htmlspecialchars($p['payment_method']) ?></td>
            <td><?= htmlspecialchars($p['date']) ?></td>
            <td><?= htmlspecialchars($p['due_date']) ?></td>
            <td><?= htmlspecialchars($p['status']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<div class="box">
    <h2>Maintenance Requests Summary</h2>

    <table>
        <tr>
            <th>Status</th>
            <th>Total Requests</th>
        </tr>

        <?php while($m = mysqli_fetch_assoc($maintenanceStats)): ?>
        <tr>
            <td>
                <span class="badge <?= $m['status'] ?>">
                    <?= ucfirst($m['status']) ?>
                </span>
            </td>
            <td><?= $m['total'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<div class="box">
    <h2>Inspection Summary</h2>

    <table>
        <tr>
            <th>Status</th>
            <th>Total Inspections</th>
        </tr>

        <?php while($i = mysqli_fetch_assoc($inspectionStats)): ?>
        <tr>
            <td>
                <span class="badge <?= strtolower($i['status']) ?>">
                    <?= $i['status'] ?>
                </span>
            </td>
            <td><?= $i['total'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<center>
    <a href="staff_dashboard.php" class="back">← Back to Staff Dashboard</a>
</center>

</body>
</html>