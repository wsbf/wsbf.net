<?php

/**
 * @file schedule/schedule.php
 * @author Ben Shealy
 */
require_once("../connect.php");

/**
 * Get the schedule for a day of the week.
 *
 * @param mysqli  MySQL connection
 * @param day     index of day
 * @return array of daily schedule
 */
function get_schedule($mysqli, $day)
{
	/* get schedule */
	$keys = array(
		"s.scheduleID",
		"s.show_name",
		"s.show_typeID",
		"s.start_time",
		"s.end_time"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `schedule` AS s "
		. "WHERE s.active=1 AND s.dayID='$day' "
		. "ORDER BY s.start_time ASC;";
	$result = $mysqli->query($q);

	$schedule = array();
	while ( ($s = $result->fetch_assoc()) ) {
		/* get hosts for each show */
		$q = "SELECT u.preferred_name FROM `schedule_hosts` AS h "
			. "INNER JOIN `users` AS u ON u.username=h.username "
			. "WHERE h.scheduleID='$s[scheduleID]';";
		$result_hosts = $mysqli->query($q);

		$s["hosts"] = array();
		while ( ($h = $result_hosts->fetch_assoc()) ) {
			$s["hosts"][] = $h["preferred_name"];
		}

		$schedule[] = $s;
	}

	return $schedule;
}

$day = $_GET["day"];

if ( !is_numeric($day) || $day < 0 || 6 < $day ) {
	header("HTTP/1.1 404 Not Found");
	exit("Day of week is empty or invalid.");
}

$mysqli = construct_connection();
$schedule = get_schedule($mysqli, $day);
$mysqli->close();

header("Content-Type: application/json");
exit(json_encode($schedule));
?>
