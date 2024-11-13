<?php

namespace UbcVpfoSpacesPage;

defined( 'ABSPATH' ) || exit;

global $is_classroom_template;
$is_classroom_template = false;

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
	 * Define hooks related to page handling.
	 *
	 * @since    1.0.0
	 */
	public function __construct( array $settings ) {
		$this->define_hooks();

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

		$building_slug = get_query_var( 'building_slug' );

		if ( ! $building_slug ) {
			return;
		}

		$building = $this->airtable_api->get_building_by_slug( $building_slug );

		$building_code       = isset( $building->fields->{'Code'} ) ? $building->fields->{'Code'} : null;
		$building_classrooms = $building_code ? $this->airtable_api->get_classrooms_for_building( $building_code ) : (object) array();

		// If the lookup had no results, allow WordPress to 404.
		if ( null === $building ) {
			return;
		}

		$template_name = 'building-single.php';
		$args          = array(
			'building'            => $building,
			'building_classrooms' => $building_classrooms,
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

	public function handle_classroom_template_redirect() {

		global $is_classroom_template;

		$classroom_slug = get_query_var( 'classroom_slug' );

		if ( ! $classroom_slug ) {
			return;
		}

		$classroom = $this->airtable_api->get_classroom_by_slug( $classroom_slug );

		$classroom_building_code = isset( $classroom->fields->{'Building Code'} ) ? $classroom->fields->{'Building Code'} : '';
		$classroom_building_slug = $this->airtable_api->get_classroom_building_slug( $classroom_building_code );

		// If the lookup had no results, allow WordPress to 404.
		if ( null === $classroom ) {
			return;
		}

		// Set the flag to true only for this specific template.
		$is_classroom_template = true;

		$template_name = 'classroom-single.php';
		$args          = array(
			'classroom'               => $classroom,
			'classroom_building_slug' => $classroom_building_slug,
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
