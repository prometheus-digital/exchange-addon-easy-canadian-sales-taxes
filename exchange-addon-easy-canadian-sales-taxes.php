<?php
/*
 * Plugin Name: iThemes Exchange - Easy Canadian Sales Taxes
 * Version: 1.3.1
 * Description: Adds Easy Canadian Sales Taxes to iThemes Exchange.
 * Plugin URI: http://ithemes.com/exchange/easy-canadian-sales-taxes/
 * Author: iThemes
 * Author URI: http://ithemes.com
 * iThemes Package: exchange-addon-easy-canadian-sales-taxes
 
 * Installation:
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 *
*/

/**
 * This registers our plugin as a customer pricing addon
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_register_easy_canadian_sales_taxes_addon() {
	$options = array(
		'name'              => __( 'Easy Canadian Sales Taxes', 'LION' ),
		'description'       => __( 'Now store owners can charge the proper tax for each of their product types, regardless of where their customers live in the Canada.', 'LION' ),
		'author'            => 'iThemes',
		'author_url'        => 'http://ithemes.com/exchange/easy-canadian-sales-taxes/',
		'icon'              => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/lib/images/taxes50px.png' ),
		'file'              => dirname( __FILE__ ) . '/init.php',
		'category'          => 'taxes',
		'basename'          => plugin_basename( __FILE__ ),
		'labels'      => array(
			'singular_name' => __( 'Easy Canadian Sales Taxes', 'LION' ),
		),
		'settings-callback' => 'it_exchange_easy_canadian_sales_taxes_settings_callback',
	);
	it_exchange_register_addon( 'easy-canadian-sales-taxes', $options );
}
add_action( 'it_exchange_register_addons', 'it_exchange_register_easy_canadian_sales_taxes_addon' );

/**
 * Loads the translation data for WordPress
 *
 * @uses load_plugin_textdomain()
 * @since 1.0.0
 * @return void
*/
function it_exchange_easy_canadian_sales_taxes_set_textdomain() {
	load_plugin_textdomain( 'LION', false, dirname( plugin_basename( __FILE__  ) ) . '/lang/' );
}
//add_action( 'plugins_loaded', 'it_exchange_easy_canadian_sales_taxes_set_textdomain' );

/**
 * Registers Plugin with iThemes updater class
 *
 * @since 1.0.0
 *
 * @param object $updater ithemes updater object
 * @return void
*/
function ithemes_exchange_addon_easy_canadian_sales_taxes_updater_register( $updater ) { 
	$updater->register( 'exchange-addon-easy-canadian-sales-taxes', __FILE__ );
}
add_action( 'ithemes_updater_register', 'ithemes_exchange_addon_easy_canadian_sales_taxes_updater_register' );
require( dirname( __FILE__ ) . '/lib/updater/load.php' );
