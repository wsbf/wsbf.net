<?php

/**
 * @file shows/playlist.php
 * @author Ben Shealy
 */
require_once("../connect.php");
require_once("functions.php");

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
	$result = exec_query($mysqli, $q);

	return fetch_array($result);
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$showID = array_access($_GET, "showID");
	$limit = PHP_INT_MAX;

	if ( isset($showID) && !is_numeric($showID) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Show ID is invalid.");
	}

	$mysqli = construct_connection();

	if ( !isset($showID) ) {
		$showID = get_current_show_id($mysqli);
		$limit = 20;
	}

	$playlist = get_show_playlist($mysqli, $showID, $limit);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($playlist));
}
?>
