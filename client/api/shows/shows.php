<?php

/**
 * @file shows/shows.php
 * @author Ben Shealy
 */
require_once("../connect.php");
require_once("functions.php");

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	$page = $_GET["page"];
	$term = $mysqli->escape_string($_GET["term"]);

	if ( is_numeric($page) && $page >= 0 ) {
		$shows = get_shows($mysqli, $page, 50, true);
	}
	else if ( strlen($term) >= 3 ) {
		$shows = search_shows($mysqli, $term);
	}
	else {
		header("HTTP/1.1 404 Not Found");
		exit("Invalid input.");
	}

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($shows));
}
?>
