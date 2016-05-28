<?php

/**
 * @file zautomate/log_cart.php
 * @author Ben Shealy
 */
require_once("../connect.php");

define('VALID_IP_ADDR', "130.127.17.5");

/**
 * Get the current show, or create a new
 * Automation show if there is no show.
 *
 * @param mysqli  MySQL connection
 * @return current show ID
 */
function get_current_show($mysqli)
{
	// get the most recent show
	$q = "SELECT showID, end_time FROM `show` "
		. "ORDER BY start_time DESC "
		. "LIMIT 1;";
	$show = $mysqli->query($q)->fetch_assoc();

	// check whether the show has ended yet
	if ( $show["end_time"] == null ) {
		return $show["showID"];
	}
	else {
		// login Automation (confer logbook/sign_on.php)
		$q = "INSERT INTO `show` SET "
			. "show_name = 'The Best of WSBF', "
			. "show_typeID = 8;";
		$mysqli->query($q);

		$showID = $mysqli->insert_id;

		$q = "INSERT INTO `show_hosts` SET "
			. "showID = '$showID', "
			. "username = 'Automation';";
		$mysqli->query($q);

		return $showID;
	}
}

/**
 * Log a cart in the logbook.
 *
 * @param mysqli  MySQL connection
 * @param showID  show ID
 * @param cartID  cart ID
 */
function log_cart($mysqli, $showID, $cartID)
{
	// get cart
	$q = "SELECT * FROM `libcart` AS c "
		. "INNER JOIN `def_cart_type` AS t ON t.cart_typeID=c.cart_typeID "
		. "WHERE c.cartID = '$cartID';";
	$cart = $mysqli->query($q)->fetch_assoc();

	// log cart
	$q = "INSERT INTO `logbook` SET "
		. "showID = '$showID', "
		. "lb_album_code = '$cartID', "
		. "lb_rotation = '$cart[type]', "
		. "lb_artist = '$cart[issuer]', "
		. "lb_track_name = '$cart[title]', "
		. "played = 1;";
	$mysqli->query($q);
}

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	if ( $_SERVER["REMOTE_ADDR"] !== VALID_IP_ADDR ) {
		header("HTTP/1.1 404 Not Found");
		exit("Yeah, I logged that cart...");
	}

	$cartID = $_GET["cartid"];

	if ( !is_numeric($cartID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$mysqli = construct_connection();

	$showID = get_current_show($mysqli);

	log_cart($mysqli, $showID, $cartID);
	$mysqli->close();

	exit("Successfully logged cart $cartID to show $showID.");
}
?>
