<?php
require '../config.php';
require_once 'lib/phpmailer/Exception.php';
require_once 'lib/phpmailer/PHPMailer.php';
require_once 'lib/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function emailLogs($conversationId, $senderEmail, $receiverEmail, $chatLogs) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Server settings
        if (SMTP) {
            $mail->isSMTP();
            $mail->Host = smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = smtp_user;
            $mail->Password = smtp_pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = smtp_port;
        }

        // Recipients
        $mail->setFrom(mail_from, mail_name);
        $mail->addAddress($senderEmail);
        $mail->addAddress($receiverEmail);
        $mail->addReplyTo(mail_from, mail_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Chat Log';

        // Body
        $mail->Body = generateEmailBody($chatLogs);
        $mail->AltBody = strip_tags($mail->Body);

        // Send mail
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Output error message
        exit('Error: Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
    }
}

function generateEmailBody($chatLogs) {
    // Customize the email body based on your requirements
    $body = '<h1>Chat Log</h1>';
    foreach ($chatLogs as $date => $messages) {
        $body .= '<p>Date: ' . $date . '</p>';
        foreach ($messages as $message) {
            $body .= '<p><strong>' . date('H:i A', strtotime($message['submit_date'])) . ' by ' . htmlspecialchars($message['full_name'], ENT_QUOTES) . '</strong>: ' . nl2br(htmlspecialchars($message['msg'], ENT_QUOTES)) . '</p>';
        }
    }
    return $body;
}

// Usage example:
// emailLogs($conversation['id'], $conversation['account_sender_email'], $conversation['account_receiver_email'], $conversation['messages']);
?>
