<?php

/**
 * @file schedule/functions.php
 * @author Ben Shealy
 */

/**
 * Get a show in the schedule.
 *
 * @param mysqli
 * @param scheduleID
 * @return associative array of show
 */
function get_schedule_show($mysqli, $scheduleID)
{
	// get show
	$keys = array(
		"s.scheduleID",
		"s.show_name",
		"s.dayID",
		"s.show_timeID",
		"s.show_typeID",
		"s.description",
		"s.general_genreID"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `schedule` AS s "
		. "WHERE s.scheduleID = '$scheduleID';";
	$show = exec_query($mysqli, $q)->fetch_assoc();

	// get show hosts
	$host_keys = array(
		"u.username",
		"u.preferred_name",
		"h.schedule_alias"
	);

	$q = "SELECT " . implode(",", $host_keys) . " FROM `schedule_hosts` AS h "
		. "INNER JOIN `users` AS u ON h.username=u.username "
		. "WHERE h.scheduleID = '$scheduleID';";
	$result = exec_query($mysqli, $q);

	$show["hosts"] = fetch_array($result);

	return $show;
}
?>
