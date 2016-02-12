<?php

/**
 * @file review/album_list.php
 * @author Ben Shealy
 *
 * Get albums that are available to review.
 */
require_once("../auth.php");
require_once("../connect.php");

// TODO: add page offset
/**
 * Get albums that are available to review.
 *
 * @param mysqli    MySQL connection
 * @return array of albums
 */
function get_albums($mysqli)
{
	$keys = array(
		"al.albumID",
		"al.album_code",
		"al.album_name",
		"ar.artist_name",
	);

	$query = "SELECT " . implode(",", $keys) . " FROM `libalbum` AS al "
			. "INNER JOIN `libartist` AS ar ON al.artistID=ar.artistID "
			. "WHERE al.rotationID=0;";
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

$albums = get_albums($mysqli);
$mysqli->close();

header("Content-Type: application/json");
exit(json_encode($albums));
?>
