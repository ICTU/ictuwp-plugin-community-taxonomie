<?php
/**
 * Template Name: [Community] overzicht
 *
 * @package    WordPress
 * @subpackage Timber v2
 */

$context                       = Timber::context();
$context['post']               = Timber::get_post();
$context['modifier']            = 'community-overview';
$context['item_type']          = 'community-overview'; // Pass item_type to grid section (adds ID to grid)
$context['has_centered_intro'] = false;

// TODO: implement Query Filters ($context['query_filters']) (like template-instumenten-tt.php)?

/**
 * Add communities (terms in Community taxonomy)
 */
if ( function_exists( 'fn_ictu_community_get_community_terms' ) ) {
	$community_items = [];

	// Fill items (cards) for overview template

	// NOTE [1]:
	// fn_ictu_community_get_community_terms() returns
	// an array of WP_Term objects, ordered by `name`.
	//
	// We want to order the Communities alphabetically
	// based on their name. But we use the linked Page Title
	// for the actual display. So really, we expect to order
	// based on Term -> Page -> Title
	// This is why we need to re-order the array
	// here, after we've retrieved the page title
	// in the loop below.

	// skip any community terms that do not have any content attached
	$select_args = array(
		'taxonomy'   => GC_COMMUNITY_TAX,
		// NO Terms with NO linked content
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	);

	foreach ( fn_ictu_community_get_community_terms( null, $select_args ) as $community ) {

		/**
		 * $community is a WP_Term object
		 * `term_id`                        {Integer} ID of Term
		 * `name`                           {String}  Name of Term
		 * `slug`                           {String}  Slug of Term
		 * `description`                    {String}  Description of Term
		 *
		 * .. etc..
		 *
		 * ..BUT also with added ACF fields as properties!
		 * (These could be empty)
		 *
		 * `community_taxonomy_colorscheme` {String}  Name of colorscheme, eg. "pink"
		 * `community_taxonomy_visualm`     {Array}   Path to Community image
		 * `community_taxonomy_link`        {Array}   Link object (title, url, target)
		 * `community_taxonomy_page`        {Integer} ID of linked page
		 */

		$item = array(
			// type, translates to card-type--{type}
			'type'  => 'community',
			// Title default: term name
			'title' => $community->name,
			'slug' => $community->slug,
			// Descr default: term description
			'descr' => $community->description,
		);

		// Setup properties to use for Card
		$item_url = null;
		$item_img = null;

		// Linked Landingpage
		if ( empty( $community->community_taxonomy_page ) ) {
			// We do NOT have a linked Page
			// Abort *unless* we are viewing the page logged-in
			// (in that case we show a warning)
			if ( ! is_user_logged_in() ) {
				// We do not show the card
				continue;
			} else {
				// we show the card, but with a warning
				$item['type']  = $item['type'] . ' card--has-warning';
				$item['descr'] = sprintf(
					'<a style="color:red" href="%s">%s</a>',
					get_edit_term_link( $community->term_id, GC_COMMUNITY_TAX ),
					_x('Deze card is verborgen totdat een pagina wordt gekoppeld aan deze Community!', 'Community overview: card warning', 'gctheme'),
				);
			}

		} else {
			// Fetch page from ID
			$item_page = get_post( $community->community_taxonomy_page );
			// If no ID or invalid, get_post _could_ return the *current* page..
			if ( $item_page instanceof WP_Post ) {
				$item_page_id = $item_page->ID;
				// .. so check if it has the correct template
				$item_page_template    = get_post_meta( $item_page_id, '_wp_page_template', true );
				// .. and if page is published
				$item_page_status      = get_post_status( $item_page_id );

				$item_page_template_ok = GC_COMMUNITY_TAX_DETAIL_TEMPLATE === $item_page_template;
				$item_page_status_ok   = 'publish' === $item_page_status;

				// Only continue if page has the correct template and is published
				if ( $item_page_template_ok && $item_page_status_ok ) {
					// Set Card image from page thumbnail
					$item_img      = get_the_post_thumbnail_url( $item_page, 'image-16x9' ) ?: $item_img;
					// Override: card Title from page link
					$item['title'] = get_the_title( $item_page );
					// Set Card url from page link
					$item_url      = get_page_link( $item_page );
					// the sort order is based on the slug of the page (which is *probably* based on the page title)
					$item['slug'] = $item_page->post_name;
					// Override: card Description from:
					// - the page excerpt (if set)
					// - else: the page 00 - intro (if set)
					// - else: the term descr
					if ( $item_page->post_excerpt ) {
						// only use the excerpt if the field is actually filled
						$item['descr'] = get_the_excerpt( $item_page );
					} elseif ( get_field( 'post_inleiding', $item_page ) ) {
						$item['descr'] = get_field( 'post_inleiding', $item_page );
					}
					// No else needed for Term description as this was the default already..
				} else {
					// Not a proper page?
					// Show warning *when* we are viewing the page logged-in
					if ( ! is_user_logged_in() ) {
						// We do not show the card
						continue;
					} else {
						// we show the card, but with a warning
						// (optionally check specific error with: $item_page_template_ok and $item_page_status_ok)
						$item['type']  = $item['type'] . ' card--has-warning';
						$item['descr'] = sprintf(
							'<a style="color:red" href="%s">%s</a>',
							get_edit_post_link( $item_page, 'edit' ),
							_x('Deze card is verborgen totdat de pagina het  \'<em>[Community] detailpagina</em>\' template krijgt en gepubliceerd wordt!', 'Community overview: card warning', 'gctheme'),
						);
					}
				}
			}
		}

		// Visual
		if ( $community->community_taxonomy_visual ) {
			// Store the `visual` in the Card item
			$item['visual'] = $community->community_taxonomy_visual;
			// Use visual as card image only if no other image is set
			if ( ! $item_img ) {
				$item_img = $community->community_taxonomy_visual;
			}
		}

		// Colorscheme:
		if ( $community->community_taxonomy_colorscheme ) {
			// Store the `palette` in the Card item
			$item['palette'] = $community->community_taxonomy_colorscheme;
			// Add extra class to card-type
			$item['type'] = $item['type'] . ' palette--' . $item['palette'];
		}

		// Link: link to subsite?
		if ( $community->community_taxonomy_link ) {
			// Store the `link` in the Card item
			// NOTE: this is NOT the Card URL.
			// We do not link to a subsite directly,
			// but _always_ refer to the landing page (URL) first..
			$item['link'] = $community->community_taxonomy_link;
		}

		// Use preferred image for card
		if ( $item_img ) {
			$item['img'] = '<img src="' . $item_img . '" alt=""/>';
		}

		// Use preferred URL for card
		if ( $item_url ) {
			$item['url'] = $item_url;
		}

		// Fill all items with new item
		$community_items[] = $item;

	}

	// NOTE [1]:
	// Re-order the Communities alphabetically
	// based on their linked slug (this is oftentimes derived from the Page Title).
	// They were originally ordered by Term name
	usort( $community_items, function( $a, $b ) {
		return strcmp( strtoupper( $a['slug'] ), strtoupper( $b['slug'] ) );
	} );

	$context['items'] = $community_items;

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