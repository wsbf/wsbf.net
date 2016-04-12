<?php

/**
 * @file library/functions.php
 * @author Ben Shealy
 *
 * Functions for interacting with the music library.
 */

/**
 * Add an action by the current user to `libaction`.
 *
 * @param mysqli       MySQL connection
 * @param description  description of action
 */
function add_action($mysqli, $description)
{
	$q = "INSERT INTO `libaction` (username, change_description) "
		. "VALUES ('$_SESSION[username]', '$description');";
	$mysqli->query($q);
}

/**
 * Search `libartist` by artist name.
 *
 * @param mysqli       MySQL connection
 * @param artist_name  artist name
 * @return artist ID, or null if not found
 */
function find_artist($mysqli, $artist_name)
{
	$q = "SELECT artistID FROM `libartist` WHERE artist_name='$artist_name';";
	$result = $mysqli->query($q);

	if ( $result->num_rows > 0 ) {
		$assoc = $result->fetch_assoc();
		return $assoc["artistID"];
	}

	return null;
}

/**
 * Add an artist to `libartist`.
 *
 * @param mysqli       MySQL connection
 * @param artist_name  artist name
 * @return ID of the inserted artist
 */
function add_artist($mysqli, $artist_name)
{
	$q = "INSERT INTO `libartist` (artist_name) VALUES ('$artist_name');";
	$mysqli->query($q);

	return $mysqli->insert_id;
}

/**
 * Search `liblabel` by label name.
 *
 * @param mysqli      MySQL connection
 * @param label_name  label name
 * @return label ID, or null if not found
 */
function find_label($mysqli, $label)
{
	$q = "SELECT labelID FROM `liblabel` WHERE label='$label';";
	$result = $mysqli->query($q);

	if ( $result->num_rows > 0 ) {
		$assoc = $result->fetch_assoc();
		return $assoc["labelID"];
	}

	return null;
}

/**
 * Add a label to `liblabel`.
 *
 * @param mysqli      MySQL connection
 * @param label_name  label name
 * @return ID of the inserted label
 */
function add_label($mysqli, $label)
{
	$q = "INSERT INTO `liblabel` (label) VALUES ('$label');";
	$mysqli->query($q);

	return $mysqli->insert_id;
}
?>
