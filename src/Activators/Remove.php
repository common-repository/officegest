<?php

namespace OfficeGest\Activators;

class Remove
{
    public static function run()
    {
        global $wpdb;

        $tables = 'SELECT table_name FROM information_schema.tables WHERE TABLE_SCHEMA = '.DB_NAME.' AND TABLE_NAME LIKE "officegest_%" ORDER BY table_name DESC';

        foreach ($tables as $table){
            $wpdb->query("DROP TABLE '".$table."'");
        }
//        $wpdb->query("DROP TABLE officegest_api");
//        $wpdb->query("DROP TABLE officegest_api_config");
//	    $wpdb->query("DROP TABLE officegest_api_articles");
//	    $wpdb->query("DROP TABLE officegest_api_categories");
//	    $wpdb->query("DROP TABLE officegest_articles");
//	    $wpdb->query("DROP TABLE officegest_brands");
//	    $wpdb->query("DROP TABLE officegest_vats");
//	    $wpdb->query("DROP TABLE officegest_families");
        wp_clear_scheduled_hook('officegestProductsSync');
	    wp_clear_scheduled_hook('officegestStockSync');
	    wp_clear_scheduled_hook('officegestStockSync');
        wp_clear_scheduled_hook('syncArvore');
    }

}
