<?php


namespace OfficeGest\Controllers;


use OfficeGest\Log;
use OfficeGest\OfficeGestDBModel;

class SyncEcoautoForceClearParts
{
    private $found = 0;

    /**
     * Run the sync operation
     * @return SyncEcoautoForceClearParts
     */
    public function run()
    {
        global $wpdb;
        $has_ecoauto = OfficeGestDBModel::getOption('officegest_ecoauto')==1;
        $has_parts = OfficeGestDBModel::getOption('ecoauto_sync_pecas')==1;
        if ($has_ecoauto && $has_parts){
            Log::write( 'A limpar peças' );
            OfficeGestDBModel::clearEcoAutoParts();
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