<?php

/**
 * @file shows/now.php
 * @author Ben Shealy
 */
require_once("../connect.php");

/**
 * Get the current track.
 *
 * @param mysqli  MySQL connection
 * @return associative array of current track
 */
function get_current_track($mysqli)
{
	/* get track */
	$keys = array(
		"l.lb_track_name",
		"l.lb_artist",
		"l.lb_album",
		"s.show_name",
		"s.showID"
	);

	$q = "SELECT " . implode(",", $keys) . " "
			. "FROM `now_playing` AS n "
			. "INNER JOIN `logbook` AS l ON n.logbookID=l.logbookID "
			. "LEFT OUTER JOIN `show` AS s ON s.showID=l.showID;"
	$track = $mysqli->query($q)->fetch_assoc();

	/* get show hosts */
	$q = "SELECT u.preferred_name FROM `show_hosts` AS h "
		. "INNER JOIN `users` AS u ON u.username=h.username "
		. "WHERE h.showID='$track[showID]';";
	$result = $mysqli->query($q);

	$track["show_hosts"] = array();
	while ( ($h = $result->fetch_assoc()) ) {
		$track["show_hosts"][] = $h["preferred_name"];
	}

	return $track;
}

$mysqli = construct_connection();
$track = get_current_track($mysqli);
$mysqli->close();

header("Content-Type: application/json");
exit(json_encode($track));
?>
