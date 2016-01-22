<?php

/**
 * @file hosts.php
 * @author Ben Shealy
 *
 * @section DESCRIPTION
 *
 * Search for users who are eligible to host shows.
 */
require_once("../auth.php");
require_once("../connect.php");

/**
 * Get a list of valid show hosts.
 * 
 * @param mysqli  MySQL connection
 * @param term    search term
 * @return array of matching users
 */
function get_hosts($mysqli, $term)
{
	$keys = array(
		"u.username",
		"u.first_name",
		"u.last_name",
		"u.preferred_name"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `users` AS u "
		. "WHERE u.first_name LIKE '%$term%' "
		. "OR u.last_name LIKE '%$term%' "
		. "OR u.preferred_name LIKE '%$term%';";
	$result = $mysqli->query($q);

	$hosts = array();
	while ( ($h = $result->fetch_assoc()) ) {
		if ( $h["preferred_name"] == "$h[first_name] $h[last_name]" ) {
			$name = $h["preferred_name"];
		}
		else {
			$name = "$h[first_name] $h[last_name] [$h[preferred_name]]";
		}

		$hosts[] = array(
			"username" => $h["username"],
			"name" => $name
		);
	}

	return $hosts;
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	$term = $mysqli->escape_string($_GET["term"]);

	if ( strlen($term) < 2 ) {
		header("HTTP/1.1 404 Not Found");
		exit("Search term must be at least 2 characters long.");
	}

	$hosts = get_hosts($mysqli, $term);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($hosts));
}
?>
