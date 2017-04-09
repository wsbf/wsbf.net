<?php

/**
 * @file carts/carts.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get carts of a cart type.
 *
 * @param mysqli
 * @param cart_typeID
 * @return array of carts
 */
function get_carts($mysqli, $cart_typeID)
{
	$keys = array(
		"c.cartID",
		"c.start_date",
		"c.end_date",
		"c.issuer",
		"c.title",
		"c.cart_typeID"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `libcart` AS c "
		. "WHERE c.cart_typeID = '$cart_typeID';";
	$result = exec_query($mysqli, $q);

	return fetch_array($result);
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$cart_typeID = $_GET["cart_typeID"];

	if ( !is_numeric($cart_typeID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$carts = get_carts($mysqli, $cart_typeID);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($carts));
}
?>
