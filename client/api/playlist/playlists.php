<?php

/**
 * @file playlist/playlists.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get the current user's playlists.
 *
 * @param mysqli
 * @return array of playlists
 */
function get_playlists($mysqli)
{
	$keys = array(
		"p.playlistID",
		"p.name"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `playlist` AS p "
		. "WHERE p.username = '$_SESSION[username]';";
	$result = exec_query($mysqli, $q);

	$playlists = fetch_array($result);

	return $playlists;
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	authenticate();
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$playlists = get_playlists($mysqli);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($playlists));
}
?>
