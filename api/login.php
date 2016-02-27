<?php

/**
 * @file login.php
 * @author Ben Shealy
 *
 * Attempt to login a user with username and password.
 *
 * There are two foreseeable ways to implement login:
 *  1) redirect to login or index
 *  2) return a status and any error message for client to interpret
 */
require_once("connect.php");
require_once("password_functions.php");

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
