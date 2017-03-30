<?php

/**
 * @file users/users.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get the list of active users.
 *
 * @param mysqli
 * @return array of active users
 */
function get_users($mysqli)
{
	$keys = array(
		"u.username",
		"u.first_name",
		"u.last_name",
		"u.preferred_name",
		"u.email_addr",
		"u.teamID"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `users` AS u "
		. "WHERE u.statusID = 0;";
	$result = exec_query($mysqli, $q);

	return fetch_array($result);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_member($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$users = get_users($mysqli);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($users));
}
?>
