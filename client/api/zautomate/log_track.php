<?php

/**
 * @file zautomate/log_track.php
 * @author Ben Shealy
 */
require_once("../connect.php");
require_once("../logbook/functions.php");
require_once("auth.php");

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

	// get current show or login Automation
	$showID = get_current_show_id($mysqli);

	if ( !isset($showID) ) {
		$showID = sign_on($mysqli, AUTOMATION_SCHEDULE_ID);
	}

	// log track
	$album = get_album($mysqli, $album_code);
	$track = get_track($mysqli, $album["albumID"], $disc_num, $track_num);

	log_track($mysqli, $showID, $track);
	$mysqli->close();

	exit("Successfully logged track $album_code-$disc_num-$track_num to show $showID.");
}
?>
