<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://favethemes.io/author/zahid
 * @since             1.0.0
 * @package           Homey_Experiences
 *
 * @wordpress-plugin
 * Plugin Name:       homey experiences
 * Plugin URI:        https://favethemes.io
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Zahid
 * Author URI:        https://favethemes.io/author/zahid
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       homey-experiences
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
define( 'HOMEY_EXPERIENCES_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-homey-experiences-activator.php
 */
function activate_homey_experiences() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-homey-experiences-activator.php';
	Homey_Experiences_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-homey-experiences-deactivator.php
 */
function deactivate_homey_experiences() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-homey-experiences-deactivator.php';
	Homey_Experiences_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_homey_experiences' );
register_deactivation_hook( __FILE__, 'deactivate_homey_experiences' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-homey-experiences.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_homey_experiences() {

	$plugin = new Homey_Experiences();
	$plugin->run();

}
run_homey_experiences();
