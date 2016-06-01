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
 * @param mysqli      MySQL connection
 * @param album_code  album code
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
	$album = $mysqli->query($q)->fetch_assoc();

	return $album;
}

/**
 * Get information about a track.
 *
 * @param mysqli     MySQL connection
 * @param albumID    album ID
 * @param disc_num   disc number
 * @param track_num  track number
 */
function get_track($mysqli, $albumID, $disc_num, $track_num)
{
	$keys = array(
		"track_name",
		"airabilityID"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `libtrack` "
		. "WHERE albumID = '$albumID' "
		. "AND disc_num = '$disc_num' AND track_num = '$track_num';";
	$track = $mysqli->query($q)->fetch_assoc();

	return $track;
}

// TODO: duplicated from zautomate/log_track.php
/**
 * Log a track in the logbook.
 *
 * @param mysqli     MySQL connection
 * @param showID     show ID
 * @param albumID    album ID
 * @param disc_num   disc number
 * @param track_num  track number
 */
function log_track($mysqli, $showID, $albumID, $disc_num, $track_num)
{
	// get track
	$keys = array(
		"al.album_code",
		"r.binAbbr AS rotation",
		"t.track_name",
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
	$track = $mysqli->query($q)->fetch_assoc();

	// log track
	$q = "INSERT INTO `logbook` SET "
		. "showID = '$showID', "
		. "lb_album_code = '$track[album_code]', "
		. "lb_rotation = '$track[rotation]', "
		. "lb_disc_num = '$disc_num', "
		. "lb_track_num = '$track_num', "
		. "lb_track_name = '$track[track_name]', "
		. "lb_artist = '$track[artist_name]', "
		. "lb_album = '$track[album_name]', "
		. "lb_label = '$track[label]', "
		. "played = 1;";
	$mysqli->query($q);

	// update now playing
	$q = "UPDATE `now_playing` SET "
		. "logbookID = LAST_INSERT_ID(), "
		. "lb_track_name = '$track[track_name]', "
		. "lb_artist_name = '$track[artist_name]';";
	$mysqli->query($q);

	// TODO: send RDS
}

authenticate();
authenticate_logbook();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	$album_code = $mysqli->escape_string($_GET["album_code"]);
	$disc_num = $_GET["disc_num"];
	$track_num = $_GET["track_num"];

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

	$albumID = $_GET["albumID"];
	$disc_num = $_GET["disc_num"];
	$track_num = $_GET["track_num"];

	if ( !is_numeric($albumID)
	  || !is_numeric($disc_num)
	  || !is_numeric($track_num) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$showID = get_current_show_id($mysqli);

	if ( $showID == null ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	log_track($mysqli, $showID, $albumID, $disc_num, $track_num);
	$mysqli->close();

	exit;
}
?>
