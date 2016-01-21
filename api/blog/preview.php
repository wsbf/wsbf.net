<?php
require_once("$_SERVER[DOCUMENT_ROOT]/blog/wp-blog-header.php");

function custom_excerpt_length($length)
{
	return 15;
}

$wp_posts = get_posts(array(
	"numberposts" => 20,
	"orderby" => "date",
	"order" => "DESC"
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

	$posts[] = array(
		"id" => $post->ID,
		"title" => get_the_title(),
		"date" => get_the_date(),
		"author" => get_the_author(),
		"category" => get_the_category_list(", "),
		"imageUrl" => $imageUrl,
		"excerpt" => get_the_excerpt()
	);
}

header("Content-Type: application/json");
exit(json_encode($posts));
?>
