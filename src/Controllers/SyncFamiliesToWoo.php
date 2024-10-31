<?php

namespace OfficeGest\Controllers;

use OfficeGest\OfficeGestDBModel;
use OfficeGest\OfficeGestCurl;
use OfficeGest\Log;
use OfficeGest\OfficegestProduct;
use WC_Product;
use WC_Tax;

class SyncFamiliesToWoo
{

    private $since;
    private $updated = 0;
	private $created = 0;
    private $equal = 0;

    /**
     * Run the sync operation
     * @return SyncFamiliesToWoo
     */
    public function run()
    {
    	global $wpdb;
	    Log::write( "A sincronizar Familias para o Woocommerce" );
	    $this->created =0;
	    $this->updated =0;
	    $fams = OfficeGestCurl::getArticlesFams();
	    foreach ( $fams as $fam ) {
		    $data = [
			    'id'=>$fam['id'],
			    'description'=>$fam['description'],
			    'subfamilia'=>'^'
		    ];
		    $cat_exists = OfficeGestDBModel::getSingleCategorie($fam['id'])['total']==1;
		    if ($cat_exists){
			    $where = [ 'id' => $fam['id'] ];
			    unset($data['id']);
			    $wpdb->update('officegest_categories',$data,$where);
			    $this->updated++;
		    }
		    else{
			    $wpdb->insert('officegest_categories',$data);
			    $this->created++;
		    }

	    }
	    $fams = OfficeGestDBModel::getCategories(true);
	    foreach ($fams as $fam){
		    $data = OfficeGestCurl::getArticlesSubFamsByFam($fam['id']);
		    if ($data!==false){
			    foreach ($data as $sfam) {
				    $to_db = [
					    'id'          => $sfam['id'],
					    'description' => $sfam['description'],
					    'subfamilia'  => $fam['id']
				    ];
				    $cat_exists = OfficeGestDBModel::getSingleCategorie($sfam['id'])['total']===1;
				    if ($cat_exists){
					    $where = [ 'id' => $sfam['id'] ];
					    unset($to_db['id']);
					    $wpdb->update('officegest_categories',$to_db,$where);
					    $this->updated++;
				    }
				    else{
					    $wpdb->insert('officegest_categories',$to_db);
					    $this->created++;
				    }
			    }
		    }
	    }
	    OfficegestProduct::create_woo_fams();
	    OfficegestProduct::create_woo_sfams();
	    OfficegestProduct::create_woo_ssfams();
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