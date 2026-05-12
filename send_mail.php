
<?php
// ═══════════════════════════════════════════════
//  HousingHub — Central Mail Helper
//  Usage: send_mail($to, $subject, $body)
// ═══════════════════════════════════════════════
 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
 
require_once __DIR__ . '/vendor/autoload.php';
 
function send_mail($to, $subject, $body, $is_html = false, $attachments = []) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'nawzvivian@gmail.com';
        $mail->Password   = 'gatecynxwpqlzirl';   // no spaces
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
 
        $mail->setFrom('nawzvivian@gmail.com', 'HousingHub'); // must match Username
        $mail->addAddress($to);
        $mail->addReplyTo('nawzvivian@gmail.com', 'HousingHub Support');

        foreach ($attachments as $attachment) {
            if (!empty($attachment['data']) && !empty($attachment['name'])) {
                $mail->addStringAttachment(
                    $attachment['data'],
                    $attachment['name'],
                    'base64',
                    $attachment['type'] ?? 'application/octet-stream'
                );
            } elseif (!empty($attachment['path'])) {
                $mail->addAttachment($attachment['path'], $attachment['name'] ?? '');
            }
        }
 
        $mail->isHTML($is_html);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        if ($is_html) {
            $mail->AltBody = strip_tags($body);
        }
 
        $mail->send();
        return true;
 
    } catch (Exception $e) {
        error_log("HousingHub Mail Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>
