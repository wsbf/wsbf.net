<?php

/**
 * @file showsub/request_history.php
 * @author jai agarwal
 * this is a separate file because it felt easier to do that
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get the list of all sub requests.
 *
 * @param mysqli
 * @return array of sub requests
 */
function get_all_sub_requests($mysqli, $page, $page_size)
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
		. "ORDER BY r.date DESC "		
        . "LIMIT "  . ($page * $page_size) . ", $page_size;";
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

    $page = $_GET["page"];

	if ( !is_numeric($page) && !($page >= 0) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Invalid input.");
	}

	$requests = get_all_sub_requests($mysqli, $page, 25);

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($requests));
}
?>
