<?php

/**
 * @file import/cart.php
 * @author Ben Shealy
 *
 * Get or import a cart from the import directory.
 *
 * Carts are assumed to have the following filename pattern:
 *
 * <title>.mp3
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("config.php");

/**
 * Get a cart in the import directory.
 *
 * @param filename
 * @return associative array of cart
 */
function get_cart($filename)
{
	return array(
		"filename" => $filename,
		"title" => explode(".", $filename)[0]
	);
}

/**
 * Validate a cart.
 *
 * @param mysqli
 * @param cart
 * @return true if cart is valid, false otherwise
 */
function validate_cart($mysqli, $cart)
{
	// required fields should be defined
	if ( empty($cart["filename"])
	  || empty($cart["title"])
	  || empty($cart["issuer"])
	  || !is_numeric($cart["cart_typeID"])
	  || empty($cart["start_date"]) ) {
		return false;
	}

	return true;
}

/**
 * Import a cart.
 *
 * @param mysqli
 * @param cart
 */
function import_cart($mysqli, $cart)
{
	$src = IMPORT_SRC . "/carts/" . stripslashes($cart["filename"]);
	$dst = IMPORT_DST . "/carts/" . stripslashes($cart["filename"]);

	if ( !copy($src, $dst) ) {
		header("HTTP/1.1 500 Internal Server Error");
		exit("Could not copy files.");
	}

	unlink($src);

	$q = "INSERT INTO `libcart` SET "
		. "start_date = '$cart[start_date]', "
		. "end_date = '$cart[end_date]', "
		. "issuer = '$cart[issuer]', "
		. "title = '$cart[title]', "
		. "cart_typeID = '$cart[cart_typeID]', "
		. "filename = '$cart[filename]';";
	exec_query($mysqli, $q);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !auth_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$filename = urldecode($_GET["filename"]);

	if ( empty($filename) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$cart = get_cart($filename);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($cart));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !auth_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$cart = json_decode(file_get_contents("php://input"), true);
	$cart = escape_json($mysqli, $cart);

	if ( !validate_cart($mysqli, $cart) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Submitted cart data is invalid.");
	}

	import_cart($mysqli, $cart);
	$mysqli->close();

	exit;
}
?>
