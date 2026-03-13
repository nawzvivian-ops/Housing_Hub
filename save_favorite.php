<?php
session_start();
include "db_connect.php";

// check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// get property ID from URL
$property_id = intval($_POST['id']);

// check if property exists (optional but recommended)
$propertyCheck = mysqli_query($conn, "SELECT id FROM properties WHERE id=$property_id");
if(mysqli_num_rows($propertyCheck) == 0){
    die("Property does not exist.");
}

// check if already saved
$check = mysqli_query($conn, "SELECT id FROM favorites WHERE user_id=$user_id AND property_id=$property_id");
if(mysqli_num_rows($check) == 0){
    // insert into favorites
    mysqli_query($conn, "INSERT INTO favorites (user_id, property_id, created_at) VALUES ($user_id, $property_id, NOW())");
}

// redirect back to the property page
header("Location: property_view.php?id=$property_id");
exit();
?>