<?php

/**
 * @file logbook/listener_count.php
 * @author Ben Shealy
 *
 * Get the listener count for the Internet stream.
 *
 * This script reads the list of connections from
 * /proc/net/tcp, filters the connections to Icecast,
 * and removes the connections from internal WSBF
 * machines (such as archiving).
 */
define('TCP_ESTABLISHED', "107");

$output = shell_exec("cat /proc/net/tcp"
	. " | grep '04117F82:1F40'"
	. " | grep -v '06117F82:'");

$lines = explode("\n", $output);

array_pop($lines);

$addresses = array_map(function($line) {
	$row = explode(" ", preg_replace("/\s+/", " ", $line));

	if ( $row[8] == TCP_ESTABLISHED ) {
		return explode(":", $row[3])[0];
	}
	else {
		return null;
	}
}, $lines);

$addresses = array_filter($addresses, function($addr) {
	return isset($addr);
});

$count = count(array_unique($addresses));

header("Content-Type: application/json");
exit(json_encode($count));
?>
