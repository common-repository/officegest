<?php

namespace OfficeGest\Controllers;

use OfficeGest\OfficeGestDBModel;
use OfficeGest\OfficeGestCurl;
use OfficeGest\Log;
use OfficeGest\Tools;
use WC_Product;
use WC_Tax;

class SyncArticles
{

    private $since;
    private $found = 0;
    private $updated = 0;
    private $created = 0;
    private $notFound = 0;

    /**
     * Run the sync operation
     * @return SyncArticles
     */
    public function run()
    {
	    Log::write( "A sincronizar Artigos" );
        OfficeGestDBModel::getAllOfficeGestProducts();

	    $updatedProducts = $this->getAllWooProducts();
	    $this->found = count($updatedProducts);
	    foreach ($updatedProducts as $product) {
		    $ids = $product->get_category_ids();
		    $familia = '';
		    $subfamilia = '';
		    foreach ($ids as $cats){
			    $res = Tools::get_category($cats);
			    if ($res->parent==0){
				    $fam = OfficeGestCurl::getFamily(trim($res->name));
				    if ($fam['total']>0){
					    $families = array_values($fam['families']);
					    $familia = $families[0]['id'];
				    }
			    }
			    else{
				    $sfam = OfficeGestCurl::getSubFamily(trim($res->name));
				    if ($sfam['total']>0){
					    if (is_array($sfam['subfamilies'])){
						    $subfamilies = array_values($sfam['subfamilies']);
						    $subfamilia = $subfamilies[0]['id'];
					    }
				    }
			    }
		    }
		    $res = OfficeGestCurl::createProduct($product,$familia,$subfamilia);
		    if ($res['status']==='created'){
		    	$this->created++;
		    }
		    else  if ($res['status']==='updated'){
			    $this->updated++;
		    }
		    else{
		    	$this->notFound++;
		    }
	    }
	    return $this;
    }

    /**
     * Get the amount of records found
     * @return int
     */
    public function countFoundRecord()
    {
        return $this->found;
    }

    /**
     * Get the amount of records update
     * @return int
     */
    public function countUpdated()
    {
        return $this->updated;
    }

    /**
     * Get the amount of records that had the same stock count
     * @return int
     */
    public function countCreated()
    {
        return $this->created;
    }

    /**
     * Get the amount of products not found in WooCommerce
     * @return int
     */
    public function countNotFound()
    {
        return $this->notFound;
    }

    /**
     * Return the updated products
     * @return int
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Return the list of products that had the same stock as in WooCommerce
     * @return int
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Return the list of products update in OfficeGest but not found in WooCommerce
     * @return int
     */
    public function getNotFound()
    {
        return $this->notFound;
    }

	private function getAllWooProducts() {
		$args = array(
			'paginate' => false,
			'limit'=>-1
		);
		return wc_get_products($args);
	}

}
