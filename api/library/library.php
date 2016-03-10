<?php

/**
 * @file library/library.php
 * @author Ben Shealy
 *
 * Get albums in the music library, or update
 * the music library.
 *
 * TODO: add page offset
 */
require_once("../auth.php");
require_once("../connect.php");

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
		"al.rotationID",
		"ar.artist_name",
		"UNIX_TIMESTAMP(r.review_date) * 1000 AS review_date",
		"u.preferred_name AS reviewer"
	);

	$query = "SELECT " . implode(",", $keys) . " FROM `libalbum` AS al "
			. "INNER JOIN `libartist` AS ar ON al.artistID=ar.artistID "
			. "LEFT OUTER JOIN `libreview` AS r ON r.albumID=al.albumID "
			. "LEFT OUTER JOIN `users` AS u ON r.username=u.username "
			. "WHERE al.rotationID='$rotationID';";
	$result = $mysqli->query($query);

	$albums = array();
	while ( ($a = $result->fetch_assoc()) ) {
		$albums[] = $a;
	}

	return $albums;
}

/**
 * Move albums through rotation.
 *
 * @param mysqli  MySQL connection
 * @param albums  array of album IDs and rotation IDs
 */
function move_rotation($mysqli, $albums)
{
	foreach ( $albums as $a ) {
		$q = "UPDATE `libalbum` SET rotationID = '$a[rotationID]' "
			. " WHERE albumID = '$a[albumID]';";
		$mysqli->query($q);
	}
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
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
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_music_director($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to update the music library.");
	}

	$albums = json_decode(file_get_contents("php://input"), true);
	$albums = escape_json($mysqli, $albums);

	// TODO: validate albums

	move_rotation($mysqli, $albums);

	$mysqli->close();
	exit;
}
?>
