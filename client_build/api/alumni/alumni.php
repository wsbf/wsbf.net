<?php

/**
 * @file alumni/alumni.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get the list of alumni.
 *
 * @param mysqli
 * @return array of alumni
 */
function get_alumni($mysqli)
{
	$keys = array(
		"alumniID",
		"name",
		"story",
		"current_location",
		"positions_held",
		"years_active"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `alumni`;";
	$result = exec_query($mysqli, $q);

	return fetch_array($result);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	$alumni = get_alumni($mysqli);

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($alumni));
}
?>
