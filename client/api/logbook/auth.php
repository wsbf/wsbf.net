<?php

/**
 * @file logbook/auth.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");

/**
 * Authenticate the user to access the logbook.
 * Only the computer engineer and the machines in
 * Studio A can access the logbook.
 *
 * @param mysqli
 */
function authenticate_logbook($mysqli)
{
	// computer engineer always has access
	if ( get_position($mysqli) == "8" ) {
		return;
	}

	// otherwise, only Studio A machines have access
	$valid_addr = array(
		"130.127.17.5",
		"130.127.17.40"
	);

	if ( !in_array($_SERVER["REMOTE_ADDR"], $valid_addr) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}
}
?>
