<?php

/**
 * @file zautomate/automation_generate_showid.php
 * @author Ben Shealy
 */
require_once("../connect.php");

/**
 * Get a random show ID from the last 100 rotation shows,
 * excluding the previous show played by Automation.
 *
 * @param mysqli  MySQL connection
 * @param prev    previous show ID
 * @return random show ID
 */
function get_random_show($mysqli, $prev)
{
	$q = "SELECT showID FROM `show` "
		. "WHERE showID != '$prev' "
		. "AND show_typeID = 0 "
		. "AND TIMESTAMPDIFF(MINUTE, start_time, end_time) > 60 "
		. "ORDER BY showID DESC "
		. "LIMIT 100;";
	$result = $mysqli->query($q);

	$shows = array();
	while ( ($s = $result->fetch_assoc()) ) {
		$shows[] = $s;
	}

	$i = rand(0, count($shows) - 1);

	return (int) $shows[$i]["showID"];
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$prev = $_GET["showid"];

	if ( !is_numeric($prev) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$mysqli = construct_connection();
	$showID = get_random_show($mysqli, $prev);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($showID));
}
?>
