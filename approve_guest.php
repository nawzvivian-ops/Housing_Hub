<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id'] ?? 0);
mysqli_query($conn, "UPDATE guests SET status='approved' WHERE id=$id");

header("Location: admin_dashboard.php?page=guests");
exit();

