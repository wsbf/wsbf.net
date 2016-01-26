<?php

/**
 * @file shows/shows.php
 * @author Ben Shealy
 */
require_once("functions.php");

$page = $_GET["page"];

if ( !is_numeric($page) || $page < 0 ) {
	header("HTTP/1.1 404 Not Found");
	exit("Page number is empty or invalid.");
}

$mysqli = construct_connection();
$shows = get_shows($mysqli, $page, 50, true);
$mysqli->close();

header("Content-Type: application/json");
exit(json_encode($shows));
?>
