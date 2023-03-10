<?php

/**
 * @link                https://github.com/ICTU/ictuwp-plugin-community-taxonomie
 * @package             ictuwp-plugin-community-taxonomie
 *
 * @wordpress-plugin
 * Plugin Name:         ICTU / Gebruiker Centraal / Community taxonomie
 * Plugin URI:          https://github.com/ICTU/ictuwp-plugin-community-taxonomie
 * Description:         Plugin voor het aanmaken van de 'community'-taxonomie
 * Version:             1.0.1
 * Version description: Rename constants to be consistent with GC
 * Author:              David Hund
 * Author URI:          https://github.com/ICTU/ictuwp-plugin-community-taxonomie/
 * License:             GPL-3.0+
 * License URI:         http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:         gctheme
 * Domain Path:         /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//========================================================================================================

// Dutch slug for taxonomy
$slug = 'community';

if ( get_bloginfo( 'language' ) !== 'nl-NL' ) {
	// non Dutch slug for taxonomy
	$slug = 'community';
}

defined( 'GC_COMMUNITY_TAX' ) or define( 'GC_COMMUNITY_TAX', $slug );
defined( 'GC_COMMUNITY_TAX_OVERVIEW_TEMPLATE' ) or define( 'GC_COMMUNITY_TAX_OVERVIEW_TEMPLATE', 'template-overview-communities.php' );
defined( 'GC_COMMUNITY_TAX_DETAIL_TEMPLATE' ) or define( 'GC_COMMUNITY_TAX_DETAIL_TEMPLATE', 'template-detail-communities.php' );

//========================================================================================================
// only this plugin should activate the GC_COMMUNITY_TAX taxonomy
if ( ! taxonomy_exists( GC_COMMUNITY_TAX ) ) {
	add_action( 'plugins_loaded', array( 'ICTU_GC_community_taxonomy', 'init' ), 10 );
}


//========================================================================================================

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */


if ( ! class_exists( 'ICTU_GC_community_taxonomy' ) ) :

	class ICTU_GC_community_taxonomy {

		/** ----------------------------------------------------------------------------------------------------
		 * Init
		 */
		public static function init() {

			$newtaxonomy = new self();

		}

		/** ----------------------------------------------------------------------------------------------------
		 * Constructor
		 */
		public function __construct() {

			$this->fn_ictu_community_setup_actions();

		}

		/** ----------------------------------------------------------------------------------------------------
		 * Hook this plugins functions into WordPress.
		 * Use priority = 20, to ensure that the taxonomy is registered for post types from other plugins,
		 * such as the podcasts plugin (seriously-simple-podcasting)
		 */
		private function fn_ictu_community_setup_actions() {

			add_action( 'init', array( $this, 'fn_ictu_community_register_taxonomy' ), 20 );

			// add page templates
			add_filter( 'template_include', array( $this, 'fn_ictu_community_append_template_locations' ) );

			// filter the breadcrumbs
			add_filter( 'wpseo_breadcrumb_links', array( $this, 'fn_ictu_community_yoast_filter_breadcrumb' ) );

		}


		/** ----------------------------------------------------------------------------------------------------
		 * Do actually register the post types we need
		 *
		 * @return void
		 */
		public function fn_ictu_community_register_taxonomy() {

			require_once plugin_dir_path( __FILE__ ) . 'includes/community-taxonomy.php';

		}


		/**
		 * Checks if the template is assigned to the page
		 *
		 * @in: $template (string)
		 *
		 * @return: $template (string)
		 *
		 */
		public function fn_ictu_community_append_template_locations( $template ) {

			// Get global post
			global $post;
			$file        = '';
			$pluginpath = plugin_dir_path( __FILE__ );


			if ( $post ) {
				// Do we have a post of whatever kind at hand?
				// Get template name; this will only work for pages, obviously
				$page_template = get_post_meta( $post->ID, '_wp_page_template', true );

				if ( ( GC_COMMUNITY_TAX_OVERVIEW_TEMPLATE === $page_template ) || ( GC_COMMUNITY_TAX_DETAIL_TEMPLATE === $page_template ) ) {
					// these names are added by this plugin, so we return
					// the actual file path for this template
					$file = $pluginpath . $page_template;
				} else {
					// exit with the already set template
					return $template;
				}

			} elseif ( is_tax( GC_COMMUNITY_TAX ) ) {
				// Are we dealing with a term for the GC_COMMUNITY_TAX taxonomy?
				$file = $pluginpath . 'taxonomy-community.php';

			} else {
				// Not a post, not a term, return the template
				return $template;
			}

			// Just to be safe, check if the file actually exists
			if ( $file && file_exists( $file ) ) {
				return $file;
			} else {
				// o dear, who deleted the file?
				echo $file;
			}

			// If all else fails, return template
			return $template;
		}


		/**
		 * Filter the Yoast SEO breadcrumb
		 *
		 * @in: $links (array)
		 *
		 * @return: $links (array)
		 *
		 */
		public function fn_ictu_community_yoast_filter_breadcrumb( $links ) {

			if ( is_tax( GC_COMMUNITY_TAX ) ) {
				// this filter is only for terms in GC_COMMUNITY_TAX taxonomy

				$term = get_queried_object();
				// Append taxonomy if 1st-level child term only
				// old: Home > Term
				// new: Home > Taxonomy > Term

				if ( ! $term->parent ) {

					$community_overview_page_id = $this->fn_ictu_community_get_community_overview_page();

					if ( $community_overview_page_id ) {
						// Use this page as GC_COMMUNITY_TAX term parent in the breadcrumb
						// If not available,
						// - [1] Do not display root
						// - [2] OR fall back to Taxonomy Rewrite

						$taxonomy_link = array(
							'url'  => get_permalink( $community_overview_page_id ),
							'text' => get_the_title( $community_overview_page_id )
						);
						array_splice( $links, - 1, 0, array( $taxonomy_link ) );

					} else {
						// [1] .. do nothing...

						// [2] OR .. use Taxonomy Rewrite as root

						// $taxonomy      = get_taxonomy( GC_COMMUNITY_TAX );
						// $taxonomy_link = [
						// 	'url' => get_home_url() . '/' . $taxonomy->rewrite['slug'],
						// 	'text' => $taxonomy->labels->archives,
						// 	'term_id' => get_queried_object_id(),
						// ];
						// array_splice( $links, -1, 0, [$taxonomy_link] );
					}
				}
			}

			return $links;

		}

		/**
		 * Retrieve a page that is the GC_COMMUNITY_TAX overview page. This
		 * page shows all available GC_COMMUNITY_TAX terms
		 *
		 * @in: $args (array)
		 *
		 * @return: $overview_page_id (integer)
		 *
		 */

		private function fn_ictu_community_get_community_overview_page( $args = array() ) {

			$return = 0;

			// TODO: discuss if we need to make this page a site setting
			// there is no technical way to limit the number of pages with
			// template GC_COMMUNITY_TAX_OVERVIEW_TEMPLATE, so the page we find may not be
			// the exact desired page for in the breadcrumb.
			//
			// Try and find 1 Page
			// with the GC_COMMUNITY_TAX_OVERVIEW_TEMPLATE template...
			$page_template_query_args = array(
				'number'      => 1,
				'sort_column' => 'post_date',
				'sort_order'  => 'DESC',
				'meta_key'    => '_wp_page_template',
				'meta_value'  => GC_COMMUNITY_TAX_OVERVIEW_TEMPLATE
			);
			$overview_page = get_pages( $page_template_query_args );

			if ( $overview_page && isset( $overview_page[0]->ID ) ) {
				$return = $overview_page[0]->ID;
			}

			return $return;

		}


	}

endif;


//========================================================================================================

if ( defined( GC_COMMUNITY_TAX ) or taxonomy_exists( GC_COMMUNITY_TAX ) ) {

	/**
	 * Load plugin textdomain.
	 * only load translations if we can safely assume the taxonomy is active
	 */
	add_action( 'init', 'fn_ictu_community_load_plugin_textdomain' );

	function fn_ictu_community_load_plugin_textdomain() {

		load_plugin_textdomain( 'gctheme', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

}

//========================================================================================================

/**
 * Returns array of allowed page templates
 *
 * @return array with extra templates
 */
function fn_ictu_community_add_templates() {

	$return_array = array(
		GC_COMMUNITY_TAX_OVERVIEW_TEMPLATE => _x( 'Community / overzicht', 'label page template', 'gctheme' ),
		GC_COMMUNITY_TAX_DETAIL_TEMPLATE   => _x( 'Community / detailpagina', 'label page template', 'gctheme' )
	);

	return $return_array;

}
