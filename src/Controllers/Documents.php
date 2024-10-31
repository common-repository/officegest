<?php

namespace OfficeGest\Controllers;

use Exception;
use OfficeGest\Error;
use OfficeGest\Log;
use OfficeGest\OfficeGestCurl;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;
use WC_Order;
use WC_Order_Item_Fee;
use WC_Order_Item_Product;

/**
 * Class Documents
 * Used to create or update a OfficeGest Document
 * @package OfficeGest\Controllers
 */
class Documents {
	/** @var array */
	private $company = [];

	/** @var int */
	private $orderId;

	/** @var WC_Order */
	public $order;

	/** @var bool|Error */
	private $error = false;

	/** @var int */
	public $document_id;

	/** @var int */
	private $customer_id;

	/** @var int */
	private $document_set_id;

	/** @var int */
	private $documentId;

	/** @var string */
	private $our_reference = '';

	/** @var string */
	private $your_reference = '';

	/** @var string in Y-m-d */
	private $date;

	/** @var string in Y-m-d */
	private $expiration_date;

	private $params;

	// Delivery parameters being used if the option is set
	private $delivery_datetime;
	private $delivery_method_id = 0;

	private $delivery_departure_address = '';
	private $delivery_departure_city = '';
	private $delivery_departure_zip_code = '';
	private $delivery_departure_country = '';
	private $pickup_point = '';

	private $delivery_destination_address = '';
	private $delivery_destination_city = '';
	private $delivery_destination_country = '';
	private $delivery_destination_zip_code = '';
	private $notes = '';

	private $status = 0;

	private $products = [];
	private $payments = [];

	public $documentType;

	/** @var int */
	private $exchange_currency_id;
	private $exchange_rate;

	/**
	 * Documents constructor.
	 *
	 * @param int $orderId
	 *
	 * @throws Error
	 */
	public function __construct( $orderId ) {
		$this->orderId = $orderId;
		$this->order   = new WC_Order( (int) $orderId );


		if ( ! defined( 'DOCUMENT_TYPE' ) ) {
			throw new Error( __( 'Tipo de documento não definido nas opções' ) );
		}

		$this->documentType = isset( $_GET['document_type'] ) ? sanitize_text_field( $_GET['document_type'] ) : DOCUMENT_TYPE;
	}

	/**
	 * Gets the error object
	 * @return bool|Error
	 */
	public function getError() {
		return $this->error ?: false;
	}

	/**
	 * @return mixed
	 * @throws Error
	 */
	public function getDocumentId() {
		if ( $this->documentId > 0 ) {
			return $this->documentId;
		}

		throw new Error( __( 'Document not found' ) );
	}

	/**
	 * @return Documents
	 * @throws \ErrorException
	 */
	public function createDocument() {

		try {
			$this->customer_id     = ( new OrderCustomer( $this->order ) )->create();
			$this->date            = date( 'Y-m-d' );
			$this->expiration_date = date( 'Y-m-d' );

			$this->your_reference = '#' . $this->order->get_order_number();
			$this->pickup_point = $this->order->get_meta('_billing_pp');

			$this
				->setProducts()
				->setShipping()
				->setFees()
				->setExchangeRate()
				->setShippingInfo()
				//->setPaymentMethod()
				->setNotes();


			// One last validation
			if ( ( ! isset( $_GET['force'] ) || sanitize_text_field( $_GET['force'] ) !== 'true' ) && $this->isReferencedInDatabase() ) {
				throw new Error(
					__( 'O documento da encomenda ' . $this->order->get_order_number() . ' já foi gerado anteriormente!' ) .
					" <a href='admin.php?page=officegest&action=genInvoice&id=" . $this->orderId . "&force=true'>" . __( 'Gerar novamente' ) . '</a>'
				);
			}
			Log::write('Document:'.json_encode($this));

			$insertedDocument = OfficeGestCurl::criaDocumento( $this->documentType, $this->mapPropsToValues() );
			if ( ! isset( $insertedDocument['numdoc'] ) ) {
				throw new Error( sprintf( __( 'Atenção, houve um problema ao inserir o documento %s' ), $this->order->get_order_number() ) );
			}





			$this->document_id = $insertedDocument['numdoc'];
			add_post_meta( $this->orderId, '_officegest_sent', $this->document_id, true );
			add_post_meta( $this->orderId, '_officegest_doctype', $this->documentType, true );
			$document_status = OfficeGestDBModel::getOption('DOCUMENT_STATUS');
			add_post_meta( $this->orderId, '_officegest_docstatus', $document_status, true );
			$addedDocument = OfficeGestCurl::getDocument($this->documentType, $insertedDocument['numdoc']);
			$order_total = $this->order->get_total();

			if ( $document_status === 'closed' ) {
				// Validate if the document totals match can be closed
				$orderTotal    = ( (float) $order_total - (float) $this->order->get_total_refunded() );
				$documentTotal = (float) $addedDocument['document']['total'] > 0 ? (float)  $addedDocument['document']['total'] : 0;

				if ( $orderTotal !== $documentTotal ) {
					$viewUrl = admin_url( 'admin.php?page=officegest&action=getInvoice&id=' . $this->orderId );
					throw new Error(
						__( 'O documento foi inserido mas os totais não correspondem. ' ) .
						'<a href="' . $viewUrl . '" target="_BLANK">Ver documento</a>'
					);
				}

				$closeDocument                = [];
				$closeDocument['document_id'] = $this->document_id;
				$closeDocument['status']      = 1;

				// Send email to the client
				if ( defined( 'EMAIL_SEND' ) && EMAIL_SEND ) {
					$this->order->add_order_note( __( 'Documento enviado por email para o cliente' ) );

					$closeDocument['send_email']   = [];
					$closeDocument['send_email'][] = [
						'email' => $this->order->get_billing_email(),
						'name'  => $addedDocument['entity_name'],
						'msg'   => ''
					];
				}
				$this->order->add_order_note( __( 'Documento inserido no OfficeGest' ) );
			} else {
				$this->order->add_order_note( __( 'Documento inserido como rascunho no OfficeGest' ) );
			}
		} catch ( Error $error ) {
			$this->document_id = 0;
			$this->error       = $error;
		}

		return $this;
	}

	/**
	 * @return $this
	 * @throws Error
	 * @throws \ErrorException
	 */
	private function setProducts() {
		foreach ( $this->order->get_items() as $itemIndex => $orderProduct ) {
			/** @var $orderProduct WC_Order_Item_Product */
			$meta = OfficeGestDBModel::getPostMeta($orderProduct->get_product_id());

			if (!isset($meta['_ecoauto_id'])){
				//artigo_normal
				$newOrderProduct  = new OrderProduct( $orderProduct, $this->order, count( $this->products ) );
				$this->products[] = $newOrderProduct->create()->mapPropsToValues();
			}
			else{
				$newOrderProduct  = new OrderPeca( $orderProduct, $this->order, count( $this->products ) );
				$this->products[] = $newOrderProduct->create()->mapPropsToValues();
			}
		}
		return $this;
	}

	/**
	 * @return $this
	 * @throws Error
	 */
	private function setShipping() {
		if ( $this->order->get_shipping_method() && (float) $this->order->get_shipping_total() > 0 ) {
			$newOrderShipping = new OrderShipping( $this->order, count( $this->products ) );
			$this->products[] = $newOrderShipping->create()->mapPropsToValues();
		}

		return $this;
	}

	/**
	 * @return $this
	 * @throws Error
	 */
	private function setFees() {
		foreach ( $this->order->get_fees() as $key => $item ) {
			/** @var $item WC_Order_Item_Fee */
			$feePrice = abs( $item['line_total'] );

			if ( $feePrice > 0 ) {
				$newOrderFee      = new OrderFees( $item, count( $this->products ) );
				$this->products[] = $newOrderFee->create()->mapPropsToValues();

			}
		}

		return $this;
	}

	/**
	 * @return $this
	 * @throws Error
	 */
	private function setExchangeRate() {

		$currency = OfficeGestCurl::getParams('defaultcurrencycode');
		if ($currency !== $this->order->get_currency() ) {
			$this->exchange_currency_id = Tools::getCurrencyIdFromCode( $this->order->get_currency() );
			$this->exchange_rate        = 1;

			if ( ! empty( $this->products ) && is_array( $this->products ) ) {
				foreach ( $this->products as &$product ) {
					$product['price'] /= $this->exchange_rate;
				}
			}
		}

		return $this;
	}

	/**
	 * Set the document Payment Method
	 * @return $this
	 * @throws Error
	 */
	private function setPaymentMethod() {
		$paymentMethodName = $this->order->get_payment_method_title();

		if ( ! empty( $paymentMethodName ) ) {
			$paymentMethod = new Payment( $paymentMethodName );
			if ( ! $paymentMethod->loadByName() ) {
				$paymentMethod->create();
			}

			if ( (int) $paymentMethod->payment_method_id > 0 ) {
				$this->payments[] = [
					'payment_method_id' => (int) $paymentMethod->payment_method_id,
					'date'              => date( 'Y-m-d H:i:s' ),
					'value'             => $orderTotal = ( (float) $this->order->get_total() - (float) $this->order->get_total_refunded() )
				];
			}
		}

		return $this;
	}

	/**
	 * Set the document customer notes
	 */
	private function setNotes() {
		$notes = $this->order->get_customer_order_notes();
		if ( ! empty( $notes ) ) {
			foreach ( $notes as $index => $note ) {
				$this->notes .= $note->comment_content;
				if ( $index !== count( $notes ) - 1 ) {
					$this->notes .= '<br>';
				}
			}
		}
	}

	/**
	 * @return $this
	 * @throws Error
	 * @throws \ErrorException
	 */
	public function setShippingInfo() {
		if ( defined( 'SHIPPING_INFO' ) && SHIPPING_INFO ) {
			$this->company  = OfficeGestCurl::getCompany();
			$this->params = OfficeGestCurl::getParams();
			$this->delivery_destination_zip_code = $this->order->get_shipping_postcode();
			if ( $this->order->get_shipping_country() === 'PT' ) {
				$this->delivery_destination_zip_code = Tools::zipCheck( $this->delivery_destination_zip_code );
			}

			//$this->delivery_method_id = $this->company['delivery_method_id'];
			$this->delivery_datetime  = date( 'Y-m-d H:i:s' );
			$this->delivery_departure_address  = $this->company['address'];
			$this->delivery_departure_city     = $this->company['city'];
			$this->delivery_departure_zip_code = $this->company['zip'];
			$this->delivery_departure_country  = $this->params['countrydefaultcode'];

			$this->delivery_destination_address = $this->order->get_shipping_address_1() . ' ' . $this->order->get_shipping_address_2();
			$this->delivery_destination_city    = $this->order->get_shipping_city();
			$this->delivery_destination_country = Tools::getCountryIdFromCode( $this->order->get_shipping_country() );
		}

		return $this;
	}

	/**
	 * Checks if this document is referenced in database
	 * @return bool
	 */
	public function isReferencedInDatabase() {
		return $this->order->get_meta( '_officegest_sent' ) ? true : false;
	}

	/**
	 * Map this object properties to an array to insert/update a officegest document
	 * @return array
	 */
	private function mapPropsToValues() {
		$values                        = [];
		$values['idcustomer']         = $this->customer_id;
		$this->recalculateLines();
		$this->products[] = [
			'idarticle'=>'.',
			'extradescription'=>'Encomenda '.get_option('blogname').': '.$this->orderId,
			'quantity'=>'1',
			'sellingprice'=>0
		];

		if ($this->notes!=''){
			$this->products[] = [
				'idarticle'=>'.',
				'extradescription'=>$this->notes,
				'quantity'=>'1',
				'sellingprice'=>0
			];
		}


		if ($this->pickup_point!=''){
			$values['dlv_mode'] = 1;
			$values['id_pickup'] = $this->pickup_point;
			$pp = OfficeGestCurl::getPickupPoints();
			$billing_pp = $this->pickup_point;
			$ponto_recolha = array_filter($pp, static function ($value) use ($billing_pp) {
				return ($value["id"] == $billing_pp);
			});
			if (!empty($ponto_recolha)){
				$pr = array_values($ponto_recolha)[0];
				$this->products[] = [
					'idarticle'=>'.',
					'extradescription'=>' ',
					'quantity'=>'1',
					'sellingprice'=>0
				];
				$this->products[] = [
					'idarticle'=>'.',
					'extradescription'=>'Ponto de Recolha: '.  '['.$pr['id'].'] '.$pr['name'],
					'quantity'=>'1',
					'sellingprice'=>0
				];
				$this->products[] = [
					'idarticle'=>'.',
					'extradescription'=>'Morada Ponto de Recolha: '.  $pr['address'].' '.$pr['zipcode']. ' '.$pr['city'],
					'quantity'=>'1',
					'sellingprice'=>0
				];
				$this->products[] = [
					'idarticle'=>'.',
					'extradescription'=>'Obs Pontos de Recolha: '.  $pr['obs'],
					'quantity'=>'1',
					'sellingprice'=>0
				];
				$values['obs'] = 'Ponto de Recolha: '.  '['.$pr['id'].'] - '.$pr['name'].PHP_EOL.$pr['address'].' '.$pr['zipcode']. ' '.$pr['city'].PHP_EOL.$pr['obs'];
			}

		}
		$values['lines'] = $this->products;
		if ( ! empty( $this->exchange_currency_id ) ) {
			$values['exchange_currency_id'] = $this->exchange_currency_id;
			$values['exchange_rate']        = $this->exchange_rate;
		}


		return $values;
	}

	/**
	 * This method will download a document if it is closed
	 * Or it will redirect to the OfficeGest edit page
	 *
	 * @param $documentId
	 *
	 * @return bool
	 * @throws Error
	 */
	public static function showDocument( $documentId ) {
		
		$order = new WC_Order( (int) $documentId );

		$documentType = $order->get_meta('_officegest_doctype');
		$documentStatus = $order->get_meta('_officegest_docstatus');
		$documentId = $order->get_meta('_officegest_sent');

		$domain = OfficeGestDBModel::getOFFICEGESTDOMAIN();
		if ($documentStatus=='draft'){
			$url = $domain . '/vendas/' . $documentType . '/rasc/' . base64_encode($documentId) ;
		}
		else{
			$url = $domain . '/vendas/' . $documentType . '/view/' . base64_encode($documentId) ;
		}
		header( 'Location: '.$url );
		exit;
	}

	private static function getDocumentTypeName( $invoice ) {
		switch ( $invoice['document_type']['saft_code'] ) {
			case 'FT' :
			default:
				$typeName = 'Faturas';
				break;
			case 'FR' :
				$typeName = 'FaturasRecibo';
				break;
			case 'FS' :
				$typeName = 'FaturaSimplificada';
				break;
			case 'GT' :
				$typeName = 'GuiasTransporte';
				break;
			case 'NEF' :
				$typeName = 'NotasEncomenda';
				break;
			case 'WOR':
			case 'ORC':
			case 'OR':
				$typeName = 'Orcamentos';
				break;
		}

		return $typeName;
	}

	private function recalculateLines() {
		$taxable=OfficeGestDBModel::getOption('articles_taxa')==1;
		foreach ($this->products as $k=>$v){
			$artigo = OfficeGestDBModel::findArticle($this->products[$k]['idarticle']);
			if ($taxable===1){
				$taxa = OfficeGestDBModel::findTaxById($artigo['vatid']);
				$calcula_iva = $this->products[$k]['sellingprice'];
				$iva = 1+($taxa['value']/100);
				$this->products[$k]['sellingprice'] =$calcula_iva / $iva;
			}
		}
	}
}