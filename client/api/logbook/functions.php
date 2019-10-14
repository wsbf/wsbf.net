<?php

/**
 * @file logbook/functions.php
 * @author Ben Shealy
 */
require_once("../schedule/functions.php");

/**
 * Get the current show.
 *
 * @param mysqli
 * @return show ID, or null if there is no current show
 */
function get_current_show_id($mysqli)
{
	$q = "SELECT showID, end_time FROM `show` "
		. "ORDER BY start_time DESC "
		. "LIMIT 1;";
	$show = exec_query($mysqli, $q)->fetch_assoc();

	if ( $show["end_time"] == null ) {
		return $show["showID"];
	}
	else {
		return null;
	}
}

/**
 * Start a new show with a given schedule ID.
 *
 * @param mysqli
 * @param scheduleID
 * @return new show ID
 */
function sign_on($mysqli, $scheduleID)
{
	// get show from schedule
	$schedule_show = get_schedule_show($mysqli, $scheduleID);
	$schedule_show = escape_json($mysqli, $schedule_show);

	// insert show
	$q = "INSERT INTO `show` SET "
		. "show_name = '$schedule_show[show_name]', "
		. "show_typeID = '$schedule_show[show_typeID]', "
		. "scheduleID = '$scheduleID';";
	exec_query($mysqli, $q);

	$showID = $mysqli->insert_id;

	// insert show hosts
	foreach ( $schedule_show["hosts"] as $h ) {
		$q = "INSERT INTO `show_hosts` SET "
			. "showID = '$showID', "
			. "username = '$h[username]';";
		exec_query($mysqli, $q);
	}

	return $showID;
}

/**
 * End the current show.
 *
 * @param mysqli
 */
function sign_off($mysqli)
{
	$showID = get_current_show_id($mysqli);

	$q = "UPDATE `show` SET end_time = NOW() "
		. "WHERE showID = '$showID' AND end_time IS NULL;";
	exec_query($mysqli, $q);
}

/**
 * Get a cart.
 *
 * @param mysqli
 * @param cartID
 * @return associative array of cart
 */
function get_cart($mysqli, $cartID)
{
	$q = "SELECT * FROM `libcart` AS c "
		. "INNER JOIN `def_cart_type` AS t ON t.cart_typeID=c.cart_typeID "
		. "WHERE c.cartID = '$cartID';";
	$cart = exec_query($mysqli, $q)->fetch_assoc();

	return $cart;
}

/**
 * Log a cart in the logbook.
 *
 * @param mysqli
 * @param showID
 * @param cart
 */
function log_cart($mysqli, $showID, $cart)
{
	$q = "INSERT INTO `logbook` SET "
		. "showID = '$showID', "
		. "lb_album_code = '$cart[cartID]', "
		. "lb_rotation = '$cart[type]', "
		. "lb_artist = '$cart[issuer]', "
		. "lb_track_name = '$cart[title]', "
		. "played = 1;";
	exec_query($mysqli, $q);
}

/**
 * Get information about an album.
 *
 * @param mysqli
 * @param album_code
 * @return associative array of album
 */
function get_album($mysqli, $album_code)
{
	$keys = array(
		"al.albumID",
		"r.bin_abbr AS rotation",
		"ar.artist_name",
		"al.album_name",
		"la.label"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `libalbum` AS al "
		. "INNER JOIN `libartist` AS ar ON ar.artistID=al.artistID "
		. "INNER JOIN `liblabel` AS la ON la.labelID=al.labelID "
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
 * @return associative array of track
 */
function get_track($mysqli, $albumID, $disc_num, $track_num)
{
	$keys = array(
		"al.album_code",
		"t.disc_num",
		"t.track_num",
		"r.bin_abbr AS rotation",
		"t.track_name",
		"t.airabilityID",
		"ar.artist_name",
		"al.album_name",
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

/**
 * Log a track in the logbook.
 *
 * @param mysqli
 * @param showID
 * @param track
 */
function log_track($mysqli, $showID, $track)
{
	// log track
	$q = "INSERT INTO `logbook` SET "
		. "showID = '$showID', "
		. "lb_album_code = '$track[album_code]', "
		. "lb_disc_num = '$track[disc_num]', "
		. "lb_track_num = '$track[track_num]', "
		. "lb_rotation = '$track[rotation]', "
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
?>
