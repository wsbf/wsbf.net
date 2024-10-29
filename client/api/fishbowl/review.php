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
		"f.disputes",
        "u.username",
        "u.preferred_name",
    );

    $q = "SELECT " . implode(",", $keys) . " FROM `fishbowl_leaderboard` AS f "
        . "INNER JOIN `users` AS u ON u.username = f.username "
        . "WHERE u.username = '$username';";
    $summary = exec_query($mysqli, $q)->fetch_assoc();

    // Get fishbowl log for the user
    $log_keys = array(
		"fishbowl_logID",
        "date",
        "log_type",
        "description",
		"disputed",
		"dispute_description"
    );

	$q = "SELECT " . implode(",", $log_keys) . " FROM `fishbowl_log` "
    . "WHERE username = '$username' "
    . "AND date BETWEEN FROM_UNIXTIME(" . REVIEW_BEGIN . ") AND FROM_UNIXTIME(" . DEADLINE . ") "
    . "ORDER BY date DESC;";
	$summary["log"] = fetch_array(exec_query($mysqli, $q));

    // Compute the number of album reviews
    $q = "SELECT COUNT(*) FROM `libreview` AS r "
        . "WHERE r.username = '$username' "
        . "AND " . REVIEW_BEGIN . " <= UNIX_TIMESTAMP(r.review_date) "
        . "AND UNIX_TIMESTAMP(r.review_date) <= " . DEADLINE . ";";
    $result = exec_query($mysqli, $q);
    $row = $result->fetch_row();

    $summary["review_count"] = $row[0];
    $summary["fall"] = SEMESTER;

    return $summary;
}


/**
 * Dispute a fishbowl item.
 *
 * @param mysqli
 * @param fishbowl_logID
 * @return
 */
function dispute_fishbowl($mysqli, $fishbowl_logID, $dispute_description)
{
    // update the dispute status in the fishbowl_log table
	$q = "UPDATE fishbowl_log "
		. "SET disputed = 1, dispute_description = '$dispute_description' "
		. "WHERE fishbowl_logID = '$fishbowl_logID'";
    exec_query($mysqli, $q);


    // retreive the username from the fishbowl_log table
	$q = "SELECT username FROM fishbowl_log WHERE fishbowl_logID = '$fishbowl_logID'";
	$result = exec_query($mysqli, $q);
	$row = $result->fetch_assoc();
	$username = $row['username'];

	// update the disputes count in the fishbowl_leaderboard table so
	// we have a separate record of disputes and know how to adjust points
	$q = "UPDATE fishbowl_leaderboard "
		. "SET disputes = disputes + 1 "
		. "WHERE username = '$username'";
	exec_query($mysqli, $q);

}

/**
 * UNdispute a fishbowl item.
 *
 * @param mysqli
 * @param fishbowl_logID
 * @return
 */
function undo_dispute_fishbowl($mysqli, $fishbowl_logID)
{
    // update the dispute status in the fishbowl_log table
	$q = "UPDATE fishbowl_log "
		. "SET disputed = 0, dispute_description = '' "
		. "WHERE fishbowl_logID = '$fishbowl_logID'";
    exec_query($mysqli, $q);

    // retreive the username from the fishbowl_log table
	$q = "SELECT username FROM fishbowl_log WHERE fishbowl_logID = '$fishbowl_logID'";
	$result = exec_query($mysqli, $q);
	$row = $result->fetch_assoc();
	$username = $row['username'];

	// update the disputes count in the fishbowl_leaderboard table
	$q = "UPDATE fishbowl_leaderboard "
		. "SET disputes = disputes - 1 "
		. "WHERE username = '$username'";
	exec_query($mysqli, $q);

}

// // TODO: maybe move to POST fishbowl.php
// /**
//  * Validate fishbowl ratings.
//  *
//  * @param apps
//  * @return true if app ratings are valid, false otherwise
//  */
// function validate_fishbowl_ratings($apps)
// {
// 	foreach ( $apps as $a ) {
// 		if ( !is_numeric($a["fishbowlID"])
// 		  || !is_numeric($a["rating"])
// 		  || $a["rating"] < 1 || 5 < $a["rating"] ) {
// 			return false;
// 		}
// 	}

// 	return true;
// }

// /**
//  * Rate all fishbowl apps.
//  *
//  * @param mysqli
//  * @param apps
//  */
// function rate_fishbowl_apps($mysqli, $apps)
// {
// 	// recalculate ranks for all users
// 	$q = "UPDATE fishbowl_leaderboard AS f "
// 		. "SET f.rank = ( "
// 		. "SELECT COUNT(*) + 1 "
// 		. "FROM fishbowl_leaderboard AS sub "
// 		. "WHERE sub.points > f.points "
// 		. ");";
// 	$result = exec_query($mysqli, $q);
// }

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !auth_senior_staff($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	$username = $_GET["username"];

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

	$item = json_decode(file_get_contents("php://input"), true);
	$item = escape_json($mysqli, $item);
	
	error_log(print_r($item, true));  // Log the entire POST array to the error log

    $action = isset($item['action']) ? $item['action'] : null;
	$fishbowl_logID = $item['fishbowl_logID'];
	$dispute_description = isset($item['dispute_description']) ? $item['dispute_description'] : null;

	if (!is_numeric($fishbowl_logID)) {
		header("HTTP/1.1 400 Bad Request");
		exit("Invalid fishbowl_logID input");
	}

	// Handle dispute action
	if ($action === "dispute") {
		if ($dispute_description === null) {
			header("HTTP/1.1 400 Bad Request");
			exit("dispute_description is required for dispute action");
		}

		dispute_fishbowl($mysqli, $fishbowl_logID, $dispute_description);
	} else if ($action === "undispute") {
		undispute_fishbowl($mysqli, $fishbowl_logID);
	} else {
		header("HTTP/1.1 400 Bad Request");
		exit("Invalid action specified. Use 'dispute' or 'undispute'");
	}

	$mysqli->close();

	exit;
}
?>
