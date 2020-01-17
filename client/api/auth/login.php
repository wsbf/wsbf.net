<?php
header("Access-Control-Allow-Origin: *");

/**
 * @file login.php
 * @author Ben Shealy
 *
 * Attempt to login a user with username and password.
 */
require_once("../connect.php");
require_once("functions.php");

session_start();

$mysqli = construct_connection();

$data = json_decode(file_get_contents("php://input"), true);
$data = escape_json($mysqli, $data);

if ( empty($data["username"]) || empty($data["password"]) ) {
	header("HTTP/1.1 401 Unauthorized");
	exit("Username and/or password is empty.");
}

/* retrieve the user's password hash */
$q = "SELECT password FROM users WHERE username='$data[username]';";
$user = exec_query($mysqli, $q)->fetch_assoc();

$hash = $user["password"];

if ( !isset($hash) || !validate_password($data["password"], $hash) ) {
	header("HTTP/1.1 401 Unauthorized");
	exit("Invalid credentials.");
}

/* add user to current session */
$_SESSION["username"] = $data["username"];

$mysqli->close();

header("Content-Type: application/json");
exit(json_encode("/dj/"));
?>
