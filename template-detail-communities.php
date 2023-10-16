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
	// Set custom page class
	$context['pageclass'] = 'community--' . $current_community_term->slug;
}

$templates               = [ 'community-detail.twig', 'page.twig' ];

/**
 * returns the ID for the community term that is
 * attached to this page in ACF field 'community_detail_select_community_term'
 *
 * @return int
 */
function get_current_community_tax() {
	global $post;

	$term_id = 0;
	if ( get_field( 'community_detail_select_community_term' ) ) {
		$term_id = get_field( 'community_detail_select_community_term' );
	} else {
		$aargh = ' No community attached to this page. ';
		if ( current_user_can( 'editor' ) ) {
			$editlink = get_edit_post_link( $post );
			$aargh    .= '<a href="' . $editlink . '">Please choose the appropriate community to this page.</a>';
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

// DEBUG: see custom ACF fields to this WP_Term..
// $current_community_term_fields = get_fields( $current_community_term );
// if ( $current_community_term_fields ) {
// 	dump( $current_community_term_fields );
// }


if ( $current_community_term && ! is_wp_error( $current_community_term ) ) {

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
