<?php

/**
 * @file fishbowl/archive.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");

// TODO: maybe move to DELETE fishbowl.php
/**
 * Archive the current fishbowl.
 *
 * @param mysqli  MySQL connection
 */
function archive_fishbowl($mysqli)
{
	$keys = array(
		"timestamp",
		"username",
		"semesters",
		"missedShows",
		"liveShows",
		"springFest",
		"specialty",
		"dead_hours",
		"other",
		"average",
		"weight"
	);

	$q = "INSERT INTO `fishbowl_log` (" . implode(",", $keys) . ") "
		. "SELECT " . implode(",", $keys) . " FROM `fishbowl`;";
	$mysqli->query($q);

	// DELETE is slow, but TRUNCATE requires DROP privilege
	$mysqli->query("DELETE FROM `fishbowl`;");
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to view the fishbowl.");
	}

	archive_fishbowl($mysqli);
	$mysqli->close();
	exit;
}
?>
