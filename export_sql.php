<?php
include "db_connect.php";

// Database credentials
$dbHost = $db_host;    // from db_connect.php
$dbUser = $db_user;
$dbPass = $db_pass;
$dbName = $db_name;

// File name for download
$backupFile = "housinghub_backup_" . date("Y-m-d_H-i-s") . ".sql";

// Use mysqldump if available
$command = "mysqldump --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} > {$backupFile}";

// Execute command
exec($command);

// Force download
if (file_exists($backupFile)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="'.basename($backupFile).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($backupFile));
    readfile($backupFile);
    unlink($backupFile); // remove the temporary file
    exit;
} else {
    echo "Backup failed. Make sure mysqldump is available.";
}
?>