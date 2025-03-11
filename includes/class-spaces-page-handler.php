<?php

namespace UbcVpfoSpacesPage;

use Parsedown;

defined( 'ABSPATH' ) || exit;

global $is_classroom_template;
$is_classroom_template = false;

global $is_building_template;
$is_building_template = false;

class Spaces_Page_Handler {

	/**
	 * Api integration with Airtable.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Airtable_Api    $airtable_api
	 */
	protected $airtable_api;

	/**
	 * Plugin settings, containing airtable information and base urls.
	 */
	protected array $settings;

	/**
	 * Define hooks related to page handling.
	 *
	 * @since    1.0.0
	 */
	public function __construct( array $settings ) {
		$this->define_hooks();

		$this->settings     = $settings;
		$this->airtable_api = new Airtable_Api( $settings );
	}

	/**
	 * Register all of the hooks related to the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_hooks() {

		add_action( 'init', array( $this, 'handle_building_rewrite' ) );
		add_action( 'template_redirect', array( $this, 'handle_building_template_redirect' ) );
		add_filter( 'wpseo_title', array( $this, 'modify_building_yoast_seo_title' ), 10 ); // Yoast SEO title filter

		add_action( 'init', array( $this, 'handle_classroom_rewrite' ) );
		add_action( 'template_redirect', array( $this, 'handle_classroom_template_redirect' ) );
		add_filter( 'wpseo_title', array( $this, 'modify_classroom_yoast_seo_title' ), 10 ); // Yoast SEO title filter
	}

	public function handle_building_rewrite() {
		$flush = get_option( 'ubc_vpfo_spaces_page_permalinks_flush' );

		add_rewrite_rule(
			'^buildings/([^/]*)/?', // Regular expression for matching the URL
			'index.php?building_slug=$matches[1]', // URL rewrite to a query variable
			'top' // Priority of the rule
		);

		// Register a query variable for the custom slug
		add_rewrite_tag( '%building_slug%', '([^&]+)' );

		if ( ! $flush ) {
			flush_rewrite_rules( false );
			update_option( 'ubc_vpfo_spaces_page_permalinks_flush', 1 );
		}
	}

	public function handle_building_template_redirect() {

		global $is_building_template;

		$building_slug = sanitize_text_field( get_query_var( 'building_slug' ) );
		$all_classroom = rest_sanitize_boolean( $_REQUEST['all_classroom'] ?? false );

		if ( ! $building_slug ) {
			return;
		}

		$building_slug = strtolower( $building_slug );

		$building = $this->airtable_api->get_building_by_slug( $building_slug );

		$building_code       = isset( $building->fields->{'Code'} ) ? $building->fields->{'Code'} : null;
		$building_classrooms = $building_code ? $this->airtable_api->get_classrooms_for_building( $building_code ) : (object) array();

		$building_options_links_raw = $this->airtable_api->get_building_options_links();
		$building_options_links     = array();
		foreach ( $building_options_links_raw as $building_options_link ) {
			$building_options_links[ $building_options_link->fields->{'Key'} ] = $building_options_link->fields->{'Value'};
		}

		// If the lookup had no results, allow WordPress to 404.
		if ( null === $building ) {
			return;
		}

		$is_building_template = true;

		$building->fields->{'Building Notes'} = ( new Parsedown() )->text( $building->fields->{'Building Notes'} );

		$template_name = 'building-single.php';
		$args          = array(
			'building'               => $building,
			'building_classrooms'    => $building_classrooms,
			'all_classroom'          => $all_classroom,
			'building_options_links' => $building_options_links,
		);

		if ( ! locate_template( sprintf( 'spaces-page/%s', $template_name ), true, true, $args ) ) {
			load_template( plugin_dir_path( __DIR__ ) . 'templates/building-single.php', true, $args );
		}

		exit;
	}

	public function modify_building_yoast_seo_title( $title ) {
		if ( ! function_exists( 'wpseo_replace_vars' ) ) {
			return $title; // Yoast SEO is not active, return the default title
		}

		$building_slug = get_query_var( 'building_slug' );
		$building_slug = strtolower( $building_slug );

		if ( ! $building_slug ) {
			return $title; // Not a building page, return the default title
		}

		// Access the API and retrieve the building data
		$building = $this->airtable_api->get_building_by_slug( $building_slug );

		if ( null === $building ) {
			return $title; // No building found, keep the default title
		}

		// Get the building name from the fields
		$building_name = isset( $building->fields->{'Building Name'}[0] ) ? $building->fields->{'Building Name'}[0] : null;
		$building_code = isset( $building->fields->{'Code'} ) ? $building->fields->{'Code'} : null;

		// Get the separator from Yoast SEO using wpseo_replace_vars
		$separator = wpseo_replace_vars( '%%sep%%', array() );

		if ( $building_name ) {
			// Modify the Yoast title with building name and the separator
			$title  = $building_name;
			$title .= ' ' . $separator . ' ' . get_bloginfo( 'name' );
		}

		return $title;
	}

	public function handle_classroom_rewrite() {
		$flush = get_option( 'ubc_vpfo_spaces_page_permalinks_flush' );

		add_rewrite_rule(
			'^classrooms/([^/]*)/?', // Regular expression for matching the URL
			'index.php?classroom_slug=$matches[1]', // URL rewrite to a query variable
			'top' // Priority of the rule
		);

		// Register a query variable for the custom slug
		add_rewrite_tag( '%classroom_slug%', '([^&]+)' );

		if ( ! $flush ) {
			flush_rewrite_rules( false );
			update_option( 'ubc_vpfo_spaces_page_permalinks_flush', 1 );
		}
	}

	private function add_av_resource_to_classroom( $classroom ) {
		// Fetch resources from Airtable, often from a transient cache.
		$resources = $this->airtable_api->get_resources();

		if ( $classroom->fields && isset( $classroom->fields->{'Shared AV Guide'} ) && ! empty( $classroom->fields->{'Shared AV Guide'} ) ) {
			$resource_id = sanitize_text_field( $classroom->fields->{'Shared AV Guide'}[0] );

			foreach ( $resources as $r ) {
				if ( $r->id === $resource_id ) {
					$attachment = $r->fields->Attachment[0]->url ?? null;

					if ( $attachment ) {
						$classroom->fields->{'Shared AV Guide'} = $attachment;
						break;
					}
				}
			}
		}

		return $classroom;
	}

	public function handle_classroom_template_redirect() {

		global $is_classroom_template;

		$classroom_slug = sanitize_text_field( get_query_var( 'classroom_slug' ) );

		if ( ! $classroom_slug ) {
			return;
		}

		$classroom_slug = strtolower( $classroom_slug );

		$classroom = $this->airtable_api->get_classroom_by_slug( $classroom_slug );

		// Append AV resources to the classroom object
		$classroom = $this->add_av_resource_to_classroom( $classroom );

		$classroom_building_code = isset( $classroom->fields->{'Building Code'} ) ? $classroom->fields->{'Building Code'} : '';
		$classroom_building      = $this->airtable_api->get_classroom_building( $classroom_building_code );
		$classroom_building_slug = isset( $classroom_building->fields->{'Slug'} ) ? $classroom_building->fields->{'Slug'} : '';
		$building_alert_message  = isset( $classroom_building->fields->{'Alert Message'} ) ? $classroom_building->fields->{'Alert Message'} : '';

		$classroom_options_links_raw = $this->airtable_api->get_classroom_options_links();
		$classroom_options_links     = array();
		foreach ( $classroom_options_links_raw as $classroom_options_link ) {
			$classroom_options_links[ $classroom_options_link->fields->{'Key'} ] = $classroom_options_link->fields->{'Value'};
		}

		// If the lookup had no results, allow WordPress to 404.
		if ( null === $classroom ) {
			return;
		}

		// Set the flag to true only for this specific template.
		$is_classroom_template = true;

		$classroom->fields->{'Space Overview'}         = ( new Parsedown() )->text( $classroom->fields->{'Space Overview'} );
		$classroom->fields->{'Accessibility Notes'}    = ( new Parsedown() )->text( $classroom->fields->{'Accessibility Notes'} );
		$classroom->fields->{'Alert Message'}          = ( new Parsedown() )->text( $classroom->fields->{'Alert Message'} );
		$classroom->fields->{'Building Alert Message'} = ( new Parsedown() )->text( $building_alert_message );

		$template_name = 'classroom-single.php';
		$args          = array(
			'classroom'               => $classroom,
			'classroom_building_slug' => $classroom_building_slug,
			'classroom_options_links' => $classroom_options_links,
			'campus'                  => $this->settings['airtable_location'] ?? 'van_airtable',
		);

		if ( ! locate_template( sprintf( 'spaces-page/%s', $template_name ), true, true, $args ) ) {
			load_template( plugin_dir_path( __DIR__ ) . 'templates/classroom-single.php', true, $args );
		}

		exit;
	}

	public function modify_classroom_yoast_seo_title( $title ) {
		if ( ! function_exists( 'wpseo_replace_vars' ) ) {
			return $title; // Yoast SEO is not active, return the default title
		}

		$classroom_slug = get_query_var( 'classroom_slug' );
		$classroom_slug = strtolower( $classroom_slug );

		if ( ! $classroom_slug ) {
			return $title; // Not a classroom page, return the default title
		}

		// Access the API and retrieve the classroom data
		$classroom = $this->airtable_api->get_classroom_by_slug( $classroom_slug );

		if ( null === $classroom ) {
			return $title; // No classroom found, keep the default title
		}

		// Get the classroom name from the fields
		$classroom_name = isset( $classroom->fields->{'Title'} ) ? $classroom->fields->{'Title'} : null;

		// Get the separator from Yoast SEO using wpseo_replace_vars
		$separator = wpseo_replace_vars( '%%sep%%', array() );

		if ( $classroom_name ) {
			// Modify the Yoast title with classroom name and the separator
			$title = $classroom_name . ' ' . $separator . ' ' . get_bloginfo( 'name' );
		}

		return $title;
	}
}
