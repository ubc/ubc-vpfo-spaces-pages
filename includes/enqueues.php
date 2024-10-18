<?php
/**
 * Enqueue styles and scripts for the plugin.
 *
 * @since 1.0.0
 */

// Front-end enqueue
function ubc_vpfo_spaces_pages_enqueue_styles() {
    wp_enqueue_style(
        'ubc-vpfo-spaces-pages-style',
        plugin_dir_url( __DIR__ ) . 'style.css',
        array(), // Dependencies
        '1.0.0'
    );
}
add_action( 'wp_enqueue_scripts', 'ubc_vpfo_spaces_pages_enqueue_styles' );