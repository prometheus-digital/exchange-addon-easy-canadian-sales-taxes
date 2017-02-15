<?php
/**
 * Load the Canadian Sales Taxes add-on.
 *
 * @since 2.0.0
 * @license GPLv2
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

it_exchange_easy_canadian_sales_taxes_set_textdomain();