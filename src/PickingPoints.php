<?php


namespace OfficeGest;


use WC_Countries;
use WC_Order;

class PickingPoints {

	public $parent;
	private $wc_version;
	public function __construct($parent)
	{
		$this->parent = $parent;
		$this->wc_version = WC_VERSION;
		$usa_pontos_recolha = OfficeGestDBModel::getOption('pontos_de_recolha');
		if ($usa_pontos_recolha==1){
			add_action( 'plugins_loaded', [$this,'woocommerce_pp_init'] );
			add_filter( 'woocommerce_billing_fields', array($this,'woocommerce_pp_billing_fields'), 10, 2 );
			add_filter( 'woocommerce_admin_billing_fields', array($this,'woocommerce_pp_admin_billing_fields') );
			add_action( 'woocommerce_customer_meta_fields', array($this,'woocommerce_pp_customer_meta_fields') );
			add_action( 'woocommerce_order_details_after_customer_details', array($this,'woocommerce_pp_order_details_after_customer_details') );
			add_filter( 'woocommerce_email_customer_details_fields', array($this,'woocommerce_pp_email_customer_details_fields'), 10, 3 );
			add_filter( 'woocommerce_ajax_get_customer_details', array($this,'woocommerce_pp_ajax_get_customer_details'), 10, 3 );
			add_filter( 'woocommerce_api_order_response', array($this,'woocommerce_pp_woocommerce_api_order_response'), 11, 2 ); //After WooCommerce own add_customer_data
			add_filter( 'woocommerce_api_customer_response', array($this,'woocommerce_pp_woocommerce_api_customer_response'), 10, 2 );
			add_action( 'woocommerce_checkout_process', array($this,'woocommerce_pp_checkout_process') );
			add_action( 'wp_enqueue_scripts', array($this,'woocommerce_pp_billing_fields_enqueue_scripts') );
        }

	}

	public function woocommerce_pp_init() {
	    Log::write('Initing picking points');


	}
	//Javascript
	public function woocommerce_pp_billing_fields_enqueue_scripts() {
		if ( is_checkout()) { //Default = USE Javascript (4.0)

			wp_enqueue_script( 'officegest-pp',   plugins_url( 'assets/js/functions.js', OFFICEGEST_PLUGIN_FILE), array( 'jquery' ) );
		}
	}

	//Add field to billing address fields - Globally
	public function woocommerce_pp_billing_fields( $fields, $country ) {
	        $options = OfficeGestCurl::getPickupPoints();
	        $data=[];
	        $data[null] = 'Seleccione o seu ponto de recolha';
	        foreach ($options as $k=>$v){
               $data[$v['id']] = $v['name'] . ' | ' . $v['address'].' '.$v['zipcode']. ' '.$v['city'].' | '.$v['obs'];
            }

			$fields['billing_pp'] = array(
				'type'			=>	'select',
				'label'			=> __('Seleccione o seu ponto de recolha'),
				'placeholder'	=> __('Escolha o Ponto de Recolha'),
				'class'         =>array('officegest_select2','form-row-wide'),
				'required'		=> true,
				'clear'			=> true, //Should be an option (?)
				'autocomplete'	=> false,
                'options'=>$data
            );
			return $fields;
		}

	//Add field to order admin panel

	public function woocommerce_pp_admin_billing_fields( $billing_fields ) {
		global $post;
		if ( $post->post_type === 'shop_order' || $post->post_type === 'shop_subscription' ) {
			$order = new WC_Order( $post->ID );
			$countries = new WC_Countries();
			$billing_country = version_compare( $this->wc_version, '3.0', '>=' ) ? $order->get_billing_country() : $order->billing_country;
			//Customer is portuguese or it's a new order ?
			$billing_fields['pp'] = array(
				'label' => __('Ponto de Recolha') ,
				'description' => __('Ponto de Recolha')
			);
		}
		return $billing_fields;
	}

	//Pre 3.0
	public function woocommerce_pp_found_customer_details_old( $customer_data, $user_id, $type_to_load ) {
		if ( $type_to_load === 'billing' ) {
			$customer_data['billing_pp'] = get_user_meta( $user_id, 'billing_pp', true );
		}
		return $customer_data;
	}
	//3.0 and above - See https://github.com/woocommerce/woocommerce/issues/12654
	public function woocommerce_pp_ajax_get_customer_details( $customer_data, $customer, $user_id ) {
		$customer_data['billing']['pp'] = $customer->get_meta( 'billing_pp' );
		return $customer_data;
	}

	//Add field to the admin user edit screen

	public function woocommerce_pp_customer_meta_fields( $show_fields ) {
		if ( isset( $show_fields['billing'] ) && is_array( $show_fields['billing']['fields'] ) ) {
			$show_fields['billing']['fields']['billing_pp'] = array(
				'label' => __('Ponto de Recolha'),
				'description' => __('Ponto de Recolha')
			);
		}
		return $show_fields;
	}

	//Add field to customer details on the Thank You page

	public function woocommerce_pp_order_details_after_customer_details( $order ) {
		$billing_country = version_compare( $this->wc_version, '3.0', '>=' ) ? $order->get_billing_country() : $order->billing_country;
		$billing_pp = version_compare( $this->wc_version, '3.0', '>=' ) ? $order->get_meta( '_billing_pp' ) : $order->billing_pp;
		$pp = OfficeGestCurl::getPickupPoints();
		$ponto_recolha = array_filter($pp, static function ($value) use ($billing_pp) {
			return ($value["id"] == $billing_pp);
		});
		$html = '';
		if (!empty($ponto_recolha)){
			$pr = array_values($ponto_recolha)[0];
			$html  = '['.$pr['id'].']'.$pr['name'] . ' ' . $pr['address'].' '.$pr['zipcode']. ' '.$pr['city'].' '.$pr['obs'];
		}
		if ($billing_pp ) {
			?>
			<tr>
				<th><?php echo  __( 'Ponto de Recolha'); ?>:</th>
				<td><?php echo esc_html( $html ); ?></td>
			</tr>
			<?php
		}
	}

	//Add field to customer details on Emails

	public function woocommerce_pp_email_customer_details_fields( $fields, $sent_to_admin, $order ) {
		$billing_pp = version_compare( $this->wc_version, '3.0', '>=' ) ? $order->get_meta( '_billing_pp' ) : $order->billing_pp;
		$pp = OfficeGestCurl::getPickupPoints();
		$ponto_recolha = array_filter($pp, static function ($value) use ($billing_pp) {
			return ($value["id"] == $billing_pp);
		});
		$html = '';
		if (!empty($ponto_recolha)){
			$pr = array_values($ponto_recolha)[0];
			$html  = '['.$pr['id'].']'.$pr['name'] . ' ' . $pr['address'].' '.$pr['zipcode']. ' '.$pr['city'].' '.$pr['obs'];
		}
		if ( $billing_pp ) {
			$fields['billing_pp'] = array(
				'label' => __('Ponto de Recolha'),
				'value' => wptexturize( $html )
			);
		}
		return $fields;
	}

	//Add field to the REST API

	public function woocommerce_pp_woocommerce_api_order_response( $order_data, $order ) {
		//Order
		if ( isset( $order_data['billing_address'] ) ) {
			$billing_pp = version_compare( $this->wc_version, '3.0', '>=' ) ? $order->get_meta( '_billing_pp' ) : $order->billing_pp;
			$order_data['billing_address']['pp'] = $billing_pp;
		}
		return $order_data;
	}

	public function woocommerce_pp_woocommerce_api_customer_response( $customer_data, $customer ) {
		//Customer
		if ( isset( $customer_data['billing_address'] ) ) {
			$billing_pp = version_compare( $this->wc_version, '3.0', '>=' ) ? $customer->get_meta( 'billing_pp' ) : get_user_meta( $customer->get_id(), 'billing_pp', true );
			$customer_data['billing_address']['pp'] = $billing_pp;
		}
		return $customer_data;
	}

	//Validation - Checkout

	public function woocommerce_pp_checkout_process() {
		$customer_country = version_compare( $this->wc_version, '3.0', '>=' ) ? WC()->customer->get_billing_country() : WC()->customer->get_country();
		$billing_pp = wc_clean( isset( $_POST['billing_pp'] ) ? $_POST['billing_pp'] : '' );
	}


}