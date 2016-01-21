<?php
require_once("$_SERVER[DOCUMENT_ROOT]/blog/wp-blog-header.php");

// TODO: check proper range of post IDs
$postID = $_GET["p"];

if ( !is_numeric($postID) ) {
	header("HTTP/1.1 404 Not Found");
	exit("Post ID is empty or invalid.");
}

setup_postdata(get_post($postID));

header("Content-Type: text/html");
exit(get_the_content());
?>
