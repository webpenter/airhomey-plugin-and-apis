<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://favethemes.io/author/zahid
 * @since      1.0.0
 *
 * @package    Homey_Experiences
 * @subpackage Homey_Experiences/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Homey_Experiences
 * @subpackage Homey_Experiences/includes
 * @author     Zahid <zahid@favethemes.com>
 */
class Homey_Experiences_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'homey-experiences',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
