<?php

/**
 * @file shows/functions.php
 * @author Ben Shealy
 */

/**
 * Get a list of recorded shows.
 *
 * @param mysqli      MySQL connection
 * @param page        page offset
 * @param page_size   number of shows
 * @param automation  whether to include automation
 * @return array of shows
 */
function get_shows($mysqli, $page, $page_size, $automation)
{
	/* get shows */
	$keys = array(
		"s.showID",
		"s.show_name",
		"s.show_typeID",
		"UNIX_TIMESTAMP(s.start_time) * 1000 AS start_time",
		"UNIX_TIMESTAMP(s.end_time) * 1000 AS end_time"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `show` AS s "
		. ($automation ? "" : "WHERE s.show_typeID != 8 ")
		. "ORDER BY s.showID DESC "
		. "LIMIT "  . ($page * $page_size) . ", $page_size;";
	$result = $mysqli->query($q);

	$shows = array();
	while ( ($s = $result->fetch_assoc()) ) {
		/* get show hosts for each show */
		$q = "SELECT u.preferred_name FROM `show_hosts` AS h "
			. "INNER JOIN `users` AS u ON u.username=h.username "
			. "WHERE h.showID='$s[showID]';";
		$result_hosts = $mysqli->query($q);

		$s["show_hosts"] = array();
		while ( ($h = $result_hosts->fetch_assoc()) ) {
			$s["show_hosts"][] = $h["preferred_name"];
		}

		$shows[] = $s;
	}

	return $shows;
}

/**
 * Search shows by DJ name.
 *
 * Currently this function searches for an exact match
 * with `users`.preferred_name, but this function could
 * be expanded in the future to perform more sophisticated
 * queries.
 *
 * @param mysqli  MySQL connection
 * @param name    DJ name
 * @return array of shows
 */
function search_shows($mysqli, $name)
{
	/* get username */
	$q = "SELECT username FROM `users` WHERE preferred_name='$name';";
	$result = $mysqli->query($q);

	if ( $result->num_rows == 0 ) {
		return array();
	}

	$user = $result->fetch_assoc();
	$username = $user["username"];

	/* get shows */
	$keys = array(
		"s.showID",
		"s.show_name",
		"s.show_typeID",
		"UNIX_TIMESTAMP(s.start_time) * 1000 AS start_time",
		"UNIX_TIMESTAMP(s.end_time) * 1000 AS end_time"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `show` AS s "
		. "INNER JOIN `show_hosts` AS h ON h.showID=s.showID "
		. "WHERE h.username='$username' "
		. "ORDER BY s.showID DESC;";
	$result = $mysqli->query($q);

	$shows = array();
	while ( ($s = $result->fetch_assoc()) ) {
		/* get show hosts for each show */
		$q = "SELECT u.preferred_name FROM `show_hosts` AS h "
			. "INNER JOIN `users` AS u ON u.username=h.username "
			. "WHERE h.showID='$s[showID]';";
		$result_hosts = $mysqli->query($q);

		$s["show_hosts"] = array();
		while ( ($h = $result_hosts->fetch_assoc()) ) {
			$s["show_hosts"][] = $h["preferred_name"];
		}

		$shows[] = $s;
	}

	return $shows;
}
?>
