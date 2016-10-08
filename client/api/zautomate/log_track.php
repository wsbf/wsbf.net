<?php

/**
 * @file zautomate/log_track.php
 * @author Ben Shealy
 */
require_once("../connect.php");

define('VALID_IP_ADDR', "130.127.17.5");

// TODO: duplicated from log_cart.php
/**
 * Get the current show, or create a new
 * Automation show if there is no show.
 *
 * @param mysqli
 * @return current show ID
 */
function get_current_show($mysqli)
{
	// get the most recent show
	$q = "SELECT showID, end_time FROM `show` "
		. "ORDER BY start_time DESC "
		. "LIMIT 1;";
	$show = exec_query($mysqli, $q)->fetch_assoc();

	// check whether the show has ended yet
	if ( $show["end_time"] == null ) {
		return $show["showID"];
	}
	else {
		// login Automation (confer logbook/sign_on.php)
		$q = "INSERT INTO `show` SET "
			. "show_name = 'The Best of WSBF', "
			. "show_typeID = 8;";
		exec_query($mysqli, $q);

		$showID = $mysqli->insert_id;

		$q = "INSERT INTO `show_hosts` SET "
			. "showID = '$showID', "
			. "username = 'Automation';";
		exec_query($mysqli, $q);

		return $showID;
	}
}

// TODO: combine with get_track() in logbook/functions.php
/**
 * Log a track in the logbook.
 *
 * @param mysqli
 * @param showID
 * @param albumID
 * @param disc_num
 * @param track_num
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
//		. "WHERE t.albumID = '$albumID' "
		. "WHERE al.album_code = '$albumID' "
		. "AND t.disc_num = '$disc_num' AND t.track_num = '$track_num';";
	$track = exec_query($mysqli, $q)->fetch_assoc();

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
	exec_query($mysqli, $q);

	// update now playing
	$q = "UPDATE `now_playing` SET "
		. "logbookID = LAST_INSERT_ID(), "
		. "lb_track_name = '$track[track_name]', "
		. "lb_artist_name = '$track[artist_name]';";
	exec_query($mysqli, $q);

	// TODO: send RDS
}

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	if ( $_SERVER["REMOTE_ADDR"] !== VALID_IP_ADDR ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$albumID = $_GET["albumID"];
	$disc_num = $_GET["disc_num"];
	$track_num = $_GET["track_num"];

//	if ( !is_numeric($albumID)
	if ( !isset($albumID)
	  || !is_numeric($disc_num)
	  || !is_numeric($track_num) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$mysqli = construct_connection();

	$showID = get_current_show($mysqli);

	log_track($mysqli, $showID, $albumID, $disc_num, $track_num);
	$mysqli->close();

	exit("Successfully logged track $albumID-$disc_num-$track_num to show $showID.");
}
?>
