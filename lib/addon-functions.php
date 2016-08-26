<?php
/**
 * iThemes Exchange Easy Canadian Sales Taxes Add-on
 * @package exchange-addon-easy-canadian-sales-taxes
 * @since   1.0.0
 */

function it_exchange_easy_canadian_sales_taxes_get_tax_row_settings( $row, $province = 'AB', $rate = array() ) {
	if ( empty( $rate ) ) { //just set some defaults
		$rate = array(
			'type'     => 'GST',
			'rate'     => '5',
			'shipping' => false,
		);
	}

	$output = '<div class="item-row block-row">'; //start block-row

	$output .= '<div class="item-column block-column block-column-1">';
	$output .= '<select name="tax-rates[' . $row . '][province]">';

	$provinces = it_exchange_get_data_set( 'states', array( 'country' => 'CA' ) );
	foreach ( $provinces as $abbr => $name ) {

		$output .= '<option value="' . $abbr . '" ' . selected( $abbr, $province, false ) . '>' . $name . '</option>';

	}

	$output .= '</select>';
	$output .= '</div>';

	$output .= '<div class="item-column block-column block-column-2">';
	$output .= '<select name="tax-rates[' . $row . '][type]">';

	$tax_types = it_exchange_easy_canadian_sales_taxes_get_tax_types();
	foreach ( $tax_types as $type ) {

		$output .= '<option value="' . $type . '" ' . selected( $type, $rate['type'], false ) . '>' . $type . '</option>';

	}

	$output .= '</select>';
	$output .= '</div>';

	$output .= '<div class="item-column block-column block-column-3">';
	$output .= '<input type="text" name="tax-rates[' . $row . '][rate]" value="' . $rate['rate'] . '" />';
	$output .= '</div>';

	$output .= '<div class="item-column block-column block-column-4">';
	$shipping = empty( $rate['shipping'] ) ? false : true;
	$output .= '<input type="checkbox" name="tax-rates[' . $row . '][shipping]" ' . checked( $shipping, true, false ) . ' />';
	$output .= '</div>';

	$output .= '<div class="item-column block-column block-column-delete">';
	$output .= '<a href class="it-exchange-easy-canadian-sales-taxes-addon-delete-tax-rate it-exchange-remove-item">&times;</a>';
	$output .= '</div>';

	$output .= '</div>'; //end block-row

	return $output;
}

/**
 * Returns valid Tax types for Canada (GST, PST, and HST)
 *
 * @since 1.0.0
 *
 * @return array Valid tax typese
 */
function it_exchange_easy_canadian_sales_taxes_get_tax_types() {
	return apply_filters( 'it_exchange_easy_canadian_sales_taxes_tax_types', array( 'GST', 'HST', 'PST' ) );
}

function it_exchange_easy_canadian_sales_taxes_setup_session( $clear_cache = false ) {

	$cart = it_exchange_get_current_cart();

	if ( ! $cart->get_items()->count() ) {
		return false;
	}

	$provider      = new ITE_Canadian_Taxes_Provider();
	$tax_session   = it_exchange_get_session_data( 'addon_easy_canadian_sales_taxes' );
	$cart_subtotal = 0;

	foreach ( $cart->get_items()->without( 'shipping' ) as $item ) {
		if ( $item instanceof ITE_Taxable_Line_Item && ! $item->is_tax_exempt( $provider ) ) {
			$cart_subtotal += $item->get_total();
		}
	}

	$tax_session['cart_subtotal']            = $cart_subtotal;
	$tax_session['cart_subtotal_w_shipping'] = $cart_subtotal + $cart->calculate_total( 'shipping' );

	$taxes = $cart->get_items( 'tax', true )->with_only_instances_of( 'ITE_Canadian_Tax_Item' );
	$data  = array();

	/** @var ITE_Canadian_Tax_Item $tax */
	foreach ( $taxes as $tax ) {

		if ( ! $tax->get_tax_rate() ) {
			continue;
		}

		$data[] = array( $tax->get_tax_rate()->to_array() ) + array( 'total' => $tax->get_total() );
	}

	$tax_session['taxes']       = $data;
	$tax_session['total_taxes'] = $taxes->total();

	it_exchange_update_session_data( 'addon_easy_canadian_sales_taxes', $tax_session );

	return true;
}

/**
 * Gets tax information based on products in cart
 *
 * @since 1.0.0
 *
 * @param bool $format_price Whether or not to format the price or leave as a float
 * @param bool $clear_cache  Whether or not to force clear any cached tax values
 *
 * @return string The calculated tax
 */
function it_exchange_easy_canadian_sales_taxes_addon_get_total_taxes_for_cart( $format_price = true, $clear_cache = false ) {
	$taxes = 0;

	if ( it_exchange_easy_canadian_sales_taxes_setup_session() ) {
		$tax_session = it_exchange_get_session_data( 'addon_easy_canadian_sales_taxes' );
		if ( ! empty( $tax_session['total_taxes'] ) ) {
			$taxes = $tax_session['total_taxes'];
		}
	}

	if ( $format_price ) {
		$taxes = it_exchange_format_price( $taxes );
	}

	return $taxes;
}
