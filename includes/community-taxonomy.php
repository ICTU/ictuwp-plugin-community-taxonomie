<?php
/**
 * Custom Taxonomy: Community
 * -  hierarchical (like 'category')
 *
 * @package GebruikerCentraalTheme
 *
 * @see https://developer.wordpress.org/reference/functions/register_taxonomy/
 * @see https://developer.wordpress.org/reference/functions/get_taxonomy_labels/
 *
 * CONTENTS:
 * - Set GC_COMMUNITY_TAX taxonomy labels
 * - Set GC_COMMUNITY_TAX taxonomy arguments
 * - Register GC_COMMUNITY_TAX taxonomy
 * - public function fn_ictu_community_get_post_community_terms() - Retreive Community terms with custom field data for Post
 * ----------------------------------------------------- */



if ( ! taxonomy_exists( GC_COMMUNITY_TAX ) ) {

	// [1] Community Taxonomy Labels
	$community_tax_labels = [
		'name'                       => _x( 'Community', 'Custom taxonomy labels definition', 'gctheme' ),
		'singular_name'              => _x( 'Community', 'Custom taxonomy labels definition', 'gctheme' ),
		'search_items'               => _x( 'Zoek communities', 'Custom taxonomy labels definition', 'gctheme' ),
		'popular_items'              => _x( 'Populaire communities', 'Custom taxonomy labels definition', 'gctheme' ),
		'all_items'                  => _x( 'Alle communities', 'Custom taxonomy labels definition', 'gctheme' ),
		'edit_item'                  => _x( 'Bewerk community', 'Custom taxonomy labels definition', 'gctheme' ),
		'view_item'                  => _x( 'Bekijk community', 'Custom taxonomy labels definition', 'gctheme' ),
		'update_item'                => _x( 'Community bijwerken', 'Custom taxonomy labels definition', 'gctheme' ),
		'add_new_item'               => _x( 'Voeg nieuw community toe', 'Custom taxonomy labels definition', 'gctheme' ),
		'new_item_name'              => _x( 'Nieuwe community', 'Custom taxonomy labels definition', 'gctheme' ),
		'separate_items_with_commas' => _x( 'Kommagescheiden communities', 'Custom taxonomy labels definition', 'gctheme' ),
		'add_or_remove_items'        => _x( 'Communities toevoegen of verwijderen', 'Custom taxonomy labels definition', 'gctheme' ),
		'choose_from_most_used'      => _x( 'Kies uit de meest-gekozen communities', 'Custom taxonomy labels definition', 'gctheme' ),
		'not_found'                  => _x( 'Geen communities gevonden', 'Custom taxonomy labels definition', 'gctheme' ),
		'no_terms'                   => _x( 'Geen communities gevonden', 'Custom taxonomy labels definition', 'gctheme' ),
		'items_list_navigation'      => _x( 'Navigatie door communitylijst', 'Custom taxonomy labels definition', 'gctheme' ),
		'items_list'                 => _x( 'Communitylijst', 'Custom taxonomy labels definition', 'gctheme' ),
		'item_link'                  => _x( 'Community Link', 'Custom taxonomy labels definition', 'gctheme' ),
		'item_link_description'      => _x( 'Een link naar een Community', 'Custom taxonomy labels definition', 'gctheme' ),
		'menu_name'                  => _x( 'Communities', 'Custom taxonomy labels definition', 'gctheme' ),
		'back_to_items'              => _x( 'Terug naar communities', 'Custom taxonomy labels definition', 'gctheme' ),
		'not_found_in_trash'         => _x( 'Geen communities gevonden in de prullenbak', 'Custom taxonomy labels definition', 'gctheme' ),
		'featured_image'             => _x( 'Uitgelichte afbeelding', 'Custom taxonomy labels definition', 'gctheme' ),
		'archives'                   => _x( 'Community overzicht', 'Custom taxonomy labels definition', 'gctheme' ),
	];

	// [2] Community Taxonomy Arguments
	$community_slug = GC_COMMUNITY_TAX;
	// TODO: discuss if slug should be set to a page with the overview template
	// like so:
	// $community_slug = fn_ictu_community_get_community_overview_page();

	$community_tax_args = [
		"labels"             => $community_tax_labels,
		"label"              => _x( 'Communities', 'Custom taxonomy arguments definition', 'gctheme' ),
		"description"        => _x( 'Communities op het gebied van een gebruikersvriendelijke overheid', 'Custom taxonomy arguments definition', 'gctheme' ),
		"hierarchical"       => true,
		"public"             => true,
		"show_ui"            => true,
		"show_in_menu"       => true,
		"show_in_nav_menus"  => false,
		"query_var"          => false,
		// Needed for tax to appear in Gutenberg editor.
		'show_in_rest'       => true,
		"show_admin_column"  => true,
		// Needed for tax to appear in Gutenberg editor.
		"rewrite"            => [
			'slug'       => $community_slug,
			'with_front' => true,
		],
		"show_in_quick_edit" => true,
	];

	// register the taxonomy with these post types
	$post_types_with_community = [
		'post',
		'page',
		'podcast',
		'session',
		'keynote',
		'speaker',
		'event',
		'video_page'
	];

	// check if the post types exist
	$post_types_with_community = array_filter( $post_types_with_community, 'post_type_exists' );

	// [3] Register our Custom Taxonomy
	register_taxonomy( GC_COMMUNITY_TAX, $post_types_with_community, $community_tax_args );

} // if ( ! taxonomy_exists( GC_COMMUNITY_TAX ) )


/**
 * fn_ictu_community_get_community_terms()
 *
 * 'Community' is a custom taxonomy (tag)
 * It has 2 extra ACF fields for an
 * image and a landingspage
 *
 * This function fills an array of all
 * terms, with their extra fields...
 *
 * If one $community_name is passed it returns only that
 * If $term_args is passed it uses that for the query
 *
 * @see https://developer.wordpress.org/reference/functions/get_terms/
 * @see https://www.advancedcustomfields.com/resources/adding-fields-taxonomy-term/
 * @see https://developer.wordpress.org/reference/classes/wp_term_query/__construct/
 *
 * @param String $community_name Specific term name/slug to query
 * @param Array $community_args Specific term query Arguments to use
 */


function fn_ictu_community_get_community_terms( $community_name = null, $term_args = null ) {

	// TODO: I foresee that editors will want to have a custom order to the taxonomy terms
	// but for now the terms are ordered alphabetically
	$community_taxonomy = GC_COMMUNITY_TAX;
	$community_terms    = [];
	$community_query    = is_array( $term_args ) ? $term_args : [
		'taxonomy'   => $community_taxonomy,
		// We also want Terms with NO linked content, in this case
		'hide_empty' => false
	];

	// Query specific term name
	if ( ! empty( $community_name ) ) {
		// If we find a Space, or an Uppercase letter, we assume `name`
		// otherwise we use `slug`
		$RE_disqualify_slug                  = "/[\sA-Z]/";
		$query_prop_type                     = preg_match( $RE_disqualify_slug, $community_name ) ? 'name' : 'slug';
		$community_query[ $query_prop_type ] = $community_name;
	}

	$found_community_terms = get_terms( $community_query );

	if ( is_array( $found_community_terms ) && ! empty( $found_community_terms ) ) {
		// Add our custom Fields to each found WP_Term instance
		// And add to $community_terms[]
		foreach ( $found_community_terms as $community_term ) {
			$community_term_fields = get_fields( $community_term );
			if ( is_array( $community_term_fields ) ) {
				foreach ( $community_term_fields as $key => $val ) {

					// Add path to image url
					if( $key == 'community_taxonomy_visual' && defined( 'GC_COMMUNITY_TAX_VISUALS_PATH' ) ) {
						$val = sprintf(
							'<img width="800" height="450" src="%s/%s" class="community-taxonomy-visual" alt="" decoding="async" loading="lazy" />',
							GC_COMMUNITY_TAX_VISUALS_PATH,
							$val
						);
					}

					$community_term->$key = $val;
				}
			}
			$community_terms[] = $community_term;
		}
	}

	return $community_terms;
}

/**
 * fn_ictu_community_get_post_community_terms()
 *
 * This function fills an array of all
 * terms, with their extra fields _for a specific Post_...
 *
 * - Only top-lever Terms
 * - 1 by default
 *
 * used in [themes]/ictuwp-theme-gc2020/includes/gc-fill-context-with-acf-fields.php
 *
 * @param String|Number $post_id Post to retrieve linked terms for
 *
 * @return Array        Array of WPTerm Objects with extra ACF fields
 */
function fn_ictu_community_get_post_community_terms( $post_id = null, $term_number = 1 ) {
	$return_terms = [];
	if ( ! $post_id ) {
		return $return_terms;
	}

	$post_community_terms = wp_get_post_terms( $post_id, GC_COMMUNITY_TAX, [
		'taxonomy'   => GC_COMMUNITY_TAX,
		'number'     => $term_number, // Return max $term_number Terms
		'hide_empty' => true,
		'parent'     => 0,
		'fields'      => 'names' // Only return names (to use in `fn_ictu_community_get_community_terms()`)
	] );

	foreach ( $post_community_terms as $_term ) {
		$full_post_community_term = fn_ictu_community_get_community_terms( $_term );
		if ( ! empty( $full_post_community_term ) ) {
			$return_terms[] = $full_post_community_term[0];
		}
	}

	return $return_terms;
}
