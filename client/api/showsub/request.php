<?php

/**
 * @file showsub/request.php
 * @author Ben Shealy
 *
 * Add, fill, or remove a show sub request.
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("../schedule/functions.php");

/**
 * Validate a sub request.
 *
 * @param mysqli
 * @param request
 * @param show
 * @return true if request is valid, false otherwise
 */
function validate_request($mysqli, $request, $show)
{
	// request show should exist
	if ( !$show || $request["scheduleID"] != $show["scheduleID"] ) {
		return false;
	}

	// request show should be hosted by the current user
	$found_user = false;
	foreach ( $show["hosts"] as $h ) {
		if ( $h["username"] == $_SESSION["username"] ) {
			$found_user = true;
			break;
		}
	}

	if ( !$found_user ) {
		return false;
	}

	// request date should be same day of week as show
	if ( $show["dayID"] != date("w", strtotime($request["date"])) ) {
		return false;
	}

	return true;
}

/**
 * Validate a sub request fill.
 *
 * @param mysqli
 * @param requestID
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
	$result = exec_query($mysqli, $q);

	// sub request must exist
	if ( $result->num_rows == 0 ) {
		return false;
	}

	$request = $result->fetch_assoc();

	// sub request must not already be filled
	if ( isset($request["filled_by"]) ) {
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
 * @param mysqli
 * @param requestID
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
	$result = exec_query($mysqli, $q);

	// sub request must exist
	if ( $result->num_rows == 0 ) {
		return false;
	}

	$request = $result->fetch_assoc();

	// sub request must not already be filled
	if ( isset($request["filled_by"]) ) {
		return false;
	}

	// sub request must belong to the current user
	if ( $request["username"] != $_SESSION["username"] ) {
		return false;
	}

	return true;
}

/**
 * Add a show sub request for the current user.
 *
 * @param mysqli
 * @param request
 */
function add_sub_request($mysqli, $request)
{
	$show = get_schedule_show($mysqli, $request["scheduleID"]);

	if ( !validate_request($mysqli, $request, $show) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Show sub request is invalid.");
	}

	$q = "INSERT INTO `sub_request` SET "
		. "username = '$_SESSION[username]', "
		. "scheduleID = '$request[scheduleID]', "
		. "date = '$request[date]', "
		. "reason = '$request[reason]';";
	exec_query($mysqli, $q);
}

/**
 * Fill a sub request with the current user.
 *
 * @param mysqli
 * @param requestID
 */
function fill_sub_request($mysqli, $requestID)
{
	$q = "INSERT INTO `sub_fill` SET "
		. "sub_requestID = '$requestID', "
		. "username = '$_SESSION[username]';";
	exec_query($mysqli, $q);
}

/**
 * Remove a show sub request for the current user.
 *
 * @param mysqli
 * @param requestID
 */
function remove_sub_request($mysqli, $requestID)
{
	$q = "DELETE FROM `sub_request` "
		. "WHERE sub_requestID = '$requestID' "
		. "AND username = '$_SESSION[username]';";
	exec_query($mysqli, $q);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_member($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	$requestID = array_access($_GET, "requestID");

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

	if ( !check_member($mysqli) ) {
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
