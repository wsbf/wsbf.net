<?php

/**
 * @file showsub/request_list.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get the list of outstanding sub requests.
 *
 * @param mysqli
 * @return array of sub requests
 */
function get_sub_requests($mysqli)
{
	$keys = array(
		"r.sub_requestID",
		"r.username",
		"ur.preferred_name AS requested_by",
		"sc.show_name",
		"t.type AS show_type",
		"d.day",
		"sc.show_timeID",
		"r.date",
		"r.reason",
		"uf.preferred_name AS filled_by"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `sub_request` AS r "
		. "INNER JOIN `users` AS ur ON ur.username=r.username "
		. "INNER JOIN `schedule` AS sc ON sc.scheduleID=r.scheduleID "
		. "INNER JOIN `def_days` AS d ON d.dayID=sc.dayID "
		. "INNER JOIN `def_show_types` AS t ON t.show_typeID=sc.show_typeID "
		. "LEFT OUTER JOIN `sub_fill` AS f ON f.sub_requestID=r.sub_requestID "
		. "LEFT OUTER JOIN `users` AS uf ON uf.username=f.username "
		. "WHERE CURRENT_DATE() <= r.date "
		. "ORDER BY r.date;";
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

	$requests = get_sub_requests($mysqli);

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($requests));
}
?>
