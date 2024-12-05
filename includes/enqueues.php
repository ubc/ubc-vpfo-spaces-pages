<?php
/**
 * Enqueue styles and scripts for the plugin.
 *
 * @since 1.0.0
 */

// Front-end enqueue
function ubc_vpfo_spaces_pages_enqueue_styles_scripts() {
	wp_enqueue_style(
		'ubc-vpfo-spaces-pages-style',
		plugin_dir_url( __DIR__ ) . 'style.css',
		array(), // Dependencies
		'1.0.0'
	);

	wp_enqueue_style(
		'font-whitney',
		plugin_dir_url( __DIR__ ) . 'fonts/whitney/font-whitney.css',
		array(), // Dependencies
		'1.0.0'
	);

	wp_enqueue_style(
		'font-fontawesome-6-pro',
		plugin_dir_url( __DIR__ ) . 'fonts/fontawesome/css/all.min.css',
		array(), // Dependencies
		'6.6.0'
	);

	// conditionally load classroom-specific JS if it's a classroom
	global $is_classroom_template;
	global $is_building_template;

	if ( isset( $is_classroom_template ) && $is_classroom_template ) {
		wp_enqueue_script(
			'vpfo-classrooms-image-gallery-glider',
			plugin_dir_url( __DIR__ ) . 'js/classrooms-image-gallery-glider.js',
			array(),
			'1.0',
			array( 'strategy' => 'defer' )
		);

		wp_enqueue_script(
			'vpfo-accordion-js',
			plugin_dir_url( __DIR__ ) . 'js/accordion.js',
			array(),
			'1.0',
			array( 'strategy' => 'defer' )
		);

		wp_enqueue_script(
			'vpfo-modal-js',
			plugin_dir_url( __DIR__ ) . 'js/layout-modal.js',
			array(),
			'1.0',
			array( 'strategy' => 'defer' )
		);

	}

	if ( ( isset( $is_building_template ) && $is_building_template )
		|| ( isset( $is_classroom_template ) && $is_classroom_template ) ) {
		wp_enqueue_script(
			'vpfo-clipboard-js',
			plugin_dir_url( __DIR__ ) . 'js/clipboard.js',
			array(),
			'1.0',
			array( 'strategy' => 'defer' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'ubc_vpfo_spaces_pages_enqueue_styles_scripts' );
