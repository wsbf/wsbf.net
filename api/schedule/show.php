<?php

// TODO: rename to schedule/show.php, add GET, POST (update), and DELETE
/**
 * @file schedule/show.php
 * @author Ben Shealy
 *
 * @section DESCRIPTION
 *
 * Add a show to the schedule.
 */
require_once("../auth.php");
require_once("../connect.php");

/**
 * Validate a show.
 *
 * @param mysqli  MySQL connection
 * @param show    associative array of show
 * @return true if show is valid, false otherwise
 */
function validate_show($mysqli, $show)
{
	if ( !is_numeric($show["dayID"])
	  || empty($show["start_time"])
	  || empty($show["end_time"])
	  || !is_numeric($show["show_typeID"])
	  || !is_array($show["hosts"]) ) {
		return false;
	}

	// end time should be after start time
	if ( $show["start_time"] != "23:00:00"
		&& $show["end_time"] <= $show["start_time"] ) {
		return false;
	}

	// show should have at least one host
	if ( count($show["hosts"]) < 1 ) {
		return false;
	}

	// show should not start at the same time as another active show
	$q = "SELECT s.scheduleID FROM `schedule` AS s "
		. "WHERE s.active=1 AND s.dayID='$show[dayID]' "
		. "AND s.start_time='$show[start_time]';";
	$result = $mysqli->query($q);

	if ( $result->num_rows > 0 ) {
		return false;
	}

	return true;
}

/**
 * Add a show to the schedule.
 *
 * @param mysqli  MySQL connection
 * @param show    associative array of show
 */
function add_show($mysqli, $show)
{
	/* add show */
	$q = "INSERT INTO `schedule` SET "
		. "show_name = '$show[show_name]', "
		. "dayID = '$show[dayID]', "
		. "start_time = '$show[start_time]', "
		. "end_time = '$show[end_time]', "
		. "show_typeID = '$show[show_typeID]', "
		. "active = 1;";
	$mysqli->query($q);

	/* add show hosts */
	$scheduleID = $mysqli->insert_id;

	foreach ( $show["hosts"] as $h ) {
		$q = "INSERT INTO `schedule_hosts` SET "
			. "scheduleID = '$scheduleID', "
			. "username = '$h';";
		$mysqli->query($q);
	}
}

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to add shows.");
	}

	$show = json_decode(file_get_contents("php://input"), true);
	$show = escape_json($mysqli, $show);

	if ( !validate_show($mysqli, $show) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Submitted show data is invalid.");
	}

	add_show($mysqli, $show);

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($show));
}
?>
