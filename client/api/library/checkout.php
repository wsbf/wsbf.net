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
		. "AND a.rotationID = 0 "
		. "AND (c.expiration_date IS NULL OR c.expiration_date <= CURDATE());";
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
	// album rotation remains equal to 0

	$q = "INSERT INTO `checkout` SET "
		. "username = '$_SESSION[username]', "
		. "albumID = '$albumID', "
		. "expiration_date = ADDDATE(CURDATE(), 7);";
	exec_query($mysqli, $q);
}

/**
 * Return a checked-out album.
 *
 * @param mysqli
 * @param albumID
 */
function return_album($mysqli, $albumID)
{
	$q = "DELETE FROM `checkout` "
		. "WHERE username = '$_SESSION[username]' "
		. "AND albumID = '$albumID' "
		. "AND CURDATE() < expiration_date;";
	exec_query($mysqli, $q);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
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
?>
