<?php

/**
 * @file login.php
 * @author Ben Shealy
 *
 * @section DESCRIPTION
 *
 * Attempt to login a user with username and password.
 *
 * Currently the password is sent as plaintext, so we should
 * either add TLS encryption to the web server or have the
 * client encrypt the password before submitting.
 *
 * There are two foreseeable ways to implement login:
 *  1) redirect to login or index
 *  2) return a status and any error message for client to interpret
 */
require_once("connect.php");

// TODO: move into script for hash functions
/**
 * Validate a password against a hash.
 *
 * This function is based on the existing production code,
 * but in the future it may be better to use a library that
 * handles salting, hashing, and validating.
 *
 * @param password
 * @param hash
 * @return true if the password hashes to the given hash, false otherwise
 */
function validate_password($password, $hash)
{
	$password = md5($password);
	$salt = substr($hash, 0, 64);
	$valid_hash = substr($hash, 64, 128);

	$test_hash = hash("sha512", $salt . $password);

	return $test_hash === $valid_hash;
}

session_start();

/* return error if credentials are missing */
if ( empty($_POST["username"]) || empty($_POST["password"]) ) {
	// header("HTTP/1.1 401 Unauthorized");
	// exit("Username and/or password is empty.");

	header("Location: /login.html");
	exit;
}

$mysqli = construct_connection();

$username = $mysqli->escape_string($_POST["username"]);
$password = $mysqli->escape_string($_POST["password"]);

/* retrieve the user's password hash */
$q = "SELECT password FROM users WHERE username='$username';";
$result = $mysqli->query($q)->fetch_assoc();
$hash = $result["password"];

if ( !isset($hash) || !validate_password($password, $hash) ) {
	// header("HTTP/1.1 401 Unauthorized");
	// exit("Invalid credentials.");

	header("Location: /login.html");
	exit;
}

/* add user to current session */
$_SESSION["username"] = $username;

$mysqli->close();

header("Location: /wizbif/");
exit;
?>
