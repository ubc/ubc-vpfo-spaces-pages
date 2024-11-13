<?php

namespace UbcVpfoSpacesPage;

defined( 'ABSPATH' ) || exit;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://paper-leaf.com
 * @since      1.0.0
 *
 * @package    Ubc_Vpfo_Spaces_Page
 * @subpackage Ubc_Vpfo_Spaces_Page/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ubc_Vpfo_Spaces_Page
 * @subpackage Ubc_Vpfo_Spaces_Page/includes
 * @author     Paperleaf ZGM <info@paper-leaf.com>
 */
class Spaces_Page {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Spaces_Page_Handler    $handler
	 */
	protected $page_handler;

	/**
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Spaces_Airtable_Options    $airtable_options
	 */
	protected $airtable_options;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'UBC_VPFO_SPACES_PAGE_VERSION' ) ) {
			$this->version = UBC_VPFO_SPACES_PAGE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ubc-vpfo-spaces-page';

		// Always instantiate the options page class.
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-spaces-page-airtable-options.php';
		$this->airtable_options = new Spaces_Page_Airtable_Options();

		$settings = $this->airtable_options->get_settings();

		// Only load the plugin when the Airtable API key and Base ID are defined.
		if ( $settings['api_key'] && $settings['base_id_van'] ) {
			$this->load_dependencies( $settings );
		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies( array $settings ) {
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-spaces-page-handler.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-airtable-api.php';

		$this->page_handler = new Spaces_Page_Handler( $settings );

		// Hook the virtual page addition to the Yoast sitemap
		add_action( 'init', function () use ( $settings ) {
			$this->add_virtual_page_urls_to_yoast_page_sitemap( $settings );
		});
	}

	/**
	 * Add Buildings and Classrooms virtual pages to the Yoast pages sitemap.
	 *
	 * @param array $settings Settings containing API keys and Base IDs.
	 * @since 1.0.0
	 */
	private function add_virtual_page_urls_to_yoast_page_sitemap( array $settings ) {
		if ( empty( $settings['api_key'] ) || empty( $settings['base_id_van'] ) || empty( $settings['base_id_okan'] ) ) {
			return;
		}

		$airtable_api = new \UbcVpfoSpacesPage\Airtable_Api(
			array(
				'api_key'      => $settings['api_key'],
				'base_id_van'  => $settings['base_id_van'],
				'base_id_okan' => $settings['base_id_okan'],
			)
		);

		$buildings  = $airtable_api->get_building_slugs_for_yoast();
		$classrooms = $airtable_api->get_classroom_slugs_for_yoast();

		if ( empty( $buildings ) && empty( $classrooms ) ) {
			return;
		}

		$buildings_array  = json_decode( wp_json_encode( $buildings ), true );
		$classrooms_array = json_decode( wp_json_encode( $classrooms ), true );

		add_filter(
			'wpseo_sitemap_page_content',
			function ( $sitemap_added_items ) use ( $buildings_array, $classrooms_array ) {
				// Sort arrays alphabetically by 'Slug'
				usort( $buildings_array, fn( $a, $b ) => strcmp( $a['fields']['Slug'], $b['fields']['Slug'] ) );
				usort( $classrooms_array, fn( $a, $b ) => strcmp( $a['fields']['Slug'], $b['fields']['Slug'] ) );

				// Add building URLs to the sitemap
				foreach ( $buildings_array as $building ) {
					if ( ! isset( $building['fields']['Slug'], $building['fields']['Last Modified'] ) ) continue;
					$url = esc_url( get_bloginfo( 'url' ) . '/buildings/' . esc_attr( $building['fields']['Slug'] ) );
					$last_modified = $this->format_last_modified( $building['fields']['Last Modified'] );
					$sitemap_added_items .= "<url><loc>{$url}</loc><lastmod>{$last_modified}</lastmod></url>";
				}

				// Add classroom URLs to the sitemap
				foreach ( $classrooms_array as $classroom ) {
					if ( ! isset( $classroom['fields']['Slug'], $classroom['fields']['Last Modified'] ) ) continue;
					$url = esc_url( get_bloginfo( 'url' ) . '/classrooms/' . esc_attr( $classroom['fields']['Slug'] ) );
					$last_modified = $this->format_last_modified( $classroom['fields']['Last Modified'] );
					$sitemap_added_items .= "<url><loc>{$url}</loc><lastmod>{$last_modified}</lastmod></url>";
				}

				return $sitemap_added_items;
			},
			10,
			1
		);
	}

	/**
	 * Format the last modified date for Yoast sitemap.
	 *
	 * @param string $last_modified_raw Raw date string.
	 * @return string Formatted date or an empty string if parsing fails.
	 */
	private function format_last_modified( $last_modified_raw ) {
		try {
			$date = new \DateTime( $last_modified_raw );
			return $date->format( 'Y-m-d H:i:s O' );
		} catch ( \Exception $e ) { 
			return '';
		}
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
