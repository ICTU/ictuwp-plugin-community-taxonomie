<?php
/**
 * WIP:
 * The template for displaying Community (taxonomy) Landings pages.
 *
 * @package    WordPress
 * @subpackage Timber
 * @since      Timber 1.22
 */


$context       = Timber::context();
$archive       = get_queried_object();
$taxonomy_name = $archive->taxonomy;
$templates     = [
	'overview-' . $taxonomy_name . '.twig',
	'overview.twig',
	'page.twig',
];


// Add our custom ACF fields
// (that we've added to our custom Tax)
// to this WP_Term..
foreach ( get_fields( $archive ) as $key => $val ) {

	// Add path to image url
	if( $key == 'community_taxonomy_visual' && defined( 'GC_COMMUNITY_TAX_ASSETS_PATH' ) ) {
		// Optionally convert to img tag with:
		//   '<img width="800" height="450" src="%s/%s" class="community-taxonomy-visual" alt="" decoding="async" loading="lazy" />',
		// for now just return the path:
		$val = sprintf( '%s/images/%s', GC_COMMUNITY_TAX_ASSETS_PATH, $val );
	}

	$archive->$key = $val;
}

$context['intro'] = $archive->description;

$context['post'] = [
	'title' => 'TODO COMMUNITY LANDINGSPAGE (taxonomy-community.php):<br> ' . get_the_archive_title(),
	'descr' => $archive->description,
	'img'   => $archive->community_taxonomy_image
];

// Set data for overview: linked posts with this Term
foreach ( $context['posts'] as $post ) {
	$context['overview']['items'][] = prepare_card_content( $post );
}

Timber::render( $templates, $context );
