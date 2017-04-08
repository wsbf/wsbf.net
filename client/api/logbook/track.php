<?php

/**
 * @file logbook/track.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("auth.php");
require_once("functions.php");

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	authenticate_logbook($mysqli);

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

	authenticate_logbook($mysqli);

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
