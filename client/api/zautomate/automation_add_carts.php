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
 * @param mysqli
 * @param cart_typeID
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


	/*
	When below lines are uncommented, the StationID played by automation will always be the same.
	This is useful for mandantory announcements that are required to be played during specific time slots
	without manual interaction and when no DJ has a show.

	TO USE,
	1. cart_typeID 2 specifies the carts to be replaced are StationID type
	2. edit the sql query as needed, replacing cart_typeID as desired
	3. log into the dj website, go to the carts control panel, get the cart ID, replace as desired
	4. in the directory, execute the command "npm run build"

	TO DISABLE,
	1. add comment block starting below this one
	*/
	if( $cart_typeID == 2)
    {
            $q = "SELECT c.cartID, c.issuer, c.title, c.filename, t.type FROM `libcart` AS c INNER JOIN `def_cart_type` AS t ON t.cart_typeID = c.cart_typeID WHERE c.cart_typeID = 2 AND c.cartID = 301";
            $result = exec_query($mysqli, $q);
    }
	/*
	TO DISABLE,
	2. add comment block ending above this one
	*/

	$carts = fetch_array($result);
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
