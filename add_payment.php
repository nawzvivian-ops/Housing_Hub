<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($result);

if (strtolower(trim($user['role'])) !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenant_id = intval($_POST['tenant_id'] ?? 0);
    $property_id = intval($_POST['property_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    $date = $_POST['date'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'pending';

    if ($tenant_id <= 0 || $property_id <= 0 || $amount <= 0) {
        $error = "Please fill all required fields with valid values.";
    } else {
        $stmt = $conn->prepare("INSERT INTO payments (tenant_id, property_id, amount, date, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iidss", $tenant_id, $property_id, $amount, $date, $status);

        if ($stmt->execute()) {
            $success = "Payment recorded successfully!";
        } else {
            $error = "Database error: Could not save payment.";
        }
    }
}

// Fetch tenants and properties for dropdown
$tenants = mysqli_query($conn, "SELECT id, fullname FROM tenants ORDER BY fullname ASC");
$properties = mysqli_query($conn, "SELECT id, property_name FROM properties ORDER BY property_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Record Tenant Payment | Admin Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body { font-family:"Segoe UI",sans-serif; padding:20px; background:lightblue; }
h2 { text-align:center; color:#366d21; margin-bottom:20px; }
form { max-width:500px; margin:0 auto; border:3px solid #0f0f0f; padding:30px; background: linear-gradient(135deg, #495757, #0ea5e9);border-radius:10px; box-shadow:0 5px 15px rgba(22, 20, 20, 0.97); }
label { display:block; margin-bottom:5px; font-weight:bold; }
input[type="text"], input[type="number"], input[type="date"], select { width:100%; padding:10px; margin-bottom:15px; border-radius:5px; border:1px solid #ccc; }
button { background:black; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#0284c7; }
.success { background:#16a34a; color:white; padding:10px; margin-bottom:15px; border-radius:5px; text-align:center; }
.error { background:#ef4444; color:white; padding:10px; margin-bottom:15px; border-radius:5px; text-align:center; }
a { display:block; margin-top:20px; text-align:center; text-decoration:none; color:black; }
</style>
</head>
<body>

<h2>Record Tenant Payment</h2>

<?php if($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<form method="POST">
    <label for="tenant_id">Select Tenant</label>
    <select name="tenant_id" id="tenant_id" required>
        <option value="">-- Choose Tenant --</option>
        <?php while($t = mysqli_fetch_assoc($tenants)): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['fullname']) ?></option>
        <?php endwhile; ?>
    </select>

    <label for="property_id">Select Property</label>
    <select name="property_id" id="property_id" required>
        <option value="">-- Choose Property --</option>
        <?php while($p = mysqli_fetch_assoc($properties)): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['property_name']) ?></option>
        <?php endwhile; ?>
    </select>

    <label for="amount">Amount (UGX)</label>
    <input type="number" name="amount" id="amount" step="1000" required>

    <label for="date">Payment Date</label>
    <input type="date" name="date" id="date" value="<?= date('Y-m-d') ?>" required>

    <label for="status">Status</label>
    <select name="status" id="status" required>
        <option value="pending">Pending</option>
        <option value="completed">Completed</option>
    </select>

    <button type="submit">Record Payment</button>
</form>

<a href="admin_dashboard.php?page=tenant_payments">← Back to Tenant Payments</a>

</body>
</html>