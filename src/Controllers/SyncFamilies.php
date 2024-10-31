<?php

namespace OfficeGest\Controllers;

use OfficeGest\OfficeGestDBModel;
use OfficeGest\OfficeGestCurl;
use OfficeGest\Log;
use WC_Product;
use WC_Tax;

class SyncFamilies
{

    private $since;
    private $found = 0;
    private $created = 0;
    private $equal = 0;

    /**
     * Run the sync operation
     * @return SyncFamilies
     */
    public function run()
    {
    	global $wpdb;
	    Log::write( "A sincronizar Familias" );

	    $updatedFamilies = $this->getAllWooCategories();
	    $this->found=count($updatedFamilies);
	    foreach ($updatedFamilies as $cat) {
		    if ($cat->parent==0){
			    if (OfficeGestCurl::getFamily($cat->name)==false) {
				    OfficeGestCurl::createFamily( $cat->cat_ID, $cat->name );
				    $this->created++;
			    }
			    else{
			    	$this->equal++;
			    }
		    }

	    }
	    foreach ($updatedFamilies as $cat) {
		    if ($cat->category_parent>0){
		    	if (OfficeGestCurl::getSubFamily($cat->name)==false){
				    OfficeGestCurl::createSubFamily($cat->category_parent,$cat->cat_ID,$cat->name);
				    $this->created++;
			    }
			    else{
				    $this->equal++;
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
     * Get the amount of records that had the same stock count
     * @return int
     */
    public function countEqual()
    {
        return $this->equal;
    }


	/**
	 * Return the updated products
	 * @return int
	 */
    public function getCreated()
    {
        return $this->created;
    }

	/**
	 * Return the list of products that had the same stock as in WooCommerce
	 * @return int
	 */
    public function getEqual()
    {
        return $this->equal;
    }

	private function getAllWooCategories() {
		$taxonomy     = 'product_cat';
		$orderby      = 'id';
		$show_count   = 0;      // 1 for yes, 0 for no
		$pad_counts   = 0;      // 1 for yes, 0 for no
		$hierarchical = 1;      // 1 for yes, 0 for no
		$title        = '';
		$empty        = 1;

		$args = array(
			'taxonomy'     => $taxonomy,
			'orderby'      => $orderby,
			'show_count'   => $show_count,
			'pad_counts'   => $pad_counts,
			'hierarchical' => $hierarchical,
			'title_li'     => $title,
			'hide_empty'   => $empty
		);
		return get_categories( $args );


	}

}
