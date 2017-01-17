<?php

/**
 * @file zautomate/log_cart.php
 * @author Ben Shealy
 */
require_once("../connect.php");
require_once("auth.php");
require_once("../logbook/functions.php");

/**
 * Log a cart in the logbook.
 *
 * @param mysqli
 * @param showID
 * @param cartID
 */
function log_cart($mysqli, $showID, $cartID)
{
	// get cart
	$q = "SELECT * FROM `libcart` AS c "
		. "INNER JOIN `def_cart_type` AS t ON t.cart_typeID=c.cart_typeID "
		. "WHERE c.cartID = '$cartID';";
	$cart = exec_query($mysqli, $q)->fetch_assoc();
	$cart = escape_json($mysqli, $cart);

	// log cart
	$q = "INSERT INTO `logbook` SET "
		. "showID = '$showID', "
		. "lb_album_code = '$cartID', "
		. "lb_rotation = '$cart[type]', "
		. "lb_artist = '$cart[issuer]', "
		. "lb_track_name = '$cart[title]', "
		. "played = 1;";
	exec_query($mysqli, $q);
}

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

	log_cart($mysqli, $showID, $cartID);
	$mysqli->close();

	exit("Successfully logged cart $cartID to show $showID.");
}
?>
