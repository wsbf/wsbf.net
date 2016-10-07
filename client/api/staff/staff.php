<?php

/**
 * @file staff/staff.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get the list of senior staff.
 *
 * @param mysqli
 * @return array of senior staff
 */
function get_staff($mysqli)
{
	$keys = array(
		"s.positionID",
		"s.username",
		"s.start_date",
		"s.end_date",
		"u.preferred_name"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `staff` AS s "
		. "INNER JOIN `users` AS u ON u.username=s.username "
		. "WHERE NOW() <= s.end_date;";
	$result = exec_query($mysqli, $q);

	return fetch_array($result);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$staff = get_staff($mysqli);

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($staff));
}
?>
