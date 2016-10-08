<?php

/**
 * @file zautomate/automation_generate_showplist.php
 * @author Ben Shealy
 *
 * TODO: maybe combine the two generate scripts
 */
require_once("../connect.php");

define('MIN_PLAYLIST_SIZE', 10);

/**
 * Get the playlist of a previous show.
 *
 * Selected tracks must currently be in rotation, and no
 * more than one track from each album is included.
 *
 * @param mysqli
 * @param showID
 * @return array of tracks
 */
function get_playlist($mysqli, $showID)
{
	$keys = array(
		"l.lb_album_code",
		"l.lb_disc_num",
		"l.lb_track_num",
		"l.lb_track_name",
		"r.binAbbr AS rotation",
		"al.album_name",
		"ar.artist_name",
		"t.file_name"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `logbook` AS l "
		. "INNER JOIN `libalbum` AS al ON al.album_code=l.lb_album_code "
		. "INNER JOIN `libartist` AS ar ON ar.artistID=al.artistID "
		. "INNER JOIN `libtrack` AS t ON t.albumID=al.albumID AND t.track_num=l.lb_track_num "
		. "INNER JOIN `def_rotations` AS r on r.rotationID=al.rotationID "
		. "WHERE l.showID = '$showID' "
		. "AND al.rotationID IN (1, 2, 3, 4) "
		. "GROUP BY l.lb_album_code " 
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
	$showID = $_GET["showid"];

	if ( !is_numeric($showID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$mysqli = construct_connection();

	$playlist = get_playlist($mysqli, $showID);

	// TODO: maybe use automation_generate_showid.php
//	while ( count($playlist) < MIN_PLAYLIST_SIZE ) {
//		// get another valid show ID
//		$playlist = get_playlist($mysqli, $showID);
//	};

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($playlist));
}
?>
