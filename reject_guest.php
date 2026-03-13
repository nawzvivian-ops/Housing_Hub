<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_POST['id'] ?? 0);
mysqli_query($conn, "UPDATE guests SET status='rejected' WHERE id=$id");

header("Location: admin_dashboard.php?page=guests");
exit();