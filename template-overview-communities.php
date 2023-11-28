<?php
/**
 * Template Name: [Community] overzicht
 *
 * @package    WordPress
 * @subpackage Timber
 * @since      Timber 0.1
 */

$context                       = Timber::context();
$context['post']               = new Timber\Post();
$context['modifier']            = 'community-overview';
$context['item_type']          = 'community-overview'; // Pass item_type to grid section (adds ID to grid)
$context['has_centered_intro'] = false;

// TODO: implement Query Filters ($context['query_filters']) (like template-instumenten-tt.php)?

/**
 * Add communities (terms in Community taxonomy)
 */
if ( function_exists( 'fn_ictu_community_get_community_terms' ) ) {
	$context['items']    = [];

	foreach ( fn_ictu_community_get_community_terms() as $community ) {

		$item = array(
			// type, translates to card-type--{type}
			'type'  => 'community',
			// Title defaults to term name
			'title' => $community->name,
			// Descr defaults to term description
			'descr' => $community->description,
		);

		// Setup image to use in Card
		$item_img = null;

		// Linked Landingpage
		if ( $community->community_taxonomy_page ) {
			// Fetch page from ID
			$item_page = get_post( $community->community_taxonomy_page );
			// If no ID or invalid, get_post _could_ return the *current* page..
			if ( $item_page instanceof WP_Post ) {
				$item_page_id = $item_page->ID;
				// .. so check if it has the correct template
				$item_page_template = get_post_meta( $item_page_id, '_wp_page_template', true );
				if ( GC_COMMUNITY_TAX_DETAIL_TEMPLATE === $item_page_template ) {
					// Set Card image from page thumbnail
					$item_img      = get_the_post_thumbnail_url( $item_page, 'image-16x9' ) ?: $item_img;
					// Override: card Title from page link
					$item['title'] = get_the_title( $item_page );
					// Set Card url from page link
					$item['url']   = get_page_link( $item_page );
					// Override: card Description from page intro
					// - the page excerpt (if set)
					// - else: the term descr (set by default, could be empty)
					// - else: the page 00 - intro
					$item_page_excerpt   = get_the_excerpt( $item_page );
					$item_page_inleiding = get_field( 'post_inleiding', $item_page );
					if ( ! empty( $item_page_excerpt ) ) {
						$item['descr'] = $item_page_excerpt;
					} elseif ( empty( $item['descr'] && ! empty( $item_page_inleiding ) ) ) {
						$item['descr'] = $item_page_inleiding;
					}
				}
			}
		}

		// Image:
		if ( $community->community_taxonomy_image ) {
			$item_img = '<img src="' . $community->community_taxonomy_image['sizes']['image-16x9'] . '" alt=""/>';
		}

		// Visual
		if ( $community->community_taxonomy_visual ) {
			$item['visual'] = $community->community_taxonomy_visual;
			// Use visual as featured image if no other image is set
			if ( ! $item_img ) {
				$item_img = $community->community_taxonomy_visual;
			}
		}

		// Finally use preferred image for card
		if ( $item_img ) {
			$item['img'] = '<img src="' . $item_img . '" alt=""/>';
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