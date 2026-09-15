<?php

/**
 * @file users/users_admin.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("../fishbowl/config.php");

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

	$users = fetch_array($result);
	foreach ($users as &$user) {
		$user["attendanceFlag"] = false;
		$user["missedMeetings"] = array();
		$user["virtualAttendances"] = array();
		if ((int) $user["statusID"] !== 0) {
			continue;
		}
		$username = $mysqli->escape_string($user["username"]);
		$missed = "SELECT e.eventID, e.name, e.event_date AS eventDate FROM attendance_events e "
			. "WHERE e.event_type='full_staff' AND e.event_date < CURDATE() "
			. "AND UNIX_TIMESTAMP(e.event_date) BETWEEN " . REVIEW_BEGIN . " AND " . DEADLINE . " "
			. "AND NOT EXISTS (SELECT 1 FROM attendance a WHERE a.eventID=e.eventID AND a.username='$username') "
			. "ORDER BY e.event_date;";
		$user["missedMeetings"] = fetch_array(exec_query($mysqli, $missed));
		$virtual = "SELECT e.eventID, e.name, e.event_date AS eventDate FROM attendance_events e "
			. "INNER JOIN attendance a ON a.eventID=e.eventID AND a.username='$username' "
			. "WHERE e.event_type='virtual_full_staff' AND UNIX_TIMESTAMP(e.event_date) BETWEEN " . REVIEW_BEGIN . " AND " . DEADLINE . " "
			. "ORDER BY e.event_date;";
		$user["virtualAttendances"] = fetch_array(exec_query($mysqli, $virtual));
		$user["attendanceFlag"] = count($user["missedMeetings"]) > 1;
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
	  || !is_numeric($user["statusID"]) ) {
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

	if ( !auth_senior_staff($mysqli) ) {
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

	if ( !auth_senior_staff($mysqli) ) {
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
