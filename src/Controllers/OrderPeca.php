<?php

namespace OfficeGest\Controllers;

use ErrorException;
use OfficeGest\Error;
use OfficeGest\OfficeGestCurl;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;
use WC_Order;
use WC_Order_Item_Product;
use WC_Tax;


class OrderPeca
{

	/** @var int */
	public $product_id = 0;

	/** @var int */
	private $order;

	/**
	 * @var WC_Order_Item_Product
	 */
	private $product;

	/** @var WC_Order */
	private $wc_order;

	/** @var array */
	private $taxes = [];

	/** @var float */
	public $qty;

	/** @var float */
	public $price;

	/** @var string */
	private $exemption_reason;

	/** @var string */
	private $name;

	/** @var string */
	private $summary;

	/** @var float */
	private $discount;

	/** @var int */
	private $warehouse_id = 0;

	private $meta = [];

	/**
	 * OrderProduct constructor.
	 * @param WC_Order_Item_Product $product
	 * @param WC_Order $wcOrder
	 * @param int $order
	 */
	public function __construct($product, $wcOrder, $order = 0)
	{
		$this->product = $product;
		$this->meta = OfficeGestDBModel::getPostMeta($this->product->get_product_id());
		$this->wc_order = $wcOrder;
		$this->order = $order;
	}

	/**
	 * @return $this
	 * @throws Error
	 * @throws ErrorException
	 */
	public function create()
	{
		$this
			->setName()
			->setPrice()
			->setQty()
			->setSummary()
			->setProductId()
			->setDiscount()
			->setTaxes()
			->setWarehouse();

		return $this;
	}

	public function setName()
	{
		$this->name = $this->product->get_name();
		return $this;
	}

	/**
	 * @param null|string $summary
	 * @return $this
	 */
	public function setSummary($summary = null)
	{
		if ($summary) {
			$this->summary = $summary;
		} else {
			$this->summary .= $this->getSummaryVariationAttributes();

			if (!empty($this->summary)) {
				$this->summary .= "\n";
			}

			$this->summary .= $this->getSummaryExtraProductOptions();
		}

		return $this;
	}

	/**
	 * @return string
	 */
	private function getSummaryVariationAttributes()
	{
		$summary = '';

		if ($this->product->get_variation_id() > 0) {
			$product = wc_get_product($this->product->get_variation_id());
			$attributes = $product->get_attributes();
			if (is_array($attributes) && !empty($attributes)) {
				$summary = wc_get_formatted_variation($attributes, true);
			}
		}

		return $summary;
	}

	/**
	 * @return string
	 */
	private function getSummaryExtraProductOptions()
	{
		$summary = '';
		$checkEPO = $this->product->get_meta('_tmcartepo_data', true);
		$extraProductOptions = maybe_unserialize($checkEPO);

		if ($extraProductOptions && is_array($extraProductOptions)) {
			foreach ($extraProductOptions as $extraProductOption) {
				if (isset($extraProductOption['name'], $extraProductOption['value'])) {

					if (!empty($summary)) {
						$summary .= "\n";
					}

					$summary .= $extraProductOption['name'] . ' ' . $extraProductOption['value'];
				}
			}
		}

		return $summary;
	}

	/**
	 * @return OrderPeca
	 */
	public function setPrice()
	{
		$this->price = (float)$this->product->get_subtotal() / (float)$this->product->get_quantity();

		$refundedValue = $this->wc_order->get_total_refunded_for_item($this->product->get_id());
		if ((float)$refundedValue > 0) {
			$this->price -= (float)$refundedValue;
		}

		return $this;
	}

	/**
	 * @return OrderPeca
	 */
	public function setQty()
	{
		$this->qty = (float)$this->product->get_quantity();

		$refundedQty = $this->wc_order->get_qty_refunded_for_item($this->product->get_id());
		if ((float)$refundedQty > 0) {
			$this->qty -= (float)$refundedQty;
		}

		return $this;
	}

	/**
	 * @return $this
	 * @throws Error
	 */
	private function setProductId()
	{
		$product = new Product($this->product->get_product());

		if (!$product->loadByReference()) {
			$product->create();
			$this->wc_order->add_order_note( __( 'Produto '.$product->reference.'  inserido no OfficeGest' ) );
		}
		$this->product_id = $product->getProductId();
		return $this;
	}


	/**
	 * Set the discount in percentage
	 * @return $this
	 */
	private function setDiscount()
	{
		if ((float)$this->product->get_total()>0) {


			$this->discount = ( 100 - ( ( (float) $this->product->get_total() * 100 ) / (float) $this->product->get_subtotal() ) );

			if ( $this->discount > 100 ) {
				$this->discount = 100;
			}

			if ( $this->discount < 0 ) {
				$this->discount = 0;
			}
		}
		else {
			$this->discount = 0;
		}

		return $this;
	}

	/**
	 * Set the taxes of a product
	 * @return OrderPeca
	 * @throws ErrorException
	 */
	private function setTaxes()
	{
		$taxRate = 0;
		$taxes = $this->product->get_taxes();
		foreach ($taxes['subtotal'] as $taxId => $value) {
			if (!empty($value)) {
				$taxRate = preg_replace('/[^0-9.]/', '', WC_Tax::get_rate_percent($taxId));
				if ((float)$taxRate > 0) {
					$this->taxes[] = $this->setTax($taxRate);
				}
			}
		}

		if (empty($this->taxes) || (float)$taxRate === 0) {
			$this->exemption_reason = OfficeGestDBModel::getOption('exemption_reason');
		}

		return $this;
	}

	/**
	 * @param float $taxRate Tax Rate in percentage
	 *
	 * @return array
	 * @throws ErrorException
	 */
	private function setTax($taxRate)
	{
		$tax = [];
		$tax['tax_id'] = OfficeGestDBModel::findTaxByValue((float)$taxRate);
		$tax['value'] = $taxRate;

		return $tax;
	}

	/**
	 * @param bool|int $warehouseId
	 *
	 * @return OrderPeca
	 */
	private function setWarehouse($warehouseId = false)
	{
		if ((int)$warehouseId > 0) {
			$this->warehouse_id = $warehouseId;
			return $this;
		}

		if (defined('OFFICEGEST_PRODUCT_WAREHOUSE') && (int)OFFICEGEST_PRODUCT_WAREHOUSE > 0) {
			$this->warehouse_id = (int)OFFICEGEST_PRODUCT_WAREHOUSE;
		}


		return $this;
	}

	/**
	 * @return array
	 * @throws ErrorException
	 */
	public function mapPropsToValues()
	{
		$values = [];
		$values['idarticle'] = $this->meta['_ecoauto_article_id'];
		if (OfficeGestCurl::getArticle($this->product_id)==false){
			$values['extradescription'] = $this->name;
		}

		$values['id_lproc_comp'] = $this->meta['_ecoauto_id'];
		$values['quantity'] = $this->qty;
		$values['sellingprice'] = $this->price;
		$values['discount'] = $this->discount;
		$values['order'] = $this->order;
		$values['re'] = $this->exemption_reason;
		if (count($this->taxes)>0){
			$values['vatid'] = $this->taxes[0]['tax_id'];
		}
		else{
			$values['vatid'] = 'N';
		}
		return $values;
	}
}