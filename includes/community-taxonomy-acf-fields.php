<?php
/**
 * GC ACF Fields for: Community Taxonomy
 *
 * ACF fields for `community` taxonomy
 *
 * @see https://www.advancedcustomfields.com/resources/register-fields-via-php/
 *
 * - It is important to remember that each field group’s key and each field’s key must be unique.
 * The key is a reference for ACF to find, save and load data. If 2 fields or 2 groups are added using
 * the same key, the later will override the original.
 *
 * - Field Groups and Fields registered via code will NOT be visible/editable via
 * the “Edit Field Groups” admin page.
 *
 * Initialize with eg:
 * add_action('acf/init', 'my_acf_add_local_field_groups');
 *
 */

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

// OPTIONAL: use current flavor
// ----------------------------
// $get_theme_option = get_option( 'gc2020_theme_options' );
// $currentflavor    = isset( $get_theme_option['flavor_select'] ) ? $get_theme_option['flavor_select'] : 'GC';
// ----------------------------

// Available Taxonomy visuals
// ----------------------------
// Base path for existing community taxonomy visuals
$VISUALS_BASE_IMG = '<img src="' . GC_COMMUNITY_TAX_ASSETS_PATH . '/images/%s" width="75" height="50" class="community-taxonomy-visual" alt="" />%s';

$visuals = array(
	'c-default.svg' => 'Standaard',
);
$available_visuals = glob(__DIR__ . '/../assets/images/*.svg');
if( $available_visuals ) {

	foreach ( $available_visuals as $key => $val ) {

		// Skip existing default
		if ( $val !== 'c-default.svg' ) {

			$visual_filename = preg_replace( '/.*\/assets\/images\//i', '', $val );

			if ( $visual_filename ) {

				// $visual_filekey = str_replace( array( 'c-', '.svg' ), '', $visual_filename );
				// $visuals[$visual_filekey] = $visual_filename;

				$visual_label = str_replace( array( 'c-', '.svg' ), '', $visual_filename );
				$visual_label = str_replace( '-', ' ', $visual_label );
				$visual_label = ucwords( $visual_label );

				// Specific Exceptions
				// 'Default' => 'Standaard',
				if ( $visual_label == 'Default' ) { $visual_label = 'Standaard'; }
				// Two-letter acronyms? => Uppercase
				if ( strlen( $visual_label ) == 2 ) { $visual_label = strtoupper( $visual_label ); }

				$visuals[$visual_filename] = addslashes( sprintf( $VISUALS_BASE_IMG, $visual_filename, $visual_label ) );

			}

		}

	}

}

// Setup available color schemes
// ----------------------------
$color_themes = array(
	'default' => '<span class="swatch swatch--green">Standaard</span>',
);
if ( function_exists( 'gc_get_colorschemes' ) ) {
	$available_color_themes = gc_get_colorschemes();
	foreach ( $available_color_themes as $key => $val ) {
		$color_themes[ $key ] = '<span class="swatch swatch--' . $key . '">' . $val['name'] . ' ' . _x( 'Kleurenschema', 'Community taxonomy ACF field definition', 'gctheme' ) . '</span>';
	}

	// hardcode
	// If we have a `green` color, set that as default
	// and remove the `default` color
	if ( isset( $color_themes['green'] ) ) {
		unset( $color_themes['default'] );
	}

}

// Add the field group
acf_add_local_field_group( array(
	'key' => 'group_654a4b2352a09',
	'title' => 'GC - Community taxonomy',
	'fields' => array(
		array(
			'key' => 'field_654a52adade74',
			'label' => 'Communitypagina',
			'name' => 'community_taxonomy_page',
			'aria-label' => '',
			'type' => 'post_object',
			'instructions' => 'Deze pagina zal worden getoond als een overzichtspagina met alle informatie over de community.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'post_type' => array(
				0 => 'page',
			),
			'post_status' => '',
			'taxonomy' => '',
			'return_format' => 'id',
			'multiple' => 0,
			'allow_null' => 0,
			'bidirectional' => 1,
			'bidirectional_target' => array(
				0 => 'field_6526b1c436369',
			),
			'ui' => 1,
		),
		array(
			'key' => 'field_654a4b24fc3cd',
			'label' => 'Colorscheme',
			'name' => 'community_taxonomy_colorscheme',
			'aria-label' => '',
			'type' => 'radio',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => $color_themes,
			'default_value' => isset( $color_themes['green'] ) ? 'green' : 'default',
			'return_format' => 'value',
			'allow_null' => 0,
			'other_choice' => 0,
			'layout' => 'vertical',
			'save_other_choice' => 0,
		),
		array(
			'key' => 'field_654a4be4fc3ce',
			'label' => 'Visual',
			'name' => 'community_taxonomy_visual',
			'aria-label' => '',
			'type' => 'radio',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => $visuals,
			// 'choices' => array(
			// 	'default' 						=> addslashes( sprintf( $VISUALS_BASE_IMG, 'c-default.svg', 'Standaard Community Afbeelding' ) ),
			// 	'cx' 							=> addslashes( sprintf( $VISUALS_BASE_IMG, 'c-cx.svg', 'CX' ) ),
			// 	'design-thinking' 				=> addslashes( sprintf( $VISUALS_BASE_IMG, 'c-design-thinking.svg', 'Design Thinking' ) ),
			// 	'omnichannel' 					=> addslashes( sprintf( $VISUALS_BASE_IMG, 'c-omnichannel.svg', 'Omnichannel' ) ),
			// 	// 'direct-duidelijk' 				=> addslashes( sprintf( $VISUALS_BASE_IMG, 'c-direct-duidelijk.svg', 'Direct Duidelijk' ) ),
			// 	// 'inclusieve-dienstverlening' 	=> addslashes( sprintf( $VISUALS_BASE_IMG, 'c-inclusieve-dienstverlening.svg', 'Inclusieve Dienstverlening' ) ),
			// 	'online-formulieren'			=> addslashes( sprintf( $VISUALS_BASE_IMG, 'c-online-formulieren.svg', 'Online Formulieren' ) ),
			// 	'user-centric-cities'			=> addslashes( sprintf( $VISUALS_BASE_IMG, 'c-usercentricities.svg', 'User-centric Cities' ) ),

			// ),
			'default_value' => 'default',
			'return_format' => 'value',
			'allow_null' => 0,
			'other_choice' => 0,
			'layout' => 'vertical',
			'save_other_choice' => 0,
		),
		array(
			'key' => 'field_654a4c2afc3cf',
			'label' => 'Link',
			'name' => 'community_taxonomy_link',
			'aria-label' => '',
			'type' => 'link',
			'instructions' => '(Optionele) link naar bijvoorbeeld een subsite',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'return_format' => 'array',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'taxonomy',
				'operator' => '==',
				'value' => GC_COMMUNITY_TAX,
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


/**
 * The following appends CSS styles to the global
 * ACF styles. This is needed to customize our added <images>
 * (Somehow adding these styles to editor-styles did not work?)
 */
function gc_add_tax_admin_css () {
	$css = '
		.community-taxonomy-visual,
		.swatch::before {
			display: inline-block;
			vertical-align: middle;
			margin-inline-end: .5rem;
		}
		.swatch::before {
			content: "";
			width: 32px;
			height: 32px;
			border: 2px solid white;
			background-color: white;
			background-image: linear-gradient(-90deg, white 30%, black 30%);
		}
		.swatch--green::before { background-image: linear-gradient(-90deg, #148839 30%, #148839 30%); }
	';

	// Dynamically add available colors
	if ( function_exists( 'gc_get_colorschemes' ) ) {
		$available_color_themes = gc_get_colorschemes();
			foreach ( $available_color_themes as $key => $val ) {
			$css .= '.swatch--' . $key . '::before { background-image: linear-gradient(-90deg, ' . $val['primary']['color'] . ' 30%, ' . $val['secondary']['color'] . ' 30%); }';
		}

	}

	wp_add_inline_style( 'acf-global', trim($css) );
}

add_action( 'admin_enqueue_scripts', 'gc_add_tax_admin_css' );