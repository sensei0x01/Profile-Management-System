<?php 
if (!defined('ALLOW_ACCESS')) {
    exit("Access denied.");
}else {
    require_once('config.php');
    session_start();
    if (defined('VERIFY') || defined('LOGIN') || defined('PROFILE') ) {
        $otp = generateNumericOTP(6);
        $sql = "UPDATE `users` SET otp = '{$otp}' WHERE username = '".$_SESSION['username']."'";
        $updateResult = mysqli_query($conn, $sql);
        if ($updateResult) {
            $_SESSION["otp"] = $otp;
        } else {
            $_SESSION['error_message'] = "Creating otp code failed. Please try again later";
        }
    }
    if (isset($_SESSION['update_data'])) {
        $send_to =substr($_SESSION['update_data']['email'],9,-1);
    }else {
        $send_to = $_SESSION['email'];
    }
}



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


// Include the PHPMailer library files
require('PHPMailer/src/Exception.php');
require('PHPMailer/src/PHPMailer.php');
require('PHPMailer/src/SMTP.php');

// Create a new instance of PHPMailer
$mail = new PHPMailer(true);

try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host       = MAILHOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = USERNAME; // Your Gmail address
    $mail->Password   = PASSWORD;  // Your Gmail password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom(SEND_FROM, 'Profile Management System'); // Your Gmail address and website name

    $mail->addAddress($send_to, $_SESSION['fname']); // Recipient's email address and name

    // Content
    $mail->isHTML(true);                                  
    $mail->Subject = 'Welcome! Confirm Your Email for Sign-Up Verification';
    $mail->Body    = "
        <h2>Thank You for Signing Up!</h2>
        <p>To complete your registration, please verify your email address by entering the following verification code:</p>
        <p><b>".$_SESSION["otp"]."</b></p>
        <p>If you did not sign up, please ignore this email.</p>
        <br>
        <p>Best regards,<br>Your Website Team</p>";
    $mail->AltBody = "Thank you for signing up! To complete your registration, please verify your email address by entering the following verification code: ".$_SESSION["otp"]." .";

    // Send the email
    $mail->send();
    $_SESSION['error_message'] = 'Check your email for verification code';
    header('Location: verify.php');
    exit();

} catch (Exception $e) {
    $_SESSION['error_message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    header('Location: verify.php');
    exit();
}




?>
