<?php

/**
 * @file users/users_admin.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get a list of users.
 *
 * @param mysqli
 * @return array of users
 */
function get_users($mysqli)
{
	$keys = array(
		"u.username",
		"u.first_name",
		"u.last_name",
		"u.preferred_name",
		"u.email_addr",
		"u.teamID",
		"u.statusID"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `users` AS u "
		. "WHERE 1;";
	$result = exec_query($mysqli, $q);

	$users = array();
	while ( ($u = $result->fetch_assoc()) ) {
		$users[] = $u;
	}

	return $users;
}

/**
 * Validate a user.
 *
 * @param mysqli
 * @param user
 * @return true if user is valid, false otherwise
 */
function validate_user($mysqli, $user)
{
	if ( empty($user["username"])
	  || empty($user["statusID"]) ) {
		return false;
	}

	return true;
}

/**
 * Update a user.
 *
 * @param mysqli
 * @param user
 */
function update_user($mysqli, $user)
{
	$q = "UPDATE `users` SET "
		. "statusID = '$user[statusID]',"
		. "teamID = '$user[teamID]' "
		. "WHERE username = '$user[username]';";
	exec_query($mysqli, $q);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$users = get_users($mysqli);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($users));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$user = json_decode(file_get_contents("php://input"), true);
	$user = escape_json($mysqli, $user);

	if ( !validate_user($mysqli, $user) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Invalid input.");
	}

	update_user($mysqli, $user);
	$mysqli->close();

	exit;
}
?>
