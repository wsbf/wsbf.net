<?php

/**
 * @file library/album.php
 * @author Ben Shealy
 *
 * Get, update, or delete an album in the music library.
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("../import/config.php");
require_once("functions.php");

/**
 * Get an album in the music library.
 *
 * @param mysqli
 * @param albumID
 * @return associative array of album
 */
function get_album($mysqli, $albumID)
{
	// get album object
	$keys = array(
		"al.albumID",
		"al.album_code",
		"al.album_name",
		"ar.artist_name",
		"la.label",
		"al.genre",
		"al.general_genreID",
		"UNIX_TIMESTAMP(r.review_date) * 1000 AS review_date",
		"r.review",
		"u.preferred_name AS reviewer"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `libalbum` AS al "
		. "INNER JOIN `libartist` AS ar ON al.artistID=ar.artistID "
		. "INNER JOIN `liblabel` AS la ON al.labelID=la.labelID "
		. "LEFT OUTER JOIN `libreview` AS r ON r.albumID=al.albumID "
		. "LEFT OUTER JOIN `users` AS u ON u.username=r.username "
		. "WHERE al.albumID='$albumID';";
	$album = exec_query($mysqli, $q)->fetch_assoc();

	// get array of tracks
	$track_keys = array(
		"t.disc_num",
		"t.track_num",
		"t.track_name",
		"ar.artist_name",
		"t.airabilityID",
		"t.file_name"
	);

	$q = "SELECT " . implode(",", $track_keys) . " FROM `libtrack` AS t "
		. "INNER JOIN `libartist` AS ar ON t.artistID=ar.artistID "
		. "WHERE t.albumID='$albumID';";
	$result = exec_query($mysqli, $q);

	$album["tracks"] = array();
	while ( ($t = $result->fetch_assoc()) ) {
		// convert numeric properties
		$t["disc_num"] = (int) $t["disc_num"];
		$t["track_num"] = (int) $t["track_num"];

		// temporary hack to transform file_name to path
		$f = urldecode($t["file_name"]);
		$t["file_name"] = "/wizbif/ZAutoLib/$f[0]/$f[1]/" . substr($f, 2);

		$album["tracks"][] = $t;
	}

	return $album;
}

/**
 * Validate an album.
 *
 * @param mysqli
 * @param album
 * @return true if album is valid, false otherwise
 */
function validate_album($mysqli, $album)
{
	// required fields should be defined
	if ( !is_numeric($album["albumID"])
	  || empty($album["artist_name"])
	  || empty($album["album_name"])
	  || empty($album["label"])
	  || !is_numeric($album["general_genreID"])
	  || empty($album["genre"])
	  || !is_array($album["tracks"]) ) {
		return false;
	}

	// album should exist in `libalbum`
	$q = "SELECT rotationID FROM `libalbum` "
		. "WHERE albumID = '$album[albumID]';";
	$result = exec_query($mysqli, $q);

	if ( $result->num_rows == 0 ) {
		return false;
	}

	$assoc = $result->fetch_assoc();
	$rotationID = $assoc["rotationID"];

	if ( $rotationID != 0 ) {
		// reviewed albums should have a review
		// (reviewer username is fixed upon review)
		if ( empty($album["review"]) ) {
			return false;
		}

		// reviewed albums should have at least one recommended track
		// and by extension, not all tracks as no-air
		$count_rec = 0;
		foreach ( $album["tracks"] as $t ) {
			if ( $t["airabilityID"] == 1 ) {
				$count_rec++;
			}
		}

		if ( $count_rec == 0 ) {
			return false;
		}
	}

	return true;
}

/**
 * Update an album.
 *
 * @param mysqli
 * @param album
 */
function update_album($mysqli, $album)
{
	// fetch artist and label IDs
	$artistID = find_artist($mysqli, $album["artist_name"]);
	if ( !isset($artistID) ) {
		$artistID = add_artist($mysqli, $album["artist_name"]);
	}

	$labelID = find_label($mysqli, $album["label"]);
	if ( !isset($labelID) ) {
		$labelID = add_label($mysqli, $album["label"]);
	}

	// update album
	$q = "UPDATE `libalbum` SET "
		. "album_name = '$album[album_name]', "
		. "artistID = '$artistID', "
		. "labelID = '$labelID', "
		. "general_genreID = '$album[general_genreID]', "
		. "genre = '$album[genre]' "
		. "WHERE albumID = '$album[albumID]';";
	exec_query($mysqli, $q);

	// update tracks
	foreach ( $album["tracks"] as $t ) {
		$artistID = find_artist($mysqli, $t["artist_name"]);
		if ( !isset($artistID) ) {
			$artistID = add_artist($mysqli, $t["artist_name"]);
		}

		$q = "UPDATE `libtrack` SET "
			. "track_name = '$t[track_name]', "
			. "artistID = '$artistID', "
			. "airabilityID = '$t[airabilityID]' "
			. "WHERE albumID = '$album[albumID]' "
			. "AND disc_num='$t[disc_num]' AND track_num='$t[track_num]';";
		exec_query($mysqli, $q);
	}

	// update review
	$q = "UPDATE `libreview` SET "
		. "review = '$album[review]' "
		. "WHERE albumID = '$album[albumID]';";
	exec_query($mysqli, $q);

	// add action
	add_action($mysqli, "EDITED REVIEW FOR albumID = $album[albumID]");
}

/**
 * Determine whether an album can be deleted.
 *
 * @param mysqli
 * @param albumID
 * @return true if the album can be deleted, false otherwise
 */
function validate_album_delete($mysqli, $albumID)
{
	if ( !is_numeric($albumID) ) {
		return false;
	}

	// album must be in To Be Reviewed (rotationID = 0)
	$q = "SELECT rotationID FROM `libalbum` WHERE albumID='$albumID';";
	$result = exec_query($mysqli, $q);

	if ( $result->num_rows == 0 ) {
		return false;
	}

	$album = $result->fetch_assoc();

	if ( $album["rotationID"] != 0 ) {
		return false;
	}

	return true;
}

/**
 * Delete an album.
 *
 * All files associated with the album are moved to the import directory.
 *
 * @param mysqli
 * @param albumID
 */
function delete_album($mysqli, $albumID)
{
	// get track filenames
	$q = "SELECT file_name FROM `libtrack` WHERE albumID='$albumID';";
	$result = exec_query($mysqli, $q);

	$tracks = array();

	while ( ($t = $result->fetch_assoc()) ) {
		$f = urldecode($t["file_name"]);
		$t["file_name"] = "/$f[0]/$f[1]/" . substr($f, 2);

		$tracks[] = $t;
	}

	// move files from digital library to import directory
	$pairs = array_map(function($t) {
		return array(
			"src" => IMPORT_DST . $t["file_name"],
			"dst" => IMPORT_SRC . "/albums/" . substr($t["file_name"], 4)
		);
	}, $tracks);

	foreach ( $pairs as $p ) {
		if ( !copy($p["src"], $p["dst"]) ) {
			header("HTTP/1.1 500 Internal Server Error");
			exit("Could not copy files.");
		}
	}

	foreach ( $pairs as $p ) {
		unlink($p["src"]);
	}

	// delete album and track records
	$q = "DELETE FROM `libtrack` WHERE albumID='$albumID';";
	exec_query($mysqli, $q);

	$q = "DELETE FROM `libalbum` WHERE albumID='$albumID';";
	exec_query($mysqli, $q);

	// insert action
	add_action($mysqli, "DELETED album $albumID");
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$albumID = $_GET["albumID"];

	if ( !is_numeric($albumID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$album = get_album($mysqli, $albumID);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($album));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_music_director($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$album = json_decode(file_get_contents("php://input"), true);
	$album = escape_json($mysqli, $album);

	if ( !validate_album($mysqli, $album) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Submitted album data is invalid.");
	}

	update_album($mysqli, $album);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($album));
}
else if ( $_SERVER["REQUEST_METHOD"] == "DELETE" ) {
	$mysqli = construct_connection();

	if ( !check_music_director($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$albumID = $_GET["albumID"];

	if ( !validate_album_delete($mysqli, $albumID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	delete_album($mysqli, $albumID);
	$mysqli->close();

	exit;
}
?>
