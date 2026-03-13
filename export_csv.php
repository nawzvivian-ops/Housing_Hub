<?php
include "db_connect.php";

$table = $_GET['table'] ?? '';

if (!$table) {
    die("No table specified.");
}

// Get table data
$result = mysqli_query($conn, "SELECT * FROM `$table`");

if (!$result) {
    die("Invalid table or query error.");
}

// Set CSV headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $table . '_' . date("Y-m-d_H-i-s") . '.csv');

$output = fopen('php://output', 'w');

// Output column headers
$columns = mysqli_fetch_fields($result);
$headers = [];
foreach ($columns as $col) {
    $headers[] = $col->name;
}
fputcsv($output, $headers);

// Output rows
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>