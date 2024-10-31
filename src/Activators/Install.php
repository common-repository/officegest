<?php

namespace OfficeGest\Activators;

class Install
{
    /**
     * Run the installation process
     * Install API Connection table
     * Install Settings table
     * Start sync crons
     */
    public static function run()
    {
        if (!function_exists('curl_version')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(esc_html__('cURL library is required for using OfficeGest Plugin.', 'officegest-pt'));
        }

        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(esc_html__('Requires WooCommerce 3.0.0 or above.', 'officegest-pt'));
        }

        self::createTables();
        self::insertSettings();
    }

    /**
     * Create API connection table
     */
    private static function createTables()
    {
	    function createTables()
	    {
		    global $wpdb;
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
			  id_spaces_dimensions varchar(8) DEFAULT NULL,
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
		    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'officegest_articles' AND column_name = 'id_spaces_dimensions'" );
		    if(empty($row)){
			    $wpdb->query("ALTER TABLE officegest_articles ADD id_spaces_dimensions varchar(8) DEFAULT NULL");
		    }
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
		    $wpdb->query('CREATE TABLE IF NOT EXISTS officegest_eco_inventory(part_id int(11), PRIMARY KEY (part_id) USING BTREE ) ENGINE=MyISAM   DEFAULT CHARSET=utf8');
		    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
WHERE table_name = 'officegest_eco_photos' AND column_name = 'woo_attach_id'" );
		    if(empty($row)){
			    $wpdb->query("ALTER TABLE officegest_eco_photos ADD woo_attach_id bigint default(0) NOT NULL;");
		    }
		    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
WHERE table_name = 'officegest_eco_parts' AND column_name = 'photos_imported'"  );
		    if(empty($row)){
			    $wpdb->query("ALTER TABLE officegest_eco_parts ADD photos_imported tinyint default(0) NOT NULL;");
		    }

		    $wpdb->query('CREATE TABLE IF NOT EXISTS officegest_eco_processes(
							id int(11) NOT NULL,
							id_process VARCHAR(50) NOT NULL,
							barcode VARCHAR(50) NOT NULL,
							plate VARCHAR(50) NOT NULL,
							brand VARCHAR(50) NOT NULL,
							model VARCHAR(50) NOT NULL,
							brand_id  int(11) NOT NULL,
							location_id  int(11) NOT NULL,
							model_id  int(11) NOT NULL,
							fuel_id  int(11) NOT NULL,
							fuel  varchar(50) NOT NULL,
							version  int(11) NOT NULL,
							version_id  varchar(50) NOT NULL,
							date_alter VARCHAR(50) NOT NULL,
							obs mediumtext,
							state VARCHAR(50) NOT NULL,
							photos int(11),
							photo VARCHAR(191),
							attach_num int(11),
							PRIMARY KEY (id)
                      )');


	    }

    }

    /**
     * Create OfficeGest account settings
     */
    private static function insertSettings()
    {
        global $wpdb;
	    $wpdb->query("
INSERT INTO officegest_api_config(config, description) VALUES ('exemption_reason', 'Escolha uma Isenção de Impostos para os produtos que não têm impostos');
INSERT INTO officegest_api_config(config, description) VALUES ('exemption_reason_shipping', 'Escolha uma Isenção de Impostos para os portes que não têm impostos');
INSERT INTO officegest_api_config(config, description) VALUES ('payment_method', 'Escolha um metodo de pagamento por defeito');
INSERT INTO officegest_api_config(config, description) VALUES ('measure_unit', 'Escolha a unidade de medida a usar');
INSERT INTO officegest_api_config(config, description) VALUES ('maturity_date', 'Prazo de Pagamento');
INSERT INTO officegest_api_config(config, description) VALUES ('document_status', 'Escolha o estado do documento (fechado ou em rascunho)');
INSERT INTO officegest_api_config(config, description) VALUES ('document_type', 'Escolha o tipo de documentos que deseja emitir');
INSERT INTO officegest_api_config(config, description) VALUES ('articles_taxa', 'Artigos com Taxa');
INSERT INTO officegest_api_config(config, description) VALUES ('update_final_consumer', 'Actualizar consumidor final');
INSERT INTO officegest_api_config(config, description) VALUES ('shipping_info', 'Informação de envio');
INSERT INTO officegest_api_config(config, description,selected) VALUES ('vat_field', 'Número de contribuinte (Clientes)','_billing_nif');
INSERT INTO officegest_api_config(config, description) VALUES ('officegest_stock_sync', 'Sincronizar Stocks');
INSERT INTO officegest_api_config(config, description) VALUES ('officegest_products_sync', 'Sincronizar Artigos');
INSERT INTO officegest_api_config(config, description) VALUES ('company_slug', 'Empresa');
INSERT INTO officegest_api_config(config, description) VALUES ('contribuinte', 'Contribuinte da Empresa');
INSERT INTO officegest_api_config(config, description) VALUES ('invoice_auto', 'Facturação Automatica');
INSERT INTO officegest_api_config(config, description) VALUES ('officegest_stock_sync', 'Sincronizacao Automatica de Stocks');
INSERT INTO officegest_api_config(config, description) VALUES ('articles_web_only', 'Apenas Artigos Web');
INSERT INTO officegest_api_config(config, description) VALUES ('articles_service', 'Artigos Servico');
INSERT INTO officegest_api_config(config, description) VALUES ('officegest_product_warewouse', 'Armazém por defeito');
INSERT INTO officegest_api_config(config, description) VALUES ('article_portes', 'Artigo para Portes');
INSERT INTO officegest_api_config(config, description) VALUES ('articles_variants', 'Sincronizar artigos com variantes');
INSERT INTO officegest_api_config(config, description,selected) VALUES ('artigos_ean', 'Campo EAN','_officegest_ean');
INSERT INTO officegest_api_config(config, description,selected) VALUES ('artigos_marca', 'Campo Marca','_officegest_brand');
");

    }

}
