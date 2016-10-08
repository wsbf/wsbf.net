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
		"d.day",
		"s.start_time",
		"s.end_time",
		"t.type",
		"s.description",
		"s.genre"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `schedule` AS s "
		. "INNER JOIN `def_days` AS d ON s.dayID=d.dayID "
		. "INNER JOIN `def_show_types` AS t ON t.show_typeID=s.show_typeID "
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
