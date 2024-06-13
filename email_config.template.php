<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendMagicLink($email, $token) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 0;                      // Enable verbose debug output (0 for no output)
        $mail->isSMTP();                           // Set mailer to use SMTP
        $mail->Host       = 'smtp server';    // Specify main and backup SMTP servers
        $mail->SMTPAuth   = true;                  // Enable SMTP authentication
        $mail->Username   = 'smtp username'; // SMTP username
        $mail->Password   = 'smtp password';  // SMTP password
        $mail->SMTPSecure = 'tls';                 // Enable TLS encryption, `ssl` also accepted
        $mail->Port       = 587;                   // TCP port to connect to

        // UTF-8 encoding
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom('sender@email', 'Sender Name');
        $mail->addAddress($email);                 // Add a recipient

        // Content
        $mail->isHTML(true);                       // Set email format to HTML
        $mail->Subject = 'Mail subject';
        $link = "https://domain.com/login.php?token=$token";
        $mail->Body    = "Klikk på lenken for å logge inn: <a href='$link'>$link</a>";
        $mail->AltBody = "Klikk på lenken for å logge inn: $link";

        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
