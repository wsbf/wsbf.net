<?php

/**
 * @file blog/preview.php
 * @author Ben Shealy
 */
/*
require_once("$_SERVER[DOCUMENT_ROOT]/blog/wp-blog-header.php");

function custom_excerpt_length($length)
{
	return 15;
}

$wp_posts = get_posts(array(
	"numberposts" => 5,
	"orderby" => "date",
	"order" => "DESC",
	"tag" => "featured"
));

$posts = array();

foreach ( $wp_posts as $post ) {
	setup_postdata($post);

	// temporary hack to get image URL from HTML element
	$imageUrl = get_the_post_thumbnail();
	$start = strpos($imageUrl, "src=\"") + 5;
	$end = strpos($imageUrl, "\"", $start);
	$imageUrl = substr($imageUrl, $start, $end - $start);

	// apply custom length to excerpt
	add_filter("excerpt_length", "custom_excerpt_length", 20);

	// temporary hack to remove "Continue Reading" from excerpt
	$excerpt = get_the_excerpt();
	$excerpt = preg_replace("/<p .*<\/p>/", "", $excerpt);
	$excerpt = html_entity_decode($excerpt);

	$posts[] = array(
		"id" => $post->ID,
		"title" => html_entity_decode(get_the_title()),
		"imageUrl" => $imageUrl,
		"excerpt" => $excerpt
	);
}

header("Content-Type: application/json");
exit(json_encode($posts));
*/
?>
