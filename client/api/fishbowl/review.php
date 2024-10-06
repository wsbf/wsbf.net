<?php

/**
 * @file fishbowl/review.php
 * @author Ben Shealy
 * modified by Jai Agarwal
 * 
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("config.php");

/**
 * Get fishbowl user summary information.
 *
 * @param mysqli
 * @param username
 * @return associative array of user summary
 */
function get_user_summary($mysqli, $username)
{

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
    . "AND date BETWEEN FROM_UNIXTIME(" . REVIEW_BEGIN . ") AND FROM_UNIXTIME(" . DEADLINE . ") "
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
	// recalculate ranks for all users
	$q = "UPDATE fishbowl_leaderboard AS f "
		. "SET f.rank = ( "
		. "SELECT COUNT(*) + 1 "
		. "FROM fishbowl_leaderboard AS sub "
		. "WHERE sub.points > f.points "
		. ");";
	$result = exec_query($mysqli, $q);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !auth_senior_staff($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	$username = $_GET["username"];
	error_log("Received username: " . $username);

	if (empty($username)) {
    	header("HTTP/1.1 400 Bad Request");
    	exit("Invalid username provided.");
	}

	$app = get_user_summary($mysqli, $username);
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
