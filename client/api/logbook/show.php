<?php

/**
 * @file logbook/show.php
 * @author Ben Shealy
 *
 * Get the current show, start a new show, or end the current show.
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("auth.php");
require_once("functions.php");

/**
 * Get a show.
 *
 * @param mysqli  MySQL connection
 * @return associative array of show
 */
function get_show($mysqli, $showID)
{
	// get show
	$keys = array(
		"sh.showID",
		"sh.scheduleID",
		"UNIX_TIMESTAMP(sh.start_time) * 1000 AS start_time",
		"UNIX_TIMESTAMP(sh.end_time) * 1000 AS end_time",
		"sc.show_name",
		"t.type"
	);

	$q = "SELECT " . implode(",", $keys) . " "
			. "FROM `show` AS sh "
			. "LEFT OUTER JOIN `schedule` AS sc ON sh.scheduleID=sc.scheduleID "
			. "LEFT OUTER JOIN `def_show_types` AS t ON sc.show_typeID=t.show_typeID "
			. "WHERE sh.showID = '$showID';";
	$show = exec_query($mysqli, $q)->fetch_assoc();

	// get show hosts
	$q = "SELECT u.preferred_name FROM `show_hosts` AS h "
		. "INNER JOIN `users` AS u ON u.username=h.username "
		. "WHERE h.showID='$show[showID]';";
	$result_hosts = exec_query($mysqli, $q);

	$show["hosts"] = array();
	while ( ($h = $result_hosts->fetch_assoc()) ) {
		$show["hosts"][] = $h["preferred_name"];
	}

	// get show playlist
	$keys_playlist = array(
		"l.lb_album_code",
		"l.lb_disc_num",
		"l.lb_rotation",
		"l.lb_track_num",
		"l.lb_track_name",
		"l.lb_artist",
		"l.lb_album",
		"l.lb_label",
		"1 AS logged"
	);

	$q = "SELECT " . implode(",", $keys_playlist) . " FROM `logbook` AS l "
		. "WHERE l.showID = '$show[showID]' "
		. "ORDER BY l.time_played DESC;";
	$result_playlist = exec_query($mysqli, $q);

	$show["playlist"] = array();
	while ( ($t = $result_playlist->fetch_assoc()) ) {
		$show["playlist"][] = $t;
	}

	return $show;
}

/**
 * Validate a schedule ID.
 *
 * @param mysqli      MySQL connection
 * @param scheduleID  schedule ID
 * @param true if schedule ID is valid, false otherwise
 */
function validate_show($mysqli, $scheduleID)
{
	if ( !is_numeric($scheduleID) ) {
		return false;
	}

	// show must belong to the current user
	$q = "SELECT h.username FROM `schedule_hosts` AS h "
		. "WHERE h.scheduleID = '$scheduleID' "
		. "AND h.username = '$_SESSION[username]';";
	$result = exec_query($mysqli, $q);

	if ( $result->num_rows == 0 ) {
		return false;
	}

	return true;
}

/**
 * Start a new show with a given schedule ID.
 *
 * @param mysqli      MySQL connection
 * @param scheduleID  schedule ID
 * @param new show ID
 */
function sign_on($mysqli, $scheduleID)
{
	// get show hosts from schedule
	$q = "SELECT username FROM `schedule_hosts` "
		. "WHERE scheduleID = '$scheduleID';";
	$result = exec_query($mysqli, $q);

	// insert show
	$q = "INSERT INTO `show` SET "
		. "scheduleID = '$scheduleID';";
	exec_query($mysqli, $q);

	$showID = $mysqli->insert_id;

	// insert show hosts
	while ( ($h = $result->fetch_assoc()) ) {
		$q = "INSERT INTO `show_hosts` SET "
			. "showID = '$showID', "
			. "username = '$h[username]';";
		exec_query($mysqli, $q);
	}

	return $showID;
}

/**
 * End the current show.
 *
 * @param mysqli  MySQL connection
 */
function sign_off($mysqli)
{
	$showID = get_current_show_id($mysqli);

	$q = "UPDATE `show` SET end_time = NOW() "
		. "WHERE showID = '$showID' AND end_time IS NULL;";
	exec_query($mysqli, $q);
}

authenticate();
authenticate_logbook();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	$showID = get_current_show_id($mysqli);

	if ( $showID == null ) {
		$show = array();
	}
	else {
		$show = get_show($mysqli, $showID);
	}

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($show));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	$scheduleID = $_GET["scheduleID"];

	if ( !validate_show($mysqli, $scheduleID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	// sign off the current show
	$showID = get_current_show_id($mysqli);
	if ( $showID ) {
		sign_off($mysqli);
	}

	$showID = sign_on($mysqli, $scheduleID);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($showID));
}
else if ( $_SERVER["REQUEST_METHOD"] == "DELETE" ) {
	$mysqli = construct_connection();

	sign_off($mysqli);
	$mysqli->close();

	exit;
}
?>
