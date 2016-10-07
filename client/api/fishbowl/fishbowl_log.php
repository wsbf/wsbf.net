<?php

/**
 * @file fishbowl/fishbowl_log.php
 * @author Ben Shealy
 *
 * Get the current user's fishbowl log.
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get the current user's fishbowl log.
 *
 * @param mysqli
 */
function get_fishbowl_log($mysqli)
{
	$keys = array(
		"fishbowlLogID",
		"date",
		"log_type",
		"description"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `fishbowl_log` "
		. "WHERE username='$_SESSION[username]';";
	$result = exec_query($mysqli, $q);

	$logs = array();
	while ( ($log = $result->fetch_assoc()) ) {
		$logs[] = $log;
	}

	return $logs;
}

/**
 * Validate a fishbowl log item.
 *
 * @param mysqli
 * @param item
 * @return true if item is valid, false otherwise
 */
function validate_fishbowl_item($mysqli, $item)
{
	if ( empty($item["date"])
	  || empty($item["log_type"]) ) {
		return false;
	}

	return true;
}

/**
 * Add an item to the current user's fishbowl log.
 *
 * @param mysqli
 * @param item
 */
function log_fishbowl_item($mysqli, $item)
{
	$q = "INSERT INTO `fishbowl_log` SET "
		. "username = '$_SESSION[username]', "
		. "date = '$item[date]', "
		. "log_type = '$item[log_type]', "
		. "description = '$item[description]';";
	exec_query($mysqli, $q);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	$logs = get_fishbowl_log($mysqli);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($logs));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	$item = json_decode(file_get_contents("php://input"), true);
	$item = escape_json($mysqli, $item);

	if ( !validate_fishbowl_item($mysqli, $item) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Invalid input");
	}

	log_fishbowl_item($mysqli, $item);
	$mysqli->close();

	exit;
}
?>
