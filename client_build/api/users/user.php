<?php

/**
 * @file user.php
 * @author Ben Shealy
 *
 * Get or update the current user.
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get the current user.
 *
 * @param mysqli
 * @param username
 * @return associative array of current user
 */
function get_user($mysqli, $username)
{
	// get user object
	$keys = array(
		"u.username",
		"u.first_name",
		"u.last_name",
		"u.preferred_name",
		"u.email_addr",
		"u.teamID",
		"u.statusID",
		"u.profile_paragraph",
		"u.has_picture",
		"s.positionID"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `users` AS u "
		. "LEFT OUTER JOIN `staff` AS s ON s.username=u.username "
		. "AND s.end_date IS NULL "
		. "WHERE u.username='$username';";
	$user = exec_query($mysqli, $q)->fetch_assoc();

	// get array of active shows
	$show_keys = array(
		"s.scheduleID",
		"s.dayID",
		"s.show_timeID",
		"t.type AS show_type",
		"s.show_name",
		"s.description",
		"s.general_genreID",
		"h.schedule_alias"
	);

	$q = "SELECT " . implode(",", $show_keys) . " FROM `schedule` AS s "
		. "INNER JOIN `def_show_types` AS t ON t.show_typeID=s.show_typeID "
		. "LEFT OUTER JOIN `schedule_hosts` AS h ON s.scheduleID=h.scheduleID "
		. "WHERE h.username='$username' AND s.active=1;";
	$result = exec_query($mysqli, $q);

	$user["shows"] = fetch_array($result);

	// convert boolean types
	$user["has_picture"] = (bool) $user["has_picture"];

	return $user;
}

/**
 * Validate a user object.
 *
 * @param mysqli
 * @param user
 * @return true if user is valid, false otherwise
 */
function validate_user($mysqli, $user)
{
	// required fields should be defined
	if ( empty($user["first_name"])
	  || empty($user["last_name"])
	  || empty($user["preferred_name"])
	  || empty($user["email_addr"])
	  || !is_array($user["shows"]) ) {
		return false;
	}

	// required fields for shows should be defined
	foreach ( $user["shows"] as $s ) {
		if ( !is_numeric($s["scheduleID"]) ) {
			return false;
		}
	}

	return true;
}

/**
 * Update the current user.
 *
 * @param mysqli
 * @param user
 */
function update_user($mysqli, $user)
{
	// update user
	$q = "UPDATE users SET "
		. "first_name = '$user[first_name]', "
		. "last_name = '$user[last_name]', "
		. "preferred_name = '$user[preferred_name]', "
		. "email_addr = '$user[email_addr]', "
		. "profile_paragraph = '$user[profile_paragraph]', "
		. "has_picture = '$user[has_picture]' "
		. "WHERE username = '$user[username]';";
	exec_query($mysqli, $q);

	// update user's shows
	foreach ( $user["shows"] as $s ) {
		$q = "UPDATE `schedule` AS s, `schedule_hosts` AS h SET "
			. "s.show_name = '$s[show_name]', "
			. "s.description = '$s[description]', "
			. "s.general_genreID = '$s[general_genreID]', "
			. "h.schedule_alias = '$s[schedule_alias]' "
			. "WHERE s.scheduleID='$s[scheduleID]' AND h.scheduleID='$s[scheduleID]';";
		exec_query($mysqli, $q);
	}

	// TODO: implement image upload
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();
	$user = get_user($mysqli, $_SESSION["username"]);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($user));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !auth_reviewer($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$user = json_decode(file_get_contents("php://input"), true);
	$user = escape_json($mysqli, $user);

	if ( !validate_user($mysqli, $user) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Submitted user data is invalid.");
	}

	update_user($mysqli, $user);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($user));
}
?>
