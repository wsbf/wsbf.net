<?php

/**
 * @file auth/auth.php
 * @author Ben Shealy
 *
 * Authentication and authorization functions for
 * users, senior staff, and other priviledges. this
 * script should be used by every script that has
 * restricted access.
 */
define('AUTOMATION_SCHEDULE_ID', 0);

/**
 * Get the status of the current user.
 *
 * @param mysqli
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
 * @param mysqli
 * @return position ID, or false if user has no position
 */
function get_position($mysqli)
{
	/* save position to session for later requests */
	if ( !isset($_SESSION["positionID"]) ) {
		$q = "SELECT positionID FROM `staff` "
			. "WHERE username='$_SESSION[username]' "
			. "AND end_date IS NULL;";
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
 * Authenticate the user to access the logbook.
 * Only the computer engineer and the machines in
 * Studio A can access the logbook.
 *
 * @param mysqli
 */
function authenticate_logbook($mysqli)
{
	session_start();

	// computer engineer always has access
	if ( get_position($mysqli) == "8" || get_position($mysqli) == "13" ) {
		return;
	}

	// otherwise, only Studio A machines have access
	$valid_addr = array(
		"130.127.17.5",
		"130.127.17.230"
	);

	if ( !in_array($_SERVER["REMOTE_ADDR"], $valid_addr) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}
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
 * @param mysqli
 * @return true if current user has one of the above statuses,
 *         false otherwise
 */
function auth_reviewer($mysqli)
{
	return in_array(get_status($mysqli), array(0, 1, 2, 4, 5));
}

/**
 * Check whether the current user is a member.
 *
 * The following user statuses are included:
 *  0: Active
 *  1: Semi-Active
 *  2: Inactive
 *  4: Alumni
 *
 * @param mysqli
 * @return true if current user has one of the above statuses,
 *         false otherwise
 */
function auth_member($mysqli)
{
	return in_array(get_status($mysqli), array(0, 1, 2, 4));
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
 * @param mysqli
 * @return true if current user holds one of the above staff
 *         positions, false otherwise
 */
function auth_senior_staff($mysqli)
{
	return in_array(get_position($mysqli), array(0, 1, 2, 3, 4, 5, 6, 7, 8, 13));
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
 * @param mysqli
 * @return true if current user holds one of the above staff
 *         positions, false otherwise
 */
function auth_music_director($mysqli)
{
	return in_array(get_position($mysqli),
			array(0, 1, 2, 3, 8, 13, 14, 17, 18, 19, 20));
}

authenticate();

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$mysqli = construct_connection();

    if (isset($_SESSION["username"])) {
        echo json_encode(["username" => $_SESSION["username"]]);
    } else {
        echo json_encode(["username" => null]);
    }

    $mysqli->close();  // Close connection

    header("Content-Type: application/json");  // Set the content type
    exit;
}
?>
