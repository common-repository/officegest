<?php
/**
 *
 *   Plugin Name:  OfficeGest
 *   Plugin URI:   https://www.officegest.com/woocommerce
 *   Description:  A forma mais fácil de ligar a sua loja online com a sua faturação.
 *   Version:      1.1.4
 *   Author:       OfficeGest
 *   Author URI:   https://www.officegest.com
 *   License:      GPL2
 *   License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 *
 */

namespace OfficeGest;

if (!defined('ABSPATH')) {
    exit;
}


$composer_autoloader = __DIR__ . '/vendor/autoload.php';
if (is_readable($composer_autoloader)) {
    /** @noinspection PhpIncludeInspection */
    require $composer_autoloader;
}

if (!defined('OFFICEGEST_PLUGIN_FILE')) {
    define('OFFICEGEST_PLUGIN_FILE', __FILE__);
}

if (!defined('OFFICEGEST_DIR')) {
    define('OFFICEGEST_DIR', __DIR__);
}

if (!defined('OFFICEGEST_TEMPLATE_DIR')) {
    define('OFFICEGEST_TEMPLATE_DIR', __DIR__ . "/src/Templates/");
}

if (!defined('OFFICEGEST_PLUGIN_URL')) {
	define('OFFICEGEST_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('OFFICEGEST_IMAGES_URL')) {
    define('OFFICEGEST_IMAGES_URL', plugin_dir_url(__FILE__) . "images/");
}

register_activation_hook(__FILE__, '\OfficeGest\Activators\Install::run');
register_deactivation_hook(__FILE__, '\OfficeGest\Activators\Remove::run');
createTables();
defineTables();

add_action('plugins_loaded', Start::class);

function createTables()
{
	global $wpdb;
    $wpdb->flush();
	$wpdb->query( 'CREATE TABLE IF NOT EXISTS officegest_api( 
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                domain varchar(200),
                username VARCHAR(100), 
                password VARCHAR(100), 
                api_key VARCHAR(100), 
                company_id INT DEFAULT -1,
                dated TIMESTAMP default CURRENT_TIMESTAMP
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;' );
	$wpdb->query('CREATE TABLE IF NOT EXISTS officegest_families( 
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                description varchar(200)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
	$wpdb->query('CREATE TABLE IF NOT EXISTS officegest_brands( 
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                description varchar(200)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
	$wpdb->query( 'CREATE TABLE IF NOT EXISTS officegest_api_config( 
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                config VARCHAR(100), 
                description VARCHAR(100), 
                selected VARCHAR(100), 
                changed TIMESTAMP default CURRENT_TIMESTAMP
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
	$wpdb->query( 'CREATE TABLE IF NOT EXISTS officegest_articles (
			  id varchar(30) NOT NULL,
			  description varchar(254) NULL,
			  articletype char(1) NOT NULL,
			  purchasingprice double,
			  sellingprice double,
			  vatid char(3) DEFAULT NULL,
			  unit char(3) DEFAULT NULL,
			  stock_quantity double,
			  family varchar(10) DEFAULT NULL,
			  subfamily varchar(10) DEFAULT NULL,
			  barcode varchar(30),
			  brand varchar(10) DEFAULT NULL,
			  active VARCHAR(1),
			  spaces_dimensions varchar(30),
			  activeforweb VARCHAR(1),
			  alterationdate TIMESTAMP,
			  re varchar(30),
			  idforweb varchar(30),
			  priceforweb double,
			  referenceforweb TEXT,
			  long_description TEXT,
			  short_description TEXT,
			  PRIMARY KEY (id)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;' );
	$wpdb->query('CREATE TABLE IF NOT EXISTS officegest_vats (
  id varchar(11) NOT NULL,
  description varchar(255) DEFAULT NULL,
  value float DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
	$wpdb->query(
		'CREATE TABLE IF NOT EXISTS officegest_categories( 
                id varchar(30),
                description varchar(254) NULL,
                subfamilia  varchar(30) NOT NULL,
                term_id int(11) DEFAULT NULL,
  				term_taxonomy_id int(11) DEFAULT NULL,
                PRIMARY KEY (id)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;'
	);
    $wpdb->flush();

	$wpdb->query('CREATE TABLE IF NOT EXISTS officegest_price_tables (
  id int(11) NOT NULL,
  type varchar(255) DEFAULT NULL,
  description varchar(255) DEFAULT NULL,
  start_date datetime DEFAULT NULL,
  end_date datetime DEFAULT NULL,
  price float DEFAULT NULL,
  discount_perc float DEFAULT NULL,
  discount_perc2 float DEFAULT NULL,
  id_discount_type int(11) DEFAULT NULL,
  article_id varchar(30) DEFAULT NULL,
  classifcode varchar(30) DEFAULT NULL,
  paymentmethod varchar(30) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY article_id (article_id),
  KEY article_id_2 (article_id,classifcode)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
	$wpdb->query('CREATE TABLE IF NOT EXISTS officegest_eco_cats( id INT NOT NULL PRIMARY KEY, description varchar(200),woo_id int(11)) ENGINE=MyISAM  DEFAULT CHARSET=utf8');
	$wpdb->query("CREATE TABLE IF NOT EXISTS `officegest_eco_photos` (
  `component_id` int(11) NOT NULL,
  `attach_num` int(11) NOT NULL,
  `main` int(11) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `woo_attach_id` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`component_id`,`attach_num`),
  KEY `photo` (`photo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

	$wpdb->query('CREATE TABLE IF NOT EXISTS officegest_eco_comps( id INT NOT NULL PRIMARY KEY, category_id int(11),description varchar(200),value float,article_id VARCHAR(200),active TINYINT(1),woo_id bigint) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');
	$wpdb->query('CREATE TABLE IF NOT EXISTS officegest_eco_parts (
	id int(11) NOT NULL PRIMARY key,
	process_id int(11),
	cod_process int(11),
	plate VARCHAR(255),
	category_id VARCHAR(255),
	category VARCHAR(255),
	component_id int(11),
	component VARCHAR(255),
	article_id VARCHAR(255),
	barcode VARCHAR(255),
	brand_id int(11),
	model_id int(11),
	version_id int(11),
	fuel_id int(11),
	year int(11),
	engine_num VARCHAR(255),
	location_id VARCHAR(255),
	document_type VARCHAR(255),
	document_num VARCHAR(255),
	obs VARCHAR(255),
	cost_price VARCHAR(255),
	selling_price VARCHAR(255),
	checked int(11),
	color VARCHAR(255),
	type VARCHAR(255),
	deadline VARCHAR(255),
	weight VARCHAR(255),
	height VARCHAR(255),
	width VARCHAR(255),
	depth VARCHAR(255),
	codoem VARCHAR(255),
	date_alter VARCHAR(255),
	vin VARCHAR(255),
	article VARCHAR(255),
	brand VARCHAR(255),
	model VARCHAR(255),
	version VARCHAR(255),
	version_date_start VARCHAR(255),
	version_date_end VARCHAR(255),
	version_year_start VARCHAR(255),
	version_year_end VARCHAR(255),
	version_vin VARCHAR(255),
	fuel VARCHAR(255),
	type_desc VARCHAR(255),
	status_desc VARCHAR(255),
	location_desc VARCHAR(255),
	location_barcode VARCHAR(255),
	valuevat VARCHAR(255),
	selling_price_withvat VARCHAR(255),
	attach_num VARCHAR(255),
	photos VARCHAR(255),
	photo VARCHAR(255),
	woo_id bigint(20)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');
    $wpdb->flush();
	$wpdb->query('CREATE TABLE IF NOT EXISTS officegest_eco_inventory(part_id int(11), PRIMARY KEY (part_id) USING BTREE ) ENGINE=MyISAM   DEFAULT CHARSET=utf8');
	$row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA  = '". DB_NAME ."' AND table_name = 'officegest_eco_photos' AND column_name = 'woo_attach_id'" );
	if(empty($row)){
		$wpdb->query("ALTER TABLE officegest_eco_photos ADD woo_attach_id bigint default(0) NOT NULL;");
	}
    $wpdb->flush();
	$row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA  = '". DB_NAME ."' AND table_name = 'officegest_eco_parts' AND column_name = 'photos_imported'"  );
	if(empty($row)){
		$wpdb->query("ALTER TABLE officegest_eco_parts ADD photos_imported tinyint default(0) NOT NULL;");
	}

	$wpdb->query('CREATE TABLE IF NOT EXISTS officegest_eco_processes(
							id int(11) NOT NULL,
							id_process VARCHAR(50),
							barcode VARCHAR(50),
							plate VARCHAR(50) ,
							brand VARCHAR(50) ,
							model VARCHAR(50),
							brand_id  int(11),
							location_id  int(11),
							model_id  int(11),
							fuel_id  int(11) ,
							fuel  varchar(50),
							version  int(11) ,
							version_id  varchar(50),
							date_alter VARCHAR(50) ,
							obs mediumtext,
							state VARCHAR(50),
							photos int(11),
							photo VARCHAR(191),
							attach_num int(11),
							woo_id bigint default 0,
							price float default 0,
							PRIMARY KEY (id)
                      )');
	$wpdb->query('CREATE TABLE IF NOT EXISTS officegest_car_brands( 
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                description varchar(200)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

    $wpdb->query('CREATE TABLE IF NOT EXISTS officegest_cron_jobs( 
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                cron_type varchar(255) DEFAULT NULL, 
                description varchar(200),
                process_values JSON DEFAULT NULL,
                running int(11) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

    $wpdb->query("CREATE TABLE IF NOT EXISTS `officegest_article_photos` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
              `component_id` varchar(255) DEFAULT NULL,
              `attach_num` varchar(255) DEFAULT NULL,
              `main` int(11) DEFAULT NULL,
              `photo` varchar(255) DEFAULT NULL,
              `woo_attach_id` bigint(20) NOT NULL DEFAULT '0',
              KEY `photo` (`photo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $wpdb->query("CREATE TABLE IF NOT EXISTS officegest_ecoauto_inventory (
              id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
              part_id varchar(255) DEFAULT NULL,
              process_id varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $wpdb->flush();
    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA  = '". DB_NAME ."' AND table_name = 'officegest_articles' AND column_name = 'woo_id'" );
    if(empty($row)){
        $wpdb->query("ALTER TABLE officegest_articles ADD woo_id INT DEFAULT 0");
    }
    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA  = '". DB_NAME ."' AND table_name = 'officegest_articles' AND column_name = 'article_imported'" );
    if(empty($row)){
        $wpdb->query("ALTER TABLE officegest_articles ADD article_imported INT DEFAULT 0");
    }
    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA  = '". DB_NAME ."' AND table_name = 'officegest_articles' AND column_name = 'created_at'" );
    if(empty($row)){
        $wpdb->query("ALTER TABLE officegest_articles ADD created_at TIMESTAMP DEFAULT current_timestamp ");
    }
    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA  = '". DB_NAME ."' AND table_name = 'officegest_articles' AND column_name = 'updated_at'" );
    if(empty($row)){
        $wpdb->query("ALTER TABLE officegest_articles ADD updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }
    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA  = '". DB_NAME ."' AND table_name = 'officegest_articles' AND column_name = 'photos'" );
    if(empty($row)){
        $wpdb->query("ALTER TABLE officegest_articles ADD photos VARCHAR(255) DEFAULT NULL");
    }
    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA  = '". DB_NAME ."' AND table_name = 'officegest_articles' AND column_name = 'photo'" );
    if(empty($row)){
        $wpdb->query("ALTER TABLE officegest_articles ADD photo VARCHAR(255) DEFAULT NULL");
    }
    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA  = '". DB_NAME ."' AND table_name = 'officegest_articles' AND column_name = 'photos_imported'" );
    if(empty($row)){
        $wpdb->query("ALTER TABLE officegest_articles ADD photos_imported int(11) DEFAULT 0");
    }
    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA  = '". DB_NAME ."' AND table_name = 'officegest_eco_parts' AND column_name = 'created_at'" );
    if(empty($row)){
        $wpdb->query("ALTER TABLE officegest_eco_parts ADD created_at TIMESTAMP DEFAULT current_timestamp ");
    }
    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA  = '". DB_NAME ."' AND table_name = 'officegest_eco_parts' AND column_name = 'updated_at'" );
    if(empty($row)){
        $wpdb->query("ALTER TABLE officegest_eco_parts ADD updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }
    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA  = '". DB_NAME ."' AND table_name = 'officegest_eco_processes' AND column_name = 'created_at'" );
    if(empty($row)){
        $wpdb->query("ALTER TABLE officegest_eco_processes ADD created_at TIMESTAMP DEFAULT current_timestamp ");
    }
    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA  = '". DB_NAME ."' AND table_name = 'officegest_eco_processes' AND column_name = 'updated_at'" );
    if(empty($row)){
        $wpdb->query("ALTER TABLE officegest_eco_processes ADD updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }
    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA  = '". DB_NAME ."' AND table_name = 'officegest_eco_inventory' AND column_name = 'process_id'" );
    if(empty($row)){
        $wpdb->query("ALTER TABLE officegest_eco_inventory ADD process_id int(11) DEFAULT NULL");
    }

    $wpdb->flush();
    $row = $wpdb->get_results(  "SELECT COLUMN_NAME, table_schema FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA  = '". DB_NAME ."' AND table_name = 'officegest_articles' AND column_name = 'id_spaces_dimensions'" );
    if(empty($row)){
        $wpdb->query("ALTER TABLE officegest_articles ADD id_spaces_dimensions varchar(8) DEFAULT NULL");
    }

    wp_clear_scheduled_hook('syncArvore');
    wp_clear_scheduled_hook('syncProcesses');
    wp_clear_scheduled_hook('syncEcoAuto');
    wp_clear_scheduled_hook('syncImagens');
}

function defineTables(){
	if (!defined('TABLE_OFFICEGEST_API')) {
		define( 'TABLE_OFFICEGEST_API', 'officegest_api' );
	}
	if (!defined('TABLE_OFFICEGEST_API_CONFIG')) {
		define( 'TABLE_OFFICEGEST_API_CONFIG', 'officegest_api_config' );
	}
	if (!defined('TABLE_OFFICEGEST_ARTICLES')) {
		define( 'TABLE_OFFICEGEST_ARTICLES', 'officegest_articles' );
	}
	if (!defined('TABLE_OFFICEGEST_CATEGORIES')) {
		define( 'TABLE_OFFICEGEST_CATEGORIES', 'officegest_categories' );
	}
	if (!defined('TABLE_OFFICEGEST_VATS')) {
		define( 'TABLE_OFFICEGEST_VATS', 'officegest_vats' );
	}
	if (!defined('TABLE_OFFICEGEST_FAMILIES')) {
		define( 'TABLE_OFFICEGEST_FAMILIES', 'officegest_families' );
	}
	if (!defined('TABLE_OFFICEGEST_BRANDS')) {
		define( 'TABLE_OFFICEGEST_BRANDS', 'officegest_brands' );
	}
	if (!defined('TABLE_OFFICEGEST_PRICE_TABLES')) {
		define( 'TABLE_OFFICEGEST_PRICE_TABLES', 'officegest_price_tables');
	}

	if (!defined('TABLE_OFFICEGEST_ECO_CATS')) {
		define( 'TABLE_OFFICEGEST_ECO_CATS', 'officegest_eco_cats');
	}

	if (!defined('TABLE_OFFICEGEST_ECO_COMPS')) {
		define( 'TABLE_OFFICEGEST_ECO_COMPS', 'officegest_eco_comps');
	}

	if (!defined('TABLE_OFFICEGEST_ECO_PARTS')) {
		define( 'TABLE_OFFICEGEST_ECO_PARTS', 'officegest_eco_parts');
	}

	if (!defined('TABLE_OFFICEGEST_ECO_INVENTORY')) {
		define( 'TABLE_OFFICEGEST_ECO_INVENTORY', 'officegest_eco_inventory');
	}
	if (!defined('TABLE_OFFICEGEST_ECO_PHOTOS')) {
		define( 'TABLE_OFFICEGEST_ECO_PHOTOS', 'officegest_eco_photos');
	}
	if (!defined('TABLE_OFFICEGEST_ECO_PROCESSES')) {
		define( 'TABLE_OFFICEGEST_ECO_PROCESSES', 'officegest_eco_processes');
	}
	if (!defined('TABLE_OFFICEGEST_CAR_BRANDS')){
		define('TABLE_OFFICEGEST_CAR_BRANDS','officegest_car_brands');
	}
    if (!defined('TABLE_OFFICEGEST_CRON_JOBS')){
        define('TABLE_OFFICEGEST_CRON_JOBS','officegest_cron_jobs');
    }
    if (!defined('TABLE_OFFICEGEST_ARTICLE_PHOTOS')){
        define('TABLE_OFFICEGEST_ARTICLE_PHOTOS','officegest_article_photos');
    }
    if (!defined('TABLE_OFFICEGEST_ECOAUTO_INVENTORY')){
        define('TABLE_OFFICEGEST_ECOAUTO_INVENTORY','officegest_ecoauto_inventory');
    }
}

function Start()
{
    return new Plugin();
}