<?php
// Load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'libs/PHPMailer/src/Exception.php';
require 'libs/PHPMailer/src/PHPMailer.php';
require 'libs/PHPMailer/src/SMTP.php';

// Function to send registration confirmation email
function sendRegistrationEmail($student_email, $reference_id) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'pupccisfaculty@gmail.com'; // Your Gmail
        $mail->Password = 'cexx vahv kupw rbmb'; // The app-specific password
        $mail->SMTPSecure = 'tls'; // Enable TLS encryption
        $mail->Port = 587; // TCP port to connect to

        // Recipients
        $mail->setFrom('pupccisfaculty@gmail.com', 'PUP CCIS Faculty'); // Sender email and name
        $mail->addAddress($student_email); // Student email

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Registration is Successful!';
        $mail->Body = "Dear Student,<br><br>Your registration is complete. Your Reference ID is: <strong>$reference_id</strong>.<br><br>Best regards,<br>PUP CCIS Faculty";

        $mail->send();
        return true;

    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
