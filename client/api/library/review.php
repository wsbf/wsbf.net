<?php

/**
 * @file library/review.php
 * @author Ben Shealy
 *
 * Review an album.
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("functions.php");

/**
 * Get the next album code.
 *
 * According to PHP syntax, incrementing 'Z' evaluates to 'AA',
 * so if the albums ever reach 'Z999', the next album code
 * will be 'AA000'.
 *
 * @param mysqli
 * @return next album code
 */
function get_next_album_code($mysqli)
{
	// query the most recent album code
	$q = "SELECT album_code FROM `libalbum` WHERE albumID != album_code "
		. "ORDER BY album_code DESC LIMIT 1;";
	$result = exec_query($mysqli, $q);
	$assoc = $result->fetch_assoc();

	$prev = $assoc["album_code"];

	// extract letter and number parts
	$alpha = substr($prev, 0, strlen($prev) - 3);
	$num = (int) substr($prev, -3);

	// increment number, increment letter every 1000
	$num = ($num + 1) % 1000;
	if ( $num == 0 ) {
		$alpha++;
	}

	return $alpha . str_pad($num, 3, "0", STR_PAD_LEFT);
}

/**
 * Determine whether a reviewed album is valid.
 *
 * @param mysqli
 * @param album
 * @return true if reviewed album is valid, false otherwise
 */
function validate_review($mysqli, $album)
{
	// required fields should be defined
	if ( !is_numeric($album["albumID"])
	  || empty($album["artist_name"])
	  || empty($album["album_name"])
	  || empty($album["label"])
	  || !is_numeric($album["general_genreID"])
	  || empty($album["genre"])
	  || empty($album["tracks"])
	  || empty($album["review"]) ) {
		return false;
	}

	// album should have at least one recommended track
	// and by extension, not all tracks as no-air
	$num_rec = 0;
	foreach ( $album["tracks"] as $t ) {
		if ( $t["airabilityID"] == 1 ) {
			$num_rec++;
		}
	}

	if ( $num_rec == 0 ) {
		return false;
	}

	// album should be checked out by the current user
	$q = "SELECT a.rotationID FROM `libalbum` AS a "
		. "INNER JOIN `checkout` as c ON c.albumID = a.albumID "
		. "WHERE a.albumID = '$album[albumID]' "
		. "AND a.rotationID = 1 "
		. "AND c.username = '$_SESSION[username]' "
		. "AND CURDATE() < c.expiration_date;";
	$result = exec_query($mysqli, $q);

	if ( $result->num_rows == 0 ) {
		return false;
	}

	return true;
}

/**
 * Review an album.
 *
 * @param mysqli
 * @param album
 */
function review_album($mysqli, $album)
{
	// fetch artist ID, label ID, album code
	$artistID = find_artist($mysqli, $album["artist_name"]);
	if ( !isset($artistID) ) {
		$artistID = add_artist($mysqli, $album["artist_name"]);
	}

	$labelID = find_label($mysqli, $album["label"]);
	if ( !isset($labelID) ) {
		$labelID = add_label($mysqli, $album["label"]);
	}

	$album["album_code"] = get_next_album_code($mysqli);

	// update album
	$q = "UPDATE `libalbum` SET "
		. "album_name = '$album[album_name]', "
		. "album_code = '$album[album_code]', "
		. "artistID = '$artistID', "
		. "labelID = '$labelID', "
		. "general_genreID = '$album[general_genreID]', "
		. "genre = '$album[genre]', "
		. "rotationID = 2 "
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

	// insert review
	$q = "REPLACE INTO `libreview` SET "
		. "albumID = '$album[albumID]', "
		. "review = '$album[review]', "
		. "username = '$_SESSION[username]', "
		. "review_date = CURDATE();";
	exec_query($mysqli, $q);

	// add action
	add_action($mysqli, "SUBMITTED REVIEW FOR albumID = $album[albumID]");
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !auth_reviewer($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$album = json_decode(file_get_contents("php://input"), true);
	$album = escape_json($mysqli, $album);

	if ( !validate_review($mysqli, $album) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Album review is invalid.");
	}

	review_album($mysqli, $album);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($album));
}
?>
