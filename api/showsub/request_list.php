<?php

/**
 * @file showsub/request_list.php
 * @author Ben Shealy
 */
require_once("../auth.php");
require_once("../connect.php");

/**
 * Get the list of outstanding sub requests.
 *
 * @param mysqli  MySQL connection
 * @return array of sub requests
 */
function get_sub_requests($mysqli)
{
	$keys = array(
		"r.sub_requestID",
		"r.username",
		"ur.preferred_name AS requested_by",
		"r.show_name",
		"t.type AS show_type",
		"d.day",
		"r.start_time",
		"r.end_time",
		"r.date",
		"r.reason",
		"uf.preferred_name AS filled_by"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `sub_request` AS r "
		. "INNER JOIN `users` AS ur ON ur.username=r.username "
		. "INNER JOIN `def_days` AS d ON d.dayID=r.dayID "
		. "INNER JOIN `def_show_types` AS t ON t.show_typeID=r.show_typeID "
		. "LEFT OUTER JOIN `sub_fill` AS f ON f.sub_requestID=r.sub_requestID "
		. "LEFT OUTER JOIN `users` AS uf ON uf.username=f.username "
		. "WHERE NOW() <= r.date "
		. "ORDER BY r.date;";
	$result = $mysqli->query($q);

	$requests = array();

	while ( ($r = $result->fetch_assoc()) ) {
		$requests[] = $r;
	}

	return $requests;
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to view sub requests.");
	}

	$requests = get_sub_requests($mysqli);

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($requests));
}
?>
