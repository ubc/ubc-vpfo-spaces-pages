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

	// conditionally load the glider JS if it's a classroom
	global $is_classroom_template;

	if ( isset( $is_classroom_template ) && $is_classroom_template ) {
		wp_enqueue_script(
			'classrooms-image-gallery-glider',
			plugin_dir_url( __DIR__ ) . 'js/classrooms-image-gallery-glider.js',
			array(),
			'1.0',
			array( 'strategy' => 'defer' )
		);

		wp_enqueue_script(
			'bootstrap',
			plugin_dir_url( __DIR__ ) . 'js/bootstrap.js',
			array(),
			'1.0',
			array( 'strategy' => 'defer' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'ubc_vpfo_spaces_pages_enqueue_styles_scripts' );
