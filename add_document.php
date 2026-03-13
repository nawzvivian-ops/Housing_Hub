<?php
session_start();
include "db_connect.php";

// Check if admin is logged in
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

// Handle form submission
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenant_id = intval($_POST['tenant_id'] ?? 0);
    $document_name = trim($_POST['document_name'] ?? '');
    
    if ($tenant_id <= 0 || empty($document_name)) {
        $error = "Please select a tenant and enter a document name.";
    } elseif (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== 0) {
        $error = "Please upload a valid file.";
    } else {
        // Handle file upload
        $uploads_dir = 'uploads/tenant_documents';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0777, true);
        }

        $file_tmp = $_FILES['document_file']['tmp_name'];
        $file_name = basename($_FILES['document_file']['name']);
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $document_name) . '.' . $file_ext;
        $target_file = $uploads_dir . '/' . $new_file_name;

        if (move_uploaded_file($file_tmp, $target_file)) {
            // Insert into DB
            $stmt = $conn->prepare("INSERT INTO tenant_documents (tenant_id, document_name, file_path, uploaded_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iss", $tenant_id, $document_name, $target_file);
            
            if ($stmt->execute()) {
                $success = "Document uploaded successfully!";
            } else {
                $error = "Database error: Could not save document.";
            }
        } else {
            $error = "Failed to move uploaded file.";
        }
    }
}

// Fetch tenants for dropdown
$tenants_result = mysqli_query($conn, "SELECT id, fullname FROM tenants ORDER BY fullname ASC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Tenant Document | Admin Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body { font-family: "Segoe UI", sans-serif; padding:20px; background:lightblue; }
h2 { text-align:center; color:#366d21; margin-bottom:20px; }
form { max-width:500px; margin:0 auto; background:white;border:3px solid black; ;background: linear-gradient(135deg, #495757, #0ea5e9);padding:30px; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.1); }
label { display:block; margin-bottom:5px; font-weight:bold; }
input[type="text"], select, input[type="file"] { width:100%; padding:10px; margin-bottom:15px; border-radius:5px; border:1px solid #ccc; }
button { background:black; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#0284c7; }
.success { background:#16a34a; color:white; padding:10px; margin-bottom:15px; border-radius:5px; text-align:center; }
.error { background:#ef4444; color:white; padding:10px; margin-bottom:15px; border-radius:5px; text-align:center; }
a { display:block; margin-top:20px; text-align:center; text-decoration:none; color:black; }
</style>
</head>
<body>

<h2>Add Tenant Document</h2>

<?php if($success): ?>
<div class="success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if($error): ?>
<div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <label for="tenant_id">Select Tenant</label>
    <select name="tenant_id" id="tenant_id" required>
        <option value="">-- Choose Tenant --</option>
        <?php while($t = mysqli_fetch_assoc($tenants_result)): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['fullname']) ?></option>
        <?php endwhile; ?>
    </select>

    <label for="document_name">Document Name</label>
    <input type="text" name="document_name" id="document_name" placeholder="Enter document name" required>

    <label for="document_file">Upload File</label>
    <input type="file" name="document_file" id="document_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>

    <button type="submit">Upload Document</button>
</form>

<a href="admin_dashboard.php?page=tenant_documents">← Back to Tenant Documents</a>

</body>
</html>