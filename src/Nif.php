<?php


namespace OfficeGest;


use WC_Countries;
use WC_Order;

class Nif {

	public $parent;
	private $wc_version;
	public function __construct($parent)
	{
		$this->parent = $parent;
		$this->wc_version = WC_VERSION;
		add_action( 'plugins_loaded', [$this,'woocommerce_nif_init'] );
		add_filter( 'woocommerce_billing_fields', array($this,'woocommerce_nif_billing_fields'), 10, 2 );
		add_filter( 'woocommerce_admin_billing_fields', array($this,'woocommerce_nif_admin_billing_fields') );
		add_action( 'woocommerce_customer_meta_fields', array($this,'woocommerce_nif_customer_meta_fields') );
		add_action( 'woocommerce_order_details_after_customer_details', array($this,'woocommerce_nif_order_details_after_customer_details') );
		add_filter( 'woocommerce_email_customer_details_fields', array($this,'woocommerce_nif_email_customer_details_fields'), 10, 3 );
		add_filter( 'woocommerce_ajax_get_customer_details', array($this,'woocommerce_nif_ajax_get_customer_details'), 10, 3 );
		add_filter( 'woocommerce_api_order_response', array($this,'woocommerce_nif_woocommerce_api_order_response'), 11, 2 ); //After WooCommerce own add_customer_data
		add_filter( 'woocommerce_api_customer_response', array($this,'woocommerce_nif_woocommerce_api_customer_response'), 10, 2 );
		add_action( 'woocommerce_checkout_process', array($this,'woocommerce_nif_checkout_process') );
		add_action( 'woocommerce_after_save_address_validation', array($this,'woocommerce_nif_after_save_address_validation'), 10, 3 );
		add_action( 'wp_enqueue_scripts', array($this,'woocommerce_nif_billing_fields_enqueue_scripts') );
	}

	public function woocommerce_nif_init() {
	    Log::write('Initing nif');

	}
	//Javascript
	public function woocommerce_nif_billing_fields_enqueue_scripts() {
		if ( is_checkout()) { //Default = USE Javascript (4.0)
			wp_enqueue_script( 'officegest-nif', plugins_url( 'assets/js/functions.js', OFFICEGEST_PLUGIN_FILE ), array( 'jquery' ) );
		}
	}

	//Add field to billing address fields - Globally
	public function woocommerce_nif_billing_fields( $fields, $country ) {
		$fields['billing_nif'] = array(
			'type'			=>	'text',
			'label'			=> apply_filters( 'woocommerce_nif_field_label', __( 'NIF / NIPC', 'officegest' ) ),
			'placeholder'	=> apply_filters( 'woocommerce_nif_field_placeholder', __( 'Portuguese VAT identification number', 'officegest' ) ),
			'class'			=> apply_filters( 'woocommerce_nif_field_class', array( 'form-row-first' ) ), //Should be an option (?)
			'required'		=> (
									$country == 'PT' || apply_filters( 'woocommerce_nif_show_all_countries', false )
									?
									apply_filters( 'woocommerce_nif_field_required', false ) //Should be an option (?)
									:
									false
								),
			'clear'			=> apply_filters( 'woocommerce_nif_field_clear', true ), //Should be an option (?)
			'autocomplete'	=> apply_filters( 'woocommerce_nif_field_autocomplete', 'on' ),
			'priority'		=> apply_filters( 'woocommerce_nif_field_priority', 120 ), //WooCommerce should order by this parameter but it doesn't seem to be doing so
			'maxlength'		=> apply_filters( 'woocommerce_nif_field_maxlength', 9 ),
			'validate'		=> (
									$country == 'PT'
									?
									(
										apply_filters( 'woocommerce_nif_field_validate', false )
										?
										array( 'nif_pt' ) //Does nothing, actually - Validation is down there on the 'woocommerce_checkout_process' action
										:
										array()
									)
									:
									false
								),
		);
		return $fields;

	}

	//Add field to order admin panel

	public function woocommerce_nif_admin_billing_fields( $billing_fields ) {
		global $post;
		if ( $post->post_type == 'shop_order' || $post->post_type == 'shop_subscription' ) {
			$order = new WC_Order( $post->ID );
			$countries = new WC_Countries();
			$billing_country = version_compare( WC_VERSION, '3.0', '>=' ) ? $order->get_billing_country() : $order->billing_country;
			//Customer is portuguese or it's a new order ?
			if ( $billing_country == 'PT' || ( $billing_country == '' && $countries->get_base_country() == 'PT' ) || apply_filters( 'woocommerce_nif_show_all_countries', false ) ) {
				$billing_fields['nif'] = array(
					'label' => apply_filters( 'woocommerce_nif_field_label', __( 'NIF / NIPC', 'officegest' ) ),
				);
			}
		}
		return $billing_fields;

	}

	//Pre 3.0
	public function woocommerce_nif_found_customer_details_old( $customer_data, $user_id, $type_to_load ) {
		if ( $type_to_load === 'billing' ) {
			if ( ( isset( $customer_data['billing_country'] ) && $customer_data['billing_country'] == 'PT' ) ) {
				$customer_data['billing_nif'] = get_user_meta( $user_id, $type_to_load . '_nif', true );
			}
		}
		return $customer_data;
	}
	//3.0 and above - See https://github.com/woocommerce/woocommerce/issues/12654
	public function woocommerce_nif_ajax_get_customer_details( $customer_data, $customer, $user_id ) {
		if ( ( isset( $customer_data['billing']['country']) && $customer_data['billing']['country'] == 'PT' ) || apply_filters( 'woocommerce_nif_show_all_countries', false ) ) {
			$customer_data['billing']['nif'] = $customer->get_meta( 'billing_nif' );
		}
		return $customer_data;
	}

	//Add field to the admin user edit screen

	public function woocommerce_nif_customer_meta_fields( $show_fields ) {
		if ( isset( $show_fields['billing'] ) && is_array( $show_fields['billing']['fields'] ) ) {
			$show_fields['billing']['fields']['billing_nif'] = array(
				'label' => apply_filters( 'woocommerce_nif_field_label', __( 'NIF / NIPC', 'officegest' ) ),
				'description' => apply_filters( 'woocommerce_nif_field_placeholder', __( 'Portuguese VAT identification number', 'officegest' ) ),
			);
		}
		return $show_fields;
	}

	//Add field to customer details on the Thank You page

	public function woocommerce_nif_order_details_after_customer_details( $order ) {
		$billing_country = version_compare( WC_VERSION, '3.0', '>=' ) ? $order->get_billing_country() : $order->billing_country;
		$billing_nif = version_compare( WC_VERSION, '3.0', '>=' ) ? $order->get_meta( '_billing_nif' ) : $order->billing_nif;
		if ( ( $billing_country == 'PT' || apply_filters( 'woocommerce_nif_show_all_countries', false ) ) && $billing_nif ) {
			?>
			<tr>
				<th><?php echo apply_filters( 'woocommerce_nif_field_label', __( 'NIF / NIPC', 'officegest' ) ); ?>:</th>
				<td><?php echo esc_html( $billing_nif ); ?></td>
			</tr>
			<?php
		}

	}

	//Add field to customer details on Emails

	public function woocommerce_nif_email_customer_details_fields( $fields, $sent_to_admin, $order ) {
		$billing_nif = version_compare( WC_VERSION, '3.0', '>=' ) ? $order->get_meta( '_billing_nif' ) : $order->billing_nif;
		if ( $billing_nif ) {
			$fields['billing_nif'] = array(
				'label' => apply_filters( 'woocommerce_nif_field_label', __( 'NIF / NIPC', 'officegest' ) ),
				'value' => wptexturize( $billing_nif )
			);
		}
		return $fields;
	}

	//Add field to the REST API

	public function woocommerce_nif_woocommerce_api_order_response( $order_data, $order ) {
		//Order
		if ( isset( $order_data['billing_address'] ) ) {
			$billing_nif = version_compare( WC_VERSION, '3.0', '>=' ) ? $order->get_meta( '_billing_nif' ) : $order->billing_nif;
			$order_data['billing_address']['nif'] = $billing_nif;
		}
		return $order_data;

	}

	public function woocommerce_nif_woocommerce_api_customer_response( $customer_data, $customer ) {
		//Customer
		if ( isset( $customer_data['billing_address'] ) ) {
			$billing_nif = version_compare( $this->wc_version, '3.0', '>=' ) ? $customer->get_meta( 'billing_nif' ) : get_user_meta( $customer->get_id(), 'billing_nif', true );
			$customer_data['billing_address']['nif'] = $billing_nif;
		}
		return $customer_data;
	}

	//Validation - Checkout

	public function woocommerce_nif_checkout_process() {
		if ( apply_filters( 'woocommerce_nif_field_validate', false ) ) {
			$customer_country = version_compare( WC_VERSION, '3.0', '>=' ) ? WC()->customer->get_billing_country() : WC()->customer->get_country();
			$countries = new WC_Countries();
			if ( $customer_country == 'PT' || ( $customer_country == '' && $countries->get_base_country() == 'PT' ) ) {
				$billing_nif = wc_clean( isset( $_POST['billing_nif'] ) ? $_POST['billing_nif'] : '' );
				if ( Tools::validaNIFPortugal( $billing_nif, true ) || ( trim( $billing_nif ) == '' &&  !apply_filters( 'woocommerce_nif_field_required', false ) ) ) { //If the field is NOT required and it's empty, we shouldn't validate it
					//OK
				} else {
					wc_add_notice(
						sprintf( __( 'You have entered an invalid %s for Portugal.', 'officegest' ), '<strong>'.apply_filters( 'woocommerce_nif_field_label', __( 'NIF / NIPC', 'officegest' ) ).'</strong>' ),
						'error'
					);
				}
			} else {
				//Not Portugal
			}
		} else {
			//All good - No validation required
		}

	}

	//Validation - Save address
	public function woocommerce_nif_after_save_address_validation( $user_id, $load_address, $address ) {
		if ( $load_address == 'billing' ) {
			if ( apply_filters( 'woocommerce_nif_field_validate', false ) ) {
				$country = wc_clean( isset( $_POST['billing_country'] ) ? $_POST['billing_country'] : '' );
				if ( $country == 'PT' ) {
					$billing_nif = wc_clean( isset( $_POST['billing_nif'] ) ? $_POST['billing_nif'] : '' );
					if ( Tools::validaNIFPortugal( $billing_nif, true ) || ( trim( $billing_nif ) == '' &&  !apply_filters( 'woocommerce_nif_field_required', false ) ) ) { //If the field is NOT required and it's empty, we shouldn't validate it
					//OK
					} else {
						wc_add_notice(
							sprintf( __( 'You have entered an invalid %s for Portugal.', 'officegest' ), '<strong>'.apply_filters( 'woocommerce_nif_field_label', __( 'NIF / NIPC', 'officegest' ) ).'</strong>' ),
							'error'
						);
					}
				}
			}
		}

	}


}