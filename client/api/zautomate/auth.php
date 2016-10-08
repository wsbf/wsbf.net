<?php

/**
 * @file zautomate/auth.php
 * @author Ben Shealy
 *
 * Verify that the client address is the Automation
 * computer in Studio A.
 */
define('VALID_IP_ADDR', "130.127.17.5");

function authenticate_automation()
{
	if ( $_SERVER["REMOTE_ADDR"] !== VALID_IP_ADDR ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}
}
?>
