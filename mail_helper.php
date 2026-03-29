<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

function send_mail($to, $subject, $body, $is_html = false) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'nawzvivian@gmail.com';
        $mail->Password   = 'gatecynxwpqlzirl'; // your app password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('nawzvivian@gmail.com', 'HousingHub');
        $mail->addAddress($to);

        $mail->isHTML($is_html);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        if ($is_html) {
            $mail->AltBody = strip_tags($body);
        }

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
        return false;
    }
}