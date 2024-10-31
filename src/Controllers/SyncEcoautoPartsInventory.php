<?php


namespace OfficeGest\Controllers;


use OfficeGest\Log;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;

class SyncEcoautoPartsInventory
{
    private $found = 0;

    /**
     * Run the sync operation
     * @return SyncEcoautoPartsInventory
     */
    public function run()
    {
        global $wpdb;
        $has_ecoauto = OfficeGestDBModel::getOption('officegest_ecoauto')==1;
        $has_parts = OfficeGestDBModel::getOption('ecoauto_sync_pecas')==1;
        if ($has_ecoauto){
            if ($has_parts){
                OfficeGestDBModel::clearEcoAutoParts();
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