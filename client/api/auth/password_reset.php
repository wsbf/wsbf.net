<?php

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
	$result = $mysqli->query($q);

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
	$mysqli->query($q);

	$q = "DELETE FROM `password_reset` "
		. "WHERE username='$username';";
	$mysqli->query($q);

	$mysqli->close();

	exit;
}
?>
