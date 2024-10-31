<?php

namespace OfficeGest\Controllers;

use OfficeGest\OfficeGestDBModel;
use OfficeGest\OfficegestProduct;
use OfficeGest\Tools;

class SyncOfficegestArticleImages
{
    private $found = 0;
	/**
	 * Run the sync operation
	 * @return SyncOfficegestArticleImages
	 * @throws \ErrorException
	 */
    public function run()
    {
    	global $wpdb;
        $has_articles = OfficeGestDBModel::getOption('general_configuration')==1;
        $has_articles2 = OfficeGestDBModel::getOption('general_configuration')==2;
        $has_sync_images = OfficeGestDBModel::getOption('sync_article_images')==1;
	    if (($has_articles || $has_articles2) && $has_sync_images){


            $query = "SELECT id,woo_id FROM ".TABLE_OFFICEGEST_ARTICLES." WHERE woo_id>0 AND photos_imported=0 ";
            $photos = $wpdb->get_results($query,ARRAY_A);

            foreach ($photos as $key => $photo){
                OfficeGestDBModel::getAllOfficeGestArticlesPhotosDB($photo);
            }

            $syncLimit = OfficeGestDBModel::getOption('articles_sync_images_limit');

            $partsDivided = array_chunk($photos, $syncLimit);

            foreach ($partsDivided as $key => $part){
                $parts_ids = [];
                foreach ($part as $p){
                    $parts_ids[] = $p['id'];
                }
                $data = [
                    'cron_type' => 'article_images',
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
