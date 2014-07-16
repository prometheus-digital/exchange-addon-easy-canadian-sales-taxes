<?php
/**
 * iThemes Exchange Easy Canadian Sales Taxes Add-on
 * @package exchange-addon-easy-canadian-sales-taxes
 * @since 1.0.0
*/

function it_exchange_easy_canadian_sales_taxes_get_tax_row_settings( $row, $province='AB', $rate=array() ) {
	if ( empty( $rate ) ) { //just set some defaults
		$rate = array(
			'type'     => 'GST',
			'rate'     => '5',
			'shipping' => false,
		);
	}
	
	$output  = '<div class="item-row block-row">'; //start block-row
	
	$output .= '<div class="item-column block-column block-column-1">';
    $output .= '<select name="tax-rates[' . $row . '][province]">';

	$provinces = it_exchange_get_data_set( 'states', array( 'country' => 'CA' ) );
	foreach( $provinces as $abbr => $name ) {
		
		$output .= '<option value="' . $abbr . '" ' . selected( $abbr, $province, false ) . '>' . $name . '</option>';
		
	}
	
    $output .= '</select>';
	$output .= '</div>';
	
	$output .= '<div class="item-column block-column block-column-2">';
    $output .= '<select name="tax-rates[' . $row . '][type]">';
	
	$tax_types = it_exchange_easy_canadian_sales_taxes_get_tax_types();
	foreach( $tax_types as $type ) {
		
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
 * @return array Valid tax types
*/
function it_exchange_easy_canadian_sales_taxes_get_tax_types() {
	return apply_filters( 'it_exchange_easy_canadian_sales_taxes_tax_types', array( 'GST', 'HST', 'PST' ) );	
}

function it_exchange_easy_canadian_sales_taxes_setup_session( $clear_cache=false ) {
	$tax_session = it_exchange_get_session_data( 'addon_easy_canadian_sales_taxes' );
	$settings = it_exchange_get_option( 'addon_easy_canadian_sales_taxes' );
	$taxes = array();
	$total_taxes = 0;
	
	//We always want to get the Shipping Address if it's available...
	$address = it_exchange_get_cart_shipping_address();
	
	//We only care about the province!
	if ( empty( $address['state'] ) ) 
		$address = it_exchange_get_cart_billing_address();
	
	//State = Province in Canada
	if ( !empty( $address['state'] ) && !empty( $settings['tax-rates'][$address['state']] ) ) {
		if ( !empty( $address['country'] ) && 'CA' !== $address['country'] ) {
			return false;
		}
	} else {
		return false;
	}

	$cart_subtotal = 0;
	if ( ! $products = it_exchange_get_cart_products() )
		return false;

	foreach( (array) $products as $product ) {
		if ( !it_exchange_product_supports_feature( $product['product_id'], 'canadian-tax-exempt-status' )	
				|| !it_exchange_product_has_feature( $product['product_id'], 'canadian-tax-exempt-status' ) ) {
			$cart_subtotal += it_exchange_get_cart_product_subtotal( $product, false );
		}
	}
	$cart_subtotal = apply_filters( 'it_exchange_get_cart_subtotal', $cart_subtotal );
	$cart_subtotal_w_shipping = $cart_subtotal + it_exchange_get_cart_shipping_cost( false, false );
	
	if ( !empty( $tax_session ) ) {
		//We want to store the province, in case it changes so we know we need to recalculate tax
		if ( empty( $tax_session['province'] ) || $address['state'] != $tax_session['province'] ) {
			$tax_session['province'] = $address['state'];
			$clear_cache = true; //re-calculate taxes
		}
	
		//We want to store the cart subtotal, in case it changes so we know we need to recalculate tax
		if ( empty( $tax_session['cart_subtotal'] ) || $cart_subtotal != $tax_session['cart_subtotal'] ) {
			$tax_session['cart_subtotal'] = $cart_subtotal;
			$clear_cache = true; //re-calculate taxes
		}
		
		//We want to store the cart subtotal with shipping, in case it changes so we know we need to recalculate tax
		if ( empty( $tax_session['cart_subtotal_w_shipping'] ) || $cart_subtotal_w_shipping != $tax_session['cart_subtotal_w_shipping'] ) {
			$tax_session['cart_subtotal_w_shipping'] = $cart_subtotal_w_shipping;
			$clear_cache = true; //re-calculate taxes
		}
		
	} else {
		$clear_cache = true; //not really any cache, but it's easier this way :)
	}
	
	if ( $clear_cache ) {
		$tax_rates = $settings['tax-rates'][$address['state']];
		foreach ( $tax_rates as $tax ) {
			if ( $tax['shipping'] ) {
				$tax['total'] = ( $tax_session['cart_subtotal_w_shipping'] * ( $tax['rate'] / 100 ) );
			} else {
				$tax['total'] = ( $tax_session['cart_subtotal'] * ( $tax['rate'] / 100 ) );
			}
			$taxes[] = $tax;
			$total_taxes += $tax['total'];
		}
	} else {
		$taxes = $tax_session['taxes'];
		$total_taxes = $tax_session['total_taxes'];
	}
	
	$tax_session['taxes'] = $taxes;
	$tax_session['total_taxes'] = $total_taxes;
	it_exchange_update_session_data( 'addon_easy_canadian_sales_taxes', $tax_session );
									
	return true;

}

/**
 * Gets tax information based on products in cart
 *
 * @since 1.0.0
 *
 * @param bool $format_price Whether or not to format the price or leave as a float
 * @param bool $clear_cache Whether or not to force clear any cached tax values
 * @return string The calculated tax
*/
function it_exchange_easy_canadian_sales_taxes_addon_get_total_taxes_for_cart( $format_price=true, $clear_cache=false ) {
	$taxes = 0;

	if ( it_exchange_easy_canadian_sales_taxes_setup_session() ) {
		$tax_session = it_exchange_get_session_data( 'addon_easy_canadian_sales_taxes' );
		if ( !empty( $tax_session['total_taxes'] ) ) {
			$taxes = $tax_session['total_taxes'];
		}
	}
								
	if ( $format_price )
		$taxes = it_exchange_format_price( $taxes );
	return $taxes;
}
