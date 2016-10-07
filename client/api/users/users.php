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
 * @param mysqli  MySQL connection
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

	$users = array();
	while ( ($u = $result->fetch_assoc()) ) {
		$users[] = $u;
	}

	return $users;
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	$users = get_users($mysqli);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($users));
}
?>
