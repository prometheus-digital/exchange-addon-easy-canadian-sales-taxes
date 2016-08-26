<?php
/**
 * Canadian Tax Line Item.
 *
 * @since   1.4.0
 * @license GPLv2
 */

/**
 * Class ITE_Canadian_Tax_Item
 */
class ITE_Canadian_Tax_Item extends ITE_Line_Item implements ITE_Tax_Line_Item {

	/** @var ITE_Aggregate_Line_Item|ITE_Taxable_Line_Item */
	private $aggregate;

	/** @var ITE_Canadian_Tax_Rate */
	private $rate;

	/**
	 * @inheritDoc
	 */
	public function __construct( $id, \ITE_Parameter_Bag $bag, \ITE_Parameter_Bag $frozen ) {
		parent::__construct( $id, $bag, $frozen );

		$this->rate = ITE_Canadian_Tax_Rate::from_code( $this->get_param( 'code' ) );
	}

	/**
	 * Create a new canadian tax item.
	 *
	 * @since 1.4.0
	 *
	 * @param \ITE_Canadian_Tax_Rate      $rate
	 * @param \ITE_Taxable_Line_Item|null $item
	 *
	 * @return \ITE_Canadian_Tax_Item
	 */
	public static function create( ITE_Canadian_Tax_Rate $rate, ITE_Taxable_Line_Item $item = null ) {

		$id = md5( uniqid( 'CANADIAN', true ) . $rate->get_type() );

		$self = new self( $id, new ITE_Array_Parameter_Bag( array(
			'code' => (string) $rate
		) ), new ITE_Array_Parameter_Bag() );

		if ( $item ) {
			$self->set_aggregate( $item );
		}

		return $self;
	}

	/**
	 * @inheritDoc
	 */
	public function create_scoped_for_taxable( ITE_Taxable_Line_Item $item ) {
		return self::create( $this->rate, $item );
	}

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		if ( $this->frozen->has_param( 'name' ) ) {
			return $this->frozen->get_param( 'name' );
		}

		return $this->rate->get_type();
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return $this->frozen->has_param( 'description' ) ? $this->frozen->get_param( 'description' ) : '';
	}

	/**
	 * @inheritDoc
	 */
	public function get_quantity() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_amount() {

		if ( $this->frozen->has_param( 'amount' ) ) {
			return $this->frozen->get_param( 'amount' );
		}

		if ( $this->get_aggregate() ) {
			return $this->get_aggregate()->get_taxable_amount() * $this->get_aggregate()->get_quantity() * ( $this->get_rate() / 100 );
		} else {
			return 0;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_type( $label = false ) { return $label ? __( 'Tax', 'LION' ) : 'tax'; }

	/**
	 * @inheritDoc
	 */
	public function is_summary_only() { return true; }

	/**
	 * @inheritDoc
	 */
	public function get_rate() {

		if ( $this->has_param( 'rate' ) ) {
			return $this->get_param( 'rate' );
		}

		$rate = $this->rate;

		return $rate ? $rate->get_rate() : 0;
	}

	/**
	 * @inheritDoc
	 */
	public function applies_to( ITE_Taxable_Line_Item $item ) {

		if ( $item instanceof ITE_Shipping_Line_Item && ! $this->applies_to_shipping() ) {
			return false;
		}

		if ( $item->is_tax_exempt( $this->get_provider() ) ) {
			return false;
		}

		foreach ( $item->get_taxes() as $tax ) {
			if ( $tax instanceof self ) {
				return false; // Duplicate taxes are not allowed
			}
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function freeze() {
		$this->set_param( 'rate', $this->get_rate() );
		$this->set_param( 'applies_to_shipping', $this->get_tax_rate()->applies_to_shipping() );

		parent::freeze();
	}

	/**
	 * @inheritdoc
	 */
	public function get_provider() {
		return new ITE_Canadian_Taxes_Provider();
	}

	/**
	 * Whether this tax item applies to shipping items.
	 *
	 * @since 1.36.0
	 *
	 * @return bool
	 */
	protected function applies_to_shipping() {
		if ( $this->has_param( 'applies_to_shipping' ) ) {
			return (bool) $this->get_param( 'applies_to_shipping' );
		}

		return $this->get_tax_rate()->applies_to_shipping();
	}

	/**
	 * Get the tax rate.
	 *
	 * @since 1.4.0
	 *
	 * @return \ITE_Canadian_Tax_Rate|null
	 */
	public function get_tax_rate() { return $this->rate; }

	/**
	 * @inheritDoc
	 */
	public function set_aggregate( ITE_Aggregate_Line_Item $aggregate ) { $this->aggregate = $aggregate; }

	/**
	 * @inheritDoc
	 */
	public function get_aggregate() { return $this->aggregate; }
}