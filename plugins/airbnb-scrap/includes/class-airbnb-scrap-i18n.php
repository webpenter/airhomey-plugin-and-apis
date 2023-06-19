<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       webpenter.com
 * @since      1.0.0
 *
 * @package    Airbnb_Scrap
 * @subpackage Airbnb_Scrap/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Airbnb_Scrap
 * @subpackage Airbnb_Scrap/includes
 * @author     Ahmad Raza <ahmad@test.com>
 */
class Airbnb_Scrap_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'airbnb-scrap',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
