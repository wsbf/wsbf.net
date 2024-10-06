<?php

/**
 * @file fishbowl/fishbowl.php
 * @author Ben Shealy
 *
 * Get the current fishbowl.
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get the current fishbowl.
 *
 * @param mysqli
 * @return array of fishbowl applications
 */
function get_fishbowl($mysqli)
{
	$keys = array(
		"f.points",
		"u.username",
		"u.preferred_name",
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `fishbowl_log` AS f "
		. "INNER JOIN `users` AS u ON u.username=f.username; ";
	$result = exec_query($mysqli, $q);

	return fetch_array($result);
}

/**
 * Archive the current fishbowl.
 *
 * @param mysqli
 */
function archive_fishbowl($mysqli)
{
	$q = "UPDATE `fishbowl` SET active=0 WHERE active=1;";
	exec_query($mysqli, $q);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !auth_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$fishbowl = get_fishbowl($mysqli);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($fishbowl));
}
else if ( $_SERVER["REQUEST_METHOD"] == "DELETE" ) {
	$mysqli = construct_connection();

	if ( !auth_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	archive_fishbowl($mysqli);
	$mysqli->close();

	exit;
}
?>
