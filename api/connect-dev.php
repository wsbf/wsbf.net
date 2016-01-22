<?php

/* Enable runtime error reporting in development */
error_reporting(E_ALL);

/**
 * Create a connection to the MySQL database.
 *
 * Default credentials are stored in the php config file.
 *
 * @return MySQL connection
 */
function construct_connection()
{
	$mysqli = new mysqli("localhost");

	if ( $mysqli->connect_errno ) {
		header("HTTP/1.1 404 Not Found");
		exit("MySQL Error $mysqli->connect_errno: $mysqli->connect_error");
	}

	$mysqli->select_db("wsbf_dev");
	$mysqli->set_charset("utf8");

	return $mysqli;
}

/**
 * Escape JSON data for SQL statements.
 *
 * @param mysqli  MySQL connection
 * @param json    any data type from JSON
 * @param escaped JSON data
 */
function escape_json($mysqli, $json)
{
	if ( is_array($json) ) {
		foreach ( array_keys($json) as $k ) {
			$json[$k] = escape_json($mysqli, $json[$k]);
		}

		return $json;
	}

	return $mysqli->escape_string($json);
}
?>
