<?php

/**
 * @file users/reviews.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get a list of album review counts by users.
 *
 * @param mysqli
 * @param date1
 * @param date2
 * @return array of review counts by user
 */
function get_review_counts($mysqli, $date1, $date2)
{
	$keys = array(
		"COUNT(*) AS num_reviews",
		"u.preferred_name"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `libreview` AS r "
		. "INNER JOIN `users` AS u ON r.username=u.username "
		. "WHERE '$date1' < UNIX_TIMESTAMP(r.review_date) "
		. "AND UNIX_TIMESTAMP(r.review_date) < '$date2' "
		. "GROUP BY r.username "
		. "ORDER BY num_reviews DESC;";
	$result = exec_query($mysqli, $q);

	return fetch_array($result);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !auth_member($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	$date1 = $_GET["date1"];
	$date2 = $_GET["date2"];

	if ( !is_numeric($date1) || !is_numeric($date2) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$review_counts = get_review_counts($mysqli, $date1, $date2);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($review_counts));
}
?>
