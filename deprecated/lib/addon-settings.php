<?php
/**
 * iThemes Exchange Easy Canadian Sales Taxes Add-on
 * @package exchange-addon-easy-canadian-sales-taxes
 * @since 1.0.0
*/

/**
 * Call back for settings page
 *
 * This is set in options array when registering the add-on and called from it_exchange_enable_addon()
 *
 * @since 1.0.0
 * @return void
*/
function it_exchange_easy_canadian_sales_taxes_settings_callback() {
	$IT_Exchange_Easy_Canadian_Sales_Taxes_Add_On = new IT_Exchange_Easy_Canadian_Sales_Taxes_Add_On();
	$IT_Exchange_Easy_Canadian_Sales_Taxes_Add_On->print_settings_page();
}

/**
 * Sets the default options for ccanadiantomer pricing settings
 *
 * @since 1.0.0
 * @return array settings
*/
function it_exchange_easy_canadian_sales_taxes_default_settings( $defaults ) {
	$defaults = array(
		'tax-rates' => array(
			'AB' => array( //Province
				array(
					'type'     => 'GST', //Type
					'rate'     => '5',   //Rate
					'shipping' => false, //Apply to Shipping
				),
			),
			'BC' => array( // British Columbia
				array(
					'type'     => 'GST', //Type
					'rate'     => '5',   //Rate
					'shipping' => false, //Apply to Shipping
				),
				array(
					'type'     => 'PST', //Type
					'rate'     => '7',   //Rate
					'shipping' => false, //Apply to Shipping
				),
			),
			'MB' => array( // Manitoba
				array(
					'type'     => 'GST',
					'rate'     => '5',
					'shipping' => false,
				),
				array(
					'type'     => 'PST',
					'rate'     => '8',
					'shipping' => false,
				),
			),
			'NB' => array( // New Brunswick
				array(
					'type'     => 'HST',
					'rate'     => '13',
					'shipping' => false,
				),
			),
			'NF' => array( // Newfoundland
				array(
					'type'     => 'HST',
					'rate'     => '13',
					'shipping' => false,
				),
			),
			'NT' => array( // Northwest Territories
				array(
					'type'     => 'GST',
					'rate'     => '5',
					'shipping' => false,
				),
			),
			'NS' => array( // Nova Scotia
				array(
					'type'     => 'HST',
					'rate'     => '15',
					'shipping' => false,
				),
			),
			'NU' => array( // Nunavut
				array(
					'type'     => 'GST',
					'rate'     => '5',
					'shipping' => false,
				),
			),
			'ON' => array( // Ontario
				array(
					'type'     => 'HST',
					'rate'     => '13',
					'shipping' => false,
				),
			),// Prince Edward Island
			'PE' => array(
				array(
					'type'     => 'HST',
					'rate'     => '14',
					'shipping' => false,
				),
			),
			'QC' => array( // Quebec
				array(
					'type'     => 'GST',
					'rate'     => '5',
					'shipping' => false,
				),
				array(
					'type'     => 'PST',
					'rate'     => '9.975',
					'shipping' => false,
				),
			),
			'SK' => array( // Saskatchewan
				array(
					'type'     => 'GST',
					'rate'     => '5',
					'shipping' => false,
				),
				array(
					'type'     => 'PST',
					'rate'     => '5',
					'shipping' => false,
				),
			),
			'YT' => array( // Yukon Territory
				array(
					'type'     => 'PST',
					'rate'     => '5',
					'shipping' => false,
				),
			),
		),
	);
	return $defaults;
}
add_filter( 'it_storage_get_defaults_exchange_addon_easy_canadian_sales_taxes', 'it_exchange_easy_canadian_sales_taxes_default_settings' );

class IT_Exchange_Easy_Canadian_Sales_Taxes_Add_On {

	/**
	 * @var boolean $_is_admin true or false
	 * @since 1.0.0
	*/
	var $_is_admin;

	/**
	 * @var string $_current_page Current $_GET['page'] value
	 * @since 1.0.0
	*/
	var $_current_page;

	/**
	 * @var string $_current_add_on Current $_GET['add-on-settings'] value
	 * @since 1.0.0
	*/
	var $_current_add_on;

	/**
	 * @var string $statcanadian_message will be displayed if not empty
	 * @since 1.0.0
	*/
	var $statcanadian_message;

	/**
	 * @var string $error_message will be displayed if not empty
	 * @since 1.0.0
	*/
	var $error_message;

	/**
 	 * Class constructor
	 *
	 * Sets up the class.
	 * @since 1.0.0
	 * @return void
	*/
	function __construct() {
		$this->_is_admin       = is_admin();
		$this->_current_page   = empty( $_GET['page'] ) ? false : $_GET['page'];
		$this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : $_GET['add-on-settings'];

		if ( ! empty( $_POST ) && $this->_is_admin && 'it-exchange-addons' == $this->_current_page && 'easy-canadian-sales-taxes' == $this->_current_add_on ) {
			add_action( 'it_exchange_save_add_on_settings_easy_canadian_sales_taxes', array( $this, 'save_settings' ) );
			do_action( 'it_exchange_save_add_on_settings_easy_canadian_sales_taxes' );
		}
	}

	/**
 	 * Class deprecated constructor
	 *
	 * Sets up the class.
	 * @since 1.0.0
	 * @return void
	*/
	function IT_Exchange_Easy_Canadian_Sales_Taxes_Add_On() {
		self::__construct();
	}

	function print_settings_page() {
		global $new_values;
		$settings = it_exchange_get_option( 'addon_easy_canadian_sales_taxes', true, false );
		if ( empty( $settings['tax-rates'] ) )
			$settings = it_exchange_get_option( 'addon_easy_canadian_sales_taxes', true );
	
		$form_values  = empty( $this->error_message ) ? $settings : $new_values;
		$form_options = array(
			'id'      => apply_filters( 'it_exchange_add_on_easy_canadian_sales_taxes', 'it-exchange-add-on-easy-canadian-sales-taxes-settings' ),
			'enctype' => apply_filters( 'it_exchange_add_on_easy_canadian_sales_taxes_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-exchange-addons&add-on-settings=easy-canadian-sales-taxes',
		);
		$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-easy-canadian-sales-taxes' ) );

		if ( ! empty ( $this->statcanadian_message ) )
			ITUtility::show_statcanadian_message( $this->statcanadian_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );

		?>
		<div class="wrap">
			<?php screen_icon( 'it-exchange' ); ?>
			<h2><?php _e( 'Easy Canadian Sales Taxes Settings', 'LION' ); ?></h2>

			<?php do_action( 'it_exchange_easy_canadian_sales_taxes_settings_page_top' ); ?>
			<?php do_action( 'it_exchange_addon_settings_page_top' ); ?>

			<?php $form->start_form( $form_options, 'it-exchange-easy-canadian-sales-taxes-settings' ); ?>
				<?php do_action( 'it_exchange_easy_canadian_sales_taxes_settings_form_top' ); ?>
				<?php $this->get_easy_canadian_sales_taxes_form_table( $form, $form_values ); ?>
				<?php do_action( 'it_exchange_easy_canadian_sales_taxes_settings_form_bottom' ); ?>
				<p class="submit">
					<?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'LION' ), 'class' => 'button button-primary button-large' ) ); ?>
				</p>
			<?php $form->end_form(); ?>
			<?php do_action( 'it_exchange_easy_canadian_sales_taxes_settings_page_bottom' ); ?>
			<?php do_action( 'it_exchange_addon_settings_page_bottom' ); ?>
		</div>
		<?php
	}

	function get_easy_canadian_sales_taxes_form_table( $form, $settings = array() ) {
		if ( !empty( $settings ) )
			foreach ( $settings as $key => $var )
				$form->set_option( $key, $var );
		?>
		
        <div class="it-exchange-addon-settings it-exchange-easy-canadian-sales-taxes-addon-settings">
            <h4>
            	<?php _e( 'Current Tax Rates and Settings', 'LION' ) ?> 
            </h4>
			<div id="canadian-tax-rate-table">
			<?php
			$headings = array(
				__( 'Province', 'LION' ), __( 'Tax Type', 'LION' ), __( 'Tax Rate %', 'LION' ), __( 'Apply to Shipping?', 'LION' )
			);
			?>
			<div class="heading-row block-row">
				<?php $column = 0; ?>
				<?php foreach ( (array) $headings as $heading ) : ?>
				<?php $column++ ?>
				<div class="heading-column block-column block-column-<?php echo $column; ?>">
				<p class="heading"><?php echo $heading; ?></p>
				</div>
				<?php endforeach; ?>
				<div class="heading-column block-column block-column-delete"></div>
			</div>
			<?php
			$row = 0;
			//Alpha Sort
			$tax_rates = $settings['tax-rates'];
			ksort( $tax_rates );
			foreach( $tax_rates as $province => $rates ) {
				foreach( $rates as $rate ) {
					echo it_exchange_easy_canadian_sales_taxes_get_tax_row_settings( $row, $province, $rate );
					$row++;
				}
			}
			?>
			</div>
			<script type="text/javascript" charset="utf-8">
	            var it_exchange_easy_canadian_sales_taxes_addon_iteration = <?php echo $row; ?>;
	        </script>

			<p class="add-new">
				<?php $form->add_button( 'new-tax-rate', array( 'value' => __( 'Add New Tax Rate', 'LION' ), 'class' => 'button button-secondary button-large' ) ); ?>
			</p>
            
		</div>
		<?php
	}

	/**
	 * Save settings
	 *
	 * @since 1.0.0
	 * @return void
	*/
    function save_settings() {
    	global $new_values; //We set this as global here to modify it in the error check
    	
        $defaults = it_exchange_get_option( 'addon_easy_canadian_sales_taxes' );
        $new_values = ITForm::get_post_data();
        $organized_values = array();
                
        // Check nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-easy-canadian-sales-taxes-settings' ) ) {
            $this->error_message = __( 'Error. Please try again', 'LION' );
            return;
        }

        $errors = apply_filters( 'it_exchange_add_on_easy_canadian_sales_taxes_validate_settings', $this->get_form_errors( $new_values ), $new_values );
        
        if ( !empty( $new_values['tax-rates'] ) ) {
	        foreach( $new_values['tax-rates'] as $value ) {
	        	if ( !empty( $organized_values['tax-rates'][$value['province']] ) ) {
			        array_push( $organized_values['tax-rates'][$value['province']], array(
			        	'type'     => !empty( $value['type'] ) ? $value['type'] : '',
			        	'rate'     => !empty( $value['rate'] ) ? $value['rate'] : '',
			        	'shipping' => !empty( $value['shipping'] ) ? $value['shipping'] : '',
			        ) );
	        	} else {
			        $organized_values['tax-rates'][$value['province']] = array(
				        array(
				        	'type'     => !empty( $value['type'] ) ? $value['type'] : '',
				        	'rate'     => !empty( $value['rate'] ) ? $value['rate'] : '',
				        	'shipping' => !empty( $value['shipping'] ) ? $value['shipping'] : '',
				        ),
			        );
		        }
	        }
	        $new_values['tax-rates'] = $organized_values['tax-rates'];
        } else {
	        $new_values = $defaults;
        }
                                
        if ( ! $errors && it_exchange_save_option( 'addon_easy_canadian_sales_taxes', $new_values ) ) {
            ITUtility::show_status_message( __( 'Settings saved.', 'LION' ) );
        } else if ( $errors ) {
            $errors = implode( '<br />', $errors );
            $this->error_message = $errors;
        } else {
            $this->status_message = __( 'Settings not saved.', 'LION' );
        }
    }

    /**
     * Validates for values
     *
     * Returns string of errors if anything is invalid
     *
     * @since 0.1.0
     * @return void
    */
    public function get_form_errors( $values ) {
		$provinces = it_exchange_get_data_set( 'states', array( 'country' => 'CA' ) );
    	$tax_types = it_exchange_easy_canadian_sales_taxes_get_tax_types();
    	$errors = array();
    	
    	if ( !empty( $values['tax-rates'] ) )
    		$tax_rates = $values['tax-rates'];
    	else
	        return array( __( 'Unable to find tax rates to save, please try again.', 'LION' ) );
    
        foreach( $tax_rates as $tax_rate ) {
        	if ( empty( $tax_rate['province'] ) || empty( $provinces[$tax_rate['province']] ) ) {
                $errors[] = __( 'Missing or Invalid Province.', 'LION' );
	        	break;
        	} else if ( empty( $tax_rate['type'] ) || !in_array( $tax_rate['type'], $tax_types ) ) {
                $errors[] = __( 'Missing or Invalid Tax Type.', 'LION' );
	        	break;
        	} else if ( empty( $tax_rate['rate'] ) ) {
                $errors[] = __( 'Missing or Invalid Tax Rate.', 'LION' );
	        	break;
        	}
        }

        return $errors;
    }
}
