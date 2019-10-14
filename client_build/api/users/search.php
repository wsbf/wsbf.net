<?php

/**
 * @file users/search.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Search for users by name.
 *
 * @param mysqli
 * @param term
 * @return array of matching users
 */
function search_users($mysqli, $term)
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
	$result = exec_query($mysqli, $q);

	return fetch_array($result);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !auth_member($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$term = $mysqli->escape_string($_GET["term"]);

	if ( strlen($term) < 3 ) {
		header("HTTP/1.1 404 Not Found");
		exit("Search term must be at least 3 characters long.");
	}

	$users = search_users($mysqli, $term);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($users));
}
?>
