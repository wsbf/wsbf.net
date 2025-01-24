<?php

/**
 * @file fishbowl/fishbowl.php
 * @author Ben Shealy
 * modified by Jai Agarwal
 *
 * Get the current fishbowl.
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get the current fishbowl leaderboard.
 *
 * @param mysqli
 * @return array of fishbowl applications
 */
function get_fishbowl($mysqli)
{
    $keys = array(
        "u.username",
        "u.preferred_name",
        "u.teamID"
    );

	// this is the function of get_current_date_range in file fishbowl_date_range.php
    $q = "SELECT date FROM `def_semester_dates` WHERE dateID=0";
    $result = fetch_array(exec_query($mysqli, $q));
    $REVIEW_BEGIN = $result[0]['date'];

    $q = "SELECT date FROM `def_semester_dates` WHERE dateID=1";
    $result = fetch_array(exec_query($mysqli, $q));
    $DEADLINE = $result[0]['date'];

    // get leaderboard with points, username, cd reviews
    $q = "SELECT " . implode(",", $keys) . ", "
    . "(SELECT COUNT(*) "
            . "FROM fishbowl_log AS f "
            . "WHERE f.username = u.username "
            . "AND UNIX_TIMESTAMP(f.date) BETWEEN UNIX_TIMESTAMP('$REVIEW_BEGIN') AND UNIX_TIMESTAMP('$DEADLINE') ) AS points, "
    . "(SELECT SUM(CASE WHEN f.disputed IS NOT NULL AND f.disputed != 0 THEN 1 ELSE 0 END) "
    . "FROM fishbowl_log AS f "
    . "WHERE f.username = u.username "
    . "AND UNIX_TIMESTAMP(f.date) BETWEEN UNIX_TIMESTAMP('$REVIEW_BEGIN') AND UNIX_TIMESTAMP('$DEADLINE') ) AS dispute_count, "
    . "COALESCE((SELECT COUNT(*) "
            . "FROM libreview AS r "
            . "WHERE r.username = u.username "
            . "AND UNIX_TIMESTAMP(r.review_date) BETWEEN UNIX_TIMESTAMP('$REVIEW_BEGIN') AND UNIX_TIMESTAMP('$DEADLINE') ), 0) AS review_count "
    . "FROM users AS u "
    //. "WHERE u.username IN (SELECT username FROM fishbowl_log WHERE UNIX_TIMESTAMP(date) BETWEEN " . REVIEW_BEGIN . " AND " . DEADLINE . ") "
    . "WHERE u.username IN ( "
        . "SELECT username FROM fishbowl_log as f WHERE UNIX_TIMESTAMP(f.date) BETWEEN UNIX_TIMESTAMP('$REVIEW_BEGIN') AND UNIX_TIMESTAMP('$DEADLINE') UNION "
            . "SELECT username FROM libreview as r WHERE UNIX_TIMESTAMP(r.review_date) BETWEEN UNIX_TIMESTAMP('$REVIEW_BEGIN') AND UNIX_TIMESTAMP('$DEADLINE') ) "
    . "ORDER BY points DESC;";    

    $result = exec_query($mysqli, $q);

	return fetch_array($result);
}


authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !auth_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$fishbowl = get_fishbowl($mysqli);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($fishbowl));
}
?>
