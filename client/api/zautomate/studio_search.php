<?php

/**
 * @file zautomate/studio_search.php
 * @author Ben Shealy
 *
 * TODO: some queries yield empty response: "sta", "star", "start"
 *       might be caused by character encoding/collation
 */
require_once("../connect.php");

/**
 * Search the cart library.
 *
 * @param mysqli
 * @param term
 * @return array of matching carts
 */
function search_carts($mysqli, $term)
{
	$keys = array(
		"c.cartID",
		"c.title",
		"c.issuer",
		"c.filename",
		"t.type"
	);

	$q = "SELECT " . implode(", ", $keys) . " FROM `libcart` AS c "
		. "INNER JOIN `def_cart_type` AS t ON c.cart_typeID=t.cart_typeID "
		. "WHERE c.start_date < NOW() "
		. "AND (NOW() < c.end_date OR c.end_date IS NULL) "
		. "AND (t.type LIKE '%$term%' "
		. "OR c.title LIKE '%$term%' "
		. "OR c.issuer LIKE '%$term%');";
	$result = exec_query($mysqli, $q);

	return fetch_array($result);
}

/**
 * Search the music library.
 *
 * @param mysqli
 * @param term
 * @return array of matching tracks
 */
function search_tracks($mysqli, $term)
{
	$keys = array(
		"t.albumID",
		"al.album_code",
		"t.disc_num",
		"t.track_num",
		"r.binAbbr AS rotation",
		"t.track_name",
		"t.file_name",
		"ar.artist_name",
		"al.album_name"
	);

	$q = "SELECT " . implode(", ", $keys) . " FROM `libtrack` AS t "
		. "INNER JOIN `libartist` AS ar ON t.artistID=ar.artistID "
		. "INNER JOIN `libalbum` AS al ON t.albumID=al.albumID "
		. "INNER JOIN `def_rotations` AS r ON r.rotationID=al.rotationID "
		. "WHERE t.airabilityID != 2 AND t.file_name != '' "
		. "AND ("
			. "t.track_name LIKE '%$term%' "
			. "OR ar.artist_name LIKE '%$term%' "
			. "OR al.album_name LIKE '%$term%' "
		. ");";
	$result = exec_query($mysqli, $q);

	$tracks = array();
	while ( ($t = $result->fetch_assoc()) ) {
		// temporary hack to URL decode and add directory slashes
		$f = urldecode($t["file_name"]);
		$f = "$f[0]/$f[1]/" . substr($f, 2);
		$t["file_name"] = $f;

		$tracks[] = $t;
	}

	return $tracks;
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	$term = $mysqli->escape_string($_GET["query"]);

	if ( strlen($term) < 3 ) {
		header("HTTP/1.1 404 Not Found");
		exit("Search term must be at least 3 characters long.");
	}

	$results = array(
		"carts" => search_carts($mysqli, $term),
		"tracks" => search_tracks($mysqli, $term)
	);

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($results));
}
?>
