<?php
require_once("../connect.php");

/**
 * Get the most played albums in a time period. Includes
 * albums currently in rotation that weren't played by
 * Automation.
 *
 * @param mysqli  MySQL connection
 * @param date1   start timestamp
 * @param date2   end timestamp
 * @return array of top albums
 */
function get_top_albums($mysqli, $date1, $date2)
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
		. "WHERE '$date1' < UNIX_TIMESTAMP(s.start_time) "
		. "AND UNIX_TIMESTAMP(s.end_time) < '$date2' "
		. "AND s.show_typeID != 8 "
		. "AND l.lb_rotation IN ('N','H','M','L') "
		. "GROUP BY l.lb_album_code "
		. "ORDER BY count DESC "
		. "LIMIT 100;";
	$result = $mysqli->query($q);

	$albums = array();
	while ( ($a = $result->fetch_assoc()) ) {
		$albums[] = $a;
	}

	return $albums;
}

$date1 = $_GET["date1"];
$date2 = $_GET["date2"];

if ( !is_numeric($date1) || !is_numeric($date2) ) {
	header("HTTP/1.1 404 Not Found");
	exit("Start and end dates are empty or invalid.");
}

// remove millisecond component used in Javascript
$date1 = $date1 / 1000;
$date2 = $date2 / 1000;

$mysqli = construct_connection();
$albums = get_top_albums($mysqli, $date1, $date2);
$mysqli->close();

header("Content-Type: application/json");
exit(json_encode($albums));
?>
