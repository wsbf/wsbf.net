<?php
require_once("../auth.php");
require_once("../connect-dev.php");

/**
 * Remove a show sub request for the current user.
 *
 * @param mysqli     MySQL connection
 * @param requestID  sub request ID
 */
function remove_sub_request($mysqli, $requestID)
{
	$q = "DELETE FROM `sub_request` "
		. "WHERE sub_requestID = '$requestID' "
		. "AND username = '$_SESSION[username]';";
	$mysqli->query($q);
}

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to remove sub requests.");
	}

	$requestID = $_GET["requestID"];

	if ( !is_numeric($requestID) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Sub request ID is empty or invalid.");
	}

	remove_sub_request($mysqli, $requestID);

	$mysqli->close();
	exit;
}
?>
