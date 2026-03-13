<?php
include "db_connect.php";

$result = mysqli_query($conn, "SELECT id, fullname, email, role FROM users WHERE role='admin'");

if (mysqli_num_rows($result) > 0) {
    echo "<h3>✅ Admin users found:</h3>";
    while ($user = mysqli_fetch_assoc($result)) {
        echo "ID: " . $user['id'] . "<br>";
        echo "Name: " . $user['fullname'] . "<br>";
        echo "Email: " . $user['email'] . "<br>";
        echo "Role: '" . $user['role'] . "' (length: " . strlen($user['role']) . ")<br>";
        echo "<hr>";
    }
} else {
    echo "❌ No admin users found!<br>";
    echo "<h3>All users:</h3>";
    
    $all = mysqli_query($conn, "SELECT id, fullname, email, role FROM users");
    while ($user = mysqli_fetch_assoc($all)) {
        echo "ID: " . $user['id'] . " | Name: " . $user['fullname'] . " | Email: " . $user['email'] . " | Role: '" . $user['role'] . "'<br>";
    }
}
?>