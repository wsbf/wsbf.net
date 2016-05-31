<?php

/**
 * @file logbook/auth.php
 * @author Ben Shealy
 *
 * Authenticate the user against their remote
 * address. This script is used to ensure that
 * only the machines in Studio A can manipulate
 * the logbook.
 */
function authenticate_logbook()
{
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
