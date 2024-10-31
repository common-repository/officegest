<?php
namespace OfficeGest\Hooks;

use OfficeGest\Log;
use OfficeGest\OfficeGestCurl;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;
use WC_Product;

class ProductList
{
	public $parent;
	private $allowedPostTypes = ["product"];
	private $configuracao = 0;
	private $has_ecoauto=0;

	public function __construct($parent) {
		$this->parent = $parent;
		$this->configuracao =  OfficeGestDBModel::getOption('general_configuration');
		if ($this->configuracao>0){
			add_filter('manage_edit-product_columns', array( $this,'add_posts_inog_column') );
			add_action( 'manage_product_posts_custom_column', array( $this, 'manage_product_posts_og_column' ),2 );
			$this->has_ecoauto = OfficeGestDBModel::getOption('officegest_ecoauto')==1;
			if ($this->has_ecoauto){
				add_action( 'manage_product_posts_custom_column', array( $this, 'manage_product_posts_estado_peca_column' ),2 );
			}
			add_filter( 'bulk_actions-edit-product',  array( $this,'send_to_og') );
			add_filter( 'handle_bulk_actions-edit-product',  array( $this,'send_to_og_handler'), 10, 3 );
		}

	}


	public function add_posts_inog_column($columns){
		$columns['in_og'] = _("Integrado no Officegest");
		if ($this->has_ecoauto) {
			$columns['estado_peca'] = _( "Estado Peça" );
		}
		return $columns;
	}

	public function manage_product_posts_og_column($column){
		global $product,$wpdb;
		if ($column=="in_og"){
			$sku=$product->get_sku();
			if (empty($product->get_sku())){
				$sku = Tools::createReferenceFromString($product->get_name());
			}
			$return = $wpdb->get_row( 'SELECT * FROM ' . TABLE_OFFICEGEST_ARTICLES . ' where id="' .$sku . '"', ARRAY_A );
			if (empty($return)){
				echo 'Não';
			}
			else{
				echo 'Sim';
			}
		}
	}
	public function manage_product_posts_estado_peca_column($column) {
		global $product, $wpdb;
		if ($column=="estado_peca") {
			$meta = OfficeGestDBModel::getPostMeta( $product->get_id() );
			echo $meta['_ecoauto_status_desc'] ? $meta['_ecoauto_status_desc'] : '';
		}
	}

	public function send_to_og($bulk_actions){
		$bulk_actions['send_to_og'] = __( 'Enviar para o OfficeGest', 'officegest');
		return $bulk_actions;
	}

	public function send_to_og_handler( $redirect_to, $doaction, $post_ids ) {
		if ( $doaction !== 'send_to_og' ) {
			return $redirect_to;
		}
		$created = 0;
		$updated = 0;
		$error=0;
		foreach ( $post_ids as $post_id ) {
			$product = new WC_Product($post_id);
			$cat_ids = $product->get_category_ids();
			$familia = '';
			$subfamilia = '';
			foreach ($cat_ids as $cats){
				$res = Tools::get_category($cats);
				if ($res->parent==0){
					$fam = OfficeGestCurl::getFamily(trim($res->name));
					if ($fam['total']>0){
						$families = array_values($fam['families']);
						$familia = $families[0]['id'];
					}
				}
				else{
					$sfam = OfficeGestCurl::getSubFamily(trim($res->name));
					if ($sfam['total']>0){
						if (is_array($sfam['subfamilies'])){
							$subfamilies = array_values($sfam['subfamilies']);
							$subfamilia = $subfamilies[0]['id'];
						}
					}
				}
			}
			$res = OfficeGestCurl::createProduct($product,$familia,$subfamilia);
			if ($res['status']==='created'){
				$created++;
			}
			if ($res['status']==='updated'){
				$updated++;
			}
			if ($res['status']==='error'){
				$error++;
			}
		}
		$args = [
			'action'=>'send_articles_to_og',
			'count'=>count( $post_ids ),
			'created'=>$created,
			'updated'=>$updated,
			'error'=>$error
		];
		$redirect_to = add_query_arg($args, $redirect_to );
		return $redirect_to;
	}
}