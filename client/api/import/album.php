<?php

/**
 * @file import/album.php
 * @author Ben Shealy
 *
 * Get an album to import or import an album.
 *
 * Tracks are assumed to have the following filename pattern:
 *
 * <artist_name> - <album_name> - <track_num> - <track_name>[ - Disc <disc_num>].mp3
 *
 * Track file names are URL encoded when they are saved to the database,
 * even though cart file names are not. I don't really see the purpose
 * of this step so it could probably be eliminated, but then the database
 * entries will need to be decoded.
 *
 * Also, the use of ' - ' requires that none of the track fields
 * contain ' - ', or else the tracks will be interpreted incorrectly.
 *
 * TODO: read MP3 tags from tracks instead of file name conventions
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("../library/functions.php");
require_once("config.php");

$REPLACE_PAIRS = array(
	'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z',
	'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A',
	'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E',
	'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I',
	'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O',
	'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U',
	'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
	'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a',
	'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e',
	'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o',
	'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o',
	'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'Ť'=>'T', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y',
	'ƒ'=>'f', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c', 'Ŕ'=>'R', 'ŕ'=>'r',
	'$'=>'S', 'Ů'=>'U', 'ĺ'=>'l', 'ľ'=>'l', 'ř'=>'r', 'ť'=>'t', 'ň'=>'n',
	'ď'=>'d', 'ů'=>'u', 'Ď'=>'D', 'Ĺ'=>'L', 'Ľ'=>'L', 'Ň'=>'N', 'Ř'=>'R'
);

/**
 * Get the "directory name" of a string, which
 * is the string with the following transformations:
 *
 * 1) non-alphanumeric characters are converted to an
 *    alphanumeric equivalent or removed (spaces are preserved)
 * 2) letters with embellishments are converted to plain letters
 * 3) upper-case letters are converted to lower-case letters
 * 4) "the " is removed from the beginning if it is present
 * 5) spaces are removed
 *
 * @param str
 * @return directory name of string
 */
function directory_name($str)
{
	global $REPLACE_PAIRS;

	$str = str_replace("&", "and", $str);
	$str = str_replace("!", "chk", $str);
	$str = trim(preg_replace("/[^\w\d ]/si", "", $str));
	$str = strtr($str, $REPLACE_PAIRS);

	$str = strtolower($str);

	if ( strpos($str, "the ") === 0 ) {
		$str = substr($str, 4);
	}
	$str = str_replace(" ", "", $str);

	return $str;
}

/**
 * Get an album in the import directory.
 *
 * @param artist_name
 * @param album_name
 * @return associative array of album
 */
function get_album($artist_name, $album_name)
{
	// scan and parse tracks
	$path = IMPORT_SRC . "/albums/";

	$files = array_filter(scandir($path), function($f) use($artist_name, $album_name) {
		return strpos($f, $artist_name) !== false
			&& strpos($f, $album_name) !== false;
	});

	$tracks = array_map(function($f) {
		$parts = explode(" - ", substr($f, 0, strlen($f) - strlen(".mp3")));

		$parts[4] = (count($parts) == 5)
			? substr($parts[4], 5)
			: 1;

		return array(
			"disc_num" => (int) $parts[4],
			"track_num" => (int) $parts[2],
			"track_name" => $parts[3],
			"artist_name" => $parts[0],
			"album_name" => $parts[1],
			"file_name" => $f
		);
	}, $files);
	$tracks = array_values($tracks);

	usort($tracks, function($a, $b) {
		if ( $a["disc_num"] == $b["disc_num"] ) {
			return $a["track_num"] - $b["track_num"];
		}

		return $a["disc_num"] - $b["disc_num"];
	});

	$num_discs = array_reduce($tracks, function($max, $t) {
		return max($max, $t["disc_num"]);
	}, 1);

	// construct album object
	return array(
		"artist_name" => $artist_name,
		"album_name" => $album_name,
		"num_discs" => $num_discs,
		"tracks" => $tracks
	);
}

/**
 * Validate an album.
 *
 * @param mysqli
 * @param album
 * @return true if album is valid, false otherwise
 */
function validate_album($mysqli, $album)
{
	// required fields should be defined
	if ( empty($album["artist_name"])
	  || empty($album["album_name"])
	  || !is_numeric($album["general_genreID"])
	  || !is_array($album["tracks"]) ) {
		return false;
	}

	foreach ( $album["tracks"] as $t ) {
		if ( !is_numeric($t["disc_num"])
		  || !is_numeric($t["track_num"])
		  || empty($t["track_name"])
		  || empty($t["artist_name"])
		  || empty($t["file_name"]) ) {
			return false;
		}
	}

	return true;
}

/**
 * Import an album.
 *
 * @param mysqli
 * @param album
 */
function import_album($mysqli, $album)
{
	// unescape track file names before URL-encoding
	foreach ( $album["tracks"] as &$t ) {
		$t["file_name"] = stripslashes($t["file_name"]);
	}

	// added because of stupid foreach behavior in PHP 5
	// when iterating by reference instead of value
	// might not be needed in PHP 7
	unset($t);

	// construct file paths
	$src_base = IMPORT_SRC . "/albums/";
	$dir_name = directory_name($album["artist_name"]);
	$dst_base = IMPORT_DST . $dir_name[0] . "/" . $dir_name[1] . "/";

	if ( !file_exists($dst_base) ) {
		mkdir($dst_base, 0775, true);
	}

	$pairs = array_map(function($t) use($src_base, $dst_base) {
		return array(
			"src" => realpath($src_base . $t["file_name"]),
			"dst" => $dst_base . $t["file_name"]
		);
	}, $album["tracks"]);

	// move files to digital library
	foreach ( $pairs as $p ) {
		if ( !copy($p["src"], $p["dst"]) ) {
			header("HTTP/1.1 500 Internal Server Error");
			exit("Could not copy files.");
		}
	}

//	foreach ( $pairs as $p ) {
//		unlink($p["src"]);
//	}

	// insert album
	$artistID = find_artist($mysqli, $album["artist_name"])
			or add_artist($mysqli, $album["artist_name"]);

	$labelID = find_label($mysqli, $album["label"])
			or add_label($mysqli, $album["label"]);

	$q = "INSERT INTO `libalbum` SET "
		. "album_name = '$album[album_name]', "
		. "num_discs = '$album[num_discs]', "
		. "artistID = '$artistID', "
		. "labelID = '$labelID', "
		. "general_genreID = '$album[general_genreID]', "
		. "genre = '$album[genre]';";
	$mysqli->query($q) or die($mysqli->error);

	$albumID = $mysqli->insert_id;

	// initialize album_code to albumID
	$q = "UPDATE `libalbum` SET album_code = '$albumID' "
		. "WHERE albumID = '$albumID';";
	$mysqli->query($q) or die($mysqli->error);

	// insert tracks
	foreach ( $album["tracks"] as $t ) {
		$artistID = find_artist($mysqli, $t["artist_name"])
				or add_artist($mysqli, $t["artist_name"]);

		$file_name = urlencode(substr($dir_name, 0, 2) . $t["file_name"]);

		$q = "INSERT INTO `libtrack` SET "
			. "track_name = '$t[track_name]', "
			. "disc_num = '$t[disc_num]', "
			. "track_num = '$t[track_num]', "
			. "artistID = '$artistID', "
			. "file_name = '$file_name', "
			. "albumID = '$albumID';";
		$mysqli->query($q) or die($mysqli->error);
	}

	// insert action
	add_action($mysqli, "INSERTED " . count($album["tracks"]) . " tracks for new album: $albumID");
}

authenticate();

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	if ( !check_music_director($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$artist_name = urldecode($_GET["artist_name"]);
	$album_name = urldecode($_GET["album_name"]);

	if ( empty($artist_name) || empty($album_name) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$album = get_album($artist_name, $album_name);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($album));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	$mysqli = construct_connection();

	if ( !check_music_director($mysqli) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$album = json_decode(file_get_contents("php://input"), true);
	$album = escape_json($mysqli, $album);

	if ( !validate_album($mysqli, $album) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Submitted album data is invalid.");
	}

	import_album($mysqli, $album);
	$mysqli->close();

	exit;
}
?>
