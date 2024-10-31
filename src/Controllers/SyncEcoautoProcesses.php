<?php


namespace OfficeGest\Controllers;


use OfficeGest\Log;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;

class SyncEcoautoProcesses
{
    private $found = 0;

    /**
     * Run the sync operation
     * @return SyncEcoautoProcesses
     */
    public function run()
    {
        global $wpdb;
        $has_ecoauto = OfficeGestDBModel::getOption('officegest_ecoauto')==1;
        $has_ecoauto_processes = OfficeGestDBModel::getOption('ecoauto_viaturas')==1;
        $ecoauto_processes_type = OfficeGestDBModel::getOption('ecoauto_tipos_viaturas');
        if ($has_ecoauto && $has_ecoauto_processes){
            Log::write( 'A sincronizar viaturas' );
            OfficeGestDBModel::getAllProcesses();
            $parent_term = term_exists( 'viaturas', 'product_cat', 0);
            if (!isset($parent_term['term_id'])){
                $data['description'] ='Viaturas';
                OfficeGestDBModel::createProductCategory( $data, false );
            }
            $parent_term = term_exists( 'viaturas', 'product_cat', 0);
            $parent_term_id = $parent_term['term_id'];
            if ($ecoauto_processes_type==0){
                $states = "'PDISM','ABR'";
            }
            else{
                $states = "'PDISM','ABR','PREN'";
            }

            $sql = "SELECT * FROM ".TABLE_OFFICEGEST_ECO_PROCESSES." WHERE STATE IN (".$states.")";
            $allProcesses = $wpdb->get_results($sql,ARRAY_A);

            $syncLimit = OfficeGestDBModel::getOption('ecoauto_processes_sync_limit');

            $itemsDivided = array_chunk($allProcesses, $syncLimit);

            foreach ($itemsDivided as $key => $item){
                $item_ids = [];
                foreach ($item as $i){
                    $item_ids[] = $i['id'];
                }
                $data = [
                    'cron_type' => 'processes',
                    'description' => '',
                    'process_values' => !empty($item_ids) ? json_encode($item_ids) : '',
                    'running' => 0,
                ];
                $wpdb->insert( TABLE_OFFICEGEST_CRON_JOBS, $data );
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