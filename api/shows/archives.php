<?php
require_once("../auth.php");
require_once("../connect.php");

define('BASE_PATH', "/wizbif/ZAutoLib/archives/");

// TODO: try to merge with /api/shows/shows.php
// move common functions into third file:
// - get list of shows
// - map list of shows into list of filenames

/**
 * Get a list of show archives.
 *
 * @param mysqli     MySQL connection
 * @param page       page count from most recent
 * @param page_size  number of shows
 * @return array of show archives
 */
function get_archives($mysqli, $page, $page_size)
{
	/* get shows */
	$keys = array(
		"s.showID",
		"s.show_name",
		"s.show_typeID",
		"UNIX_TIMESTAMP(s.start_time) * 1000 AS start_time",
		"UNIX_TIMESTAMP(s.end_time) * 1000 AS end_time"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `show` AS s "
		. "WHERE s.show_typeID != 8 "
		. "ORDER BY s.showID DESC "
		. "LIMIT " . ($page * $page_size) . ", $page_size;";
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

		/* get file names for each show */
		$s["files"] = array();

		$count = 0;
		while ( true ) {
			$append = $count > 0 ? " ($count)" : "";
			$filename = BASE_PATH . "$s[showID]$append.mp3";

			if ( file_exists($_SERVER["DOCUMENT_ROOT"] . $filename) ) {
				$s["files"][] = $filename;
			}
			else if ( $count > 0 ) {
				break;
			}
			$count++;
		}

		$shows[] = $s;
	}

	return $shows;
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to view archives.");
	}

	$page = $_GET["page"];

	if ( !is_numeric($page) || $page < 0 ) {
		header("HTTP/1.1 404 Not Found");
		exit("Page number is empty or invalid.");
	}

	$archives = get_archives($mysqli, $page, 50);

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($archives));
}
?>
