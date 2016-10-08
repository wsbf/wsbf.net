<?php

/**
 * @file zautomate/log_track.php
 * @author Ben Shealy
 */
require_once("../connect.php");
require_once("../logbook/functions.php");
require_once("auth.php");

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

authenticate_automation();

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$album_code = $_GET["albumID"];
	$disc_num = $_GET["disc_num"];
	$track_num = $_GET["track_num"];

	if ( !isset($album_code)
	  || !is_numeric($disc_num)
	  || !is_numeric($track_num) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$mysqli = construct_connection();

	$showID = get_current_show($mysqli);
	$album = get_album($mysqli, $album_code);
	$track = get_track($mysqli, $album["albumID"], $disc_num, $track_num);

	log_track($mysqli, $showID, $track);
	$mysqli->close();

	exit("Successfully logged track $album_code-$disc_num-$track_num to show $showID.");
}
?>
