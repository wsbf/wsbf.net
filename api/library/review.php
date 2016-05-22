<?php

/**
 * @file library/review.php
 * @author Ben Shealy
 *
 * Get an album to review, or submit a new review.
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
 */
function get_next_album_code($mysqli)
{
	/* query the most recent album code */
	$q = "SELECT album_code FROM `libalbum` WHERE albumID != album_code "
		. "ORDER BY album_code DESC LIMIT 1;";
	$result = $mysqli->query($q);
	$assoc = $result->fetch_assoc();

	$prev = $assoc["album_code"];

	/* extract letter and number parts */
	$alpha = substr($prev, 0, strlen($prev) - 3);
	$num = (int) substr($prev, -3);

	/* increment number, increment letter every 1000 */
	$num = ($num + 1) % 1000;
	if ( $num == 0 ) {
		$alpha++;
	}

	return $alpha . str_pad($num, 3, "0", STR_PAD_LEFT);
}

/**
 * Check whether an album review is valid.
 *
 * @param mysqli  MySQL connection
 * @param review  associative array of album review
 * @return true if review is valid, false otherwise
 */
function validate_review($mysqli, $review)
{
	// required fields should be defined
	if ( !is_numeric($review["albumID"])
	  || empty($review["artist_name"])
	  || empty($review["album_name"])
	  || empty($review["label"])
	  || empty($review["genre"])
	  || empty($review["tracks"])
	  || empty($review["review"]) ) {
		return false;
	}

	// album should have at least one recommended track
	// and by extension, not all tracks as no-air
	$count_rec = 0;
	foreach ( $review["tracks"] as $t ) {
		if ( $t["airabilityID"] == 1 ) {
			$count_rec++;
		}
	}

	if ( $count_rec == 0 ) {
		return false;
	}

	// album should exist in `libalbum`
	$q = "SELECT rotationID FROM `libalbum` "
		. "WHERE albumID = '$review[albumID]';";
	$result = $mysqli->query($q);

	if ( $result->num_rows == 0 ) {
		return false;
	}

	// album should be in "To Be Reviewed" (rotationID = 0)
	$assoc = $result->fetch_assoc();
	if ( $assoc["rotationID"] != 0 ) {
		return false;
	}

	return true;
}

/**
 * Submit a new album review.
 *
 * @param mysqli  MySQL connection
 * @param review  associative array of album review
 */
function review_album($mysqli, $review)
{
	/* update album */
	$artistID = find_artist($mysqli, $review["artist_name"])
			or add_artist($mysqli, $review["artist_name"]);

	$labelID = find_label($mysqli, $review["label"])
			or add_label($mysqli, $review["label"]);

	$review["album_code"] = get_next_album_code($mysqli);

	$q = "UPDATE `libalbum` SET "
		. "album_name = '$review[album_name]', "
		. "album_code = '$review[album_code]', "
		. "artistID = '$artistID', "
		. "labelID = '$labelID', "
		. "genre = '$review[genre]', "
		. "rotationID = 7 "
		. "WHERE albumID = '$review[albumID]';";
	$mysqli->query($q);

	/* update tracks */
	foreach ( $review["tracks"] as $t ) {
		$artistID = find_artist($mysqli, $t["artist_name"])
				or add_artist($mysqli, $t["artist_name"]);

		$q = "UPDATE `libtrack` SET "
			. "track_name = '$t[track_name]', "
			. "artistID = '$artistID', "
			. "airabilityID = '$t[airabilityID]' "
			. "WHERE albumID = '$review[albumID]' "
			. "AND disc_num='$t[disc_num]' AND track_num='$t[track_num]';";
		$mysqli->query($q);
	}

	/* insert review */
	$q = "INSERT INTO `libreview` SET "
		. "albumID = '$review[albumID]', "
		. "review = '$review[review]', "
		. "username = '$_SESSION[username]';";
	$mysqli->query($q);

	/* add action */
	add_action($mysqli, "SUBMITTED REVIEW FOR albumID = $review[albumID]");
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to review albums.");
	}

	$review = json_decode(file_get_contents("php://input"), true);
	$review = escape_json($mysqli, $review);

	if ( !validate_review($mysqli, $review) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Album review is invalid.");
	}

	review_album($mysqli, $review);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($review));
}
?>
