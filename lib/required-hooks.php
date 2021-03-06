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
 * Shows the nag when needed.
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_easy_canadian_sales_taxes_addon_show_conflict_nag() {
    if ( ! empty( $_REQUEST['it_exchange_easy_canadian_sales_taxes-dismiss-conflict-nag'] ) )
        update_option( 'it-exchange-easy-canadian-sales-taxes-conflict-nag', true );

    if ( true == (boolean) get_option( 'it-exchange-easy-canadian-sales-taxes-conflict-nag' ) )
        return;

	$taxes_addons = it_exchange_get_enabled_addons( array( 'category' => 'taxes' ) );
	
	if ( 1 < count( $taxes_addons ) ) {
		?>
		<div id="it-exchange-easy-canadian-sales-taxes-conflict-nag" class="it-exchange-nag">
			<?php
			$nag_dismiss = add_query_arg( array( 'it_exchange_easy_canadian_sales_taxes-dismiss-conflict-nag' => true ) );
			echo __( 'Warning: You have multiple tax add-ons enabled. You may need to disable one to avoid conflicts.', 'LION' );
			?>
			<a class="dismiss btn" href="<?php echo esc_url( $nag_dismiss ); ?>">&times;</a>
		</div>
		<script type="text/javascript">
			jQuery( document ).ready( function() {
				if ( jQuery( '.wrap > h2' ).length == '1' ) {
					jQuery("#it-exchange-easy-canadian-sales-taxes-conflict-nag").insertAfter( '.wrap > h2' ).addClass( 'after-h2' );
				}
			});
		</script>
		<?php
	}
}
add_action( 'admin_notices', 'it_exchange_easy_canadian_sales_taxes_addon_show_conflict_nag' );

/**
 * Enqueues Easy Canadian Sales Taxes scripts to WordPress Dashboard
 *
 * @since 1.0.0
 *
 * @param string $hook_suffix WordPress passed variable
 * @return void
*/
function it_exchange_easy_canadian_sales_taxes_addon_admin_wp_enqueue_scripts( $hook_suffix ) {
	global $post;
			
	if ( isset( $_REQUEST['post_type'] ) ) {
		$post_type = $_REQUEST['post_type'];
	} else {
		if ( isset( $_REQUEST['post'] ) )
			$post_id = (int) $_REQUEST['post'];
		elseif ( isset( $_REQUEST['post_ID'] ) )
			$post_id = (int) $_REQUEST['post_ID'];
		else
			$post_id = 0;

		if ( $post_id )
			$post = get_post( $post_id );

		if ( isset( $post ) && !empty( $post ) )
			$post_type = $post->post_type;
	}
	
	$url_base = ITUtility::get_url_from_file( dirname( __FILE__ ) );
		
	if ( !empty( $_GET['add-on-settings'] ) && 'exchange_page_it-exchange-addons' === $hook_suffix && 'easy-canadian-sales-taxes' === $_GET['add-on-settings'] ) {
	
		$deps = array( 'jquery' );
		wp_enqueue_script( 'it-exchange-easy-canadian-sales-taxes-addon-admin-js', $url_base . '/js/admin.js' );

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
 *
 * @return void
*/
function it_exchange_easy_canadian_sales_taxes_load_public_scripts( $current_view ) {
	
	if ( it_exchange_is_page( 'checkout' ) || it_exchange_is_page( 'confirmation' ) || it_exchange_in_superwidget() ) {

		$url_base = ITUtility::get_url_from_file( dirname( __FILE__ ) );
		wp_enqueue_style( 'ite-easy-canadian-sales-taxes-addon', $url_base . '/styles/taxes.css' );
		
	}

}
add_action( 'wp_enqueue_scripts', 'it_exchange_easy_canadian_sales_taxes_load_public_scripts' );
add_action( 'it_exchange_enqueue_super_widget_scripts', 'it_exchange_easy_canadian_sales_taxes_load_public_scripts' );

/**
 * Add Easy Canadian Sales Taxes to the content-cart totals and content-checkout loop
 *
 * @since 1.0.0
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
add_filter( 'it_exchange_get_content_checkout_totals_elements', 'it_exchange_easy_canadian_sales_taxes_addon_add_taxes_to_template_totals_elements' );
add_filter( 'it_exchange_get_content_confirmation_transaction_summary_elements', 'it_exchange_easy_canadian_sales_taxes_addon_add_taxes_to_template_totals_elements' );

/**
 * Add Easy Canadian Sales Taxes to the super-widget-checkout totals loop
 *
 * @since 1.0.0
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
add_filter( 'it_exchange_get_super-widget-checkout_after-cart-items_loops', 'it_exchange_easy_canadian_sales_taxes_addon_add_taxes_to_sw_template_totals_loops' );

/**
 * Adds our templates directory to the list of directories
 * searched by Exchange
 *
 * @since 1.0.0
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
add_filter( 'it_exchange_possible_template_paths', 'it_exchange_easy_canadian_sales_taxes_addon_taxes_register_templates', 10, 2 );

/**
 * Adjcanadiants the cart total if on a checkout page
 *
 * @since 1.0.0
 *
 * @param int $total the total passed to canadian by Exchange.
 * @return int New Total
*/
function it_exchange_easy_canadian_sales_taxes_addon_taxes_modify_total( $total ) {
	if ( !it_exchange_is_page( 'cart' ) || it_exchange_in_superwidget() ) //we jcanadiant don't want to modify anything on the cart page
		$total += it_exchange_easy_canadian_sales_taxes_addon_get_total_taxes_for_cart( false );
	return $total;
}
add_filter( 'it_exchange_get_cart_total', 'it_exchange_easy_canadian_sales_taxes_addon_taxes_modify_total' );

/**
 * Save Taxes to Transaction Meta
 *
 * @since 1.0.0
 *
 * @param int $transaction_id Transaction ID
*/
function it_exchange_easy_canadian_sales_taxes_transaction_hook( $transaction_id ) {
	$tax_session = it_exchange_get_session_data( 'addon_easy_canadian_sales_taxes' );
	
	if ( !empty( $tax_session['taxes'] ) ) {
		update_post_meta( $transaction_id, '_it_exchange_easy_canadian_sales_taxes', $tax_session['taxes'] );
	}
	if ( !empty( $tax_session['total_taxes'] ) ) {
		update_post_meta( $transaction_id, '_it_exchange_easy_canadian_sales_taxes_total', $tax_session['total_taxes'] );
	}
	
	it_exchange_clear_session_data( 'addon_easy_canadian_sales_taxes' );
	return;
}
add_action( 'it_exchange_add_transaction_success', 'it_exchange_easy_canadian_sales_taxes_transaction_hook' );
/**
 * Adds the cart taxes to the transaction object
 *
 * @since CHANGEME
 *
 * @param string $taxes incoming from WP Filter. False by default.
 * @return string
 *
*/
function it_exchange_easy_canadian_sales_taxes_add_cart_taxes_to_txn_object() {
    $formatted = ( 'it_exchange_set_transaction_objet_cart_taxes_formatted' == current_filter() );
    return it_exchange_easy_canadian_sales_taxes_addon_get_total_taxes_for_cart( $formatted );
}
add_filter( 'it_exchange_set_transaction_objet_cart_taxes_formatted', 'it_exchange_easy_canadian_sales_taxes_add_cart_taxes_to_txn_object' );
add_filter( 'it_exchange_set_transaction_objet_cart_taxes_raw', 'it_exchange_easy_canadian_sales_taxes_add_cart_taxes_to_txn_object' );

function it_exchange_easy_canadian_sales_taxes_replace_order_table_tag_before_total_row( $email_obj, $options ) {
    $tax_items = get_post_meta( $email_obj->transaction_id, '_it_exchange_easy_canadian_sales_taxes', true );
	if ( !empty( $tax_items ) ) {
		$taxes = '';
		foreach ( $tax_items as $tax ) {
			if ( !empty( $tax['total'] ) ) {
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
