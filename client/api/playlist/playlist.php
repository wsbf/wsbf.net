<?php

/**
 * @file playlist/playlist.php
 * @author Ben Shealy
 *
 * Get, update, or delete a playlist.
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Validate a playlistID.
 *
 * @param mysqli
 * @param playlistID
 * @return true if playlistID is valid, false otherwise
 */
function validate_playlistID($mysqli, $playlistID)
{
	if ( !is_numeric($playlistID) ) {
		return false;
	}

	// playlist must belong to current user
	$q = "SELECT username FROM `playlist` "
		. "WHERE playlistID = '$playlistID' "
		. "AND username = '$_SESSION[username]';";
	$result = exec_query($mysqli, $q);

	return ($result->num_rows == 1);
}

/**
 * Validate a playlist.
 *
 * @param mysqli
 * @param playlist
 * @return true if playlist is valid, false otherwise
 */
function validate_playlist($mysqli, $playlist)
{
	if ( empty($playlist["name"])
	  || !is_array($playlist["tracks"]) ) {
		return false;
	}

	if ( !empty($playlist["playlistID"])
	  && !validate_playlistID($mysqli, $playlist["playlistID"]) ) {
		return false;
	}

	return true;
}

/**
 * Get a playlist.
 *
 * @param mysqli
 * @param playlistID
 */
function get_playlist($mysqli, $playlistID)
{
	// get playlist
	$keys = array(
		"playlistID",
		"name"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `playlist` "
		. "WHERE playlistID = '$playlistID';";
	$playlist = exec_query($mysqli, $q)->fetch_assoc();

	// get playlist tracks
	$track_keys = array(
		"album_code",
		"disc_num",
		"track_num",
		"track_name",
		"artist_name",
		"album_name",
		"label"
	);

	$q = "SELECT " . implode(",", $track_keys) . " FROM `playlist_track` "
		. "WHERE playlistID = '$playlistID';";
	$result = exec_query($mysqli, $q);

	$playlist["tracks"] = fetch_array($result);

	return $playlist;
}

/**
 * Create a playlist.
 *
 * @param mysqli
 * @param playlist
 */
function create_playlist($mysqli, $playlist)
{
	// insert playlist
	$q = "INSERT INTO `playlist` SET "
		. "name = '$playlist[name]', "
		. "username = '$_SESSION[username]';";
	exec_query($mysqli, $q);

	// insert playlist tracks
	$playlistID = $mysqli->insert_id;

	foreach ( $playlist["tracks"] as $t ) {
		$q = "INSERT INTO `playlist_track` SET "
			. "playlistID = '$playlistID', "
			. "album_code = '$t[album_code]', "
			. "disc_num = '$t[disc_num]', "
			. "track_num = '$t[track_num]', "
			. "track_name = '$t[track_name]', "
			. "artist_name = '$t[artist_name]', "
			. "album_name = '$t[album_name]', "
			. "label = '$t[label]';";
		exec_query($mysqli, $q);
	}
}

/**
 * Update a playlist.
 *
 * @param mysqli
 * @param playlist
 */
function update_playlist($mysqli, $playlist)
{
	// update playlist
	$q = "UPDATE `playlist` SET "
		. "name = '$playlist[name]' "
		. "WHERE playlistID = '$playlist[playlistID]';";
	exec_query($mysqli, $q);

	// update playlist tracks
	$q = "DELETE FROM `playlist_track` "
		. "WHERE playlistID = '$playlist[playlistID]';";
	exec_query($mysqli, $q);

	foreach ( $playlist["tracks"] as $t ) {
		$q = "INSERT INTO `playlist_track` SET "
			. "playlistID = '$playlist[playlistID]', "
			. "album_code = '$t[album_code]', "
			. "disc_num = '$t[disc_num]', "
			. "track_num = '$t[track_num]', "
			. "track_name = '$t[track_name]', "
			. "artist_name = '$t[artist_name]', "
			. "album_name = '$t[album_name]', "
			. "label = '$t[label]';";
		exec_query($mysqli, $q);
	}
}

/**
 * Delete a playlist.
 *
 * @param mysqli
 * @param playlistID
 */
function delete_playlist($mysqli, $playlistID)
{
	// delete playlist tracks
	$q = "DELETE FROM `playlist_track` "
		. "WHERE playlistID = '$playlistID';";
	exec_query($mysqli, $q);

	// delete playlist
	$q = "DELETE FROM `playlist` "
		. "WHERE playlistID = '$playlistID';";
	exec_query($mysqli, $q);
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	authenticate();
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	$playlistID = $_GET["playlistID"];

	if ( !validate_playlistID($mysqli, $playlistID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$playlist = get_playlist($mysqli, $playlistID);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($playlist));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	authenticate();
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	$playlist = json_decode(file_get_contents("php://input"), true);
	$playlist = escape_json($mysqli, $playlist);

	if ( !validate_playlist($mysqli, $playlist) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Submitted playlist data is invalid.");
	}

	if ( array_key_exists("playlistID", $playlist) ) {
		update_playlist($mysqli, $playlist);
	}
	else {
		create_playlist($mysqli, $playlist);
	}

	$mysqli->close();

	exit;
}
else if ( $_SERVER["REQUEST_METHOD"] == "DELETE" ) {
	authenticate();
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	$playlistID = $_GET["playlistID"];

	if ( !validate_playlistID($mysqli, $playlistID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	delete_playlist($mysqli, $playlistID);
	$mysqli->close();

	exit;
}
?>
