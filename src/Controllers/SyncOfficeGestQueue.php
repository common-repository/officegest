<?php


namespace OfficeGest\Controllers;


use OfficeGest\Log;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;

class SyncOfficeGestQueue
{
    private $found = 0;

    /**
     * Run the sync operation
     * @return SyncOfficeGestQueue
     */
    public function run()
    {
        global $wpdb;

        Log::write( 'A sincronizar OfficeGest com o WooCommerce' );

        $query = "SELECT * FROM ".TABLE_OFFICEGEST_CRON_JOBS." WHERE running = 0";
        $jobToRun = $wpdb->get_row($query,ARRAY_A);

        if(!empty($jobToRun)){
            $data=[
                'running'=>1
            ];
            $wpdb->update(TABLE_OFFICEGEST_CRON_JOBS,$data,['id'=>$jobToRun['id']]);

            $items = json_decode($jobToRun['process_values'], true);

            if($jobToRun['cron_type'] == 'processes'){
                OfficeGestDBModel::generateEcoAutoProcessesToWoo($items);
            }elseif($jobToRun['cron_type'] == 'parts'){
                OfficeGestDBModel::generateEcoAutoPartsToWoo($items);
            }elseif($jobToRun['cron_type'] == 'articles') {
                OfficeGestDBModel::generateArticlesToWoo($items);
            }elseif($jobToRun['cron_type'] == 'parts_images') {
                OfficeGestDBModel::generateEcoautoPartsImages($items);
            }elseif($jobToRun['cron_type'] == 'article_images'){
                OfficeGestDBModel::generateOfficeGestArticlesImages($items);
            }else{
                Log::write("Tipo de cron não encontrada");
            }

            $wpdb->delete(TABLE_OFFICEGEST_CRON_JOBS,['id'=>$jobToRun['id']]);
        }else{
            Log::write("Não existem crons em queue");
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