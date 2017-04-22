<?php

/**
 * @file logbook/tracks.php
 * @author Ben Shealy
 *
 * Generate a list of tracks for automation. The tracks
 * are selected from the current rotation, and tracks that
 * have been played during recent shows are weighted more.
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get a list of recent rotation shows.
 *
 * @param mysqli
 * @param size
 * @return array of showIDs
 */
function get_recent_shows($mysqli, $size)
{
	$q = "SELECT showID FROM `show` "
		. "WHERE show_typeID = 0 "
		. "AND TIMESTAMPDIFF(MINUTE, start_time, end_time) > 60 "
		. "ORDER BY showID DESC "
		. "LIMIT $size;";
	$result = exec_query($mysqli, $q);

	return fetch_array($result);
}

/**
 * Get the playlist of a show.
 *
 * Selected tracks must currently be in rotation, and they
 * must not have been played in the last hour. Also, no
 * more than one track from the same artist is included.
 *
 * @param mysqli
 * @param showID
 * @return array of tracks
 */
function get_playlist($mysqli, $showID)
{
	$keys = array(
		"al.album_code",
		"t.disc_num",
		"t.track_num",
		"r.bin_abbr AS rotation",
		"t.track_name",
		"ar.artist_name",
		"al.album_name",
		"la.label",
		"t.file_name"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `logbook` AS l "
		. "INNER JOIN `libalbum` AS al ON al.album_code=l.lb_album_code "
		. "INNER JOIN `libartist` AS ar ON ar.artistID=al.artistID "
		. "INNER JOIN `liblabel` AS la ON la.labelID=al.labelID "
		. "INNER JOIN `def_rotations` AS r ON r.rotationID=al.rotationID "
		. "INNER JOIN `libtrack` AS t ON t.albumID=al.albumID AND t.track_num=l.lb_track_num "
		. "WHERE l.showID = '$showID' "
		. "AND al.rotationID IN (3, 4, 5, 6) "
		. "AND t.airabilityID != 2 "
		. "AND TIMESTAMPDIFF(MINUTE, time_played, NOW()) > 60 "
		. "GROUP BY ar.artistID "
		. "ORDER BY l.logbookID ASC;";
	$result = exec_query($mysqli, $q);

	$playlist = array();
	while ( ($t = $result->fetch_assoc()) ) {
		// temporary hack to URL decode and add directory slashes
		$f = urldecode($t["file_name"]);
		$f = "$f[0]/$f[1]/" . substr($f, 2);
		$t["file_name"] = $f;

		$playlist[] = $t;
	}

	return $playlist;
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	authenticate_logbook($mysqli);

	$length = $_GET["length"];

	if ( !is_numeric($length) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$shows = get_recent_shows($mysqli, 100);
	$tracks = array();

	shuffle($shows);

	while ( !empty($shows) && count($tracks) < $length ) {
		$show = array_pop($shows);
		$showID = (int) $show["showID"];

		$playlist = get_playlist($mysqli, $showID);

		$tracks = array_merge($tracks, $playlist);
	}

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($tracks));
}
?>
