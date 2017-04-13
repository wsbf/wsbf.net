<?php

/**
 * @file schedule/schedule.php
 * @author Ben Shealy
 *
 * Get or remove the current show schedule.
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get the schedule for a day of the week.
 *
 * @param mysqli
 * @param dayID
 * @return array of daily schedule
 */
function get_schedule($mysqli, $dayID)
{
	// get schedule
	$keys = array(
		"s.scheduleID",
		"s.dayID",
		"s.show_timeID",
		"s.show_name",
		"s.show_typeID"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `schedule` AS s "
		. "WHERE s.active=1 AND s.dayID='$dayID' "
		. "ORDER BY s.show_timeID;";
	$result = exec_query($mysqli, $q);

	$schedule = fetch_array($result);

	// get hosts for each show
	foreach ( $schedule as &$s ) {
		$q = "SELECT u.preferred_name FROM `schedule_hosts` AS h "
			. "INNER JOIN `users` AS u ON u.username=h.username "
			. "WHERE h.scheduleID='$s[scheduleID]';";
		$result_hosts = exec_query($mysqli, $q);

		$s["hosts"] = fetch_array($result_hosts);
	}
	unset($s);

	return $schedule;
}

/**
 * Remove the entire show schedule.
 *
 * @param mysqli
 */
function remove_schedule($mysqli)
{
	$q = "UPDATE `schedule` SET "
		. "active = 0 "
		. "WHERE active = 1;";
	exec_query($mysqli, $q);
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$dayID = $_GET["day"];

	if ( !is_numeric($dayID) || $dayID < 0 || 6 < $dayID ) {
		header("HTTP/1.1 404 Not Found");
		exit("Day of week is empty or invalid.");
	}

	$mysqli = construct_connection();
	$schedule = get_schedule($mysqli, $dayID);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($schedule));
}
else if ( $_SERVER["REQUEST_METHOD"] == "DELETE" ) {
	authenticate();
	$mysqli = construct_connection();

	if ( !auth_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	remove_schedule($mysqli);
	$mysqli->close();

	exit;
}
?>
