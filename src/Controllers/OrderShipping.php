<?php

namespace OfficeGest\Controllers;

use OfficeGest\Error;
use OfficeGest\OfficeGestCurl;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;
use WC_Order;

class OrderShipping
{

	/** @var int */
	public $product_id = 0;

	/** @var int */
	private $index;

	/** @var array */
	private $taxes = [];

	/** @var float */
	private $qty;

	/** @var float */
	private $price;

	/** @var string */
	private $exemption_reason;

	/** @var string */
	private $name;

	/** @var float */
	private $discount;

	/** @var WC_Order */
	private $order;

	/** @var string */
	private $reference;

	/** @var int */
	private $category_id;

	private $type = 2;
	private $summary = '';
	private $ean = '';
	private $unit_id;
	private $has_stock = 0;
	private $stock = 0;
	private $at_product_category = 'M';

	/**
	 * OrderProduct constructor.
	 * @param WC_Order $order
	 * @param int $index
	 */
	public function __construct($order, $index = 0)
	{
		$this->order = $order;
		$this->index = $index;
	}

	/**
	 * @return $this
	 * @throws Error
	 */
	public function create()
	{
		$this->qty = 1;
		$this->price = (float)$this->order->get_shipping_total();
		$this->name = $this->order->get_shipping_method();

		$this
			->setReference()
			->setDiscount()
			->setTaxes()
			->setProductId();

		return $this;
	}


	/**
	 * @return $this
	 */
	private function setReference()
	{
		$this->reference = OfficeGestDBModel::getOption('ARTICLE_PORTES');
		return $this;
	}

	/**
	 * @return $this
	 * @throws Error
	 * @throws \ErrorException
	 */
	private function setProductId()
	{
		$searchProduct = OfficeGestCurl::getArticle( $this->reference);
		if (!empty($searchProduct) && isset($searchProduct['id'])) {
			$this->product_id = $searchProduct['product_id'];
			return $this;
		}

		// Lets create the shipping product
		$this
			->setReference()
			->setCategory()
			->setUnitId();

		$insert = OfficeGestCurl::post('stock/articles', $this->mapPropsToValues(true));
		if (isset($insert['article_id'])) {
			$this->product_id = $insert['article_id'];
			return $this;
		}

		throw new Error('Erro ao inserir portes de envio');
	}

	/**
	 * @throws Error
	 */
	private function setCategory()
	{
		$categoryName = 'Loja Online';

		$categoryObj = new ProductCategory($categoryName);
		if (!$categoryObj->loadByName()) {
			$categoryObj->create();
		}

		$this->category_id = $categoryObj->category_id;

		return $this;
	}

	/**
	 * @return $this
	 */
	private function setUnitId()
	{
		$this->unit_id= 'UN';
		return $this;
	}


	/**
	 * Set the discount in percentage
	 * @return $this
	 */
	private function setDiscount()
	{
		$this->discount = $this->price <= 0 ? 100 : 0;
		$this->discount = $this->discount < 0 ? 0 : $this->discount > 100 ? 100 : $this->discount;

		return $this;
	}

	/**
	 * Set the taxes of a product
	 * @throws Error
	 */
	private function setTaxes()
	{
		$shippingTotal = 0;

		foreach ($this->order->get_shipping_methods() as $item_id => $item) {
			$taxes = $item->get_taxes();
			foreach ($taxes['total'] as $tax_rate_id => $tax) {
				$shippingTotal += (float)$tax;
			}
		}
		$taxRate = OfficeGestCurl::getArticle($this->reference)['vatid'];
		$taxvalue = OfficeGestDBModel::findTaxById($taxRate)['value'];


		if ((float)$taxvalue > 0) {
			$this->taxes[] = $this->setTax($taxRate,$taxvalue);
		}

		if (empty($this->taxes)) {
			$this->exemption_reason = defined('EXEMPTION_REASON_SHIPPING') ? EXEMPTION_REASON_SHIPPING : '';
		}

		return $this;
	}

	/**
	 * @param float $taxRate Tax Rate in percentage
	 *
	 * @return array
	 * @throws \ErrorException
	 */
	private function setTax($taxRate,$taxvalue)
	{
		$tax = [];
		$tax['tax_id'] = $taxRate;
		$tax['value'] = $taxvalue;
		return $tax;
	}

	/**
	 * @param bool $toInsert
	 * @return array
	 */
	public function mapPropsToValues($toInsert = false)
	{

		$values = [];
		$values['idarticle'] = $this->reference;
		$values['quantity'] = $this->qty;
		if ($this->taxes[0]['value']>0){
			$values['sellingprice'] = $this->price / ( 1+ ($this->taxes[0]['value']/100));
		}
		else{
			$values['sellingprice'] = $this->price;
		}
		$values['discount'] = $this->discount;
		$values['order'] = $this->index;
		$values['vatid'] = $this->taxes[0]['tax_id'];
		return $values;
	}
}