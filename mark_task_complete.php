<?php
session_start();
include "db_connect.php";

$id = intval($_POST['id'] ?? 0);

if ($id > 0) {
    mysqli_query($conn, "
        UPDATE tasks
        SET status='Completed'
        WHERE id=$id
    ");
}

header("Location: admin_dashboard.php?page=staff_tasks");
exit();
?>

