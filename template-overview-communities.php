<?php
/**
 * Template Name: Template communities
 *
 * @package    WordPress
 * @subpackage Timber
 * @since      Timber 0.1
 */

$context            = Timber::context();
$timber_post        = new Timber\Post();
$context['post']    = $timber_post;
$context['modifier'] = 'community-overview';

/**
 * Fill Timber $context with available page/post Blocks/Metaboxes
 * @see /includes/gc-fill-context-with-acf-fields.php
 */
if ( function_exists( 'gc_fill_context_with_acf_fields' ) ) {
	$context = gc_fill_context_with_acf_fields( $context );
}

/**
 * Add communities (terms in Community taxonomy)
 */
if ( function_exists( 'fn_ictu_community_get_community_terms' ) ) {
	$context['overview']             = [];
	$context['overview']['items']    = [];
	$context['overview']['template'] = 'card--community';

	foreach ( fn_ictu_community_get_community_terms() as $community ) {
		$taxonomy = get_taxonomy( $community->taxonomy );
		$term_url = get_term_link( $community );

		$item = array(
			'type'  => 'community',
			'title' => $community->name,
			'descr' => $community->description,
			// IGNORE custom page url for now..
			// 'url'   => $community->community_taxonomy_page,
			'url'   => $term_url
		);

		// if ( $community->community_taxonomy_image ) {
		// 	$item['img'] = '<img src="' . $community->community_taxonomy_image['sizes']['image-16x9'] . '" alt=""/>';
		// }
		if ( $community->community_taxonomy_link ) {
			$item['url'] = $community->community_taxonomy_link['url'];
		}
		if ( $community->community_taxonomy_visual ) {
			$item['img'] = $community->community_taxonomy_visual;
		}
		if ( $community->community_taxonomy_colorscheme ) {
			$item['scheme'] = $community->community_taxonomy_colorscheme;
		}

		
		$context['overview']['items'][] = $item;

	}
}

Timber::render( [ 'overview.twig', 'page.twig' ], $context );
