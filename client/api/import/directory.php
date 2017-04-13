<?php

/**
 * @file import/directory.php
 * @author Ben Shealy
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("config.php");

/**
 * Get files in a directory. Subdirectories are excluded.
 *
 * @param path
 * @return array of filenames
 */
function get_files($path)
{
	return array_filter(scandir($path), function($f) {
		return !is_dir($f);
	});
}

/**
 * Get albums and carts in the import directory.
 *
 * @return associative array of directory info
 */
function get_directory_info()
{
	$files_albums = get_files(IMPORT_SRC . "albums/");
	$files_carts = get_files(IMPORT_SRC . "carts/");

	$albums = array();
	$carts = array();

	foreach ( $files_albums as $f ) {
		if ( strstr($f, ".mp3") == ".mp3" ) {
			$parts = explode(" - ", $f);

			if ( count($parts) >= 3 ) {
				$album = array(
					"artist_name" => $parts[0],
					"album_name" => $parts[1]
				);

				// check whether album is already in array
				$unique = true;
				foreach ( $albums as $a ) {
					if ( $a["artist_name"] == $album["artist_name"]
					  && $a["album_name"] == $album["album_name"] ) {
						$unique = false;
					}
				}

				if ( $unique ) {
					$albums[] = $album;
				}
			}
		}
	}

	foreach ( $files_carts as $f ) {
		if ( strstr($f, ".mp3") == ".mp3" ) {
			$carts[] = $f;
		}
	}

	return array(
		"albums" => $albums,
		"carts" => $carts
	);
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !auth_senior_staff($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$info = get_directory_info();
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($info));
}
?>
