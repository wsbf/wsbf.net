<?php

/**
 * @file schedule/show.php
 * @author Ben Shealy
 *
 * Get, add, or remove a show in the schedule.
 *
 * TODO: add update function
 */
require_once("../auth/auth.php");
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

/**
 * Get a show in the schedule.
 *
 * @param mysqli      MySQL connection
 * @param scheduleID  schedule ID
 * @return associative array of show
 */
function get_show($mysqli, $scheduleID)
{
	/* get show */
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
	$show = $mysqli->query($q)->fetch_assoc();

	/* get show hosts */
	$host_keys = array(
		"u.preferred_name",
		"h.schedule_alias"
	);

	$q = "SELECT " . implode(",", $host_keys) . " FROM `schedule_hosts` AS h "
		. "INNER JOIN `users` AS u ON h.username=u.username "
		. "WHERE h.scheduleID = '$scheduleID';";
	$result = $mysqli->query($q);

	$show["hosts"] = array();

	while ( ($h = $result->fetch_assoc()) ) {
		$show["hosts"][] = $h;
	}

	return $show;
}

/**
 * Remove a show from the schedule.
 *
 * @param mysqli      MySQL connection
 * @param scheduleID  schedule ID
 */
function remove_show($mysqli, $scheduleID)
{
	$q = "UPDATE `schedule` SET "
		. "active = 0 "
		. "WHERE scheduleID = '$scheduleID';";
	$mysqli->query($q);
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	$scheduleID = $_GET["scheduleID"];

	if ( !is_numeric($scheduleID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$show = get_show($mysqli, $scheduleID);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($show));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	authenticate();
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
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
else if ( $_SERVER["REQUEST_METHOD"] == "DELETE" ) {
	authenticate();
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	$scheduleID = $_GET["scheduleID"];

	if ( !is_numeric($scheduleID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	remove_show($mysqli, $scheduleID);
	$mysqli->close();

	exit;
}
?>
