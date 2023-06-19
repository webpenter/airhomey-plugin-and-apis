<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              webpenter.com
 * @since             1.0.0
 * @package           Airbnb_Scrap
 *
 * @wordpress-plugin
 * Plugin Name:       Airbnb Scrap
 * Plugin URI:        webpenter.com/airbnb-scrap
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Ahmad Raza
 * Author URI:        webpenter.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       airbnb-scrap
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AIRBNB_SCRAP_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-airbnb-scrap-activator.php
 */
function activate_airbnb_scrap() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-airbnb-scrap-activator.php';
	Airbnb_Scrap_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-airbnb-scrap-deactivator.php
 */
function deactivate_airbnb_scrap() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-airbnb-scrap-deactivator.php';
	Airbnb_Scrap_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_airbnb_scrap' );
register_deactivation_hook( __FILE__, 'deactivate_airbnb_scrap' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-airbnb-scrap.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_airbnb_scrap() {

	$plugin = new Airbnb_Scrap();
	$plugin->run();

}
run_airbnb_scrap();
