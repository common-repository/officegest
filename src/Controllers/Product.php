<?php

namespace OfficeGest\Controllers;

use OfficeGest\Error;
use OfficeGest\Log;
use OfficeGest\OfficeGestCurl;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;
use WC_Product;
use WC_Tax;

/**
 * Class Product
 * @package OfficeGest\Controllers
 */
class Product
{

	/** @var WC_Product */
	private $product;

	public $product_id;
	public $category_id;
	private $type;
	public $reference;
	public $name;
	private $summary = '';
	private $ean = '';
	public $price;
	public $purchasing;
	public $pvp;
	private $unit_id;
	public $has_stock;
	public $stock;
	private $at_product_category = 'M';
	private $exemption_reason;
	private $taxes;

	/**
	 * Product constructor.
	 * @param WC_Product $product
	 */
	public function __construct($product)
	{
		$this->product = $product;
	}

	/**
	 * Loads a product
	 * @throws Error
	 * @throws \ErrorException
	 */
	public function loadByReference()
	{
		$this->setReference();
		$searchProduct = OfficeGestCurl::getArticle($this->reference);
		if ($searchProduct){
			$product =$searchProduct;
			$this->product_id = $product['id'];
			//$this->category_id = $product['family'];
			$this->has_stock = $product['stock_quantity']>0;
			$this->stock = $product['stock_quantity'];
			$this->price = $product['sellingprice'];
			$this->purchasing = $product['purchasingprice'];
			$this->ean = $product['barcode'];

			$this->pvp = (float)wc_get_price_excluding_tax($this->product);
			return $this;
		}
		return false;
	}


	/**
	 * Create a product based on a WooCommerce Product
	 * @return $this
	 * @throws Error
	 */
	public function create()
	{
		$this->setProduct();

		$insert = OfficeGestCurl::criaArtigonoOG($this->mapPropsToValues());
		if (isset($insert['article_id'])) {
			if ($insert['article_id']!=null){
				$this->product_id = $insert['article_id'];
				return $this;
			}
		}
		throw new Error(__('Erro ao inserir o artigo ') . $this->name);


	}

	/**
	 * Create a product based on a WooCommerce Product
	 * @return $this
	 * @throws Error
	 */
	public function update()
	{
		$this->setProduct();
		$update = OfficeGestCurl::post('stocks/articles/'.$this->product_id, $this->mapPropsToValues());

		if (isset($update['product_id'])) {
			$this->product_id = $update['product_id'];
			return $this;
		}

		throw new Error(__('Erro ao atualizar o artigo ') . $this->name);
	}

	/**
	 * @throws Error
	 */
	private function setProduct()
	{
		$this->setReference()
			//->setCategory()
			->setType()
			->setName()
			->setPrice()
			->setEan()
			->setUnitId()
			->setTaxes();
	}

	/**
	 * @return bool|int
	 */
	public function getProductId()
	{
		return $this->product_id ?: false;
	}

	/**
	 * @return $this
	 */
	private function setReference()
	{
		$this->reference = $this->product->get_sku();

		if (empty($this->reference)) {
			$this->reference = Tools::createReferenceFromString($this->product->get_name());
		}

		return $this;
	}

	/**
	 * @throws Error
	 */
	private function setCategory()
	{
		$categories = $this->product->get_category_ids();

		// Get the deepest category from all the trees
		if (!empty($categories) && is_array($categories)) {
			$categoryTree = [];

			foreach ($categories as $category) {
				$parents = get_ancestors($category, 'product_cat');
				$parents = array_reverse($parents);
				$parents[] = $category;

				if (is_array($parents) && count($parents) > count($categoryTree)) {
					$categoryTree = $parents;
				}
			}

			$this->category_id = 0;
			foreach ($categoryTree as $categoryId) {
				$category = get_term_by('id', $categoryId, 'product_cat');
				if (!empty($category->name)) {
					$categoryObj = new ProductCategory($category->name, $this->category_id);

					if (!$categoryObj->loadByName()) {
						$categoryObj->create();
					}

					$this->category_id = $categoryObj->category_id;
				}
			}
		}
		return $this;
	}

	/**
	 * Available types:
	 * 1 Product
	 * 2 Service
	 * 3 Other
	 * @return $this
	 */
	private function setType()
	{
		// If the product is virtual or downloadable then its a service
		if ($this->product->is_virtual() || $this->product->is_downloadable()) {
			$this->type = 'S';
			$this->has_stock = 0;
		} else {
			$this->type = 'N';
			$this->has_stock = 1;
			$this->stock = (float)$this->product->get_stock_quantity();
		}

		return $this;
	}


	/**
	 * Set the name of the product
	 * @return $this
	 */
	private function setName()
	{
		$this->name = $this->product->get_name();
		return $this;
	}

	/**
	 * Set the price of the product
	 * @return $this
	 */
	private function setPrice()
	{
		$this->price = (float)wc_get_price_excluding_tax($this->product);
		return $this;
	}

	/**
	 * @return $this
	 */
	private function setEan()
	{
		$metaBarcode = $this->product->get_meta('barcode', true);
		if (!empty($metaBarcode)) {
			$this->ean = $metaBarcode;
		}

		return $this;
	}

	/**
	 * @return $this
	 * @throws Error
	 */
	private function setUnitId()
	{
		$this->unit_id = 'UN';
		return $this;
	}

	/**
	 * Sets the taxes of a product or its exemption reason
	 * @return $this
	 * @throws Error
	 * @throws \ErrorException
	 */
	private function setTaxes()
	{
		$tax=[];
		if ($this->product->get_tax_status() === 'taxable') {
			// Get taxes based on a tax class of a product
			// If the tax class is empty it means the products uses the shop default
			$productTaxes = $this->product->get_tax_class();
			$taxRates = WC_Tax::get_base_tax_rates($productTaxes);
			$id = OfficeGestDBModel::findTaxByValue(array_values($taxRates)[0]['rate']);
			$this->taxes=$id;
			$tax = OfficeGestDBModel::findTaxById($id);
		}
		if (empty($this->taxes) || (float)$tax['value'] === 0) {
			$this->exemption_reason = OfficeGestDBModel::getOption('exemption_reason');
		}
		return $this;
	}

	/**
	 * Map this object properties to an array to insert/update a officegest document
	 * @return array
	 */
	private function mapPropsToValues()
	{
		$values = [];
		$values['id'] = $this->reference;
		$values['articletype'] = $this->type;
		$values['description'] = $this->name;
		$values['sellingprice'] = $this->price;
		$values['unit'] = 'UN';
		$values['vatid'] = $this->taxes;
		return $values;
	}
}