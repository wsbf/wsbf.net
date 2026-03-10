<?php

/**
 * @file auth/spotify.php
 *
 * Authentication for the Spotify Web API.
 */

//WSBF spotify developer credentials
//define('CLIENT_ID', "6ebcab35516d4b45b69e855cd6aba3be");
//define('CLIENT_SECRET', "d19152f3dd1b41f3b3acbcb8c30658d3");

//These are from Adeep, need to be changed upon his departure
define('CLIENT_ID', "7d9d7615e0704d4e9c10ff6bd6dc5c79");
define('CLIENT_SECRET', "7a812c18ec9a446180191b0ab0f251c2");

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$auth_str = base64_encode(CLIENT_ID . ":" . CLIENT_SECRET);
	$json_result = shell_exec("curl -X \"POST\" "
		. "-H \"Authorization: Basic $auth_str\" "
		. "-d grant_type=client_credentials "
		. "https://accounts.spotify.com/api/token");

	header("Content-Type: application/json");
	exit($json_result);
}
?>
