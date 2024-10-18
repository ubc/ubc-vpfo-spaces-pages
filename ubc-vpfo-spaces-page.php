<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://paper-leaf.com
 * @since             1.0.0
 * @package           Ubc_Vpfo_Spaces_Page
 *
 * @wordpress-plugin
 * Plugin Name:       UBC VPFO Learning Spaces Virtual Pages
 * Plugin URI:        https://paper-leaf.com
 * Description:       Creates virtual pages for Learning Spaces buildings and classrooms and provides templates for those pages.
 * Version:           1.0.0
 * Author:            Paperleaf ZGM
 * Author URI:        https://paper-leaf.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ubc-vpfo-spaces-page
 * Domain Path:       /languages
 */

use UbcVpfoSpacesPage\Spaces_Page;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once 'vendor/autoload.php';

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'UBC_VPFO_SPACES_PAGE_VERSION', '1.0.0' );

/**
 * Enqueue plugin assets
 */
require plugin_dir_path( __FILE__ ) . 'includes/enqueues.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ubc-vpfo-spaces-page-activator.php
 */
function activate_ubc_vpfo_spaces_page() {
	update_option( 'ubc_vpfo_spaces_page_permalinks_flushed', 0 );
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ubc-vpfo-spaces-page-deactivator.php
 */
function deactivate_ubc_vpfo_spaces_page() {
	delete_option( 'ubc_vpfo_spaces_page_permalinks_flushed' );
}

register_activation_hook( __FILE__, 'activate_ubc_vpfo_spaces_page' );
register_deactivation_hook( __FILE__, 'deactivate_ubc_vpfo_spaces_page' );

require plugin_dir_path( __FILE__ ) . 'includes/class-spaces-page.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ubc_vpfo_spaces_page() {
	// Instantiating the plugin class will register
	// hooks related to plugin functionality.
	new Spaces_Page();
}
run_ubc_vpfo_spaces_page();
