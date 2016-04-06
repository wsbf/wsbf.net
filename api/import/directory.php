<?php

/**
 * @file import/directory.php
 * @author Ben Shealy
 */
require_once("../auth.php");
require_once("../connect.php");
require_once("config.php");

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_music_director($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit("Current user is not allowed to import files.");
	}

	// validate directory path
	$path_offset = array_key_exists("path", $_GET)
		? urldecode($_GET["path"])
		: "";
	$path = realpath(IMPORT_SRC . $path_offset) . "/";

	if ( strpos($path, IMPORT_SRC) === false ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	// scan directory for files
	$files = array_filter(scandir($path), function($f) {
		return $f != "." && $f != "..";
	});

	// construct directory info object
	$info = array(
		"directories" => array(),
		"carts" => array(),
		"artists" => array()
	);

	foreach ( $files as $f ) {
		if ( is_dir($path . $f) ) {
			$info["directories"][] = $f;
		}
		else if ( strstr($f, ".mp3") == ".mp3" ) {
			$parts = explode(" - ", $f);

			if ( count($parts) >= 3 ) {
				$artist = $parts[0];

				if ( !in_array($artist, $info["artists"]) ) {
					$info["artists"][] = $artist;
				}
			}
			else {
				$info["carts"][] = $f;
			}
		}
	}

	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($info));
}
?>
