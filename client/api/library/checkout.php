<?php

/**
 * @file library/checkout.php
 * @author Ben Shealy
 * edits by jai agarwal
 * Check out an album.
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Determine whether an album can be checked out.
 *
 * @param mysqli
 * @param albumID
 */
function validate_checkout($mysqli, $albumID)
{
	if ( !is_numeric($albumID) ) {
		return false;
	}

	// album should be in TBR and not checked out
	$q = "SELECT rotationID FROM `libalbum` AS a "
		. "LEFT OUTER JOIN `checkout` AS c ON c.albumID = a.albumID "
		. "WHERE a.albumID = '$albumID' "
		. "AND (a.rotationID = 0 "
		. "OR (a.rotationID = 1 AND (c.expiration_date IS NULL OR c.expiration_date <= CURDATE())));";

	$result = exec_query($mysqli, $q);

	if ( $result->num_rows == 0 ) {
		return false;
	}

	return true;
}

/**
 * Determine whether an album can be returned.
 *
 * @param mysqli
 * @param albumID
 */
function validate_return($mysqli, $albumID)
{
	if ( !is_numeric($albumID) ) {
		return false;
	}

	// album should be checked out by current user
	$q = "SELECT c.albumID FROM `checkout` AS c "
		. "WHERE c.username = '$_SESSION[username]' "
		. "AND c.albumID = '$albumID' "
		. "AND CURDATE() < c.expiration_date;";
	$result = exec_query($mysqli, $q);

	if ( $result->num_rows == 0 ) {
		return false;
	}

	return true;
}

/**
 * Check out an album.
 *
 * @param mysqli
 * @param albumID
 */
function checkout_album($mysqli, $albumID)
{
	// album rotation remains equal to 0   (why? - jai 2024)

	// set rotationID to 1
	$q = "UPDATE `libalbum` SET "
		. "rotationID = 1 "
		. "WHERE albumID = '$albumID';";
	if (!$mysqli->query($q)) {
		error_log("Insert Error: " . $mysqli->error);
	}

	$q = "INSERT INTO `checkout` SET "
		. "username = '$_SESSION[username]', "
		. "albumID = '$albumID', "
		. "expiration_date = ADDDATE(CURDATE(), 7);";
	if (!$mysqli->query($q)) {
		error_log("Insert Error: " . $mysqli->error);
	}
}

/**
 * Return a checked-out album.
 *
 * @param mysqli
 * @param albumID
 */
function return_album($mysqli, $albumID)
{
	$q = "UPDATE `libalbum` SET "
		. "rotationID = 0 "
		. "WHERE albumID = '$albumID';";
	exec_query($mysqli, $q);

	$q = "DELETE FROM `checkout` "
		. "WHERE username = '$_SESSION[username]' "
		. "AND albumID = '$albumID' "
		. "AND CURDATE() < expiration_date;";
	exec_query($mysqli, $q);
}

/**
 * See who has an album checkedout.
 *
 * @param mysqli
 * @param albumID
 */
function who_checkedout($mysqli, $albumID)
{
	$q = "SELECT u.preferred_name, c.username "
		. "FROM `checkout` AS c "
		. "JOIN `users` AS u ON c.username = u.username "
		. "WHERE c.albumID = '$albumID' "
		. "ORDER BY c.expiration_date DESC LIMIT 1;";

	$result = exec_query($mysqli, $q);

	if ($result && $result->num_rows > 0) {
		$row = $result->fetch_assoc();
		return [
			'username' => $row['username'],
			'preferred_name' => $row['preferred_name']
		];
	}

	// return nulls if no records found
	return [
		'username' => null,
		'preferred_name' => null
	];
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	// checkout an album, set its rotationID to 1
	// and insert new entry into the checkout table
	$mysqli = construct_connection();

	if ( !auth_reviewer($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$albumID = $_GET["albumID"];

	if ( !validate_checkout($mysqli, $albumID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	checkout_album($mysqli, $albumID);
	$mysqli->close();

	header("Content-Type: application/json");
	exit;
}
else if ( $_SERVER["REQUEST_METHOD"] == "DELETE" ) {
	// return the album, put it back into TBR 
	// by deleting its checkout table entry.
	$mysqli = construct_connection();

	if ( !auth_reviewer($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$albumID = $_GET["albumID"];

	if ( !validate_return($mysqli, $albumID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	return_album($mysqli, $albumID);
	$mysqli->close();

	header("Content-Type: application/json");
	exit;
}
else if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	// respond to GET request with the username and preferred name of
	// who has checked out an album
	$mysqli = construct_connection();

	if ( !auth_reviewer($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}
	
	if (!isset($_GET['albumID'])) {
		header("HTTP/1.1 400 Bad Request");
		echo json_encode(['error' => 'Missing albumID parameter']);
		exit;
	}

	$albumID = $_GET["albumID"];

	$response = who_checkedout($mysqli, $albumID);

	header("Content-Type: application/json");
    echo json_encode($response);

	$mysqli->close();
	exit;
}
?>
