<?php

/**
 * @file library/library.php
 * @author Ben Shealy
 *
 * @section DESCRIPTION
 *
 * Get a section of the album library.
 */
require_once("../auth.php");
require_once("../connect-dev.php");

/**
 * Get a section of the album library.
 *
 * @param mysqli      MySQL connection
 * @param rotationID  rotation ID
 * @return array of albums
 */
function get_library($mysqli, $rotationID)
{
	$keys = array(
		"al.albumID",
		"al.album_code",
		"al.album_name",
		"ar.artist_name",
		"r.review_date",
		"u.preferred_name AS reviewer"
	);

	$query = "SELECT " . implode(",", $keys) . " FROM `libalbum` AS al "
			. "INNER JOIN `libartist` AS ar ON al.artistID=ar.artistID "
			. "INNER JOIN `libreview` AS r ON r.albumID=al.albumID "
			. "INNER JOIN `users` AS u ON r.username=u.username "
			. "WHERE al.rotationID='$rotationID';";
	$result = $mysqli->query($query);

	$albums = array();
	while ( ($a = $result->fetch_assoc()) ) {
		$albums[] = $a;
	}

	return $albums;
}

$mysqli = construct_connection();

if ( !check_reviewer($mysqli) ) {
	header("HTTP/1.1 401 Unauthorized");
	exit("Current user is not allowed to view the music library.");
}

$rotationID = $_GET["rotation"];

if ( !is_numeric($rotationID) || $rotationID < 1 || 7 < $rotationID ) {
	header("HTTP/1.1 404 Not Found");
	exit("Rotation ID is empty or invalid.");
}

$albums = get_library($mysqli, $rotationID);

$mysqli->close();

header("Content-Type: application/json");
exit(json_encode($albums));
?>
