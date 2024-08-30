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
function send_mail($to, $subject, $message)
{
//    $headers = "From: WSBF Computer Engineer <computer@wsbf.net>\r\n"
//            . "Content-Type: text/plain; charset=utf-8\r\n";

//    $headers = "MIME-Version: 1.0\r\n"
//            . "Subject: $subject\r\n"
//            . "X-Mailer: PHP/" . phpversion() . "\r\n";

	//Create an instance; passing `true` enables exceptions
	$mail = new PHPMailer(true);

	try {
		//Server settings
		$mail->SMTPDebug  = SMTP::DEBUG_SERVER;                   //Enable verbose debug output
		$mail->isSMTP();                                          //Send using SMTP
		$mail->Host       = getenv('SMTP_SERVER');                //Set the SMTP server to send through
		$mail->SMTPAuth   = true;                                 //Enable SMTP authentication
		$mail->Username   = getenv('SMTP_EMAIL');                 //SMTP username
		$mail->Password   = getenv('SMTP_PASS');                  //SMTP password
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;          //Enable implicit TLS encryption
		$mail->Port       = getenv('SMTP_PORT');                  

		//Recipients
		$mail->setFrom(getenv('SMTP_EMAIL'));
		$mail->addAddress($to);     //Add a recipient


		//Content
		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = $message;

		$mail->send();

    } catch (Exception $e) {
	    file_put_contents('php://stderr', print_r("Message could not be sent. Mailer Error: {$mail->ErrorInfo}", TRUE));
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

        $success = send_mail(
                $user["email_addr"],
                "Password Reset Request",
                "Go to this link to reset your password: https://wsbf.net/login/#!/reset-password/$transaction_id"
        );

        $mysqli->close();

        header("Content-Type: application/json");
        exit(json_encode($success));
}
?>