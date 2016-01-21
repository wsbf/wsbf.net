<?php
require_once("../connect.php");

/**
 * Get a list of recorded shows.
 *
 * @param mysqli     MySQL connection
 * @param page       page count from most recent
 * @param page_size  number of shows
 * @return array of shows
 */
function get_shows($mysqli, $page, $page_size)
{
	/* get shows */
	$keys = array(
		"s.showID",
		"s.show_name",
		"UNIX_TIMESTAMP(s.start_time) * 1000 AS start_time",
		"UNIX_TIMESTAMP(s.end_time) * 1000 AS end_time"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `show` AS s "
		. "ORDER BY s.showID DESC "
		. "LIMIT "  . ($page * $page_size) . ", $page_size;";
	$result = $mysqli->query($q);

	$shows = array();
	while ( ($s = $result->fetch_assoc()) ) {
		/* get show hosts for each show */
		$q = "SELECT u.preferred_name FROM `show_hosts` AS h "
			. "INNER JOIN `users` AS u ON u.username=h.username "
			. "WHERE h.showID=$s[showID];";
		$result_hosts = $mysqli->query($q);

		$s["show_hosts"] = array();
		while ( ($h = $result_hosts->fetch_assoc()) ) {
			$s["show_hosts"][] = $h["preferred_name"];
		}

		$shows[] = $s;
	}

	return $shows;
}

$page = $_GET["page"];

if ( !is_numeric($page) || $page < 0 ) {
	header("HTTP/1.1 404 Not Found");
	exit("Page number is empty or invalid.");
}

$mysqli = construct_connection();
$shows = get_shows($mysqli, $page, 50);
$mysqli->close();

header("Content-Type: application/json");
exit(json_encode($shows));
?>
