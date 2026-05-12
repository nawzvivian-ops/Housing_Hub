<?php
$host = "localhost";
$user = "housinghub";
$pass = "7ncfEzATXrkFwE8r";
$db   = "housinghub";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");
?>
