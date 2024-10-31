<?php

namespace OfficeGest\Controllers;

use OfficeGest\OfficeGestDBModel;
use OfficeGest\OfficeGestCurl;
use OfficeGest\Log;
use WC_Product;
use WC_Tax;

class SyncTaxas
{

    private $since;
    private $found = 0;
    private $updated = 0;
    private $equal = 0;
    private $notFound = 0;

    /** @var string Switch this between outofstock or onbackorder */
    private $outOfStockStatus = 'onbackorder';

    /**
     * Run the sync operation
     * @return SyncTaxas
     */
    public function run()
    {
    	global $wpdb;
	    Log::write( "A sincronizar taxas" );
	    $updatedProducts = $this->getAllOfficeGestTaxas();
	    $wpdb->query( 'TRUNCATE '.TABLE_OFFICEGEST_VATS);
	    foreach ( $updatedProducts as $product ) {
		    $data = [
			    'id'          => $product['id'],
			    'description' => $product['description'],
			    'value'       => $product['value']
		    ];
		    $cat  = $wpdb->get_row( 'SELECT * FROM ' . TABLE_OFFICEGEST_VATS . ' where id="' . $product['id'] . '"', ARRAY_A );
		    if ( ! empty( $cat ) ) {
			    $wpdb->update( TABLE_OFFICEGEST_VATS, $data, [
				    'id' => $product['id']
			    ] );
			    $this->updated ++;
		    } else {
			    $wpdb->insert( TABLE_OFFICEGEST_VATS, $data );
			    $this->found ++;
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
     * @return array
     */
    public function getNotFound()
    {
        return $this->notFound;
    }
	private function getAllOfficeGestTaxas() {
		return OfficeGestCurl::getVats();
	}

}
