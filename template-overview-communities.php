<?php
/**
 * Template Name: [Community] overzicht
 *
 * @package    WordPress
 * @subpackage Timber
 * @since      Timber 0.1
 */

$context              = Timber::context();
$timber_post          = new Timber\Post();
$context['post']      = $timber_post;
$context['modifier']   = 'community-overview';
$context['item_type'] = 'community-overview'; // Pass item_type to grid section (adds ID to grid)

// TODO: implement Query Filters ($context['query_filters']) (like template-instumenten-tt.php)?

///**
// * Fill Timber $context with available page/post Blocks/Metaboxes
// * @see /includes/gc-fill-context-with-acf-fields.php
// */
//if ( function_exists( 'gc_fill_context_with_acf_fields' ) ) {
//	$context = gc_fill_context_with_acf_fields( $context );
//}

/**
 * Add communities (terms in Community taxonomy)
 */
if ( function_exists( 'fn_ictu_community_get_community_terms' ) ) {
	$context['items']    = [];

	foreach ( fn_ictu_community_get_community_terms() as $community ) {

		$item = array(
			// type, translates to card-type--{type}
			'type'  => 'community',
			'title' => $community->name,
			'descr' => $community->description,
			// IGNORE Archive url for now..
			// 'url'   => $term_archive_url
		);

		// Setup image to use in Card
		$landingpage_featured_image = null;

		// Linked Landingpage
		if ( $community->community_taxonomy_page ) {
			$landingpage           = get_post( $community->community_taxonomy_page );

			// Override: card Title from page link
			$item['title']         = get_the_title( $landingpage->ID );

			// Override: card Description from page intro
			$landingpage_intro     = get_field( 'post_inleiding', $landingpage->ID );
			if ( $landingpage_intro ) {
				$item['descr']     = $landingpage_intro;
			}

			// Set Card url from page link
			$item['url']           = get_page_link( $landingpage );

			// Set Card image from page thumbnail
			$landingpage_thumbnail = get_the_post_thumbnail_url( $landingpage->ID, 'image-16x9' );
			if ( $landingpage_thumbnail ) {
				$landingpage_featured_image = $landingpage_thumbnail;
			}
		}

		// Image:
		if ( $community->community_taxonomy_image ) {
			$landingpage_featured_image = '<img src="' . $community->community_taxonomy_image['sizes']['image-16x9'] . '" alt=""/>';
		}

		// Visual
		if ( $community->community_taxonomy_visual ) {
			$item['visual'] = $community->community_taxonomy_visual;
			// Use visual as featured image if no other image is set
			if ( ! $landingpage_featured_image ) {
				$landingpage_featured_image = $community->community_taxonomy_visual;
			}
		}

		// Finally use preferred image for card
		if ( $landingpage_featured_image ) {
			$item['img'] = '<img src="' . $landingpage_featured_image . '" alt=""/>';
		}

		// Colorscheme:
		if ( $community->community_taxonomy_colorscheme ) {
			$item['palette'] = $community->community_taxonomy_colorscheme;
			// Add extra class to card-type
			$item['type'] = $item['type'] . ' palette--' . $item['palette'];

		}

		// Link: link to subsite?
		if ( $community->community_taxonomy_link ) {
			// We do not yet link to a subsite
			// but _always_ refer to the landing page first..
			// $item['url'] = $community->community_taxonomy_link['url'];
			$item['link'] = $community->community_taxonomy_link;
		}


		$context['items'][] = $item;

	}
}

// Enqueue page-specific JS (checkbox filters)
// ------------------------------------------
// add_action( 'wp_enqueue_scripts', 'gc_enqueue_checkbox_filters_scripts' );
// function gc_enqueue_checkbox_filters_scripts() {
// 	// handle, source, deps, version, footer
// 	wp_enqueue_script( 'gc-checkbox-filters', get_template_directory_uri() . '/assets/js/gc-checkbox-filters.min.js', [], '1.0.0', true );
// }

// Use a special Overview template with filters
$templates = [ 'overview-with-filters.twig' ];

// Overload Theme page.php
require_once get_stylesheet_directory() . '/page.php';