<?php

/**
 * @file library/album.php
 * @author Ben Shealy
 *
 * Get an album in the music library.
 */
require_once("../auth.php");
require_once("../connect.php");
require_once("functions.php");

/**
 * Get an album in the music library.
 *
 * @param mysqli   MySQL connection
 * @param albumID  album ID
 * @return associative array of album
 */
function get_album($mysqli, $albumID)
{
	/* get album object */
	$keys = array(
		"al.albumID",
		"al.album_code",
		"al.album_name",
		"ar.artist_name",
		"la.label",
		"al.genre",
		"al.general_genreID",
		"r.review_date",
		"r.review",
		"r.username",
		"u.preferred_name AS reviewer"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `libalbum` AS al "
		. "INNER JOIN `libartist` AS ar ON al.artistID=ar.artistID "
		. "INNER JOIN `liblabel` AS la ON al.labelID=la.labelID "
		. "INNER JOIN `libreview` AS r ON r.albumID=al.albumID "
		. "INNER JOIN `users` AS u ON u.username=r.username "
		. "WHERE al.albumID='$albumID';";
	$album = $mysqli->query($q)->fetch_assoc();

	/* get array of tracks  */
	$track_keys = array(
		"t.disc_num",
		"t.track_num",
		"t.track_name",
		"ar.artist_name",
		"t.airabilityID"
	);

	$q = "SELECT " . implode(",", $track_keys) . " FROM `libtrack` AS t "
		. "INNER JOIN `libartist` AS ar ON t.artistID=ar.artistID "
		. "WHERE t.albumID='$albumID';";
	$result = $mysqli->query($q);

	$album["tracks"] = array();
	while ( ($t = $result->fetch_assoc()) ) {
		$t["disc_num"] = (int) $t["disc_num"];
		$t["track_num"] = (int) $t["track_num"];

		$album["tracks"][] = $t;
	}

	return $album;
}

/**
 * Validate an album.
 *
 * @param mysqli  MySQL connection
 * @param album   associative array of album
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
	$result = $mysqli->query($q);

	if ( $result->num_rows == 0 ) {
		return false;
	}

	$assoc = $result->fetch_assoc();
	$rotationID = $assoc["rotationID"];

	if ( $rotationID != 0 ) {
		// reviewed albums should have review and reviewer
		if ( empty($album["review"])
		  || empty($album["username"]) ) {
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
 * @param mysqli  MySQL connection
 * @param album   associative array of album
 */
function update_album($mysqli, $album)
{
	/* update album */
	$artistID = find_artist($mysqli, $album["artist_name"])
			or add_artist($mysqli, $album["artist_name"]);

	$labelID = find_label($mysqli, $album["label"])
			or add_label($mysqli, $album["label"]);

	$q = "UPDATE `libalbum` SET "
		. "album_name = '$album[album_name]', "
		. "artistID = '$artistID', "
		. "labelID = '$labelID', "
		. "general_genreID = '$album[general_genreID]', "
		. "genre = '$album[genre]' "
		. "WHERE albumID = '$album[albumID]';";
	$mysqli->query($q);

	/* update tracks */
	foreach ( $album["tracks"] as $t ) {
		$artistID = find_artist($mysqli, $t["artist_name"])
				or add_artist($mysqli, $t["artist_name"]);

		$q = "UPDATE `libtrack` SET "
			. "track_name = '$t[track_name]', "
			. "artistID = '$artistID', "
			. "airabilityID = '$t[airabilityID]' "
			. "WHERE albumID = '$album[albumID]' "
			. "AND disc_num='$t[disc_num]' AND track_num='$t[track_num]';";
		$mysqli->query($q);
	}

	/* update review */
	$q = "UPDATE `libreview` SET "
		. "review = '$album[review]', "
		. "username = '$album[username]' "
		. "WHERE albumID = '$album[albumID]';";
	$mysqli->query($q);

	/* add action */
	add_action($mysqli, "EDITED REVIEW FOR albumID = $album[albumID]");
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to view the music library.");
	}

	$albumID = $_GET["albumID"];

	if ( !is_numeric($albumID) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Album ID is empty or invalid.");
	}

	$album = get_album($mysqli, $albumID);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($album));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_music_director($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to edit albums.");
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
?>
