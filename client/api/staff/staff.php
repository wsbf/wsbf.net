<?php

/**
 * @file staff/staff.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get the list of staff.
 *
 * @param mysqli
 * @return array of staff
 */
function get_staff($mysqli)
{
	$keys = array(
		"s.positionID",
		"s.username",
		"s.start_date",
		"s.end_date",
		"u.preferred_name"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `staff` AS s "
		. "INNER JOIN `users` AS u ON u.username=s.username "
		. "WHERE s.end_date IS NULL;";
	$result = exec_query($mysqli, $q);

	return fetch_array($result);
}

/**
 * Validate a staff member.
 *
 * @param mysqli
 * @param member
 * @return true if member is valid, false otherwise
 */
function validate_staff_member($mysqli, $member)
{
	if ( empty($member["username"])
	  || !is_numeric($member["positionID"]) ) {
		return false;
	}

	return true;
}

/**
 * Add a member to the staff.
 *
 * @param mysqli
 * @param member
 */
function add_staff_member($mysqli, $member)
{
	$q = "INSERT INTO `staff` SET "
		. "positionID = '$member[positionID]', "
		. "username = '$member[username]', "
		. "start_date = NOW();";
	exec_query($mysqli, $q);
}

/**
 * Remove a member from the staff.
 *
 * @param mysqli
 * @param positionID
 */
function remove_staff_member($mysqli, $positionID)
{
	$q = "UPDATE `staff` SET "
		. "end_date = NOW() "
		. "WHERE positionID = '$positionID' AND end_date IS NULL;";
	exec_query($mysqli, $q);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$staff = get_staff($mysqli);

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($staff));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$member = json_decode(file_get_contents("php://input"), true);
	$member = escape_json($mysqli, $member);

	if ( !validate_staff_member($mysqli, $member) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	add_staff_member($mysqli, $member);
	$mysqli->close();

	exit;
}
else if ( $_SERVER["REQUEST_METHOD"] == "DELETE" ) {
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$positionID = $_GET["positionID"];

	if ( !is_numeric($positionID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	remove_staff_member($mysqli, $positionID);
	$mysqli->close();

	exit;
}
?>
