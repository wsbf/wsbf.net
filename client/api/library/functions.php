<?php

/**
 * @file library/functions.php
 * @author Ben Shealy
 *
 * Functions for interacting with the music library.
 */
require_once("../connect.php");

/**
 * Add an action by the current user to `libaction`.
 *
 * @param mysqli
 * @param description
 */
function add_action($mysqli, $description)
{
	$q = "INSERT INTO `libaction` (username, change_description) "
		. "VALUES ('$_SESSION[username]', '$description');";
	exec_query($mysqli, $q);
}

/**
 * Search `libartist` by artist name.
 *
 * @param mysqli
 * @param artist_name
 * @return artist ID, or null if not found
 */
function find_artist($mysqli, $artist_name)
{
	$q = "SELECT artistID FROM `libartist` WHERE artist_name='$artist_name';";
	$result = exec_query($mysqli, $q);

	if ( $result->num_rows > 0 ) {
		$assoc = $result->fetch_assoc();
		return $assoc["artistID"];
	}

	return null;
}

/**
 * Add an artist to `libartist`.
 *
 * @param mysqli
 * @param artist_name
 * @return ID of the inserted artist
 */
function add_artist($mysqli, $artist_name)
{
	$q = "INSERT INTO `libartist` (artist_name) VALUES ('$artist_name');";
	exec_query($mysqli, $q);

	return $mysqli->insert_id;
}

/**
 * Search `liblabel` by label name.
 *
 * @param mysqli
 * @param label_name
 * @return label ID, or null if not found
 */
function find_label($mysqli, $label)
{
	$q = "SELECT labelID FROM `liblabel` WHERE label='$label';";
	$result = exec_query($mysqli, $q);

	if ( $result->num_rows > 0 ) {
		$assoc = $result->fetch_assoc();
		return $assoc["labelID"];
	}

	return null;
}

/**
 * Add a label to `liblabel`.
 *
 * @param mysqli
 * @param label_name
 * @return ID of the inserted label
 */
function add_label($mysqli, $label)
{
	$q = "INSERT INTO `liblabel` (label) VALUES ('$label');";
	exec_query($mysqli, $q);

	return $mysqli->insert_id;
}

/**
 * Search the music library.
 *
 * @param mysqli
 * @param rotationID
 * @param term
 * @param page
 * @return array of albums
 */
function search_albums($mysqli, $rotationID, $term, $page)
{
	$keys = array(
		"al.albumID",
		"al.album_code",
		"al.album_name",
		"al.general_genreID",
		"al.rotationID",
		"ar.artist_name",
		"r.review_date",
		"u.preferred_name AS reviewer"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `libalbum` AS al "
		. "INNER JOIN `libartist` AS ar ON al.artistID=ar.artistID "
		. "LEFT OUTER JOIN `libreview` AS r ON r.albumID=al.albumID "
		. "LEFT OUTER JOIN `users` AS u ON r.username=u.username "
		. "WHERE al.rotationID = '$rotationID' "
		. "AND ("
			. "al.album_name LIKE '%$term%' "
			. "OR ar.artist_name LIKE '%$term%' "
			. "OR u.preferred_name LIKE '%$term%' "
		. ") "
		. "ORDER BY al.albumID DESC;";
	$result = exec_query($mysqli, $q);

	$albums = array();
	while ( ($a = $result->fetch_assoc()) ) {
		$albums[] = $a;
	}

	return $albums;
}
?>
