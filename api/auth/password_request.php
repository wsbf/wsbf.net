<?php

/**
 * @file password/request.php
 * @author Ben Shealy
 */
require_once("../connect.php");

/**
 * Send an email.
 *
 * TODO: Although this function seems to be correct, the
 * mail that it sends is likely rejected by most
 * mail servers. More research should be done on
 * sendmail to make this function work.
 *
 * @param to       recipient email address
 * @param subject  subject
 * @param message  message
 */
function send_mail($to, $subject, $message)
{
	$headers = array(
		"MIME-Version: 1.0",
		"Content-Type: text/plain; charset=utf-8",
		"From: WSBF Computer Engineer <computer@wsbf.net>",
		"Subject: $subject",
		"X-Mailer: PHP/" . phpversion()
	);

	mail($to, $subject, $message, implode("\r\n", $headers));
}

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	// validate username
	$username = $mysqli->escape_string($_GET["username"]);

	$q = "SELECT username, email_addr FROM `users` WHERE username='$username';";
	$result = $mysqli->query($q);

	if ( $result->num_rows == 0 ) {
		header("HTTP/1.1 404 Not Found");
		exit("Invalid username '$username'.");
	}

	// insert password reset request
	$transaction_id = bin2hex(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM));

	$q = "INSERT INTO `password_reset` SET "
		. "transaction_id = '$transaction_id',"
		. "username = '$username',"
		. "expiration_date = ADDDATE(CURDATE(), 7);";
	$mysqli->query($q);

	// send email to user
//	$user = $result->fetch_assoc();

//	send_mail(
//		$user["email_addr"],
//		"Password Reset Request",
//		"Go to this link to reset your password: https://dev.wsbf.net/reset_password.php?transaction_id=$transaction_id"
//	);

	$mysqli->close();

	exit;
}
?>
