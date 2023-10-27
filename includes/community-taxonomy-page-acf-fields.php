<?php
/**
 * ACF fields for the Community Taxonomy Detail page template
 */
if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

// Remove default Community Taxonomy metabox from side
// of Community Taxonomy Detail pages
// (remove_meta_box() does not work in GB) so we need JS:
add_action( 'admin_enqueue_scripts', 'gc_community_admin_scripts' );
 
function gc_community_admin_scripts() {
	global $post;
	if ( $post ) {
		// Do we have a post of whatever kind at hand?
		// Get template name; this will only work for pages, obviously
		$page_template = get_post_meta( $post->ID, '_wp_page_template', true );

		if ( ( GC_COMMUNITY_TAX_OVERVIEW_TEMPLATE === $page_template ) || ( GC_COMMUNITY_TAX_DETAIL_TEMPLATE === $page_template ) ) {
			// Enqueue GB JS that hides the Community Taxonomy side panel
			wp_enqueue_script( 'gc-community-editor', GC_COMMUNITY_TAX_ASSETS_PATH . '/scripts/gc-community-editor.js' );
		}
	}
}

// Add Custom ACF MetaBox for coupling a Community Term to a Page
acf_add_local_field_group( array(
	'key' => 'group_6526b1c486f7e',
	'title' => 'Community detail: selecteer community',
	'fields' => array(
		array(
			'key' => 'field_6526b1c436369',
			'label' => 'Selecteer de community voor deze pagina',
			'name' => 'community_detail_select_community_term',
			'aria-label' => '',
			'type' => 'taxonomy',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'taxonomy' => 'community',
			'add_term' => 1,
			'save_terms' => 0,
			'load_terms' => 0,
			'return_format' => 'id',
			'field_type' => 'select',
			'bidirectional' => 0,
			'multiple' => 0,
			'allow_null' => 0,
			'bidirectional_target' => array(
			),
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'page_template',
				'operator' => '==',
				'value' => 'template-detail-communities.php',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => 0,
) );
