<?php
/**
 * ExchangeWP Easy Canadian Sales Taxes Add-on
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
				<h4>License Key</h4>
				<?php
				   $exchangewp_ecst_options = get_option( 'it-storage-exchange_addon_easy_canadian_sales_taxes' );
				   $license = $exchangewp_ecst_options['ecst_license'];
				   // var_dump($license);
				   $exstatus = trim( get_option( 'exchange_ecst_license_status' ) );
				   // var_dump($exstatus);
				?>
				<p>
				 <label class="description" for="exchange_ecst_license_key"><?php _e('Enter your license key'); ?></label>
				 <!-- <input id="ecst_license" name="it-exchange-add-on-ecst-ecst_license" type="text" value="<?php #esc_attr_e( $license ); ?>" /> -->
				 <?php $form->add_text_box( 'ecst_license' ); ?>
				 <span>
				   <?php if( $exstatus !== false && $exstatus == 'valid' ) { ?>
							<span style="color:green;"><?php _e('active'); ?></span>
							<?php wp_nonce_field( 'exchange_ecst_nonce', 'exchange_ecst_nonce' ); ?>
							<input type="submit" class="button-secondary" name="exchange_ecst_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
						<?php } else {
							wp_nonce_field( 'exchange_ecst_nonce', 'exchange_ecst_nonce' ); ?>
							<input type="submit" class="button-secondary" name="exchange_ecst_license_activate" value="<?php _e('Activate License'); ?>"/>
						<?php } ?>
				 </span>
				</p>

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

				if( isset( $_POST['exchange_ecst_license_activate'] ) ) {

					// run a quick security check
				 	if( ! check_admin_referer( 'exchange_ecst_nonce', 'exchange_ecst_nonce' ) )
						return; // get out if we didn't click the Activate button

					// retrieve the license from the database
					// $license = trim( get_option( 'exchange_ecst_license_key' ) );
					$exchangewp_ecst_options = get_option( 'it-storage-exchange_addon_easy_canadian_sales_taxes' );
 				  $license = $exchangewp_ecst_options['ecst_license'];

					// data to send in our API request
					$api_params = array(
						'edd_action' => 'activate_license',
						'license'    => $license,
						'item_name'  => urlencode( 'easy-canadian-sales-taxes' ), // the name of our product in EDD
						'url'        => home_url()
					);

					// Call the custom API.
					$response = wp_remote_post( 'https://exchangewp.com', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

					// make sure the response came back okay
					if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

						if ( is_wp_error( $response ) ) {
							$message = $response->get_error_message();
						} else {
							$message = __( 'An error occurred, please try again.' );
						}

					} else {

						$license_data = json_decode( wp_remote_retrieve_body( $response ) );

						if ( false === $license_data->success ) {

							switch( $license_data->error ) {

								case 'expired' :

									$message = sprintf(
										__( 'Your license key expired on %s.' ),
										date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
									);
									break;

								case 'revoked' :

									$message = __( 'Your license key has been disabled.' );
									break;

								case 'missing' :

									$message = __( 'Invalid license.' );
									break;

								case 'invalid' :
								case 'site_inactive' :

									$message = __( 'Your license is not active for this URL.' );
									break;

								case 'item_name_mismatch' :

									$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), 'ecst' );
									break;

								case 'no_activations_left':

									$message = __( 'Your license key has reached its activation limit.' );
									break;

								default :

									$message = __( 'An error occurred, please try again.' );
									break;
							}

						}

					}

					// Check if anything passed on a message constituting a failure
					if ( ! empty( $message ) ) {
						$base_url = admin_url( 'admin.php?page=' . 'ecst-license' );
						$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

						wp_redirect( $redirect );
						exit();
					}

					//$license_data->license will be either "valid" or "invalid"
					update_option( 'exchange_ecst_license_status', $license_data->license );
					// wp_redirect( admin_url( 'admin.php?page=' . 'ecst-license' ) );
					exit();
				}

			 // deactivate here
			 // listen for our activate button to be clicked
				if( isset( $_POST['exchange_ecst_license_deactivate'] ) ) {

					// run a quick security check
				 	if( ! check_admin_referer( 'exchange_ecst_nonce', 'exchange_ecst_nonce' ) )
						return; // get out if we didn't click the Activate button

					// retrieve the license from the database
					// $license = trim( get_option( 'exchange_ecst_license_key' ) );

					$exchangewp_ecst_options = get_option( 'it-storage-exchange_addon_easy_canadian_sales_taxes' );
 				  $license = $exchangewp_ecst_options['ecst_license'];


					// data to send in our API request
					$api_params = array(
						'edd_action' => 'deactivate_license',
						'license'    => $license,
						'item_name'  => urlencode( 'easy-canadian-sales-taxes' ), // the name of our product in EDD
						'url'        => home_url()
					);
					// Call the custom API.
					$response = wp_remote_post( 'https://exchangewp.com', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

					// make sure the response came back okay
					if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

						if ( is_wp_error( $response ) ) {
							$message = $response->get_error_message();
						} else {
							$message = __( 'An error occurred, please try again.' );
						}

						// $base_url = admin_url( 'admin.php?page=' . 'ecst-license' );
						// $redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

						wp_redirect( 'admin.php?page=ecst-license' );
						exit();
					}

					// decode the license data
					$license_data = json_decode( wp_remote_retrieve_body( $response ) );
					// $license_data->license will be either "deactivated" or "failed"
					if( $license_data->license == 'deactivated' ) {
						delete_option( 'exchange_ecst_license_status' );
					}

					// wp_redirect( admin_url( 'admin.php?page=' . 'ecst-license' ) );
					exit();

				}

			}

		/**
		* This is a means of catching errors from the activation method above and displaying it to the customer
		*
		* @since 1.2.2
		*/
		function exchange_ecst_admin_notices() {
		  if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

		  	switch( $_GET['sl_activation'] ) {

		  		case 'false':
		  			$message = urldecode( $_GET['message'] );
		  			?>
		  			<div class="error">
		  				<p><?php echo $message; ?></p>
		  			</div>
		  			<?php
		  			break;

		  		case 'true':
		  		default:
		  			// Developers can put a custom success message here for when activation is successful if they way.
		  			break;

		  	}
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
