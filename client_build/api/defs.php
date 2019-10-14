<?php

/**
 * @file defs.php
 * @author Ben Shealy
 *
 * Get a definitions table.
 */
require_once("connect.php");

/**
 * Get a definitions table.
 *
 * @param mysqli
 * @param suffix
 * @return array of defs table
 */
function get_def_table($mysqli, $suffix)
{
	$q = "SELECT * FROM `def_$suffix` ORDER BY 1;";

	$result = exec_query($mysqli, $q);

	$table = array();
	while ( ($r = $result->fetch_assoc()) ) {
		$table[] = $r;
	}

	return $table;
}

$suffixes = array(
	"airability",
	"cart_type",
	"days",
	"fishbowl_log_types",
	"general_genres",
	"positions",
	"rotations",
	"show_times",
	"show_types",
	"status",
	"teams"
);

$mysqli = construct_connection();

$suffix = $mysqli->escape_string($_GET["table"]);

if ( !in_array($suffix, $suffixes) ) {
	header("HTTP/1.1 404 Not Found");
	exit("Table suffix is empty or invalid.");
}

$table = get_def_table($mysqli, $suffix);
$mysqli->close();

header("Content-Type: application/json");
exit(json_encode($table));
?>
