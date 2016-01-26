<?php

/**
 * @file fishbowl/review.php
 * @author Ben Shealy
 */
require_once("../auth.php");
require_once("../connect-dev.php");

/**
 * Get a fishbowl app.
 *
 * @param mysqli  MySQL connection
 * @param id      fishbowl app id
 * @return associative array of fishbowl app
 */
function get_fishbowl_app($mysqli, $id)
{
	// TODO: implement num_reviews once constants are
	// fiqured out (SEMESTER, SEMESTER_BEGIN, DEADLINE)

	$keys = array(
		"f.id",
		"u.preferred_name",
		"f.semesters",
		"f.missedShows",
		"0 AS num_reviews",
		"f.liveShows",
		"f.springFest",
		"f.specialty",
		"f.dead_hours",
		"f.other"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `fishbowl` AS f "
		. "INNER JOIN `users` AS u ON u.username=f.username "
		. "WHERE f.id='$id';";
	$app = $mysqli->query($q)->fetch_assoc();

	return $app;
}

/**
 * Rate a fishbowl app.
 * 
 * @param mysqli  MySQL connection
 * @param id      fishbowl app id
 * @param rating  fishbowl app rating
 */
function rate_fishbowl_app($mysqli, $id, $rating)
{
	// get fishbowl app
	$q = "SELECT average, weight FROM `fishbowl` "
		. "WHERE id='$id';";
	$app = $mysqli->query($q)->fetch_assoc();

	$average = $app["average"];
	$weight = $app["weight"];

	// apply rating to average and weight
	// - average is the average of all ratings
	// - weight is the number of ratings
	$average = ($average * $weight + $rating) / ($weight + 1);
	$weight++;

	// update fishbowl app
	$q = "UPDATE `fishbowl` SET "
		. "average = '$average', "
		. "weight = '$weight' "
		. "WHERE id='$id';";
	$mysqli->query($q);
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to review fishbowl apps.");
	}

	$id = $_GET["id"];

	if ( !is_numeric($id) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Fishbowl app id is invalid.");
	}

	$app = get_fishbowl_app($mysqli, $id);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($app));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to review fishbowl apps.");
	}

	$id = $_GET["id"];
	$rating = $_GET["rating"];

	if ( !is_numeric($id) || !is_numeric($rating) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Fishbowl app review is invalid.");
	}

	rate_fishbowl_app($mysqli, $id, $rating);
	$mysqli->close();
	exit;
}
?>
