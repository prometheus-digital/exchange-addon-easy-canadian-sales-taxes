<?php
/*
 * Plugin Name: iThemes Exchange - Easy Canadian Sales Taxes
 * Version: 2.0.0
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
 * Load the Canadian Taxes plugin.
 *
 * @since 2.0.0
 */
function it_exchange_load_easy_canadian_sales_taxes() {
	if ( ! function_exists( 'it_exchange_load_deprecated' ) || it_exchange_load_deprecated() ) {
		require_once dirname( __FILE__ ) . '/deprecated/exchange-addon-easy-canadian-sales-taxes.php';
	} else {
		require_once dirname( __FILE__ ) . '/plugin.php';
	}
}

add_action( 'plugins_loaded', 'it_exchange_load_easy_canadian_sales_taxes' );
