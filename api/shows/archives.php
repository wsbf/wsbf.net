<?php

/**
 * @file shows/archives.php
 * @author Ben Shealy
 */
require_once("../auth.php");
require_once("../connect.php");
require_once("functions.php");

define('BASE_PATH', "/wizbif/ZAutoLib/archives/");

/**
 * Get the archive filenames for a show. The array
 * of filenames are added to the "files" key.
 *
 * @param show  associative array of show
 */
function get_filenames(&$show)
{
	$filenames = array();

	$count = 0;
	while ( true ) {
		$append = $count > 0 ? " ($count)" : "";
		$filename = BASE_PATH . "$show[showID]$append.mp3";

		if ( file_exists($_SERVER["DOCUMENT_ROOT"] . $filename) ) {
			$filenames[] = $filename;
		}
		else if ( $count > 0 ) {
			break;
		}
		$count++;
	}

	$show["files"] = $filenames;
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to view archives.");
	}

	$page = $_GET["page"];
	$term = $mysqli->escape_string($_GET["term"]);

	if ( is_numeric($page) && $page >= 0 ) {
		$shows = get_shows($mysqli, $page, 50, false);
	}
	else if ( strlen($term) >= 3 ) {
		$shows = search_shows($mysqli, $term);
	}
	else {
		header("HTTP/1.1 404 Not Found");
		exit("Invalid input.");
	}

	array_walk($shows, "get_filenames");

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($shows));
}
?>
