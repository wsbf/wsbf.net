<?php

/**
 * @file library/library.php
 * @author Ben Shealy
 *
 * Get albums in the music library, or update
 * the music library.
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("functions.php");

/**
 * Get a section of the album library.
 *
 * @param mysqli
 * @param rotationID
 * @param general_genreID
 * @param page
 * @return array of albums
 */
function get_library($mysqli, $rotationID, $general_genreID, $page)
{
	$page_size = 200;
	$keys = array(
		"al.albumID",
		"al.album_code",
		"al.album_name",
		"al.general_genreID",
		"al.rotationID",
		"ar.artist_name",
		"UNIX_TIMESTAMP(r.review_date) * 1000 AS review_date",
		"u.preferred_name AS reviewer"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `libalbum` AS al "
		. "INNER JOIN `libartist` AS ar ON al.artistID=ar.artistID "
		. "LEFT OUTER JOIN `libreview` AS r ON r.albumID=al.albumID "
		. "LEFT OUTER JOIN `users` AS u ON r.username=u.username "
		. "WHERE al.rotationID='$rotationID' "
		. (isset($general_genreID)
			? "AND al.general_genreID = '$general_genreID' "
			: "")
		. "ORDER BY al.albumID DESC "
		. "LIMIT "  . ($page * $page_size) . ", $page_size;";
	$result = exec_query($mysqli, $q);

	return fetch_array($result);
}

/**
 * Move albums to the next rotation slot.
 *
 * Albums in TBR, Checked out, Optional, and Jazz are not moved.
 *
 * @param mysqli
 * @param albums
 */
function move_rotation($mysqli, $albums)
{
	// get rotationID of first album
	// (assume the other albums have the same rotationID)
	$albumID = $albums[0]["albumID"];

	$q = "SELECT rotationID FROM `libalbum`"
		. "WHERE albumID = '$albumID';";
	$assoc = exec_query($mysqli, $q)->fetch_assoc();
	$src = $assoc["rotationID"];

	// move each album to next rotation slot
	$rotationMap = array(
		"0" => "0",
		"1" => "1",
		"2" => "3",
		"3" => "4",
		"4" => "5",
		"5" => "6",
		"6" => "7",
		"7" => "7",
		"8" => "8"
	);
	$dst = $rotationMap[$src];

	foreach ( $albums as $a ) {
		$q = "UPDATE `libalbum` SET rotationID = '$dst' "
			. " WHERE albumID = '$a[albumID]';";
		exec_query($mysqli, $q);
	}
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$rotationID = $_GET["rotationID"];
	$general_genreID = array_access($_GET, "general_genreID");
	$term = array_access($_GET, "query");
	$page = array_access($_GET, "page");

	if ( is_numeric($rotationID)
			&& strlen($term) >= 3
			&& is_numeric($page) ) {
		$albums = search_albums($mysqli, $rotationID, $term, $page);
	}
	else if ( is_numeric($rotationID)
			&& (!isset($general_genreID) || is_numeric($general_genreID))
			&& is_numeric($page) ) {
		$albums = get_library($mysqli, $rotationID, $general_genreID, $page);
	}
	else {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($albums));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_music_director($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$albums = json_decode(file_get_contents("php://input"), true);
	$albums = escape_json($mysqli, $albums);

	// validate albums
	foreach ( $albums as $a ) {
		if ( !is_numeric($a["albumID"]) ) {
			header("HTTP/1.1 404 Not Found");
			exit;
		}
	}

	move_rotation($mysqli, $albums);
	$mysqli->close();

	exit;
}
?>
