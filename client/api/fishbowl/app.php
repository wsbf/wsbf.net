<?php

/**
 * @file fishbowl/app.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("config.php");

/**
 * Validate fishbowl app.
 *
 * @param mysqli
 * @param app
 * @return true if app is valid, false otherwise
 */
function validate_fishbowl_app($mysqli, $app)
{
	// required fields should be defined
	if ( !is_numeric($app["semesters"])
	  || !is_numeric($app["missedShows"])
	  || empty($app["liveShows"])
	  || !is_bool($app["dead_hours"])
	  || !is_bool($app["specialty"]) ) {
		return false;
	}

	// current user must not have already submitted an app
	$q = "SELECT COUNT(*) FROM `fishbowl` "
		. "WHERE active=1 AND username='$_SESSION[username]';";
	$result = exec_query($mysqli, $q)->fetch_row();

	if ( $result[0] > 0 ) {
		return false;
	}

	return true;
}

/**
 * Submit a fishbowl application for the current user.
 *
 * @param mysqli
 * @param app
 */
function submit_fishbowl_app($mysqli, $app)
{
	$q = "INSERT INTO `fishbowl` SET "
		. "username = '$_SESSION[username]', "
		. "semesters = '$app[semesters]', "
		. "missedShows = '$app[missedShows]', "
		. "liveShows = '$app[liveShows]', "
		. "springFest = '$app[springFest]', "
		. "specialty = '$app[specialty]', "
		. "dead_hours = '$app[dead_hours]', "
		. "other = '$app[other]', "
		. "active = 1;";
	exec_query($mysqli, $q);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_edit_profile($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to submit a fishbowl app.");
	}

	$mysqli->close();

	$info = array(
		"fall" => SEMESTER,
		"deadline" => DEADLINE
	);

	header("Content-Type: application/json");
	exit(json_encode($info));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_edit_profile($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to submit a fishbowl app.");
	}

	if ( DEADLINE < time() ) {
		header("HTTP/1.1 404 Not Found");
		exit("The fishbowl application is currently closed.");
	}

	$app = json_decode(file_get_contents("php://input"), true);
	$app = escape_json($mysqli, $app);

	// convert boolean values
	$app["dead_hours"] = (bool) $app["dead_hours"];
	$app["specialty"] = (bool) $app["specialty"];

	if ( !validate_fishbowl_app($mysqli, $app) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Fishbowl app is invalid.");
	}

	submit_fishbowl_app($mysqli, $app);
	$mysqli->close();
	exit;
}
?>
