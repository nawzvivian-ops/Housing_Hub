<?php
include "db_connect.php";

// Hash admin123 securely
$hashedPassword = password_hash("admin12345", PASSWORD_DEFAULT);

// Update ONLY admin users
$sql = "UPDATE users 
        SET password = '$hashedPassword' 
        WHERE role = 'admin'";

if (mysqli_query($conn, $sql)) {
    echo "✅ Admin password successfully set to admin12345";
} else {
    echo "❌ Error: " . mysqli_error($conn);
}
?>