<?php
/**
 * Tax Provider for Canadian Taxes.
 *
 * @since   1.4
 * @license GPLv2
 */

/**
 * Class ITE_Canadian_Taxes_Provider
 */
class ITE_Canadian_Taxes_Provider extends ITE_Tax_Provider {

	/**
	 * @inheritDoc
	 */
	public function is_product_tax_exempt( IT_Exchange_Product $product ) {
		return (bool) $product->get_feature( 'canadian-tax-exempt-status' );
	}

	/**
	 * Get all of the tax rates for a given state.
	 *
	 * @since 2.0.0
	 *
	 * @param string $state
	 *
	 * @return \ITE_Canadian_Tax_Rate[]
	 */
	public function get_rates_for_state( $state ) {

		$settings = it_exchange_get_option( 'addon_easy_canadian_sales_taxes', true, false );

		if ( empty( $settings['tax-rates'] ) ) {
			$settings = it_exchange_get_option( 'addon_easy_canadian_sales_taxes', true );
		}

		if ( ! isset( $settings['tax-rates'][ $state ] ) ) {
			return array();
		}

		$rates = array();

		foreach ( $settings['tax-rates'][ $state ] as $i => $data ) {
			$rates[] = new ITE_Canadian_Tax_Rate( $state, $i, $data );
		}

		return $rates;
	}

	/**
	 * @inheritDoc
	 */
	public function get_item_class() {
		return 'ITE_Canadian_Tax_Item';
	}

	/**
	 * @inheritDoc
	 */
	public function add_taxes_to( ITE_Taxable_Line_Item $item, ITE_Cart $cart ) {

		$address = $cart->get_shipping_address() ? $cart->get_shipping_address() : $cart->get_billing_address();
		$rates   = $this->get_rates_for_state( $address['state'] );

		foreach ( $rates as $rate ) {
			$item->add_tax( ITE_Canadian_Tax_Item::create( $rate, $item ) );
			$cart->save_item( $item );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function is_restricted_to_location() {
		return new ITE_Simple_Zone( array(
			'country' => 'CA',
			'state'   => array_keys( it_exchange_get_data_set( 'states', array( 'country' => 'CA' ) ) )
		) );
	}
}