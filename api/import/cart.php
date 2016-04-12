<?php

/**
 * @file import/cart.php
 * @author Ben Shealy
 *
 * Get a cart to import or import a cart.
 *
 * Carts are assumed to have the following filename pattern:
 *
 * <title>.mp3
 */
require_once("../auth.php");
require_once("../connect.php");
require_once("config.php");

/**
 * Validate a cart.
 *
 * @param mysqli  MySQL connection
 * @param cart    associative array of cart
 * @return true if cart is valid, false otherwise
 */
function validate_cart($mysqli, $cart)
{
	// required fields should be defined
	if ( empty($cart["filename"])
	  || !isset($cart["path"])
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
 * @param mysqli  MySQL connection
 * @param cart    associative array of cart
 */
function import_cart($mysqli, $cart)
{
	$src = realpath(IMPORT_SRC
		. stripslashes($cart["path"]) . "/"
		. stripslashes($cart["filename"]));
	$dst = IMPORT_DST . "carts/" . stripslashes($cart["filename"]);

	if ( strpos($src, IMPORT_SRC) === false ) {
		header("HTTP/1.1 404 Not Found");
		exit("Invalid filename.");
	}

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
	$mysqli->query($q);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_music_director($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to import files.");
	}

	// validate directory path
	$path_offset = urldecode($_GET["path"]);
	$path = realpath(IMPORT_SRC . $path_offset) . "/";

	if ( strpos($path, IMPORT_SRC) === false ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	// validate cart name
	$cart_name = urldecode($_GET["cart"]);

	if ( empty($cart_name) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	// construct cart object
	$cart = array(
		"filename" => $cart_name,
		"title" => explode(".", $cart_name)[0]
	);

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($cart));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_music_director($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to import files.");
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
