<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'broker') {
    header("Location: index.php"); exit();
}

$broker_id = (int)$_SESSION['user_id'];

// Auto-create submissions table
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS broker_property_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    broker_id INT NOT NULL,
    property_name VARCHAR(200) NOT NULL,
    property_type VARCHAR(100) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    units INT DEFAULT 1,
    rent_amount DECIMAL(12,2) DEFAULT 0,
    bedrooms INT DEFAULT 0,
    size_sqft INT DEFAULT NULL,
    amenities TEXT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    purpose VARCHAR(50) DEFAULT 'rent',
    latitude VARCHAR(50) DEFAULT NULL,
    longitude VARCHAR(50) DEFAULT NULL,
    commission_rate DECIMAL(5,2) DEFAULT 10,
    commission_percentage DECIMAL(5,2) DEFAULT 10,
    property_image VARCHAR(300) DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    admin_notes TEXT DEFAULT NULL,
    reviewed_by VARCHAR(200) DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT NOW()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: broker_dashboard.php?view=properties");
    exit();
}

// Sanitize inputs
$property_name         = mysqli_real_escape_string($conn, trim($_POST['property_name'] ?? ''));
$property_type         = mysqli_real_escape_string($conn, trim($_POST['property_type'] ?? ''));
$address               = mysqli_real_escape_string($conn, trim($_POST['address'] ?? ''));
$units                 = max(1, (int)($_POST['units'] ?? 1));
$rent_amount           = (float)($_POST['rent_amount'] ?? 0);
$purpose               = mysqli_real_escape_string($conn, trim($_POST['purpose'] ?? 'rent'));
$bedrooms              = (int)($_POST['bedrooms'] ?? 0);
$size_sqft             = $_POST['size_sqft'] ? (int)$_POST['size_sqft'] : 'NULL';
$amenities             = mysqli_real_escape_string($conn, trim($_POST['amenities'] ?? ''));
$description           = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
$commission_rate       = (float)($_POST['commission_rate'] ?? 10);
$commission_percentage = (float)($_POST['commission_percentage'] ?? 10);
$latitude              = mysqli_real_escape_string($conn, trim($_POST['latitude'] ?? ''));
$longitude             = mysqli_real_escape_string($conn, trim($_POST['longitude'] ?? ''));

// Validate required fields
if (!$property_name || !$address || $rent_amount <= 0) {
    $_SESSION['broker_error'] = "Please fill in all required fields (name, address, rent).";
    header("Location: broker_dashboard.php?view=properties");
    exit();
}

// Handle image upload
$image_path = '';
if (!empty($_FILES['property_image']['name'])) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $file_type     = $_FILES['property_image']['type'];
    $file_size     = $_FILES['property_image']['size'];

    if (!in_array($file_type, $allowed_types)) {
        $_SESSION['broker_error'] = "Only JPG, PNG, WEBP, or GIF images are allowed.";
        header("Location: broker_dashboard.php?view=properties");
        exit();
    }
    if ($file_size > 5 * 1024 * 1024) {
        $_SESSION['broker_error'] = "Image must be under 5MB.";
        header("Location: broker_dashboard.php?view=properties");
        exit();
    }

    $upload_dir = 'uploads/property_submissions/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $ext        = pathinfo($_FILES['property_image']['name'], PATHINFO_EXTENSION);
    $filename   = 'prop_' . $broker_id . '_' . time() . '.' . strtolower($ext);
    $dest       = $upload_dir . $filename;

    if (move_uploaded_file($_FILES['property_image']['tmp_name'], $dest)) {
        $image_path = mysqli_real_escape_string($conn, $dest);
    } else {
        $_SESSION['broker_error'] = "Image upload failed. Please try again.";
        header("Location: broker_dashboard.php?view=properties");
        exit();
    }
}

$size_val = is_int($size_sqft) ? $size_sqft : 'NULL';

// Insert submission
$ok = mysqli_query($conn,
    "INSERT INTO broker_property_submissions
        (broker_id, property_name, property_type, address, units, rent_amount, bedrooms,
         size_sqft, amenities, description, purpose, latitude, longitude,
         commission_rate, commission_percentage, property_image, status, created_at)
     VALUES
        ($broker_id, '$property_name', '$property_type', '$address', $units, $rent_amount, $bedrooms,
         $size_val, '$amenities', '$description', '$purpose', '$latitude', '$longitude',
         $commission_rate, $commission_percentage, '$image_path', 'pending', NOW())"
);

if ($ok) {
    // Notify admins
    $broker_name = mysqli_real_escape_string($conn, $_SESSION['fullname'] ?? 'A broker');
    $admins = mysqli_query($conn, "SELECT id FROM users WHERE role='admin'");
    while ($admin = mysqli_fetch_assoc($admins)) {
        $aid = (int)$admin['id'];
        mysqli_query($conn,
            "INSERT INTO notifications (user_id, tenant_id, title, message, status, date)
             VALUES ($aid, 0, 'New Property Submission',
             '$broker_name has submitted a new property \"$property_name\" for review.',
             'unread', NOW())"
        );
    }
    $_SESSION['broker_success'] = "✅ Property <strong>" . htmlspecialchars($_POST['property_name']) . "</strong> submitted for admin review. You will be notified once approved.";
} else {
    $_SESSION['broker_error'] = "Submission failed. Please try again. Error: " . mysqli_error($conn);
}

header("Location: broker_dashboard.php?view=properties");
exit();