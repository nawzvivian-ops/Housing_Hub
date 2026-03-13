<?php
include "db_connect.php";

$doc_id = intval($_GET['id'] ?? 0);
if($doc_id <= 0) die("Invalid document ID.");

$result = mysqli_query($conn, "SELECT * FROM tenant_documents WHERE id='$doc_id'");
$doc = mysqli_fetch_assoc($result);

if(!$doc) die("Document not found.");

// File path
$file_path = 'uploads/' . $doc['file_name']; // adjust to your folder and column

if(!file_exists($file_path)) die("File missing.");

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
?>