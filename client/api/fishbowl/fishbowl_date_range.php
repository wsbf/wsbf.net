<?php

/**
 * @file fishbowl/fishbowl_date_range.php
 * @author Jai Agarwal
 *
 * update fishbowl semeser date range
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("config.php");

/**
 * Get the current date range for the semester
 *
 * @param mysqli
 */
function get_current_date_range($mysqli)
{
	$q = "SELECT date FROM `def_semester_dates` WHERE dateID=0";
	$result = fetch_array(exec_query($mysqli, $q));
	$start_date = $result[0];

	$q = "SELECT date FROM `def_semester_dates` WHERE dateID=1";
	$result = fetch_array(exec_query($mysqli, $q));
	$end_date = $result[0];

    return [$start_date, $end_date];
	
}

/**
 * Change the current fishbowl semester date range
 *
 * @param mysqli
 */
function update_semester_date_range($mysqli, $start_date_input, $end_date_input)
{
    $q = "UPDATE `def_semester_dates` "
        . "SET date = " . $start_date_input . " "
        . "WHERE dateID = 0;";
    exec_query($mysqli, $q);

    $q = "UPDATE `def_semester_dates` "
        . "SET date = " . $end_date_input . " "
        . "WHERE dateID = 1;";
    exec_query($mysqli, $q);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !auth_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

    list($start_date, $end_date) = get_current_date_range($mysqli);
	$mysqli->close();

    $date_range = [
        'start_date' => $start_date,
        'end_date' => $end_date
    ];

	header("Content-Type: application/json");
	exit(json_encode($date_range));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !auth_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

    // get the POST array
    $item = json_decode(file_get_contents("php://input"), true);
	$item = escape_json($mysqli, $item);
	
	error_log(print_r($item, true));  // Log the entire POST array to the error log

    $action = isset($item['action']) ? $item['action'] : null;
	$fishbowl_logID = $item['fishbowl_logID'];
	$dispute_description = isset($item['dispute_description']) ? $item['dispute_description'] : null;

	update_semester_date_range($mysqli, $start_date_input, $end_date_input);
	$mysqli->close();

	exit;
}
?>
