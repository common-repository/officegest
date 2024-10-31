<?php


namespace OfficeGest\Controllers;


use OfficeGest\Log;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;

class SyncEcoautoParts
{
    private $found = 0;

    /**
     * Run the sync operation
     * @return SyncEcoautoParts
     */
    public function run()
    {
        global $wpdb;
        $has_ecoauto = OfficeGestDBModel::getOption('officegest_ecoauto')==1;
        $has_vehicles = OfficeGestDBModel::getOption('ecoauto_sync_pecas')==1;
        if ($has_ecoauto && $has_vehicles){
            Log::write( 'A sincronizar peças' );
            OfficeGestDBModel::getAllEcoautoParts();

            $parent_term = term_exists( 'pecas', 'product_cat', 0 );
            if ( ! isset( $parent_term['term_id'] ) ) {
                $data['description'] = 'Peças';
                OfficeGestDBModel::createProductCategory( $data, false );
            }
            $parent_term    = term_exists( 'pecas', 'product_cat', 0 );
            $parent_term_id = $parent_term['term_id'];

            OfficeGestDBModel::syncEcoAutoPartsCategories($parent_term_id);

            $params = [
                'tipos_pecas'=>OfficeGestDBModel::getOption('ecoauto_tipos_pecas'),
                'imagens'=>OfficeGestDBModel::getOption('ecoauto_imagens'),
            ];

            $query = "SELECT * FROM ".TABLE_OFFICEGEST_ECO_PARTS." ";
            if ($params!=null){
                if ($params['tipos_pecas']==2){
                    $query.=' WHERE status_desc = "Desmantelado"';
                }
                if ($params['tipos_pecas']==3){
                    $query.=' WHERE status_desc = "Em Parque"';
                }
            }
            $allParts = $wpdb->get_results($query,ARRAY_A);

            $syncLimit = OfficeGestDBModel::getOption('ecoauto_parts_sync_limit');

            $partsDivided = array_chunk($allParts, $syncLimit);


            foreach ($partsDivided as $key => $part){
                $process_ids = [];
                foreach ($part as $p){
                    $process_ids[] = $p['id'];
                }
                $data = [
                    'cron_type' => 'parts',
                    'description' => '',
                    'process_values' => !empty($process_ids) ? json_encode($process_ids) : '',
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