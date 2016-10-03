<?php

/**
 * @file connect.php
 * @author Ben Shealy
 *
 * Helper functions for common tasks such as creating
 * a database connection, escaping JSON data, and so on.
 */

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
		exit("Could not connect to database.");
	}

	$mysqli->select_db("wsbf");
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

/**
 * Execute a MySQL query. If an error occurs,
 * return an HTTP error code immediately.
 *
 * @param mysqli
 * @param q
 * @return result of query
 */
function exec_query($mysqli, $q)
{
	$result = $mysqli->query($q);

	if ( !$result ) {
		header("HTTP/1.1 500 Internal Server Error");
		exit($mysqli->error);
	}

	return $result;
}

/**
 * Helper function to access an array key which
 * may not be defined.
 *
 * @param array
 * @param key
 * @return array[key], or null if key is undefined
 */
function array_access($array, $key)
{
	return array_key_exists($key, $array)
		? $array[$key]
		: null;
}
?>
