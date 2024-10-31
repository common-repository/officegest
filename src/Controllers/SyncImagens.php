<?php

namespace OfficeGest\Controllers;

use OfficeGest\OfficeGestDBModel;
use OfficeGest\OfficegestProduct;

class SyncImagens
{
    private $found = 0;
	/**
	 * Run the sync operation
	 * @return SyncImagens
	 * @throws \ErrorException
	 */
    public function run()
    {
    	global $wpdb;
        $has_ecoauto = OfficeGestDBModel::getOption('officegest_ecoauto')==1;
        $has_sync_imagens = OfficeGestDBModel::getOption('ecoauto_sync_imagens')==1;
        $limit_imagens = OfficeGestDBModel::getOption('ecoauto_sync_imagens_limit');
	    if ($has_ecoauto){
            if ($has_sync_imagens){
                OfficeGestDBModel::getAllPhotosDB();
                $query = "Select id,woo_id from ".TABLE_OFFICEGEST_ECO_PARTS." where woo_id>0 and photos_imported=0 LIMIT ".$limit_imagens;
                $filtros = $wpdb->get_results($query,ARRAY_A);
                $this->found = OfficegestProduct::update_images($filtros);
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

}
