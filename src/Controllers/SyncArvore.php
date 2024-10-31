<?php

namespace OfficeGest\Controllers;

use OfficeGest\ArraySearcher;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\OfficeGestCurl;
use OfficeGest\Log;
use OfficeGest\OfficegestProduct;
use OfficeGest\Tools;
use WC_Product;
use WC_Tax;

class SyncArvore
{

    private $since;
    private $found = 0;
    private $created = 0;
    private $equal = 0;
	private $updated = 0;

	private static function getCats(){
		global $wpdb;
		$cats = $wpdb->get_results( 'Select distinct category_id as id, category as description from ' . TABLE_OFFICEGEST_ECO_PARTS, ARRAY_A );
		return $cats;
	}

	private static function getComps($cat){
		global $wpdb;
		$comps = $wpdb->get_results( 'Select distinct  component_id as id,component as description from ' . TABLE_OFFICEGEST_ECO_PARTS . ' where category_id=' . $cat, ARRAY_A );
		return $comps;
	}

	private static function getBrands($cat,$comp){
		global $wpdb;
		$marcas = $wpdb->get_results( 'Select distinct brand_id as id,brand as description from ' . TABLE_OFFICEGEST_ECO_PARTS.' where category is not null and category_id='.$cat.' and component_id='.$comp, ARRAY_A );
		return $marcas;
	}

	private static function do_m1() {
		$cats = self::getCats();
		foreach ($cats as $k=>$v){
			$res = OfficeGestDBModel::create_category_ecoauto($v);
			$comps = self::getComps($v['id']);
			foreach ($comps as $kc=>$kv){
				$res2  = OfficeGestDBModel::create_category_ecoauto( $kv, $res['term_id'] );
			}
		}
	}

	/**
     * Run the sync operation
     * @return SyncArvore
     */
    public function run()
    {
	    global $wpdb;
	    Log::write( "A sincronizar Estrutura de Filtros para o Woocommerce" );
	    $this->created =0;
	    $this->updated =0;
	    OfficeGestDBModel::getAllEcoautoParts();
	    OfficeGestDBModel::getAllPhotosDB();
	    self::do_m1();
	    return $this;
    }

	/**
	 * Get the amount of records found
	 * @return int
	 */
	public function countCreated()
	{
		return $this->created;
	}


	/**
	 * Get the amount of records that had the same stock count
	 * @return int
	 */
	public function countUpdated()
	{
		return $this->updated;
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

}
