<?php

/**
 * @file register.php
 * @author Ben Shealy
 *
 * Register a new user.
 */
require_once("connect.php");
require_once("password_functions.php");

/**
 * Validate a user.
 *
 * @param mysqli  MySQL connection
 * @param user    associative array of user
 * @return error message if error, null otherwise
 */
function validate_user($mysqli, $user)
{
	// required fields should be defined
	if ( empty($user["username"])
	  || empty($user["password"])
	  || empty($user["first_name"])
	  || empty($user["last_name"])
	  || empty($user["email_addr"]) ) {
		return "Required fields are missing.";
	}

	// username should be available
	$q = "SELECT username FROM `users` WHERE username='$user[username]';";
	$result = $mysqli->query($q);

	if ( $result->num_rows > 0 ) {
		return "Username is already in use.";
	}

	// email should be valid (and unique?)
	if ( !filter_var($user["email_addr"], FILTER_VALIDATE_EMAIL) ) {
		return "Email address is invalid.";
	}

	return null;
}

/**
 * Register a new user.
 *
 * @param mysqli  MySQL connection
 * @param user    associative array of user
 */
function register_user($mysqli, $user)
{
	$hashedPassword = hash_password($user["password"]);

	$q = "INSERT INTO `users` SET "
		. "username = '$user[username]', "
		. "password = '$hashedPassword', "
		. "first_name = '$user[first_name]', "
		. "last_name = '$user[last_name]', "
		. "preferred_name = '$user[first_name] $user[last_name]', "
		. "email_addr = '$user[email_addr]';";
	$mysqli->query($q);
}

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	$user = json_decode(file_get_contents("php://input"), true);
	$user = escape_json($mysqli, $user);

	$error = validate_user($mysqli, $user);

	if ( $error ) {
		header("HTTP/1.1 404 Not Found");
		exit($error);
	}

	register_user($mysqli, $user);

	$mysqli->close();
	exit;
}
?>
