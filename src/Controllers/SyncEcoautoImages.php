<?php

namespace OfficeGest\Controllers;

use OfficeGest\OfficeGestDBModel;
use OfficeGest\OfficegestProduct;
use OfficeGest\Tools;

class SyncEcoautoImages
{
    private $found = 0;
	/**
	 * Run the sync operation
	 * @return SyncEcoautoImages
	 * @throws \ErrorException
	 */
    public function run()
    {
    	global $wpdb;
        $has_ecoauto = OfficeGestDBModel::getOption('officegest_ecoauto')==1;
        $has_sync_imagens = OfficeGestDBModel::getOption('ecoauto_sync_imagens')==1;
        $limit_imagens = OfficeGestDBModel::getOption('ecoauto_sync_imagens_limit');
	    if ($has_ecoauto && $has_sync_imagens){
            OfficeGestDBModel::getAllEcoautoPartsPhotosDB();

            $query = "Select id,woo_id from ".TABLE_OFFICEGEST_ECO_PARTS." where woo_id>0 and photos_imported=0 ";
            $photos = $wpdb->get_results($query,ARRAY_A);

            $syncLimit = OfficeGestDBModel::getOption('ecoauto_sync_imagens_limit');

            $partsDivided = array_chunk($photos, $syncLimit);

            foreach ($partsDivided as $key => $part){
                $parts_ids = [];
                foreach ($part as $p){
                    $parts_ids[] = $p['id'];
                }
                $data = [
                    'cron_type' => 'parts_images',
                    'description' => '',
                    'process_values' => !empty($parts_ids) ? json_encode($parts_ids) : '',
                    'running' => 0,
                ];
                $wpdb->insert( TABLE_OFFICEGEST_CRON_JOBS, $data);
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
