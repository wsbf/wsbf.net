<?php

/**
 * @file library/checkout.php
 * @author Ben Shealy
 *
 * Check out an album.
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Determine whether an album can be checked out.
 *
 * @param mysqli
 * @param albumID
 * @return true if album can be checked out, false otherwise
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
		. "AND c.expiration_date IS NULL OR c.expiration_date <= CURDATE();";
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
	$q = "UPDATE `libalbum` SET "
		. "rotationID = 0 "
		. "WHERE albumID = '$albumID';";
	exec_query($mysqli, $q);

	$q = "INSERT INTO `checkout` SET "
		. "username = '$_SESSION[username]', "
		. "albumID = '$albumID', "
		. "expiration_date = ADDDATE(CURDATE(), 7);";
	exec_query($mysqli, $q);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
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
?>
