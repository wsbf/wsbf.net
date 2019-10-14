<?php

/**
 * @file zautomate/cartmachine_load.php
 * @author Ben Shealy
 */
require_once("../connect.php");

/**
 * Get valid carts of a particular type.
 *
 * @param mysqli
 * @param cart_typeID
 * @return array of carts
 */
function get_carts($mysqli, $cart_typeID)
{
	$keys = array(
		"c.cartID",
		"c.issuer",
		"c.title",
		"c.filename",
		"t.type"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `libcart` AS c "
		. "INNER JOIN `def_cart_type` AS t ON t.cart_typeID=c.cart_typeID "
		. "WHERE c.cart_typeID = '$cart_typeID' "
		. "AND c.start_date < NOW() "
		. "AND (NOW() < c.end_date OR c.end_date IS NULL);";
	$result = exec_query($mysqli, $q);


	if( $cart_typeID == 2)
	{
		$q = "SELECT c.cartID, c.issuer, c.title, c.filename, t.type FROM `libcart` AS c INNER JOIN `def_cart_type` AS t ON t.cart_typeID = c.cart_typeID WHERE c.cart_typeID = 2 AND c.cartID = 300 AND c.start_date < NOW() AND (NOW() < c.end_date OR c.end_date IS NULL)";
		$result = exec_query($mysqli, $q);
	}

	return fetch_array($result);
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$cart_typeID = $_GET["type"];

	if ( !is_numeric($cart_typeID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$mysqli = construct_connection();
	$carts = get_carts($mysqli, $cart_typeID);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($carts));
}
?>
