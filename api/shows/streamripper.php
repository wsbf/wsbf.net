<?php

/**
 * @file shows/streamripper.php
 *
 * This page is used by streamripper, which runs on "john" (130.127.17.39).
 * Streamripper is configured to pull this metadata with the script 
 * /home/compe/fetch_external_metadata.pl, which requests this 10 seconds. 
 * 
 * Streamripper uses the result from this to split and name the tracks.
 * The default behavior for streamripper is to name the track as <TITLE> - <ARTIST>.mp3.
 * We use the -D flag to set the name to only the title (-D %T). However, it appears that
 * streamripper uses the default configuration (name,artist).mp3 in the incomplete directory,
 * and then renames it as specified by the argument once the track is changed (i.e. new show).
 * 
 * [dcohen @ 2015-02-05] 
 */    
require_once("../connect.php");

/**
 * Get the current show.
 *
 * @param mysqli  MySQL connection
 * @return associative array of current show
 */
function get_current_show($mysqli)
{
	// get show
	$q = "SELECT showID FROM `show` "
		. "ORDER BY showID DESC "
		. "LIMIT 1;";
	$show = $mysqli->query($q)->fetch_assoc();

	// get show hosts
	$q = "SELECT u.preferred_name FROM `show_hosts` AS h "
		. "INNER JOIN `users` AS u ON u.username=h.username "
		. "WHERE h.showID = '$show[showID]' "
		. "ORDER BY u.preferred_name ASC;";
	$result_hosts = $mysqli->query($q);

	$show["hosts"] = array();
	while ( ($h = $result_hosts->fetch_assoc()) ) {
		$show["hosts"][] = $h["preferred_name"];
	}

	return $show;
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	$show = get_current_show($mysqli);

	exit("title=\"$show[showID]\" artist=\"" . implode(", ", $show["hosts"]) . "\"");
}
?>
