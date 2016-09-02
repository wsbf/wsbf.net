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
	$q = "UPDATE `fishbowl` SET active=0 WHERE active=1;";
	$mysqli->query($q);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	archive_fishbowl($mysqli);
	$mysqli->close();
	exit;
}
?>
