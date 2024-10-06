<?php

/**
 * @file fishbowl/fishbowl.php
 * @author Ben Shealy
 * modified by Jai Agarwal
 * 
 * Get the current fishbowl.
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("config.php");

/**
 * Get the current fishbowl leaderboard.
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

	$q = "SELECT " . implode(",", $keys) . " FROM `fishbowl_leaderboard` AS f "
		. "INNER JOIN `users` AS u ON u.username=f.username "
		. "ORDER BY f.rank ASC;";
	$result = exec_query($mysqli, $q);

	// compute the number of album reviews
	$q = "SELECT COUNT(*) FROM `libreview` AS r "
	. "WHERE r.username = '$app[username]' "
	. "AND " . REVIEW_BEGIN . " <= UNIX_TIMESTAMP(r.review_date) "
	. "AND UNIX_TIMESTAMP(r.review_date) <= " . DEADLINE . ";";
	$reviews = exec_query($mysqli, $q);

	return fetch_array($result);
}

/**
 * Reset the current fishbowl leaderboard
 *
 * @param mysqli
 */
function archive_fishbowl($mysqli)
{
	$q = "UPDATE `fishbowl_leaderboard` SET points=0;";
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
