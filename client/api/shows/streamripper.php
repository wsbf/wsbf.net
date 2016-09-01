<?php

/**
 * @file shows/streamripper.php
 *
 * This page is used by streamripper, which runs on "john" (130.127.17.39).
 * Streamripper is configured to pull this metadata with the script 
 * streamripper_start_on_boot.sh, which requests this every 10 seconds.
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
 * @return current show ID
 */
function get_current_show($mysqli)
{
	// get show
	$q = "SELECT showID FROM `show` "
		. "ORDER BY showID DESC "
		. "LIMIT 1;";
	$show = $mysqli->query($q)->fetch_assoc();

	return $show["showID"];
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	$showID = get_current_show($mysqli);

	exit("TITLE=$showID\n"
		. "ARTIST=WSBF\n"
		. ".\n");
}
?>
