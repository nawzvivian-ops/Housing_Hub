<?php
require_once 'send_mail.php';
$result = send_mail('nawzvivian@gmail.com', 'HousingHub Test', 'Email is working!');
echo $result ? '✅ Email sent!' : '❌ Failed — check error log';
?>