<?php

/**
 * @file users/admin.php
 * @author Ben Shealy
 */
require_once("../auth.php");
require_once("../connect.php");

/**
 * Get a list of users.
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
		"u.teamID",
		"u.statusID"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `users` AS u "
		. "WHERE 1;";
	$result = $mysqli->query($q);

	$users = array();
	while ( ($u = $result->fetch_assoc()) ) {
		$users[] = $u;
	}

	return $users;
}

/**
 * Update users.
 *
 * @param mysqli  MySQL connection
 * @param users   array of usernames, status IDs and team IDs
 */
function update_users($mysqli, $users)
{
	foreach ( $users as $u ) {
		$q = "UPDATE `users` SET "
			. "statusID = '$u[statusID]',"
			. "teamID = '$u[teamID]' "
			. "WHERE username = '$u[username]';";
		$mysqli->query($q);
	}
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
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
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	$users = json_decode(file_get_contents("php://input"), true);
	$users = escape_json($mysqli, $users);

	update_users($mysqli, $users);
	$mysqli->close();

	exit;
}
?>
