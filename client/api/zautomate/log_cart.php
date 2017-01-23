<?php

/**
 * @file zautomate/log_cart.php
 * @author Ben Shealy
 */
require_once("../connect.php");
require_once("auth.php");
require_once("../logbook/functions.php");

authenticate_automation();

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$cartID = $_GET["cartid"];

	if ( !is_numeric($cartID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$mysqli = construct_connection();

	// get current show or login Automation
	$showID = get_current_show_id($mysqli);

	if ( !isset($showID) ) {
		$showID = sign_on($mysqli, AUTOMATION_SCHEDULE_ID);
	}

	// get cart information
	$cart = get_cart($mysqli, $cartID);
	$cart = escape_json($mysqli, $cart);

	// log cart
	log_cart($mysqli, $showID, $cart);
	$mysqli->close();

	exit("Successfully logged cart $cartID to show $showID.");
}
?>
