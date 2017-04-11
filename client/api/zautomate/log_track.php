<?php

/**
 * @file zautomate/log_track.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("../logbook/functions.php");

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	authenticate_logbook($mysqli);

	$album_code = $_GET["albumID"];
	$disc_num = $_GET["disc_num"];
	$track_num = $_GET["track_num"];

	if ( !isset($album_code)
	  || !is_numeric($disc_num)
	  || !is_numeric($track_num) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	// get current show or login Automation
	$showID = get_current_show_id($mysqli);

	if ( !isset($showID) ) {
		$showID = sign_on($mysqli, AUTOMATION_SCHEDULE_ID);
	}

	// get track information
	$album = get_album($mysqli, $album_code);
	$track = get_track($mysqli, $album["albumID"], $disc_num, $track_num);
	$track = escape_json($mysqli, $track);

	// log track
	log_track($mysqli, $showID, $track);
	$mysqli->close();

	exit("Successfully logged track $album_code-$disc_num-$track_num to show $showID.");
}
?>
