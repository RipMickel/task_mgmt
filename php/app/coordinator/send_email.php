<?php
// send_email.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Send email notification using Gmail SMTP.
 *
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body HTML body of the email
 * @return bool True on success, False on failure
 */
function sendEmailNotification($to, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'gaudicosmickelangelo@gmail.com';  // your Gmail
        $mail->Password   = 'zpdc qvzx oahp eprr';                 // your 16-character App Password, no spaces!
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // From and To
        $mail->setFrom('gaudicosmickelangelo@gmail.com', 'Task Management');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // fallback for non-HTML clients

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log detailed error to PHP error log
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");

        // Show error in browser for debugging (remove in production)
        echo "<strong>Email sending failed:</strong> {$mail->ErrorInfo}";
        return false;
    }
}
