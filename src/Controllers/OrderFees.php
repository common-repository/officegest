<?php

namespace OfficeGest\Controllers;

use OfficeGest\Error;
use OfficeGest\OfficeGestCurl;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;
use WC_Order_Item_Fee;
use WC_Tax;

class OrderFees
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

	/** @var WC_Order_Item_Fee */
	private $fee;

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
	 * @param WC_Order_Item_Fee $fee
	 * @param int $index
	 */
	public function __construct($fee, $index = 0)
	{
		$this->fee = $fee;
		$this->index = $index;
	}

	/**
	 * @return $this
	 * @throws Error
	 */
	public function create()
	{
		$this->qty = 1;
		$this->price = (float)$this->fee['line_total'];

		$feeName = $this->fee->get_name();
		$this->name = !empty($feeName) ? $feeName : 'Taxa';

		$this
			->setReference()
			->setTaxes()
			->setProductId();

		return $this;
	}

	/**
	 * @return $this
	 */
	private function setReference()
	{
		$this->reference = 'Fee';
		return $this;
	}

	/**
	 * @return $this
	 * @throws Error
	 */
	private function setProductId()
	{
		$searchProduct = OfficeGestCurl::getArticle($this->reference);
		if ($searchProduct){
			$product =$searchProduct;
			$this->product_id = $product['id'];
			return $this;
		}

		// Lets create the shipping product
		$this->setUnitId();

		$insert = OfficeGestCurl::criaArtigonoOG($this->mapPropsToValues());
		if (isset($insert['article_id'])) {
			if ($insert['article_id']!=null){
				$this->product_id = $insert['article_id'];
				return $this;
			}
		}

		throw new Error(__('Erro ao inserir Taxa da encomenda'));
	}


	/**
	 * @return $this
	 * @throws Error
	 */
	private function setUnitId()
	{
		if (defined('MEASURE_UNIT')) {
			$this->unit_id = MEASURE_UNIT;
		} else {
			throw new Error(__('Unidade de medida não definida!'));
		}

		return $this;
	}

	/**
	 * Set the taxes of a product
	 * @throws Error
	 */
	private function setTaxes()
	{
		$taxRate = 0;
		$taxes = $this->fee->get_taxes();
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
	 * @return array
	 * @throws Error
	 */
	private function setTax($taxRate)
	{
		$tax = [];
		$tax['tax_id'] = OfficeGestDBModel::findTaxByValue((float)$taxRate);
		$tax['value'] = $taxRate;

		return $tax;
	}

	/**
	 * @param bool $toInsert
	 * @return array
	 */
	public function mapPropsToValues()
	{
		$values = [];
		$values['id'] = $this->reference;
		$values['quantity'] = 1;
		$values['articletype'] = $this->type;
		$values['description'] = $this->name;
		$values['sellingprice'] = $this->price;
		$values['unit'] = 'UN';
		$values['order'] = $this->index;
		$values['vatid'] = 'N';
		return $values;
	}
}