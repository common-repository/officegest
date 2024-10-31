<?php

namespace OfficeGest\Controllers;

use OfficeGest\Log;
use OfficeGest\OfficeGestDBModel;
use WC_Product;
use WC_Product_Variable;
use WC_Product_Variation;

class SyncStocks
{

    private $since;
    private $found = 0;
    private $updated = 0;
    private $equal = 0;
    private $notFound = 0;
	/**
	 * Run the sync operation
	 * @return SyncStocks
	 * @throws \ErrorException
	 */
    public function run()
    {
    	global $wpdb;
	    $configuracao = OfficeGestDBModel::getOption('general_configuration');
        $limit_stock = OfficeGestDBModel::getOption('articles_sync_limit');
	    if ($configuracao>0) {
		    Log::write( 'A sincronizar artigos' );
		    $updatedProducts = OfficeGestDBModel::getAllOfficeGestProducts();
		    foreach ( $updatedProducts as $product ) {
			    $cat = $wpdb->get_row( 'SELECT * FROM ' . TABLE_OFFICEGEST_ARTICLES . ' where id="' . $product['id'] . '"', ARRAY_A );
			    if ( ! empty( $cat ) ) {
				    $wpdb->update( TABLE_OFFICEGEST_ARTICLES, $product, [
					    'id' => $product['id']
				    ] );
				    $this->updated ++;
			    } else {
				    $wpdb->insert( TABLE_OFFICEGEST_ARTICLES, $product );
				    $this->found ++;
			    }
		    }
		    $query = 'select * from '.TABLE_OFFICEGEST_ARTICLES;
		    $artigos = $wpdb->get_results($query,ARRAY_A);
		    foreach ($artigos as $k=>$v){
		    	$product = wc_get_product_id_by_sku($v['id']);
		    	if (!empty($product)){
		    		$this->updated++;
				    $tipo_artigo = OfficeGestDBModel::getArticleType($v);
				    switch ($tipo_artigo){
					    case 'variant':
						    $artigo = new WC_Product_Variation($product);
						    $artigo->set_stock_quantity($v['stock_quantity']);
						    $artigo->set_manage_stock(true);
						    $artigo->save();
						    break;
					    case 'variable':
						    $artigo = new WC_Product_Variable($product);
						    $artigo->set_stock_quantity($v['stock_quantity']);
						    $artigo->set_manage_stock(true);
						    $artigo->save();
						    break;
					    case 'simple':
					    default:
						    $artigo = new WC_Product($product);
						    $artigo->set_stock_quantity($v['stock_quantity']);
						    $artigo->set_manage_stock(true);
						    $artigo->save();
						    break;
				    }
			    }
		    	else{
		    		$this->notFound++;
			    }
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
    public function countEqual()
    {
        return $this->equal;
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
    public function getEqual()
    {
        return $this->equal;
    }

	/**
	 * Return the list of products update in OfficeGest but not found in WooCommerce
	 * @return int
	 */
    public function getNotFound()
    {
        return $this->notFound;
    }

}
