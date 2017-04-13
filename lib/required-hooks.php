<?php
/**
 * iThemes Exchange Easy Canadian Sales Taxes Add-on
 * @package exchange-addon-easy-canadian-sales-taxes
 * @since 1.0.0
*/

//For calculation shipping, we need to require billing addresses... 
//incase a product doesn't have a shipping address and the shipping add-on is not enabled
add_filter( 'it_exchange_billing_address_purchase_requirement_enabled', '__return_true' );

/**
 * Register the canadian taxes provider.
 *
 * @since 1.36.0
 *
 * @param \ITE_Tax_Managers $manager
 */
function it_exchange_register_canadian_taxes_provider( ITE_Tax_Managers $manager ) {
	$manager::register_provider( new ITE_Canadian_Taxes_Provider() );
}

add_action( 'it_exchange_register_tax_providers', 'it_exchange_register_canadian_taxes_provider' );

/**
 * Enqueues Easy Canadian Sales Taxes scripts to WordPress Dashboard
 *
 * @since 1.0.0
 *
 * @param string $hook_suffix WordPress passed variable
 * @return void
*/
function it_exchange_easy_canadian_sales_taxes_addon_admin_wp_enqueue_scripts( $hook_suffix ) {

	$url_base = ITUtility::get_url_from_file( dirname( __FILE__ ) );
		
	if ( !empty( $_GET['add-on-settings'] ) && 'exchange_page_it-exchange-addons' === $hook_suffix && 'easy-canadian-sales-taxes' === $_GET['add-on-settings'] ) {
		wp_enqueue_script( 'it-exchange-easy-canadian-sales-taxes-addon-admin-js', $url_base . '/js/admin.js', array( 'jquery' ) );
	}
}
add_action( 'admin_enqueue_scripts', 'it_exchange_easy_canadian_sales_taxes_addon_admin_wp_enqueue_scripts' );

/**
 * Enqueues Easy Canadian Sales Taxes styles to WordPress Dashboard
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_easy_canadian_sales_taxes_addon_admin_wp_enqueue_styles() {
	global $post, $hook_suffix;

	if ( isset( $_REQUEST['post_type'] ) ) {
		$post_type = $_REQUEST['post_type'];
	} else {
		if ( isset( $_REQUEST['post'] ) ) {
			$post_id = (int) $_REQUEST['post'];
		} else if ( isset( $_REQUEST['post_ID'] ) ) {
			$post_id = (int) $_REQUEST['post_ID'];
		} else {
			$post_id = 0;
		}

		if ( $post_id )
			$post = get_post( $post_id );

		if ( isset( $post ) && !empty( $post ) )
			$post_type = $post->post_type;
	}
	
	// Easy US Sales Taxes settings page
	if ( ( isset( $post_type ) && 'it_exchange_prod' === $post_type )
		|| ( !empty( $_GET['add-on-settings'] ) && 'exchange_page_it-exchange-addons' === $hook_suffix && 'easy-canadian-sales-taxes' === $_GET['add-on-settings'] ) ) {
		
		wp_enqueue_style( 'it-exchange-easy-canadian-sales-taxes-addon-admin-style', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/admin.css' );
		
	}

}
add_action( 'admin_print_styles', 'it_exchange_easy_canadian_sales_taxes_addon_admin_wp_enqueue_styles' );

/**
 * Loads the frontend CSS on all exchange pages
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @return void
*/
function it_exchange_easy_canadian_sales_taxes_load_public_scripts( $current_view ) {
	
	if ( it_exchange_is_page( 'checkout' ) || it_exchange_is_page( 'confirmation' ) || it_exchange_in_superwidget() ) {

		$url_base = ITUtility::get_url_from_file( dirname( __FILE__ ) );
		wp_enqueue_style( 'ite-easy-canadian-sales-taxes-addon', $url_base . '/styles/taxes.css' );
	}
}

/**
 * Add Easy Canadian Sales Taxes to the content-cart totals and content-checkout loop
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $elements list of existing elements
 * @return array
*/
function it_exchange_easy_canadian_sales_taxes_addon_add_taxes_to_template_totals_elements( $elements ) {
	// Locate the discounts key in elements array (if it exists)
	$index = array_search( 'totals-savings', $elements );
	if ( false === $index )
		$index = -1;
		
	// Bump index by 1 to show tax after discounts
	if ( -1 != $index )
		$index++;

	array_splice( $elements, $index, 0, 'easy-canadian-sales-taxes' );
	return $elements;
}

/**
 * Add Easy Canadian Sales Taxes to the super-widget-checkout totals loop
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $loops list of existing elements
 * @return array
*/
function it_exchange_easy_canadian_sales_taxes_addon_add_taxes_to_sw_template_totals_loops( $loops ) {
	// Locate the discounts key in elements array (if it exists)
	$index = array_search( 'discounts', $loops );
	if ( false === $index )
		$index = -1;
		
	// Bump index by 1 to show tax after discounts
	if ( -1 != $index )
		$index++;

	array_splice( $loops, $index, 0, 'easy-canadian-sales-taxes' );

	return $loops;
}

/**
 * Adds our templates directory to the list of directories
 * searched by Exchange
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $template_path existing array of paths Exchange will look in for templates
 * @param array $template_names existing array of file names Exchange is looking for in $template_paths directories
 * @return array Modified template paths
*/
function it_exchange_easy_canadian_sales_taxes_addon_taxes_register_templates( $template_paths, $template_names ) {
	// Bail if not looking for one of our templates
	$add_path = false;
	$templates = array(
		'content-checkout/elements/easy-canadian-sales-taxes.php',
		'content-confirmation/elements/easy-canadian-sales-taxes.php',
		'super-widget-checkout/loops/easy-canadian-sales-taxes.php',
	);
	foreach( $templates as $template ) {
		if ( in_array( $template, (array) $template_names ) )
			$add_path = true;
	}
	if ( ! $add_path )
		return $template_paths;

	$template_paths[] = dirname( __FILE__ ) . '/templates';

	return $template_paths;
}

/**
 * Adjcanadiants the cart total if on a checkout page
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param int $total the total passed to canadian by Exchange.
 * @return int New Total
*/
function it_exchange_easy_canadian_sales_taxes_addon_taxes_modify_total( $total ) {
	if ( !it_exchange_is_page( 'cart' ) || it_exchange_in_superwidget() ) //we jcanadiant don't want to modify anything on the cart page
		$total += it_exchange_easy_canadian_sales_taxes_addon_get_total_taxes_for_cart( false );
	return $total;
}

/**
 * Save Taxes to Transaction Meta
 *
 * @since 1.0.0
 *
 * @param int       $transaction_id Transaction ID
 * @param \ITE_Cart $cart
*/
function it_exchange_easy_canadian_sales_taxes_transaction_hook( $transaction_id, ITE_Cart $cart = null ) {

	$transaction = it_exchange_get_transaction( $transaction_id );

	$taxes = $transaction->get_items( 'tax', true )->with_only_instances_of( 'ITE_Canadian_Tax_Item' );

	if ( ! $taxes->count() ) {
		return;
	}

	$data = array();

	/** @var ITE_Canadian_Tax_Item $tax */
	foreach ( $taxes as $tax ) {

		if ( ! $tax->get_tax_rate() ) {
			continue;
		}

		$data[] = array( $tax->get_tax_rate()->to_array() ) + array( 'total' => $tax->get_total() );
	}

	update_post_meta( $transaction_id, '_it_exchange_easy_canadian_sales_taxes', $data );
	update_post_meta( $transaction_id, '_it_exchange_easy_canadian_sales_taxes_total', $taxes->total() );

	// Session is only maintained for back-compat on the main cart.
	if ( $cart && $cart->is_current() ) {
		it_exchange_clear_session_data( 'addon_easy_canadian_sales_taxes' );
	}
}

add_action( 'it_exchange_add_transaction_success', 'it_exchange_easy_canadian_sales_taxes_transaction_hook', 10, 2 );
/**
 * Adds the cart taxes to the transaction object
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 *
 * @return string
*/
function it_exchange_easy_canadian_sales_taxes_add_cart_taxes_to_txn_object() {
    $formatted = ( 'it_exchange_set_transaction_objet_cart_taxes_formatted' == current_filter() );
    return it_exchange_easy_canadian_sales_taxes_addon_get_total_taxes_for_cart( $formatted );
}

function it_exchange_easy_canadian_sales_taxes_replace_order_table_tag_before_total_row( $email_obj, $options ) {
    $tax_items = get_post_meta( $email_obj->transaction_id, '_it_exchange_easy_canadian_sales_taxes', true );
	if ( !empty( $tax_items ) ) {
		$taxes = '';
		foreach ( $tax_items as $tax ) {
			if ( !empty( $tax['total'] ) && ! empty( $tax['type'] ) ) {
				$tax['total'] = it_exchange_format_price( $tax['total'] );
				$taxes .= '<p>' . $tax['total'] . ' (' . $tax['type'] . ')</p>';
			}
		}
		?>
		<tr>
			<td colspan="2" style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Taxes', 'LION' ); ?></td>
			<td style="padding: 10px;border:1px solid #DDD;"><?php echo $taxes; ?></td>
		</tr>
		<?php
	}
}
add_action( 'it_exchange_replace_order_table_tag_before_total_row', 'it_exchange_easy_canadian_sales_taxes_replace_order_table_tag_before_total_row', 10, 2 );