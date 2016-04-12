<?php

/**
 * @file showsub/request.php
 * @author Ben Shealy
 *
 * Add, fill, or remove a show sub request.
 */
require_once("../auth.php");
require_once("../connect.php");

/**
 * Validate a sub request fill.
 *
 * @param mysqli     MySQL connection
 * @param requestID  sub request ID
 * @return true if request fill is valid, false otherwise
 */
function validate_request_fill($mysqli, $requestID)
{
	if ( !is_numeric($requestID) ) {
		return false;
	}

	$q = "SELECT r.username, f.username AS filled_by "
		. "FROM `sub_request` AS r "
		. "LEFT OUTER JOIN `sub_fill` AS f "
		. "ON f.sub_requestID=r.sub_requestID "
		. "WHERE r.sub_requestID='$requestID';";
	$result = $mysqli->query($q);

	// sub request must exist
	if ( $result->num_rows == 0 ) {
		return false;
	}

	$request = $result->fetch_assoc();

	// sub request must not already be filled
	if ( !empty($request["filled_by"]) ) {
		return false;
	}

	// sub request must not belong to the current user
	if ( $request["username"] == $_SESSION["username"] ) {
		return false;
	}

	return true;
}

/**
 * Validate a sub request remove.
 *
 * @param mysqli     MySQL connection
 * @param requestID  sub request ID
 * @return true if request remove is valid, false otherwise
 */
function validate_request_remove($mysqli, $requestID)
{
	if ( !is_numeric($requestID) ) {
		return false;
	}

	$q = "SELECT r.username, f.username AS filled_by "
		. "FROM `sub_request` AS r "
		. "LEFT OUTER JOIN `sub_fill` AS f "
		. "ON f.sub_requestID=r.sub_requestID "
		. "WHERE r.sub_requestID='$requestID';";
	$result = $mysqli->query($q);

	// sub request must exist
	if ( $result->num_rows == 0 ) {
		return false;
	}

	$request = $result->fetch_assoc();

	// TODO: sub request must not already be filled?
//	if ( !empty($request["filled_by"]) ) {
//		return false;
//	}

	// sub request must belong to the current user
	if ( $request["username"] != $_SESSION["username"] ) {
		return false;
	}

	return true;
}

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

/**
 * Fill a sub request with the current user.
 *
 * @param mysqli     MySQL connection
 * @param requestID  sub request ID
 */
function fill_sub_request($mysqli, $requestID)
{
	$q = "INSERT INTO `sub_fill` (sub_requestID, username) "
		. "VALUES ('$requestID', '$_SESSION[username]');";
	$mysqli->query($q);
}

/**
 * Remove a show sub request for the current user.
 *
 * @param mysqli     MySQL connection
 * @param requestID  sub request ID
 */
function remove_sub_request($mysqli, $requestID)
{
	$q = "DELETE FROM `sub_request` "
		. "WHERE sub_requestID = '$requestID' "
		. "AND username = '$_SESSION[username]';";
	$mysqli->query($q);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	$requestID = array_key_exists("requestID", $_GET)
		? $_GET["requestID"]
		: null;

	if ( isset($requestID) ) {
		if ( !validate_request_fill($mysqli, $requestID) ) {
			header("HTTP/1.1 404 Not Found");
			exit("Sub request fill is invalid.");
		}

		fill_sub_request($mysqli, $requestID);
	}
	else {
		$request = json_decode(file_get_contents("php://input"), true);
		$request = escape_json($mysqli, $request);

		add_sub_request($mysqli, $request);
	}
	$mysqli->close();

	exit;
}
else if ( $_SERVER["REQUEST_METHOD"] == "DELETE" ) {
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	$requestID = $_GET["requestID"];

	if ( !validate_request_remove($mysqli, $requestID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	remove_sub_request($mysqli, $requestID);
	$mysqli->close();

	exit;
}
?>
