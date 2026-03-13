<?php
session_start();
include "db_connect.php";

// --- 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// --- 2. Check if user is staff
$user_id = intval($_SESSION['user_id']);
$userQ = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($userQ);
if (!$user || strtolower($user['role']) !== 'staff') {
    echo "<h2 style='color:red;text-align:center;'>Access Denied!</h2>";
    exit();
}

// --- 3. Handle Add Payment (if staff allowed)
if (isset($_POST['add_payment'])) {
    $tenant_id   = intval($_POST['tenant_id']);
    $property_id = intval($_POST['property_id']);
    $amount      = floatval($_POST['amount']);
    $due_date    = mysqli_real_escape_string($conn, $_POST['due_date']);
    $method      = mysqli_real_escape_string($conn, $_POST['method']);
    $status      = mysqli_real_escape_string($conn, $_POST['status']);

    mysqli_query($conn, "
        INSERT INTO payments (tenant_id, property_id, amount, due_date, payment_method, status, created_at)
        VALUES ('$tenant_id','$property_id','$amount','$due_date','$method','$status', NOW())
    ");
    header("Location: staff_payments.php");
    exit();
}

// --- 4. Handle Mark as Paid
if (isset($_GET['paid'])) {
    $payment_id = intval($_GET['paid']);
    mysqli_query($conn, "UPDATE payments SET status='Paid', payment_date=NOW() WHERE id='$payment_id'");
    header("Location: staff_payments.php");
    exit();
}

// --- 5. Handle Delete Payment
if (isset($_GET['delete'])) {
    $payment_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM payments WHERE id='$payment_id'");
    header("Location: staff_payments.php");
    exit();
}

// --- 6. Filters & Search
$filter_property = $_GET['property_id'] ?? '';
$filter_tenant   = $_GET['tenant_id'] ?? '';
$filter_status   = $_GET['status'] ?? '';
$search          = $_GET['search'] ?? '';

$where = "WHERE 1=1";
if ($filter_property) $where .= " AND p.property_id='$filter_property'";
if ($filter_tenant)   $where .= " AND p.tenant_id='$filter_tenant'";
if ($filter_status)   $where .= " AND p.status='$filter_status'";
if ($search)          $where .= " AND (t.fullname LIKE '%$search%' OR t.email LIKE '%$search%')";

// --- 7. Fetch Payments with Tenant & Property info
$paymentsQ = mysqli_query($conn, "
    SELECT p.*, t.fullname AS tenant_name, pr.property_name 
    FROM payments p
    LEFT JOIN tenants t ON p.tenant_id = t.id
    LEFT JOIN properties pr ON p.property_id = pr.id
    $where
    ORDER BY p.created_at DESC
");

// --- Quick Stats
$totalQ    = mysqli_query($conn, "SELECT COUNT(*) as total FROM payments");
$paidQ     = mysqli_query($conn, "SELECT COUNT(*) as paid FROM payments WHERE status='Paid'");
$pendingQ  = mysqli_query($conn, "SELECT COUNT(*) as pending FROM payments WHERE status='Pending'");
$overdueQ  = mysqli_query($conn, "SELECT COUNT(*) as overdue FROM payments WHERE status='Pending' AND due_date < CURDATE()");

$total   = mysqli_fetch_assoc($totalQ)['total'];
$paid    = mysqli_fetch_assoc($paidQ)['paid'];
$pending = mysqli_fetch_assoc($pendingQ)['pending'];
$overdue = mysqli_fetch_assoc($overdueQ)['overdue'];

// --- Fetch Tenants & Properties for filters / add form
$tenantsQ   = mysqli_query($conn, "SELECT * FROM tenants ORDER BY fullname ASC");
$propertiesQ = mysqli_query($conn, "SELECT * FROM properties ORDER BY property_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Payments</title>
    <style>
        body { font-family:Segoe UI; background:lightblue; padding:30px; }
        h1 { color:black; }
        form { background:white; padding:20px; border:3px solid #131212; margin-bottom:20px; }
        input, select { width:100%; padding:10px; margin:10px 0; border-radius:5px; border:1px solid #ccc; }
        button { padding:10px 20px; border:none; background:#2563eb; color:white; border-radius:5px; cursor:pointer; }
        button:hover { background:#1d4ed8; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { border:1px solid #141414; padding:10px; text-align:left; }
        th { background:#2563eb; color:white; }
        a { text-decoration:none; color:#2563eb; }
        a:hover { text-decoration:underline; }
        .overdue { background:#fee; }
        .stats { display:flex; gap:20px; margin-bottom:20px; }
        .stats div { flex:1; background:black; color:white; padding:15px; border-radius:40px; text-align:center; }
    </style>
</head>
<body>

<h1>Staff Payments</h1>
<a href="staff_dashboard.php">← Back to Staff Dashboard</a><br>

<!-- Quick Stats -->
 <br>
<div class="stats">
    <div>Total: <?php echo $total; ?></div>
    <div>Paid: <?php echo $paid; ?></div>
    <div>Pending: <?php echo $pending; ?></div>
    <div>Overdue: <?php echo $overdue; ?></div>
</div>

<!-- Filter & Search -->
<form method="GET" action="">
    <h3>Filter / Search Payments</h3>
    <select name="property_id">
        <option value="">All Properties</option>
        <?php while($p = mysqli_fetch_assoc($propertiesQ)): ?>
            <option value="<?php echo $p['id']; ?>" <?php if($filter_property==$p['id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($p['property_name']); ?>
            </option>
        <?php endwhile; ?>
    </select>
    <select name="tenant_id">
        <option value="">All Tenants</option>
        <?php mysqli_data_seek($tenantsQ,0); while($t = mysqli_fetch_assoc($tenantsQ)): ?>
            <option value="<?php echo $t['id']; ?>" <?php if($filter_tenant==$t['id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($t['fullname']); ?>
            </option>
        <?php endwhile; ?>
    </select>
    <select name="status">
        <option value="">All Status</option>
        <option value="Paid" <?php if($filter_status=='Paid') echo 'selected'; ?>>Paid</option>
        <option value="Pending" <?php if($filter_status=='Pending') echo 'selected'; ?>>Pending</option>
    </select>
    <input type="text" name="search" placeholder="Search by tenant or email" value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Apply</button>
</form>

<!-- Add Payment Form -->
<form method="POST" action="">
    <h3>Add Payment</h3>
    <select name="tenant_id" required>
        <option value="">Select Tenant</option>
        <?php mysqli_data_seek($tenantsQ,0); while($t = mysqli_fetch_assoc($tenantsQ)): ?>
            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['fullname']); ?></option>
        <?php endwhile; ?>
    </select>
    <select name="property_id" required>
        <option value="">Select Property</option>
        <?php mysqli_data_seek($propertiesQ,0); while($p = mysqli_fetch_assoc($propertiesQ)): ?>
            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['property_name']); ?></option>
        <?php endwhile; ?>
    </select>
    <input type="number" step="0.01" name="amount" placeholder="Amount" required>
    <input type="date" name="due_date" required>
    <select name="method" required>
        <option value="Cash">Cash</option>
        <option value="Bank Transfer">Bank Transfer</option>
        <option value="Mobile Money">Mobile Money</option>
    </select>
    <select name="status" required>
        <option value="Pending" selected>Pending</option>
        <option value="Paid">Paid</option>
    </select>
    <button type="submit" name="add_payment">Add Payment</button>
</form>

<!-- Payments Table -->
<table>
    <tr>
        <th>#</th>
        <th>Tenant</th>
        <th>Property</th>
        <th>Amount</th>
        <th>Payment Date</th>
        <th>Due Date</th>
        <th>Status</th>
        <th>Method</th>
        <th>Actions</th>
    </tr>
    <?php $i=1; while($pay = mysqli_fetch_assoc($paymentsQ)): ?>
        <tr class="<?php if($pay['status']=='Pending' && $pay['due_date'] < date('Y-m-d')) echo 'overdue'; ?>">
            <td><?php echo $i++; ?></td>
            <td><?php echo htmlspecialchars($payment['tenant_name'] ?? 'Unknown Tenant'); ?></td>
            <td><?php echo htmlspecialchars($pay['property_name']); ?></td>
            <td><?php echo number_format($pay['amount'],2); ?></td>
             <td><?php echo htmlspecialchars($payment['payment_date'] ?? '-'); ?></td>
            <td><?php echo htmlspecialchars($payment['due_date'] ?? '-'); ?></td>
            <td><?php echo $pay['status']; ?></td>
           <td><?php echo htmlspecialchars($payment['method'] ?? '-'); ?></td>
            <td>
                <?php if($pay['status']=='Pending'): ?>
                    <a href="?paid=<?php echo $pay['id']; ?>">Mark Paid</a> |
                <?php endif; ?>
                <a href="?delete=<?php echo $pay['id']; ?>" onclick="return confirm('Delete this payment?')">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>