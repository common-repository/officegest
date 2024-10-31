<?php

namespace OfficeGest\Controllers;

use OfficeGest\OfficeGestCurl;
use OfficeGest\Error;

/**
 * Class Product Category
 * @package OfficeGest\Controllers
 */
class ProductCategory
{

	public $name;
	public $category_id;
	public $parent_id = 0;

	/**
	 * Product Category constructor.
	 * @param string $name
	 * @param int $parentId
	 */
	public function __construct($name, $parentId = 0)
	{
		$this->name = trim($name);
		$this->parent_id = $parentId;
	}

	/**
	 * This method SHOULD be replaced by a productCategories/getBySearch
	 * @throws Error
	 */
	public function loadByName()
	{
		$categoriesList = OfficeGestCurl::get('stocks/families');
		if (!empty($categoriesList['families']) && is_array($categoriesList['families'])) {
			foreach ($categoriesList['families'] as $category) {
				if (strcmp((string)$category['description'], (string)$this->name) === 0) {
					$this->category_id = $category['id'];
					return $this;
				}
			}
		}

		return false;
	}

	/**
	 * Create a product based on a WooCommerce Product
	 * @throws Error
	 */
	public function create()
	{
		$insert = OfficeGestCurl::post('stocks/families', $this->mapPropsToValues());

		if (isset($insert['family_id'])) {
			$this->category_id = $insert['family_id'];
			return $this;
		}

		throw new Error(__('Erro ao inserir a categoria') . $this->name);
	}


	/**
	 * Map this object properties to an array to insert/update a officegest product category
	 * @return array
	 */
	private function mapPropsToValues()
	{
		$values = [];

		$values['id'] = $this->parent_id;
		$values['description'] = $this->name;

		return $values;
	}
}