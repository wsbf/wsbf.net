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
 * @param mysqli  MySQL connection
 * @return array of fishbowl applications
 */
function get_fishbowl($mysqli)
{
	$keys = array(
		"f.id",
		"f.average",
		"f.weight",
		"u.preferred_name"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `fishbowl` AS f "
		. "INNER JOIN `users` AS u ON u.username=f.username;";
	$result = $mysqli->query($q);

	$fishbowl = array();
	while ( ($f = $result->fetch_assoc()) ) {
		$fishbowl[] = $f;
	}

	return $fishbowl;
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to view the fishbowl.");
	}

	$fishbowl = get_fishbowl($mysqli);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($fishbowl));
}
?>
