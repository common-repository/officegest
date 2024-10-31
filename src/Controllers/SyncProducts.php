<?php


namespace OfficeGest\Controllers;


use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;

class SyncProducts
{
    private $found = 0;

    /**
     * Run the sync operation
     * @return SyncProducts
     */
    public function run()
    {
        global $wpdb;

        OfficeGestDBModel::getBrands();
        OfficeGestDBModel::getAllArticles();
        $parent_term = term_exists( 'artigos', 'product_cat');

        if (!isset($parent_term['term_id'])){
            $data['description'] ='Artigos';
            OfficeGestDBModel::createProductCategory( $data, false );
        }

        $parent_term_id = OfficeGestDBModel::getArticleCategory();

        OfficeGestDBModel::syncArticleCategories($parent_term_id);

        $sql_articles = "SELECT * from ".TABLE_OFFICEGEST_ARTICLES." where (woo_id is null or woo_id=0) order by id LIMIT 20";
        $articles = $wpdb->get_results($sql_articles,ARRAY_A);

        if (sizeof($articles)>0){
            $counter = OfficeGestDBModel::createArticles($articles);
            $this->found++;
        } else{
            $sql_articles = "SELECT * from ".TABLE_OFFICEGEST_ARTICLES." where (woo_id is not null or woo_id!=0) order by id LIMIT 20";
            $articles = $wpdb->get_results($sql_articles,ARRAY_A);
            if (sizeof($articles)>0){
                $counter = OfficeGestDBModel::createArticles($articles);
                $this->found++;
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