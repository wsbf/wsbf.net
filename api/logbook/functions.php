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
	$result = $mysqli->query($q);

	if ( $result->num_rows > 0 ) {
		$show = $result->fetch_assoc();
		return $show["showID"];
	}
	else {
		return null;
	}
}

?>
