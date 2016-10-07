<?php

/**
 * @file auth.php
 * @author Ben Shealy
 *
 * Authentication and authorization functions for
 * users, senior staff, and other priviledges. this
 * script should be used by every script that has
 * restricted access.
 */

/**
 * Authenticate the current session by checking whether the
 * "username" property is set. Exits on failure.
 */
function authenticate()
{
	session_start();

	if ( !isset($_SESSION["username"]) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}
}

/**
 * Get the status of the current user.
 *
 * @param mysqli  MySQL connection
 * @return status ID
 */
function get_status($mysqli)
{
	/* save status to session for later requests */
	if ( !isset($_SESSION["statusID"]) ) {
		$q = "SELECT statusID FROM `users` "
			. "WHERE username='$_SESSION[username]';";
		$result = exec_query($mysqli, $q)->fetch_assoc();

		$_SESSION["statusID"] = $result["statusID"];
	}

	return $_SESSION["statusID"];
}

/**
 * Get the staff position of the current user.
 *
 * @param mysqli  MySQL connection
 * @return position ID, or false if user has no position
 */
function get_position($mysqli)
{
	/* save position to session for later requests */
	if ( !isset($_SESSION["positionID"]) ) {
		$q = "SELECT positionID FROM `staff` "
			. "WHERE username='$_SESSION[username]' "
			. "AND NOW() BETWEEN start_date AND end_date;";
		$result = exec_query($mysqli, $q);

		if ( $result->num_rows > 0 ) {
			$assoc = $result->fetch_assoc();
			$_SESSION["positionID"] = $assoc["positionID"];
		}
		else {
			$_SESSION["positionID"] = false;
		}
	}

	return $_SESSION["positionID"];
}

/**
 * Check whether the current user can edit their profile.
 *
 * The following user statuses are included:
 *  0: Active
 *  1: Semi-Active
 *  2: Inactive
 *  4: Alumni
 *
 * @param mysqli  MySQL connection
 * @return true if current user has one of the above statuses,
 *         false otherwise
 */
function check_edit_profile($mysqli)
{
	return in_array(get_status($mysqli), array(0, 1, 2, 4));
}

/**
 * Check whether the current user can review albums.
 *
 * The following user statuses are included:
 *  0: Active
 *  1: Semi-Active
 *  2: Inactive
 *  4: Alumni
 *  5: Intern
 *
 * @param mysqli  MySQL connection
 * @return true if current user has one of the above statuses,
 *         false otherwise
 */
function check_reviewer($mysqli)
{
	return in_array(get_status($mysqli), array(0, 1, 2, 4, 5));
}

/**
 * Check whether the current user is on senior staff.
 *
 * The following staff positions are included:
 *   0: General Manager
 *   1: Chief Engineer
 *   2: Chief Announcer
 *   3: Music Director
 *   4: Events Coordinator
 *   5: Promotions Director
 *   6: Production Director
 *   7: Member-At-Large
 *   8: Computer Engineer
 *
 * @param mysqli  MySQL connection
 * @return true if current user holds one of the above staff
 *         positions, false otherwise
 */
function check_senior_staff($mysqli)
{
	return in_array(get_position($mysqli), array(0, 1, 2, 3, 4, 5, 6, 7, 8));
}

/**
 * Check whether the current user has music director priviledges.
 *
 * The following staff positions are included:
 *   0: General Manager
 *   1: Chief Engineer
 *   2: Chief Announcer
 *   3: Music Director
 *   8: Computer Engineer
 *  13: Loud Rock Director
 *  14: RPM Director
 *  17: Jazz Director
 *  18: Triple-A Director
 *  19: Hip-Hop Director
 *  20: New World Director
 *
 * @param mysqli  MySQL connection
 * @return true if current user holds one of the above staff
 *         positions, false otherwise
 */
function check_music_director($mysqli)
{
	return in_array(get_position($mysqli),
			array(0, 1, 2, 3, 8, 13, 14, 17, 18, 19, 20));
}

/**
 * Check whether the current user has engineer priviledges.
 *
 * The following staff positions are included:
 *   1: Chief Engineer
 *   5: Promotions Director
 *   6: Production Director
 *   8: Computer Engineer
 *  10: Equipment Engineer
 *
 * @param mysqli  MySQL connection
 * @return true if current user holds one of the above staff
 *         positions, false otherwise
 */
function check_engineer($mysqli)
{
	return in_array(get_position($mysqli), array(1, 5, 6, 8, 10));
}
?>
