<?php

/**
 * @file zautomate/automation_add_carts.php
 * @author Ben Shealy
 */
require_once("../connect.php");

// TODO: combine with get_carts from cartmachine_load.php
/**
 * Get a random cart from the list of valid carts.
 *
 * @param mysqli       MySQL connection
 * @param cart_typeID  cart type ID
 * @return associative array of random cart
 */
function get_random_cart($mysqli, $cart_typeID)
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

	$carts = array();
	while ( ($c = $result->fetch_assoc()) ) {
		$carts[] = $c;
	}

	$i = rand(0, count($carts) - 1);

	return $carts[$i];
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$cart_typeID = $_GET["type"];

	if ( !is_numeric($cart_typeID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$mysqli = construct_connection();
	$cart = get_random_cart($mysqli, $cart_typeID);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($cart));
}
?>
