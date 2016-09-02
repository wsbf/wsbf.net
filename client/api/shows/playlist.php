<?php

/**
 * @file shows/playlist.php
 * @author Ben Shealy
 */
require_once("../connect.php");

/**
 * Get the current show ID.
 *
 * @param mysqli
 * @return current show ID
 */
function get_current_show_id($mysqli)
{
	$q = "SELECT showID FROM `show` AS s "
		. "ORDER BY s.showID DESC "
		. "LIMIT 1;";
	$show = $mysqli->query($q)->fetch_assoc();

	return $show["showID"];
}

/**
 * Get the playlist for a show.
 *
 * @param mysqli
 * @param showID
 * @param limit
 * @return array of tracks for show
 */
function get_show_playlist($mysqli, $showID, $limit)
{
	$keys = array(
		"l.lb_track_name",
		"l.lb_artist",
		"l.lb_album",
		"l.lb_label",
		"l.lb_rotation",
		"UNIX_TIMESTAMP(l.time_played) * 1000 AS time_played"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `logbook` AS l "
		. "WHERE l.showID='$showID' AND l.played=1 "
		. "ORDER BY l.time_played DESC "
		. "LIMIT $limit;";
	$result = $mysqli->query($q);

	$tracks = array();
	while ( ($t = $result->fetch_assoc()) ) {
		$tracks[] = $t;
	}

	return $tracks;
}

$showID = array_key_exists("showID", $_GET)
	? $_GET["showID"]
	: null;
$limit = PHP_INT_MAX;

if ( !empty($showID) && !is_numeric($showID) ) {
	header("HTTP/1.1 404 Not Found");
	exit("Show ID is invalid.");
}

$mysqli = construct_connection();

if ( !$showID ) {
	$showID = get_current_show_id($mysqli);
	$limit = 20;
}

$playlist = get_show_playlist($mysqli, $showID, $limit);
$mysqli->close();

header("Content-Type: application/json");
exit(json_encode($playlist));
?>
