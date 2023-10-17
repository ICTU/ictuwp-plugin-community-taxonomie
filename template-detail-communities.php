<?php
/**
 * Template Name: Template Detail Communities
 *
 * @package    WordPress
 * @subpackage Timber
 * @since      Timber 0.1
 */

$timber_post             = new Timber\Post();

$current_community_taxid = get_current_community_tax();
$current_community_term  = get_term_by( 'id', $current_community_taxid, GC_COMMUNITY_TAX );

$context                 = Timber::context();
$context['post']         = $timber_post;
$context['modifier']      = 'community-detail';
$context['is_unboxed']   = true;

if ( $current_community_term && ! is_wp_error( $current_community_term ) ) {
	// Update body class
	$context['body_class'] = ($context['body_class'] ?: '') . ' community--' . $current_community_term->slug;
}

$templates                 = [ 'community-detail.twig', 'page.twig' ];

/**
 * returns the ID for the community term that is
 * attached to this page in ACF field 'community_detail_select_community_term'
 *
 * @return int
 */
function get_current_community_tax() {
	global $post;

	$term_id = get_field( 'community_detail_select_community_term' ) ?: 0;
	if ( !$term_id ) {
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
			$context['body_class'] = ($context['body_class'] ?: '') . ' colorscheme--' . $context['colorscheme'];
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
			wp_redirect( $current_community_term_fields['community_taxonomy_link']['url'] );
			exit;
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
	$timber_post->post_title = $current_community_term->name;

	// text for 'inleiding' is taken from term description
	// $timber_post->post_content = $current_community_term->description;
	
	// Use Intro instead?!
	$context['intro'] = wpautop( $current_community_term->description );

	/**
	 *  Events box
	 * ----------------------------- */
	 // Only show events if Events Manager plugin is active
	if ( class_exists( 'EM_Events' ) ) {

		// // TEST: render block pattern
		// // Challenge: how to fill in custom data?
		// $agenda_pattern_name = 'gc/section-agenda';

		// if ( class_exists( 'WP_Block_Patterns_Registry' ) ) {

		// 	$block_pattern_registry = WP_Block_Patterns_Registry::get_instance();

		// 	if ( $block_pattern_registry ) {
		// 		$agenda_section_pattern = $block_pattern_registry->get_registered( $agenda_pattern_name );
		// 		if ( $agenda_section_pattern ) {
		// 			// Add our pattern HTML to our metabox_content context
		// 			$context['metabox_content'] = ( $context['metabox_content'] ?? '' ) . do_blocks( $agenda_section_pattern['content'] );

		// 			// dump(
		// 			// 	$agenda_section_pattern
		// 			// 	// do_blocks( $agenda_section_pattern['content'] )
		// 			// 	// $agenda_section_pattern,
		// 			// 	// $parsed_agenda_section_pattern,
		// 			// 	// render_block( $parsed_agenda_section_pattern )
		// 			// );
		// 		}
		// 	}
	
		// }

	}

}


Timber::render( $templates, $context );
