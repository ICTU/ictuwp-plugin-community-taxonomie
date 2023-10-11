<?php
/**
 * ACF fields for the Community Taxonomy Detail page template
 */
// add_action( 'acf/include_fields', function() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

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
// } );

