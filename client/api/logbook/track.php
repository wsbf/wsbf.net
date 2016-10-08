<?php

/**
 * @file logbook/track.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("auth.php");
require_once("functions.php");

/**
 * Get information about an album.
 *
 * @param mysqli
 * @param album_code
 */
function get_album($mysqli, $album_code)
{
	$keys = array(
		"al.albumID",
		"r.binAbbr AS rotation",
		"ar.artist_name",
		"al.album_name"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `libalbum` AS al "
		. "INNER JOIN `libartist` AS ar ON ar.artistID=al.artistID "
		. "INNER JOIN `def_rotations` AS r ON r.rotationID=al.rotationID "
		. "WHERE al.album_code = '$album_code';";
	$album = exec_query($mysqli, $q)->fetch_assoc();

	return $album;
}

/**
 * Get information about a track.
 *
 * @param mysqli
 * @param albumID
 * @param disc_num
 * @param track_num
 */
function get_track($mysqli, $albumID, $disc_num, $track_num)
{
	$keys = array(
		"al.album_code",
		"t.disc_num",
		"t.track_num",
		"r.binAbbr AS rotation",
		"t.track_name",
		"t.airabilityID",
		"al.album_name",
		"ar.artist_name",
		"la.label"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `libtrack` AS t "
		. "INNER JOIN `libalbum` AS al ON al.albumID=t.albumID "
		. "INNER JOIN `libartist` AS ar ON ar.artistID=al.artistID "
		. "INNER JOIN `liblabel` AS la ON la.labelID=al.labelID "
		. "INNER JOIN `def_rotations` AS r ON r.rotationID=al.rotationID "
		. "WHERE t.albumID = '$albumID' "
		. "AND t.disc_num = '$disc_num' AND t.track_num = '$track_num';";
	$track = exec_query($mysqli, $q)->fetch_assoc();

	return $track;
}

authenticate();
authenticate_logbook();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	$album_code = $mysqli->escape_string($_GET["album_code"]);
	$disc_num = array_access($_GET, "disc_num");
	$track_num = array_access($_GET, "track_num");

	if ( empty($album_code) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$album = get_album($mysqli, $album_code);

	if ( is_numeric($disc_num) && is_numeric($track_num) ) {
		$track = get_track($mysqli, $album["albumID"], $disc_num, $track_num);
		$album = array_merge($album, $track);
	}

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($album));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	// validate show
	$showID = get_current_show_id($mysqli);

	if ( $showID == null ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	// validate track
	$track = json_decode(file_get_contents("php://input"), true);
	$track = escape_json($mysqli, $track);

	log_track($mysqli, $showID, $track);
	$mysqli->close();

	exit;
}
?>
