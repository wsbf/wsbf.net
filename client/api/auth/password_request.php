<?php

/**
 * @file auth/password_request.php
 * @author ben shealy
 * edits by jai agarwal
 */
require_once("../connect.php");
//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '/usr/share/php/PHPMailer/src/Exception.php';
require '/usr/share/php/PHPMailer/src/PHPMailer.php';
require '/usr/share/php/PHPMailer/src/SMTP.php';

// Fetching environment variables
$smtp_server = getenv("SMTP_SERVER");
$smtp_user   = getenv("SMTP_EMAIL");
$smtp_pass   = getenv("SMTP_PASS");
$smtp_port   = getenv("SMTP_PORT");

/**
 * Send an email.
 *
 * Using PHPMailer to send an email with SMTP
 * through our new mail hosting service (Private Email).
 * SMTP credentials are set as environment variables.
 *
 * @param to       recipient email address
 * @param subject  subject
 * @param message  message
 * @return true if mail was sent, false otherwise
 */
function send_mail($to, $subject, $message, $smtp_server, $smtp_port, $smtp_user, $smtp_pass)
{
    // Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug  = SMTP::DEBUG_SERVER;          // Enable verbose debug output
        $mail->isSMTP();                                 // Send using SMTP
        $mail->Host       = $smtp_server;                // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                        // Enable SMTP authentication
        $mail->Username   = $smtp_user;                  // SMTP username
        $mail->Password   = $smtp_pass;                  // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable implicit TLS encryption
        $mail->Port       = $smtp_port;                  // TCP port to connect to

        // Recipients
        $mail->setFrom('computer@wsbf.net', 'Otto Mation');
        $mail->addAddress($to); // Add a recipient

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = $message;

        // Return the result of the send() method
        return $mail->send();
    } catch (Exception $e) {
        // Log the error message for debugging purposes
        file_put_contents('php://stderr', print_r("Message could not be sent. Mailer Error: {$mail->ErrorInfo}", TRUE));
        return false;
    }
}

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {

        $mysqli = construct_connection();

        // validate username
        $username = $mysqli->escape_string($_GET["username"]);

        $q = "SELECT username, email_addr FROM `users` WHERE username='$username';";
        $result = exec_query($mysqli, $q);

        if ( $result->num_rows == 0 ) {
                header("HTTP/1.1 404 Not Found");
                exit("Invalid username '$username'.");
        }

        // insert password reset request
        $transaction_id = bin2hex(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM));

        $q = "DELETE FROM `password_reset` WHERE username = '$username';";
        exec_query($mysqli, $q);

        $q = "INSERT INTO `password_reset` SET "
                . "transaction_id = '$transaction_id',"
                . "username = '$username',"
                . "expiration_date = ADDDATE(CURDATE(), 7);";
        exec_query($mysqli, $q);

        // send email to user
        $user = $result->fetch_assoc();

        $success = send_mail( $user["email_addr"],
                "Password Reset Request",
                "Go to this link to reset your password: https://wsbf.net/login/#!/reset-password/$transaction_id",
                 $smtp_server, $smtp_port, $smtp_user, $smtp_pass);

        $mysqli->close();

       // chatgpt says this will clear the output buffer
       // and cause the page to redirect properly
       if (ob_get_length()) {
           ob_end_clean();
       }

        header("Content-Type: application/json");
        exit(json_encode($success));
}
?>