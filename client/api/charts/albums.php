<?php

/**
 * @file charts/albums.php
 * @author Ben Shealy
 */
require_once("../connect.php");

/**
 * Get the most played albums in a time period. Includes
 * albums currently in rotation that weren't played by
 * Automation.
 *
 * @param mysqli
 * @param date1
 * @param date2
 * @param general_genreID
 * @return array of top albums
 */
function get_top_albums($mysqli, $date1, $date2, $general_genreID)
{
	$keys = array(
		"l.lb_album_code",
		"l.lb_album",
		"l.lb_artist",
		"l.lb_label",
		"COUNT(*) AS count"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `logbook` AS l "
		. "INNER JOIN `show` AS s ON l.showID=s.showID "
		. "LEFT OUTER JOIN `libalbum` AS a ON l.lb_album_code=a.album_code "
		. "WHERE '$date1' < UNIX_TIMESTAMP(s.start_time) "
		. "AND UNIX_TIMESTAMP(s.end_time) < '$date2' "
		. "AND s.show_typeID != 8 "
		. "AND l.lb_rotation IN ('N','H','M','L') "
		. (isset($general_genreID)
			? "AND a.general_genreID = '$general_genreID' "
			: "")
		. "GROUP BY l.lb_album_code "
		. "ORDER BY count DESC "
		. "LIMIT 100;";
	$result = exec_query($mysqli, $q);

	return fetch_array($result);
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$date1 = $_GET["date1"];
	$date2 = $_GET["date2"];
	$general_genreID = array_access($_GET, "general_genreID");

	if ( !is_numeric($date1)
	  || !is_numeric($date2)
	  || (isset($general_genreID) && !is_numeric($general_genreID)) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Parameters are empty or invalid.");
	}

	$mysqli = construct_connection();
	$albums = get_top_albums($mysqli, $date1, $date2, $general_genreID);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($albums));
}
?>
