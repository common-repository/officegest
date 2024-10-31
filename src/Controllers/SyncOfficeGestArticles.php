<?php


namespace OfficeGest\Controllers;


use OfficeGest\Log;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;

class SyncOfficeGestArticles
{
    private $found = 0;

    /**
     * Run the sync operation
     * @return SyncOfficeGestArticles
     */
    public function run()
    {
        global $wpdb;
        Log::write( 'A sincronizar artigos' );

        OfficeGestDBModel::getBrands();
        OfficeGestDBModel::getAllArticles();

        $syncLimit = OfficeGestDBModel::getOption('articles_sync_limit');

        $parent_term = term_exists( 'artigos', 'product_cat');
        if (!isset($parent_term['term_id'])){
            $data['description'] ='Artigos';
            OfficeGestDBModel::createProductCategory( $data, false );
        }
        $parent_term_id = OfficeGestDBModel::getArticleCategory();

        OfficeGestDBModel::syncArticleCategories($parent_term_id);

        $sql = "SELECT * FROM ".TABLE_OFFICEGEST_ARTICLES." WHERE (woo_id IS NULL OR woo_id=0) ORDER BY id";
        $items = $wpdb->get_results($sql,ARRAY_A);

        if (sizeof($items)>0){
            $itemsDivided = array_chunk($items, $syncLimit);

            foreach ($itemsDivided as $key => $item){
                $items_id = [];
                foreach ($item as $i){
                    $items_id[] = $i['id'];
                }
                $data = [
                    'cron_type' => 'articles',
                    'description' => '',
                    'process_values' => !empty($items_id) ? json_encode($items_id) : '',
                    'running' => 0,
                ];
                $wpdb->insert( TABLE_OFFICEGEST_CRON_JOBS, $data );
            }
        }

        $sql = "SELECT * FROM ".TABLE_OFFICEGEST_ARTICLES." WHERE (woo_id IS NOT NULL OR woo_id!=0) ORDER BY id";
        $items = $wpdb->get_results($sql,ARRAY_A);

        if (sizeof($items)>0){
            $itemsDivided = array_chunk($items, $syncLimit);

            foreach ($itemsDivided as $key => $item){
                $items_id = [];
                foreach ($item as $i){
                    $items_id[] = $i['id'];
                }
                $data = [
                    'cron_type' => 'articles',
                    'description' => '',
                    'process_values' => !empty($items_id) ? json_encode($items_id) : '',
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