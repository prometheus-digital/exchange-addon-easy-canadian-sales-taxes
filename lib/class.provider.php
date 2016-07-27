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
	public function get_tax_code_for_product( IT_Exchange_Product $product ) {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function is_product_tax_exempt( IT_Exchange_Product $product ) {
		return (bool) $product->get_feature( 'canadian-tax-exempt-status' );
	}

	/**
	 * Get all of the tax rates for a given state.
	 *
	 * @since 1.4.0
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
}