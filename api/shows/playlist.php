<?php
require_once("../connect.php");

/**
 * Get the current show.
 *
 * @param mysqli  MySQL connection
 * @return current show ID
 */
function get_current_show($mysqli)
{
	$q = "SELECT showID FROM `show` AS s "
		. "ORDER BY s.showID DESC "
		. "LIMIT 1;";
	$show = $mysqli->query($q)->fetch_assoc();

	return $show["showID"];
}

/**
 * Get the playlist for a show, or the current show
 * if no show ID is provided.
 *
 * @param mysqli  MySQL connection
 * @param showID  show ID
 * @return array of tracks for show
 */
function get_show_playlist($mysqli, $showID)
{
	$keys = array(
		"l.lb_track_name",
		"l.lb_artist",
		"l.lb_album",
		"l.lb_label",
		"l.lb_rotation",
		"UNIX_TIMESTAMP(l.time_played) * 1000 AS time_played"
	);
	$max = PHP_INT_MAX;

	if ( empty($showID) ) {
		$showID = get_current_show($mysqli);
		$max = 20;
	}

	$q = "SELECT " . implode(",", $keys) . " FROM `logbook` AS l "
		. "WHERE l.showID='$showID' AND l.played=1 "
		. "ORDER BY l.time_played DESC "
		. "LIMIT $max;";
	$result = $mysqli->query($q);

	$tracks = array();
	while ( ($t = $result->fetch_assoc()) ) {
		$tracks[] = $t;
	}

	return $tracks;
}

$showID = $_GET["showID"];

if ( !empty($showID) && !is_numeric($showID) ) {
	header("HTTP/1.1 404 Not Found");
	exit("Show ID is invalid.");
}

$mysqli = construct_connection();
$playlist = get_show_playlist($mysqli, $showID);
$mysqli->close();

header("Content-Type: application/json");
exit(json_encode($playlist));
?>
