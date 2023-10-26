<?php
/**
 * Template Name: Template Detail Communities
 *
 * @package    WordPress
 * @subpackage Timber
 * @since      Timber 0.1
 */

$timber_post = new Timber\Post();

$current_community_taxid = get_current_community_tax();
$current_community_term  = get_term_by( 'id', $current_community_taxid, GC_COMMUNITY_TAX );

$context               = Timber::context();
$context['post']       = $timber_post;
$context['modifier']   = 'community-detail';
$context['is_unboxed'] = true;

if ( $current_community_term && ! is_wp_error( $current_community_term ) ) {
	// Update body class
	$context['body_class'] = ( $context['body_class'] ?: '' ) . ' community--' . $current_community_term->slug;
}

$templates = [ 'community-detail.twig', 'page.twig' ];

/**
 * returns the ID for the community term that is
 * attached to this page in ACF field 'community_detail_select_community_term'
 *
 * @return int
 */
function get_current_community_tax() {
	global $post;

	$term_id = get_field( 'community_detail_select_community_term' ) ?: 0;
	if ( ! $term_id ) {
		$aargh = _x( 'Geen community gekoppeld aan deze pagina. ', 'Community taxonomy error message', 'gctheme' );
		if ( current_user_can( 'editor' ) ) {
			$editlink = get_edit_post_link( $post );
			$aargh    .= '<a href="' . $editlink . '">' . _x( 'Kies een relevante community voor deze pagina. ', 'Community taxonomy error message', 'gctheme' ) . '</a>';
		}
		die( $aargh );
	}

	return $term_id;
}

/**
 * Fill Timber $context with available page/post Blocks/Metaboxes
 * @see /includes/gc-fill-context-with-acf-fields.php
 */
if ( function_exists( 'gc_fill_context_with_acf_fields' ) ) {
	$context = gc_fill_context_with_acf_fields( $context );
}

// We have a valid Community Term for this page
if ( $current_community_term && ! is_wp_error( $current_community_term ) ) {

	// Get custom ACF fields for this WP_Term..
	// filter out 'empty' or nullish values
	$current_community_term_fields = array_filter(
		get_fields( $current_community_term ) ?: [],
		function ( $field ) {
			return ! empty( $field );
		}
	);

	if ( $current_community_term_fields ) {
		// We have some custom ACF fields for this Term

		// If we have a colorscheme:
		if ( isset( $current_community_term_fields['community_taxonomy_colorscheme'] ) ) {
			// .. store it in $context
			$context['colorscheme'] = $current_community_term_fields['community_taxonomy_colorscheme'];
			// // .. enqueue colorscheme CSS
			// wp_enqueue_style( $context['colorscheme'] . '-theme', get_stylesheet_directory_uri() . '/assets/css/' . $context['colorscheme'] . '-theme.css', ['gc-flavor'], 'doit', 'all' );
			// .. update body class
			$context['body_class'] = ( $context['body_class'] ?: '' ) . ' colorscheme--' . $context['colorscheme'];
		}

		// If we have a visual, store it in $context
		if ( isset( $current_community_term_fields['community_taxonomy_visual'] ) ) {
			// Story complete Path to image (if available)
			$context['visual'] = $current_community_term_fields['community_taxonomy_visual'];
			if ( defined( 'GC_COMMUNITY_TAX_ASSETS_PATH' ) ) {
				$context['visual'] = sprintf( '%s/images/%s', GC_COMMUNITY_TAX_ASSETS_PATH, $context['visual'] );
			}
		}

		// If we have an extra Community Link
		// we redirect to that URL instead of loading our detail template
		if ( isset( $current_community_term_fields['community_taxonomy_link'] ) ) {
			$context['community_link'] = $current_community_term_fields['community_taxonomy_link'];
		}
	}

	// Fallback: Term VISUAL
	if ( ! array_key_exists( 'visual', $context ) ) {
		$context['visual'] = sprintf( '%s/images/', GC_COMMUNITY_TAX_ASSETS_PATH ) . 'c-default.svg';
	}

	// // Fallback: Term COLORSCHEME
	// if ( ! array_key_exists( 'colorscheme', $context ) && function_exists( 'gc_get_colorschemes' ) ) {
	// 	$available_color_themes = gc_get_colorschemes();
	// 	$context['colorscheme'] = $available_color_themes ? reset( $available_color_themes ) : 'green';
	// }

	// CONTENT
	// -----------------------------

	// // move content from editor to metabox_freehandblocks
	// $blocks = parse_blocks( $timber_post->post_content );
	// foreach ( $blocks as $block ) {
	// 	if ( isset( $block['blockName'] ) && $block['blockName'] !== null ) {
	// 		$context['metabox_content'] = ( $context['metabox_content'] ?? '' ) . render_block( $block );
	// 	}
	// }

	// Page title is taken from term name
	// -----------------------------
	$timber_post->post_title = $current_community_term->name;

	// text for 'inleiding' is taken from term description
	// -----------------------------
	// $timber_post->post_content = $current_community_term->description;
	// Use Intro instead?!
	$context['intro'] = wpautop( $current_community_term->description );
	
	/**
	 *  Intro Text (Metabox)
	 * ----------------------------- */
	$metabox_intro_text = get_field( 'metabox_intro_text' );
	if ( ! empty( $metabox_intro_text ) ) {
		$context['metabox_intro_text'] = $metabox_intro_text;
	}


	/**
	 *  Events box
	 * ----------------------------- */
	// Only show events if Events Manager plugin is active
	if ( class_exists( 'EM_Events' ) ) {
		// the events manager is active, which helps for selecting events

		$metabox_fields = get_field( 'events' );

		if ( $metabox_fields && 'ja' === $metabox_fields['metabox_events_show_or_not'] ) {

			$maxnr            = $metabox_fields['metabox_events_max_nr'] ?? 3;
			$metabox_item_ids = array();

			// select latest events for $current_community_taxid
			// _event_start_date is a meta field for the events post type
			// this query selects future events for the $current_community_taxid
			$currentdate = date( "Y-m-d" );
			$args        = array(
				'posts_per_page' => $maxnr,
				'post_type'      => EM_POST_TYPE_EVENT,
				'meta_key'       => '_event_start_date',
				'orderby'        => 'meta_value_num',
				'post_status'    => 'publish',
				'order'          => 'ASC', // order by start date ascending
				'fields'         => 'ids', // only return IDs
				'tax_query'      => array(
					array(
						'taxonomy' => GC_COMMUNITY_TAX,
						'field'    => 'term_id',
						'terms'    => $current_community_term->term_id,
					)
				),
				'meta_query'     => array(
					array(
						'key'     => '_event_start_date',
						'value'   => $currentdate,
						'compare' => '>=',
						'type'    => 'DATE',
					),
				)
			);
			$query_items = new WP_Query( $args );

			if ( $query_items->have_posts() ) {
				// we only use post ids for the $metabox_item_ids array
				$metabox_item_ids = $query_items->posts;
			}

			// ensure to reset the main query to original main query
			wp_reset_query();

			if ( $metabox_item_ids ) {
				// we have events
				$context['metabox_events']          = [];
				$context['metabox_events']['items'] = [];
				$context['metabox_events']['title'] = $metabox_fields['metabox_events_titel'] ?? '';
				$context['metabox_events']['descr'] = $metabox_fields['metabox_events_description'] ?? '';
				$url                                = $metabox_fields['metabox_events_url_overview'] ?? [];

				// Add CTA 'overzichtslink' as cta Array to metabox_events
				if ( $url && isset( $url['title'] ) && isset( $url['url'] ) ) {
					$context['metabox_events']['cta']          = [];
					$context['metabox_events']['cta']['title'] = $url['title'];
					$context['metabox_events']['cta']['url']   = $url['url'];
				}

				foreach ( $metabox_item_ids as $post_id ) {

					$item                                 = prepare_card_content( get_post( $post_id ) );
					$context['metabox_events']['items'][] = $item;
				}
				$context['metabox_events']['columncounter'] = count( $context['metabox_events']['items'] );
			}
		}

	}

	/**
	 * Posts box
	 * ----------------------------- */
	$metabox_fields = get_field( 'berichten' );

	if ( $metabox_fields && 'ja' === $metabox_fields['metabox_posts_show_or_not'] ) {

		$method           = $metabox_fields['metabox_posts_selection_method'] ?? '';
		$maxnr            = 3; // todo TBD: should this be a user editable field?
		$metabox_item_ids = array();

		if ( 'manual' === $method ) {
			// manually selected events, returns an array of posts
			$metabox_item_ids = $metabox_fields['metabox_posts_selection_manual'];

		} else {
			$args = array(
				'posts_per_page' => $maxnr,
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'fields'         => 'ids', // only return IDs
				'tax_query'      => array(
					array(
						'taxonomy' => GC_COMMUNITY_TAX,
						'field'    => 'term_id',
						'terms'    => $current_community_term->term_id,
					)
				)
			);

			$query_items = new WP_Query( $args );
			if ( $query_items->have_posts() ) {
				// we only use post ids for the $metabox_item_ids array
				$metabox_item_ids = $query_items->posts;
			}

			// ensure to reset the main query to original main query
			wp_reset_query();
		}

		if ( $metabox_item_ids ) {

			$context['metabox_posts']                = [];
			$context['metabox_posts']['items']       = [];
			$context['metabox_posts']['cta']         = [];
			$context['metabox_posts']['title']       = $metabox_fields['metabox_posts_titel'] ?? '';
			$context['metabox_posts']['description'] = $metabox_fields['metabox_posts_description'] ?? '';
			$url                                     = $metabox_fields['metabox_posts_url_overview'] ?? [];

			// Add click through link for all posts
			if ( $url ) {
				// manually added CTA 'overzichtslink'
				$context['metabox_posts']['cta']['title'] = $url['title'];
				$context['metabox_posts']['cta']['url']   = $url['url'];
			} else {
				// automagically add link to LLK page for posts
				$template = 'template-llk-posts.php';
				$pages    = get_posts( array(
					'post_type'  => 'page',
					'fields'     => 'ids',
					'meta_key'   => '_wp_page_template',
					'meta_value' => $template
				) );

				if ( $pages && $pages[0] ) {
					// a relevant LLK page was found

					$context['metabox_posts']['cta']['title'] = _x( 'Bekijk alle berichten', 'Linktekst voor LLK pagina met berichten', 'gctheme' );

					// see if we can add GC_COMMUNITY_TAX as extra filter
					$term_info = get_term_by( 'id', $current_community_taxid, GC_COMMUNITY_TAX );

					if ( $term_info && ! is_wp_error( $term_info ) ) {
						// append community slug to LLK link
						// TODO: FIXME: GC_QUERYVAR_COMMUNITY is not defined?
						$item_url_vars                          = [ ( defined( 'GC_QUERYVAR_COMMUNITY' ) ? GC_QUERYVAR_COMMUNITY : 'community' ) => $term_info->slug ];
						$context['metabox_posts']['cta']['url'] = add_query_arg( $item_url_vars, get_permalink( $pages[0] ) );
					} else {
						// just use the permalink
						$context['metabox_posts']['cta']['url'] = get_permalink( $pages[0] );
					}
				} else {
					// no manual link added, no page found.
					// so: no link
				}
			}

			foreach ( $metabox_item_ids as $post_id ) {

				$item  = prepare_card_content( get_post( $post_id ) );
				$image = get_the_post_thumbnail_url( $post_id, IMAGESIZE_16x9 );
				if ( $image ) {
					// decorative image, no value for alt attr.
					$item['img'] = '<img src="' . $image . '" alt="" />';
					// Provide Image as URL instead of HTML?
					// $item['img']     = $image;
					// $item['img_alt'] = '';
				}
				$context['metabox_posts']['items'][] = $item;
			}
			$context['metabox_posts']['columncounter'] = count( $context['metabox_posts']['items'] );
		}
	}

	/**
	 * Contact form box
	 * (actually it's a Gravity Forms form)
	 * 
	 * $context['metabox_contactform'] is an Array() with:
	 * - $context['metabox_contactform]['form']
	 * - $context['metabox_contactform]['title']
	 * - $context['metabox_contactform]['description']
	 * ----------------------------- */
	$metabox_contactform_id = get_field( 'metabox_contactform_id' );
	if ( $metabox_contactform_id ) {
		$gravityforms_block = '<!-- wp:gravityforms/form {"formId":"' . $metabox_contactform_id . '"} /-->';
		$parsed_blocks      = do_blocks( $gravityforms_block );

		if ( $parsed_blocks ) {
			$context['metabox_contactform'] = [];
			$context['metabox_contactform']['form'] = $parsed_blocks;

			// We have a form, now try and get the form title
			// and description from the MetaBox fields
			$metabox_contactform_title       = get_field( 'metabox_contactform_title' );
			$metabox_contactform_description = get_field( 'metabox_contactform_description' );

			if ( $metabox_contactform_title ) {
				$context['metabox_contactform']['title'] = $metabox_contactform_title ;
			}

			if ( $metabox_contactform_description ) {
				$context['metabox_contactform']['description'] = $metabox_contactform_description ;
			}
		}
	}

}


Timber::render( $templates, $context );
