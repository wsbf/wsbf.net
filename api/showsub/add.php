<?php

/**
 * @file showsub/add.php
 * @author Ben Shealy
 */
require_once("../auth.php");
require_once("../connect.php");

/**
 * Add a show sub request for the current user.
 *
 * @param mysqli   MySQL connection
 * @param request  associative array of sub request
 */
function add_sub_request($mysqli, $request)
{
	$keys = array(
		"s.show_name",
		"s.dayID",
		"s.start_time",
		"s.end_time",
		"s.show_typeID"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `schedule` AS s "
		. "WHERE s.scheduleID='$request[scheduleID]' AND s.active=1 "
		. "AND '$_SESSION[username]' IN ("
			. "SELECT h.username FROM `schedule_hosts` AS h "
			. "WHERE h.scheduleID='$request[scheduleID]'"
		. ");";
	$result = $mysqli->query($q);

	// request should be for a valid show
	if ( $result->num_rows == 0 ) {
		header("HTTP/1.1 404 Not Found");
		exit("Show sub request is invalid.");
	}

	$show = $result->fetch_assoc();

	// request date should be same day of week as show
	if ( $show["dayID"] != date("w", strtotime($request["date"])) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Show sub request is invalid.");
	}

	$q = "INSERT INTO `sub_request` SET "
		. "username = '$_SESSION[username]', "
		. "date = '$request[date]', "
		. "reason = '$request[reason]', "
		. "dayID = '$show[dayID]', "
		. "start_time = '$show[start_time]', "
		. "end_time = '$show[end_time]', "
		. "show_name = '$show[show_name]', "
		. "show_typeID = '$show[show_typeID]';";
	$mysqli->query($q);
}

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to add sub requests.");
	}

	$request = json_decode(file_get_contents("php://input"), true);
	$request = escape_json($mysqli, $request);

	add_sub_request($mysqli, $request);

	$mysqli->close();
	exit;
}
?>
