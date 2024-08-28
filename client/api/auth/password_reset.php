<?php

error_reporting(-1);
ini_set('display_errors', 'On');
set_error_handler("var_dump");

/**
 * @file auth/password_reset.php
 * @author Ben Shealy
 */
require_once("../connect.php");
require_once("functions.php");

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	$data = json_decode(file_get_contents("php://input"), true);
	$data = escape_json($mysqli, $data);

	// validate transaction
	$q = "SELECT username FROM `password_reset` "
		. "WHERE transaction_id='$data[transactionID]';";
	$result = exec_query($mysqli, $q);

	if ( $result->num_rows == 0 ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	// update password
	$row = $result->fetch_assoc();
	$username = $row["username"];
	$password_hash = hash_password($data["password"]);

	$q = "UPDATE `users` SET "
		. "password = '$password_hash' "
		. "WHERE username='$username';";
	exec_query($mysqli, $q);

	$q = "DELETE FROM `password_reset` "
		. "WHERE username='$username';";
	exec_query($mysqli, $q);

	$mysqli->close();

	exit;
}
?>
