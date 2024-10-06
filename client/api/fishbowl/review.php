<?php

/**
 * @file fishbowl/review.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("config.php");

/**
 * Get a fishbowl app.
 *
 * @param mysqli
 * @param fishbowlID
 * @return associative array of fishbowl app
 */
function get_fishbowl_app($mysqli, $fishbowlID)
{
	// get fishbowl app
	$keys = array(
		"f.points",
		"u.username",
		"u.preferred_name",
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `fishbowl_leaderboard` AS f "
		. "INNER JOIN `users` AS u ON u.username=f.username; ";
		// . "WHERE f.fishbowlID='$fishbowlID';";
	$app = exec_query($mysqli, $q)->fetch_assoc();
	
	// get fishbowl log
	$log_keys = array(
		"date",
		"log_type",
		"description"
	);

	$q = "SELECT " . implode(",", $log_keys) . " FROM `fishbowl_log` "
		. "WHERE username='$app[username]';";
	$app["log"] = fetch_array(exec_query($mysqli, $q));

	// compute the number of album reviews
	$q = "SELECT COUNT(*) FROM `libreview` AS r "
		. "WHERE r.username = '$app[username]' "
		. "AND " . REVIEW_BEGIN . " <= UNIX_TIMESTAMP(r.review_date) "
		. "AND UNIX_TIMESTAMP(r.review_date) <= " . DEADLINE . ";";
	$result = exec_query($mysqli, $q);
	$row = $result->fetch_row();

	$app["num_reviews"] = $row[0];
	$app["fall"] = SEMESTER;

	return $app;
}

/**
 * Get user summary information.
 *
 * @param mysqli
 * @param username
 * @return associative array of user summary
 */
function get_user_summary($mysqli, $username)
{
    // Get basic user info (points, username, preferred name) from fishbowl_leaderboard
    $keys = array(
        "f.points",
        "u.username",
        "u.preferred_name",
    );

    $q = "SELECT " . implode(",", $keys) . " FROM `fishbowl_leaderboard` AS f "
        . "INNER JOIN `users` AS u ON u.username = f.username "
        . "WHERE u.username = '$username';";
    $user = exec_query($mysqli, $q)->fetch_assoc();

    // Get fishbowl log for the user
    $log_keys = array(
        "date",
        "log_type",
        "description"
    );

    $q = "SELECT " . implode(",", $log_keys) . " FROM `fishbowl_log` "
        . "WHERE username = '$username' "
        . "ORDER BY date DESC;";
    $user["log"] = fetch_array(exec_query($mysqli, $q));

    // Compute the number of album reviews
    $q = "SELECT COUNT(*) FROM `libreview` AS r "
        . "WHERE r.username = '$username' "
        . "AND " . REVIEW_BEGIN . " <= UNIX_TIMESTAMP(r.review_date) "
        . "AND UNIX_TIMESTAMP(r.review_date) <= " . DEADLINE . ";";
    $result = exec_query($mysqli, $q);
    $row = $result->fetch_row();

    $user["num_reviews"] = $row[0];
    $user["fall"] = SEMESTER;

    return $user;
}

// TODO: maybe move to POST fishbowl.php
/**
 * Validate fishbowl ratings.
 *
 * @param apps
 * @return true if app ratings are valid, false otherwise
 */
function validate_fishbowl_ratings($apps)
{
	foreach ( $apps as $a ) {
		if ( !is_numeric($a["fishbowlID"])
		  || !is_numeric($a["rating"])
		  || $a["rating"] < 1 || 5 < $a["rating"] ) {
			return false;
		}
	}

	return true;
}

/**
 * Rate all fishbowl apps.
 *
 * @param mysqli
 * @param apps
 */
function rate_fishbowl_apps($mysqli, $apps)
{
	foreach ( $apps as $a ) {
		// get fishbowl app
		$q = "SELECT average, weight FROM `fishbowl` "
			. "WHERE fishbowlID='$a[fishbowlID]';";
		$app = exec_query($mysqli, $q)->fetch_assoc();

		$average = $app["average"];
		$weight = $app["weight"];

		// apply rating to average and weight
		// - average is the average of all ratings
		// - weight is the number of ratings
		$average = ($average * $weight + $a["rating"]) / ($weight + 1);
		$weight++;

		// update fishbowl app
		$q = "UPDATE `fishbowl` SET "
			. "average = '$average', "
			. "weight = '$weight' "
			. "WHERE fishbowlID='$a[fishbowlID]';";
		exec_query($mysqli, $q);
	}
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !auth_senior_staff($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	$username = $_GET["username"];

	if ( !is_numeric($fishbowlID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$app = get_fishbowl_app($mysqli, $username);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($app));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !auth_senior_staff($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	$apps = json_decode(file_get_contents("php://input"), true);
	$apps = escape_json($mysqli, $apps);

	if ( !validate_fishbowl_ratings($apps) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	rate_fishbowl_apps($mysqli, $apps);
	$mysqli->close();

	exit;
}
?>
