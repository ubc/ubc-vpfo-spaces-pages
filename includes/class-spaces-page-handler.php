<?php

namespace UbcVpfoSpacesPage;

defined( 'ABSPATH' ) || exit;

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
	public function __construct() {
		$this->define_hooks();

		$this->airtable_api = new Airtable_Api();
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

		add_action( 'init', array( $this, 'handle_classroom_rewrite' ) );
		add_action( 'template_redirect', array( $this, 'handle_classroom_template_redirect' ) );
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

		// If the lookup had no results, allow WordPress to 404.
		if ( null === $building ) {
			return;
		}

		$template_name = 'building-single.php';
		$args = array(
			'building' => $building,
		);

		if ( ! locate_template( sprintf( 'spaces-page/%s', $template_name ), true, true, $args ) ) {
			load_template( plugin_dir_path( __DIR__ ) . 'templates/building-single.php', true, $args );
		}

		exit;
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

		$classroom_slug = get_query_var( 'classroom_slug' );

		if ( ! $classroom_slug ) {
			return;
		}

		$classroom = $this->airtable_api->get_classroom_by_slug( $classroom_slug );

		// If the lookup had no results, allow WordPress to 404.
		if ( null === $classroom ) {
			return;
		}

		$template_name = 'classroom-single.php';
		$args = array(
			'classroom' => $classroom,
		);

		if ( ! locate_template( sprintf( 'spaces-page/%s', $template_name ), true, true, $args ) ) {
			load_template( plugin_dir_path( __DIR__ ) . 'templates/classroom-single.php', true, $args );
		}

		exit;
	}
}
