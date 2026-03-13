<?php
session_start();
include "db_connect.php";

// Show errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($result);

$role = strtolower(trim($user['role']));
if ($role !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

/* ===== UPLOAD DOCUMENT ===== */
if (isset($_POST['upload'])) {
    $tenant_id = intval($_POST['tenant_id']);
    $document_type = mysqli_real_escape_string($conn, $_POST['document_type']);
    
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $filename = time() . "_" . basename($_FILES['file']['name']);
        $target_dir = "uploads/tenant_docs/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
            mysqli_query($conn, "INSERT INTO tenant_documents (tenant_id, document_type, file_path) 
                                 VALUES ($tenant_id, '$document_type', '$target_file')");
            $msg = "Document uploaded successfully!";
        } else {
            $msg = "Failed to upload file.";
        }
    } else {
        $msg = "No file selected.";
    }
}

/* ===== FETCH DOCUMENTS ===== */
$documents = mysqli_query($conn, "
    SELECT td.*, t.fullname 
    FROM tenant_documents td
    LEFT JOIN tenants t ON td.tenant_id = t.id
    ORDER BY td.uploaded_at DESC
");

/* ===== FETCH TENANTS FOR DROPDOWN ===== */
$tenants = mysqli_query($conn, "SELECT id, fullname FROM tenants ORDER BY fullname ASC");

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tenant Documents</title>
    <style>
        body { font-family: Segoe UI, sans-serif; padding: 30px; background: #f4f7fb; }
        table { width: 100%; border-collapse: collapse; background: white; }
        table th, table td { padding: 12px; border: 1px solid #ccc; text-align: left; }
        table th { background: #0f172a; color: white; }
        form { background: white; padding: 20px; margin-bottom: 30px; border-radius: 10px; box-shadow: 0 5px 10px rgba(0,0,0,0.1); }
        input, select { width: 100%; padding: 10px; margin: 10px 0; border-radius: 6px; border: 1px solid #ccc; }
        button { padding: 12px 20px; background: #0ea5e9; color: white; border: none; border-radius: 6px; cursor: pointer; }
        button:hover { background: #0284c7; }
        .msg { color: green; margin-bottom: 15px; }
    </style>
</head>
<body>

<h2>Tenant Documents</h2>

<?php if (!empty($msg)) echo "<div class='msg'>$msg</div>"; ?>

<!-- Upload Form -->
<form method="POST" enctype="multipart/form-data">
    <label>Select Tenant:</label>
    <select name="tenant_id" required>
        <option value="">-- Select Tenant --</option>
        <?php while ($t = mysqli_fetch_assoc($tenants)): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['fullname']) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Document Type:</label>
    <input type="text" name="document_type" placeholder="e.g., ID, Lease, Utility Bill" required>

    <label>Choose File:</label>
    <input type="file" name="file" required>

    <button type="submit" name="upload">Upload Document</button>
</form>

<!-- Documents Table -->
<table>
    <tr>
        <th>Tenant</th>
        <th>Document Type</th>
        <th>File</th>
        <th>Uploaded At</th>
    </tr>
    <?php while ($doc = mysqli_fetch_assoc($documents)): ?>
        <tr>
            <td><?= htmlspecialchars($doc['fullname'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($doc['document_type'] ?? 'N/A') ?></td>
            <td><a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank">View</a></td>
            <td><?= htmlspecialchars($doc['uploaded_at']) ?></td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>