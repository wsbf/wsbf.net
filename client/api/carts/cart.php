<?php

/**
 * @file carts/cart.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");

/**
 * Get a cart.
 *
 * @param mysqli
 * @param cartID
 * @return associative array of cart
 */
function get_cart($mysqli, $cartID)
{
	$keys = array(
		"c.cartID",
		"c.issuer",
		"c.title",
		"c.cart_typeID",
		"c.start_date",
		"c.end_date",
		"c.filename"
	);

	$q = "SELECT " . implode(",", $keys) . " FROM `libcart` AS c "
		. "WHERE c.cartID = '$cartID';";
	$cart = exec_query($mysqli, $q)->fetch_assoc();

	return $cart;
}

/**
 * Determine whether a cart is valid.
 *
 * @param mysqli
 * @param cart
 * @return true if cart is valid, false otherwise
 */
function validate_cart($mysqli, $cart)
{
	if ( empty($cart["title"])
	  || empty($cart["issuer"])
	  || !is_numeric($cart["cart_typeID"])
	  || empty($cart["start_date"]) ) {
		return false;
	}

	return true;
}

/**
 * Update a cart.
 *
 * @param mysqli
 * @param cart
 */
function update_cart($mysqli, $cart)
{
	$q = "UPDATE `libcart` SET "
		. "title = '$cart[title]', "
		. "issuer = '$cart[issuer]', "
		. "cart_typeID = '$cart[cart_typeID]', "
		. "start_date = '$cart[start_date]', "
		. "end_date = '$cart[end_date]' "
		. "WHERE cartID = '$cart[cartID]';";
	exec_query($mysqli, $q);
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$cartID = $_GET["cartID"];

	if ( !is_numeric($cartID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$cart = get_cart($mysqli, $cartID);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($cart));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$cart = json_decode(file_get_contents("php://input"), true);
	$cart = escape_json($mysqli, $cart);

	if ( !validate_cart($mysqli, $cart) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	update_cart($mysqli, $cart);
	$mysqli->close();

	exit;
}
?>
