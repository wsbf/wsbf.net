<?php
require_once("../auth.php");
require_once("../connect-dev.php");

/**
 * Validate a sub request.
 *
 * @param mysqli     MySQL connection
 * @param requestID  sub request ID
 * @return true if request is valid, false otherwise
 */
function validate_sub_request($mysqli, $requestID)
{
	$q = "SELECT r.username, f.username AS filled_by "
		. "FROM `sub_request` AS r "
		. "LEFT OUTER JOIN `sub_fill` AS f "
		. "ON f.sub_requestID=r.sub_requestID "
		. "WHERE r.sub_requestID='$requestID';";
	$result = $mysqli->query($q);

	// sub request must exist
	if ( $result->num_rows == 0 ) {
		return false;
	}

	$request = $result->fetch_assoc();

	// sub request must not already be filled
	if ( !empty($request["filled_by"]) ) {
		return false;
	}

	// sub request must not be from the current user
	if ( $request["username"] == $_SESSION["username"] ) {
		return false;
	}

	return true;
}

/**
 * Fill a sub request with the current user.
 *
 * @param mysqli     MySQL connection
 * @param requestID  sub request ID
 */
function fill_sub_request($mysqli, $requestID)
{
	$q = "INSERT INTO `sub_fill` (sub_requestID, username) "
		. "VALUES ('$requestID', '$_SESSION[username]');";
	$mysqli->query($q);
}

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_reviewer($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to fill sub requests.");
	}

	$requestID = $_GET["requestID"];

	if ( !is_numeric($requestID) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Sub request ID is empty or invalid.");
	}

	if ( !validate_sub_request($mysqli, $requestID) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Sub request fill is invalid.");
	}

	fill_sub_request($mysqli, $requestID);

	$mysqli->close();
	exit;
}
?>
