<?php
/**
 * Canadian Tax Rate.
 *
 * @since   1.4.0
 * @license GPLv2
 */

/**
 * Class ITE_Canadian_Tax_Rate
 */
class ITE_Canadian_Tax_Rate {

	/** @var string */
	private $state;

	/** @var int */
	private $index;

	/** @var array */
	private $data;

	/**
	 * ITE_Canadian_Tax_Rate constructor.
	 *
	 * @param string $state
	 * @param int    $index
	 * @param array  $data
	 */
	public function __construct( $state, $index, array $data ) {
		$this->state = $state;
		$this->index = $index;
		$this->data  = $data;
	}

	/**
	 * Create the tax rate from a code.
	 *
	 * @since 1.4.0
	 *
	 * @param string $code
	 *
	 * @return \ITE_Canadian_Tax_Rate|null
	 */
	public static function from_code( $code ) {

		list( $state, $index ) = explode( ':', $code );

		$settings = it_exchange_get_option( 'addon_easy_canadian_sales_taxes', true, false );

		if ( empty( $settings['tax-rates'] ) ) {
			$settings = it_exchange_get_option( 'addon_easy_canadian_sales_taxes', true );
		}

		if ( ! isset( $settings['tax-rates'][ $state ], $settings['tax-rates'][ $state ][ $index ] ) ) {
			return null;
		}

		return new self( $state, $index, $settings['tax-rates'][ $state ][ $index ] );
	}

	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return "{$this->get_state()}:{$this->index}";
	}

	/**
	 * Get the rate's country.
	 *
	 * @since 1.4.0
	 *
	 * @return string
	 */
	public function get_state() { return $this->state; }

	/**
	 * Get this rate's rate.
	 *
	 * @since 1.40
	 *
	 * @return float
	 */
	public function get_rate() { return $this->data['rate']; }

	/**
	 * Get the type of this rate, HST, PST, or GST.
	 *
	 * @since 1.4.0
	 *
	 * @return string
	 */
	public function get_type() { return $this->data['type']; }

	/**
	 * Does this rate apply to shipping.
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	public function applies_to_shipping() { return ! empty( $this->data['shipping'] ); }

	/**
	 * Convert the rate to an array.
	 *
	 * @since 1.4.0
	 *
	 * @return array
	 */
	public function to_array() { return $this->data; }
}