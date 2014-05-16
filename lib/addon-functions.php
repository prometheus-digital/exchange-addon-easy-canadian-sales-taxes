<?php
/**
 * iThemes Exchange Easy Canadian Sales Taxes Add-on
 * @package exchange-addon-easy-canadian-sales-taxes
 * @since 1.0.0
*/

function it_exchange_easy_canadian_sales_taxes_get_tax_row_settings( $row, $rate=array() ) {
	if ( empty( $rate ) ) { //just set some defaults
		$rate['province'] = 'AB';
		$rate['type']     = 'GST';
		$rate['rate']     = '5';
		$rate['shipping'] = false;
	}
		
	$output  = '<div class="item-row block-row">'; //start block-row
	
	$output .= '<div class="item-column block-column">';
    $output .= '<select name="tax-rates[' . $row . '][province]">';

	$provinces = it_exchange_get_data_set( 'states', array( 'country' => 'CA' ) );
	foreach( $provinces as $abbr => $name ) {
		
		$output .= '<option value="' . $abbr . '" ' . selected( $abbr, $rate['province'], false ) . '>' . $name . '</option>';
		
	}
	
    $output .= '</select>';
	$output .= '</div>';
	
	$output .= '<div class="item-column block-column">';
    $output .= '<select name="tax-rates[' . $row . '][type]">';
	
	$tax_types = it_exchange_easy_canadian_sales_taxes_get_tax_types();
	foreach( $tax_types as $type ) {
		
		$output .= '<option value="' . $type . '" ' . selected( $type, $rate['type'], false ) . '>' . $type . '</option>';
		
	}

	$output .= '</select>';
	$output .= '</div>';
	
	$output .= '<div class="item-column block-column">';
	$output .= '<input type="text" name="tax-rates[' . $row . '][rate]" value="' . $rate['rate'] . '" />';
	$output .= '</div>';
	
	$output .= '<div class="item-column block-column">';
	$shipping = empty( $rate['shipping'] ) ? false : true;
	$output .= '<input type="checkbox" name="tax-rates[' . $row . '][shipping]" ' . checked( $shipping, true, false ) . ' />';
	$output .= '</div>';
	
	$output .= '<div class="item-column block-column">';
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

/**
 * Gets tax information from transaction meta
 *
 * @since 1.0.0
 *
 * @param bool $format_price Whether or not to format the price or leave as a float
 * @return string The calculated tax from TaxCloud
*/

function it_exchange_easy_canadian_sales_taxes_addon_get_taxes_for_confirmation( $format_price=true ) {
    $taxes = 0;
    if ( !empty( $GLOBALS['it_exchange']['transaction'] ) ) {
        $transaction = $GLOBALS['it_exchange']['transaction'];
        $taxes = get_post_meta( $transaction->ID, '_it_exchange_easy_canadian_sales_taxes', true );
    }
    if ( $format_price )
        $taxes = it_exchange_format_price( $taxes );
    return $taxes;  
}

/**
 * Gets tax information from TaxCloud based on products in cart
 *
 * @since 1.0.0
 *
 * @param bool $format_price Whether or not to format the price or leave as a float
 * @param bool $clear_cache Whether or not to force clear any cached tax values
 * @return string The calculated tax from TaxCloud
*/
function it_exchange_easy_canadian_sales_taxes_addon_get_taxes_for_cart(  $format_price=true, $clear_cache=false ) {
	// Grab the tax rate
	$settings  = it_exchange_get_option( 'addon_easy_canadian_sales_taxes' );
	$taxes = 0;
	$cart = it_exchange_get_cart_data();
	$tax_cloud_session = it_exchange_get_session_data( 'addon_easy_canadian_sales_taxes' );
	
	$origin = array(
		'Address1' => $settings['bcanadianiness_address_1'],
		'City'     => $settings['bcanadianiness_city'],
		'State'    => $settings['bcanadianiness_state'],
		'Zip5'     => $settings['bcanadianiness_zip_5'],
		'Zip4'     => $settings['bcanadianiness_zip_4'],
	);
	if ( !empty( $settings['bcanadianiness_address_2'] ) )
		$origin['Address2'] = $settings['bcanadianiness_address_2'];
	
	//We always wnat to get the Shipping Address if it's available...
	$address = it_exchange_get_cart_shipping_address();
	
	//We at minimum need the Address1 and Zip
	if ( empty( $address['address1'] ) && empty( $address['zip'] ) ) 
		$address = it_exchange_get_cart_billing_address();
	
	if ( !empty( $address['address1'] ) && !empty( $address['zip'] ) ) {
		if ( !empty( $address['country'] ) && 'US' !== $address['country'] ) {
			//This is US taxes any other country and we don't need to calculate the tax
			if ( $format_price )
				$taxes = it_exchange_format_price( $taxes ); //zero
			return $taxes;
		}
		
		$dest = array(
			'Address1' => $address['address1'],
			'Address2' => !empty( $address['address2'] ) ? $address['address2'] : '',
			'City'     => !empty( $address['city'] ) ? $address['city'] : '',
			'State'    => !empty( $address['state'] ) ? $address['state'] : '',
			'Zip5'     => substr( $address['zip'], 0, 5 ), // jcanadiant get the first five
		);
		if ( !empty( $address['zip4'] ) )
			$dest['Zip4'] = $address['zip4'];
		
		$serialized_dest = maybe_serialize( $dest );
		
		//We want to store the destination, in case it changes so we know we need to generate tax from TaxCloud
		if ( empty( $tax_cloud_session['destination'] ) || $serialized_dest != $tax_cloud_session['destination'] ) {
			$tax_cloud_session['destination'] = $serialized_dest;
			$clear_cache = true; //force a new API call to TaxCloud to get the new tax
		}
	} else {
		if ( $format_price )
			$taxes = it_exchange_format_price( $taxes ); //zero
		return $taxes;
	}
	
	$products = it_exchange_get_cart_products();
	$products_hash = md5( maybe_serialize( $products ) );
	
	$shipping_cost = it_exchange_get_cart_shipping_cost( false, false );
	if ( empty( $tax_cloud_session['shipping_cost'] ) 
		|| $tax_cloud_session['shipping_cost'] != $shipping_cost ) {
		$tax_cloud_session['shipping_cost'] = $shipping_cost;
		$clear_cache = true;
	}
			
	// if we don't have a cache of the products_hash 
	// OR if the current cache doesn't match the current products hash
	if ( $clear_cache || empty( $tax_cloud_session['products_hash'] )
		|| $tax_cloud_session['products_hash'] !== $products_hash 
		|| !empty( $tax_cloud_session['new_certificate'] ) ) {
	
		$product_count = it_exchange_get_cart_products_count( true );
		$applied_coupons = it_exchange_get_applied_coupons();
		$ccanadiantomer = it_exchange_get_current_ccanadiantomer();
			
		$cart_items = array();
		$i = 0;
		//build the TaxCloud Query
		foreach( $products as $product ) {
			$price = it_exchange_get_cart_product_base_price( $product, false );
			$product_tic = it_exchange_get_product_feature( $product['product_id'], 'canadian-tic', array( 'setting' => 'code' ) );
			if ( !empty( $applied_coupons ) ) {
				foreach( $applied_coupons as $type => $coupons ) {
					foreach( $coupons as $coupon ) {
						if ( 'cart' === $type ) {
							if ( '%' === $coupon['amount_type'] ) {
								$price *= ( $coupon['amount_number'] / 100 );
							} else {
								$price -= ( $coupon['amount_number'] / $product_count );
							}
						} else if ( 'product' === $type ) {
							if ( $coupon['product_id'] === $product['product_id'] ) {
								if ( '%' === $coupon['amount_type'] ) {
									$price *= ( $coupon['amount_number'] / 100 );
								} else {
									$price -= ( $coupon['amount_number'] / $product_count );
								}
							}
						}
					}
				}
			}
			
			$cart_items[] = array(
				'Index'  => $i,
				'TIC'    => $product_tic,
				'ItemID' => $product['product_id'],
				'Price'  => $price,
				'Qty'    => $product['count'],
			);
			$i++;
		}
		
		//Add shipping, let TaxCloud decide if it needs to be taxed
		//TIC for Shipping is always 11010
		if ( !empty( $shipping_cost ) ) {
			$cart_items[] = array(
				'Index'  => $i,
				'TIC'    => '11010',
				'ItemID' => 'Shipping',
				'Price'  => $shipping_cost,
				'Qty'    => 1,
			);
		}
		
		if ( !empty( $settings['tax_exemptions'] ) && !empty( $tax_cloud_session['exempt_certificate'] ) ) {		
			$exempt_cert = $tax_cloud_session['exempt_certificate'];
			$tax_cloud_session['new_certificate'] = false;
		} else {
			$exempt_cert = null;
		}

		$query = array(
			'apiLoginID'        => $settings['tax_cloud_api_id'],
			'apiKey'            => $settings['tax_cloud_api_key'],
			'ccanadiantomerID'        => $ccanadiantomer->ID,
			'cartID'            => '',
			'cartItems'         => $cart_items,
			'origin'            => $origin,
			'destination'       => $dest,
			'deliveredBySeller' => FALSE,
			'exemptCert'        => $exempt_cert,
		);
		
		try {
        	$args = array(
        		'headers' => array(
        			'Content-Type' => 'application/json',
        		),
				'body' => json_encode( $query ),
		    );
        	$result = wp_remote_post( ITE_TAXCLOUD_API . 'Lookup', $args );

			if ( is_wp_error( $result ) ) {
				throw new Exception( $result->get_error_message() );
			} else if ( !empty( $result['body'] ) ) {
				$body = json_decode( $result['body'] );
				if ( 0 != $body->ResponseType ) {
					$checkout_taxes = 0;
					foreach( $body->CartItemsResponse as $item ) {
						$checkout_taxes += $item->TaxAmount;
					}
					$taxes = apply_filters( 'it_exchange_easy_canadian_sales_taxes_addon_get_taxes_for_cart', $checkout_taxes );
					$tax_cloud_session['cart_id'] = $body->CartID; //we need this to authorize and capture the tax
					$tax_cloud_session['products_hash'] = $products_hash;
				} else {
					$errors = array();
					foreach( $body->Messages as $message ) {
						$errors[] = $message->Message;
					}
					throw new Exception( sprintf( __( 'Unable to calculate Tax: %s', 'LION' ), implode( ',', $errors ) ) );

				}
			} else {
				throw new Exception( __( 'Unable to verify calculate Tax: Unknown Error', 'LION' ) );
			}
        } 
        catch( Exception $e ) {
			$errors[] = $e->getMessage();
			$new_values['bcanadianiness_verified'] = false;
        }
	} else {
	
		$taxes = $tax_cloud_session['taxes'];
		
	}
				
	$tax_cloud_session['taxes'] = $taxes;
	it_exchange_update_session_data( 'addon_easy_canadian_sales_taxes', $tax_cloud_session );

	if ( $format_price )
		$taxes = it_exchange_format_price( $taxes );
	return $taxes;
}