<?php

/**
 * @file auth/password_request.php
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
 * @return true if mail was accepted, false otherwise
 */
function send_mail($to, $subject, $message)
{
	$headers = "From: WSBF Computer Engineer <computer@wsbf.net>\r\n"
		. "Content-Type: text/plain; charset=utf-8\r\n";

//	$headers = "MIME-Version: 1.0\r\n"
//		. "Subject: $subject\r\n"
//		. "X-Mailer: PHP/" . phpversion() . "\r\n";

	return mail($to, $subject, $message, $headers);
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
