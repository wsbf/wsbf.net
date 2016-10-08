<?php

/**
 * @file logbook/functions.php
 * @author Ben Shealy
 */

/**
 * Get the current show.
 *
 * @param mysqli  MySQL connection
 * @return show ID, or null if there is no current show
 */
function get_current_show_id($mysqli)
{
	$q = "SELECT MAX(showID) AS showID FROM `show` "
		. "WHERE end_time IS NULL;";
	$result = exec_query($mysqli, $q);

	if ( $result->num_rows > 0 ) {
		$show = $result->fetch_assoc();
		return $show["showID"];
	}
	else {
		return null;
	}
}

/**
 * Log a track in the logbook.
 *
 * @param mysqli
 * @param showID
 * @param track
 */
function log_track($mysqli, $showID, $track)
{
	// log track
	$q = "INSERT INTO `logbook` SET "
		. "showID = '$showID', "
		. "lb_album_code = '$track[lb_album_code]', "
		. "lb_rotation = '$track[lb_rotation]', "
		. "lb_disc_num = '$track[lb_disc_num]', "
		. "lb_track_num = '$track[lb_track_num]', "
		. "lb_track_name = '$track[lb_track_name]', "
		. "lb_artist = '$track[lb_artist]', "
		. "lb_album = '$track[lb_album]', "
		. "lb_label = '$track[lb_label]', "
		. "played = 1;";
	exec_query($mysqli, $q);

	// update now playing
	$q = "UPDATE `now_playing` SET "
		. "logbookID = LAST_INSERT_ID(), "
		. "lb_track_name = '$track[track_name]', "
		. "lb_artist_name = '$track[artist_name]';";
	exec_query($mysqli, $q);

	// TODO: send RDS
}

?>
