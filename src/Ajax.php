<?php

namespace OfficeGest;

use OfficeGest\Controllers\Documents;
use OfficeGest\Controllers\ProductsList;
use WC_Product;
use WC_Product_Variable;
use WC_Product_Variation;

class Ajax
{
	public $parent;

	/**
	 *
	 * @param Plugin $parent
	 */
	public function __construct($parent)
	{
		$this->parent = $parent;
		add_action('wp_ajax_genInvoice', [$this, 'genInvoice']);
		add_action('wp_ajax_updatearticlestable',  array( $this, 'updatearticlestable'));
		add_action('wp_ajax_updateecoautotable',  array( $this, 'updateecoautotable'));
		add_action('wp_ajax_updatecategoriestable',  array( $this, 'updatecategoriestable'));
		add_action('wp_ajax_updatecomponentstable',  array( $this, 'updatecomponentstable'));
		add_action('wp_ajax_updateartigos',  array( $this, 'updateArtigos'));
		add_action('wp_ajax_updateartigosecoauto',  array( $this, 'updateartigosecoauto'));

	}


	public function updatecomponentstable(){
		global $wpdb;
		$apenas_nao_integrados = sanitize_text_field($_REQUEST['apenas_nao_integrados']);
		if (sanitize_text_field($_REQUEST['display_table'])=='0') {
			wp_send_json( [
				'valid'  => true,
				'aaData' => []
			] );
		}
		try {
			if ( Start::login( true ) ) {
				$filtro = OfficeGestDBModel::DBEcoaAutoComponents();
				if ( ( $apenas_nao_integrados !== '- 1' ) && $apenas_nao_integrados === 'NOPE' ) {
					$filtro = Tools::filtrar( $filtro, 'woo_id',0, \OfficeGest\ArraySearcher::OP_EQUALS );
				}
				foreach ($filtro as $k=>$v){
					$filtro[$k]['integrado'] = ($filtro[$k]['woo_id'] == 0 or $filtro[$k]['woo_id']==null) ?'Não':'Sim';
				}
				wp_send_json( [
					'valid'  => true,
					'aaData' => $filtro
				] );
			}
		} catch (Error $e) {
			wp_send_json(['valid' => 0, 'message' => $e->getMessage(), 'description' => $e->getError()]);
		}
	}

	public function updatecategoriestable(){
		global $wpdb;
		$apenas_nao_integrados = sanitize_text_field($_REQUEST['apenas_nao_integrados']);
		if (sanitize_text_field($_REQUEST['display_table'])=='0') {
			wp_send_json( [
				'valid'  => true,
				'aaData' => []
			] );
		}
		try {
			if ( Start::login( true ) ) {
				$filtro = OfficeGestDBModel::DBEcoautoCategories();
				if ( ( $apenas_nao_integrados !== '- 1' ) && $apenas_nao_integrados === 'NOPE' ) {
					$filtro = Tools::filtrar( $filtro, 'woo_id',0, \OfficeGest\ArraySearcher::OP_EQUALS );
				}
				foreach ($filtro as $k=>$v){
					$filtro[$k]['integrado'] = ($filtro[$k]['woo_id'] == 0 or $filtro[$k]['woo_id']==null) ?'Não':'Sim';
				}
				wp_send_json( [
					'valid'  => true,
					'aaData' => $filtro
				] );
			}
		} catch (Error $e) {
			wp_send_json(['valid' => 0, 'message' => $e->getMessage(), 'description' => $e->getError()]);
		}
	}

	public function updateecoautotable(){
		global $wpdb;
		$category=sanitize_text_field($_REQUEST['category']);
		$brand=sanitize_text_field($_REQUEST['brand']);
		$part_status=sanitize_text_field($_REQUEST['part_status']);
		$part_types=sanitize_text_field($_REQUEST['part_types']);
		$apenas_nao_integrados = sanitize_text_field($_REQUEST['apenas_nao_integrados']);
		if (sanitize_text_field($_REQUEST['display_table'])=='0') {
			wp_send_json( [
				'valid'  => true,
				'aaData' => []
			] );
		}
		try {
			if ( Start::login( true ) ) {
				$lista = $filtro = OfficeGestDBModel::getAllEcoautoPartsDB();
				foreach ($filtro as $k=>$v){
					$data=[
						'woo_id'=>wc_get_product_id_by_sku(($filtro[$k]['id']))
					];
					$wpdb->update(TABLE_OFFICEGEST_ECO_PARTS,$data,['id'=>$filtro[$k]['id']]);
				}
				$lista = $filtro = OfficeGestDBModel::getAllEcoautoPartsDB();
				if($category!='-1'){
					$filtro = Tools::filtrar( $lista, 'category_id', $category, \OfficeGest\ArraySearcher::OP_EQUALS );
				}
				if($brand!='-1'){
					$filtro = Tools::filtrar( $filtro, 'brand_id', $brand, \OfficeGest\ArraySearcher::OP_EQUALS );
				}
				if($part_status!='-1'){
					$filtro = Tools::filtrar( $filtro, 'status_desc', $part_status, \OfficeGest\ArraySearcher::OP_EQUALS );
				}
				if($part_types!='-1'){
					$filtro = Tools::filtrar( $filtro, 'type_desc', $part_types, \OfficeGest\ArraySearcher::OP_EQUALS );
				}
				if ( ( $apenas_nao_integrados !== '- 1' ) && $apenas_nao_integrados === 'NOPE' ) {
					$filtro = Tools::filtrar( $filtro, 'woo_id',0, \OfficeGest\ArraySearcher::OP_EQUALS );
				}

				foreach ($filtro as $k=>$v){
					$filtro[$k]['status']=$filtro[$k]['status_desc'];
					$filtro[$k]['type']=$filtro[$k]['type_desc'];
					$filtro[$k]['date_alter']=Tools::FDate($filtro[$k]['date_alter'],2);
					$filtro[$k]['description']=$filtro[$k]['brand'].' '.$filtro[$k]['model'].' '.$filtro[$k]['version'].' '.$filtro[$k]['fuel'].' '.$filtro[$k]['year'];
					$filtro[$k]['pecas']='['.$filtro[$k]['category'].']'.' '.$filtro[$k]['component'];
					$filtro[$k]['photo']="<img class='img img-polaroid' style='max-width:32px;' src='".$filtro[$k]["photo"]."'></img>";
					$filtro[$k]['pvp_iva']= Tools::format_number($filtro[$k]['sellingprice']+( $filtro[$k]['sellingprice'] * ($filtro[$k]['value_vat']  / 100 ) ));
					$filtro[$k]['pvp'] = Tools::format_number($filtro[$k]['sellingprice']);
					$filtro[$k]['integrado'] = ($filtro[$k]['woo_id'] == 0 or $filtro[$k]['woo_id']==null) ?'Não':'Sim';
					$filtro[$k]['woocommerce'] = wc_get_product_id_by_sku($filtro[$k]['id']);
				}
				wp_send_json( [
					'valid'  => true,
					'aaData' => $filtro
				] );
			}
		} catch (Error $e) {
			wp_send_json(['valid' => 0, 'message' => $e->getMessage(), 'description' => $e->getError()]);
		}
	}

	public function updateArtigos(){
		global $wpdb;
		$tipo = sanitize_text_field($_REQUEST['tipo']);
		$classificacao = sanitize_text_field($_REQUEST['classificacao']);
		$artigos = is_array($_REQUEST['artigos'])?$_REQUEST['artigos']:null;
		$arts = [];
		$lista_update=[];
		foreach ($artigos as $art){
			$arts[] = "'" . $art . "'";
		}
		if (is_array($arts)){
			$artigos = implode(',',$arts);
		}
		if (!empty($artigos)){
			$query = 'select * from '.TABLE_OFFICEGEST_ARTICLES.' where id in ('.$artigos.')';
			$lista_update = $wpdb->get_results($query,ARRAY_A);
		}
		if ($tipo == 'PRECO_FICHA' ){
			self::cria_artigos($lista_update);
		}
		if ( ( $tipo == 'CLASSIFICACAO' ) && $classificacao !== '- 1' ) {
			OfficeGestDBModel::getOfficeGestPriceTables();
			if (!empty($artigos)){
				$query = 'select * from '.TABLE_OFFICEGEST_ARTICLES.' where id in ('.$artigos.') and id in (select article_id from '.TABLE_OFFICEGEST_PRICE_TABLES.' where classifcode="'.$classificacao.'")';
				$lista_update = $wpdb->get_results($query,ARRAY_A);
			}
			if (!empty($lista_update)){
				self::cria_artigos($lista_update);
				foreach ($lista_update as $k=>$v){
					$product = wc_get_product_id_by_sku($v['id']);
					$data = OfficeGestCurl::calculatePrices($v['id'],$classificacao);
					$tipo_artigo = OfficeGestDBModel::getArticleType($v);
					switch ($tipo_artigo){
						case 'variant':
							$artigo = new WC_Product_Variation($product);
							$artigo->set_price($data);
							$artigo->set_sale_price($data);
							$artigo->set_regular_price($data);
							$artigo->save();
							break;
						case 'variable':
							$artigo = new WC_Product_Variable($product);
							$artigo->set_price($data);
							$artigo->set_sale_price($data);
							$artigo->set_regular_price($data);
							$artigo->save();
							break;
						case 'simple':
						default:
							$artigo = new WC_Product($product);
							$artigo->set_price($data);
							$artigo->set_sale_price($data);
							$artigo->set_regular_price($data);
							$artigo->save();
							break;
					}
				}
			}
		}
		wp_send_json( [
			'valid'  => true,
			'finish' => true
		] );
	}

	public function updateartigosecoauto(){
		global $wpdb;
		$artigos = is_array($_REQUEST['artigos'])?$_REQUEST['artigos']:null;
		$arts = [];
		$lista_update=[];
		foreach ($artigos as $art){
			$arts[] = "'" . $art . "'";
		}
		if (is_array($arts)){
			$artigos = implode(',',$arts);
		}
		if (!empty($artigos)){
			$query = 'select * from '.TABLE_OFFICEGEST_ECO_PARTS.' where id in ('.$artigos.')';
			$lista_update = $wpdb->get_results($query,ARRAY_A);
			OfficeGestDBModel::cria_peca($lista_update);
		}

		wp_send_json( [
			'valid'  => true,
			'finish' => true
		] );
	}

	public static function cria_artigos($lista_update){
		foreach ($lista_update as $k=>$v){
			$tipo_artigo = OfficeGestDBModel::getArticleType($v);
			if ( $tipo_artigo == 'simple'){
				$id = OfficeGestDBModel::create_update_product($v);
			}
			if ( $tipo_artigo == 'variable') {
				$pai = OfficeGestCurl::getArticle($v['id']);
				$pai_data = [
					'title'         => $pai['description'],
					'content'       => $pai['description'],
					'vatid'         =>$pai['vatid'],
					'regular_price' => $pai['sellingprice'], // product regular price
					'sale_price'    => $pai['sellingprice'],
					'stock_quantity'=> $pai['stock_quantity'], // Set a minimal stock quantity
					'sku'           => $pai['id'], // optional
					'brand'         => $pai['brand'], // optional
					'barcode'       => $pai['barcode'], // optional
					'attributes'    =>OfficeGestDBModel::getAllAttributesParent( $pai )
				];
				$parent_id = OfficegestProduct::create_parent_product_variation($pai_data);
			}
			if ( $tipo_artigo == 'variant') {
				$filho = OfficeGestCurl::getArticle($v['id']);
				$pai = OfficeGestCurl::getArticle($filho['parent_id']);
				$pai_data = [
					'title'         => $pai['description'],
					'content'       => $pai['description'],
					'vatid'         =>$pai['vatid'],
					'regular_price' => $pai['sellingprice'], // product regular price
					'sale_price'    => $pai['sellingprice'],
					'stock_quantity'=> $pai['stock_quantity'], // Set a minimal stock quantity
					'sku'           => $pai['id'], // optional
					'brand'         => $pai['brand'], // optional
					'barcode'       => $pai['barcode'], // optional
					'attributes'    =>OfficeGestDBModel::getAllAttributesParent( $pai )
				];
				OfficegestProduct::create_parent_product_variation($pai_data);
				$variation_data = [
					'attributes'    =>  OfficeGestDBModel::getAllAttributesChildren( $filho ),
					'title'         => $filho['description'],
					'sku'           => $filho['id'],
					'vatid'         =>$filho['vatid'],
					'regular_price' => $filho['sellingprice'], // enter variant price
					'sale_price'    => $filho['sellingprice'],
					'brand'         => $filho['brand'], // optional
					'barcode'       => $filho['barcode'], // optional
					'stock_quantity' => $filho['stock_quantity'], // enter stock qty
				];
				$variation_id   = OfficegestProduct::create_product_variation( $filho['parent_id'], $variation_data );
				OfficeGestDBModel::setWooID( $variation_id, $filho['id'] );
			}
		}
	}



	public function updatearticlestable(){
		global $wpdb;
		$family=sanitize_text_field($_REQUEST['family']);
		$brand=sanitize_text_field($_REQUEST['brand']);
		$apenas_nao_integrados = sanitize_text_field($_REQUEST['apenas_nao_integrados']);
		if (sanitize_text_field($_REQUEST['display_table'])=='0') {
			wp_send_json( [
				'valid'  => true,
				'aaData' => []
			] );
		}
		try {
			if ( Start::login( true ) ) {
				$lista = $filtro = ProductsList::getAllAvailable();
				foreach ($filtro as $k=>$v){
					$data=[
						'woo_id'=>wc_get_product_id_by_sku(($filtro[$k]['id']))
					];
					$wpdb->update(TABLE_OFFICEGEST_ARTICLES,$data,['id'=>$filtro[$k]['id']]);
				}
				$lista = $filtro = ProductsList::getAllAvailable();
				if($family!='-1'){
					$filtro = Tools::filtrar( $lista, 'family', $family, \OfficeGest\ArraySearcher::OP_EQUALS );
				}
				if($brand!='-1'){
					$filtro = Tools::filtrar( $filtro, 'brandid', $brand, \OfficeGest\ArraySearcher::OP_EQUALS );
				}
				if ( ( $apenas_nao_integrados !== '- 1' ) && $apenas_nao_integrados === 'NOPE' ) {
					$filtro = Tools::filtrar( $filtro, 'woo_id',0, \OfficeGest\ArraySearcher::OP_EQUALS );
				}

				foreach ($filtro as $k=>$v){
					if ( $filtro[ $k ]['vatid'] === null ){
						$taxa =0;
					}
					else{
						$taxa  = OfficeGestDBModel::findTaxByValue($filtro[$k]['vatid'])['value'];
						if ($taxa==false){
							$taxa =0;
						}
					}

					$filtro[$k]['pvp_iva']= Tools::format_number($filtro[$k]['sellingprice']+( $filtro[$k]['sellingprice'] * ( $taxa / 100 ) ));
					$filtro[$k]['pvp'] = Tools::format_number($filtro[$k]['sellingprice']);
					$filtro[$k]['integrado'] = $filtro[$k]['woo_id'] == 0 ?'Não':'Sim';
					$filtro[$k]['woocommerce'] = wc_get_product_id_by_sku($filtro[$k]['id']);
					$tipo_artigo = OfficeGestDBModel::getArticleType($v);
					switch ($tipo_artigo){
						case 'variable':
							$artigo_tipo='Artigo Pai';
							break;
						case 'variant':
							$artigo_tipo='Artigo Filho';
							break;
						default:
							$artigo_tipo='Normal';
					}
					$filtro[$k]['tipo_artigo'] = $artigo_tipo;
				}
				wp_send_json( [
					'valid'  => true,
					'aaData' => $filtro
				] );
			}
		} catch (Error $e) {
			wp_send_json(['valid' => 0, 'message' => $e->getMessage(), 'description' => $e->getError()]);
		}
	}

	public function genInvoice()
	{
		try {
			if (Start::login(true)) {
				$orderId = (int)sanitize_text_field($_REQUEST['id']);
				try {
					$document = new Documents($orderId);
					$document->createDocument();
					if (!$document->getError()) {
						wp_send_json(['valid' => 1, 'message' => sprintf(__('Documento %s inserido com sucesso'), $document->order->get_order_number())]);
					}

					wp_send_json([
						'valid' => 0,
						'message' => $document->getError()->getDecodedMessage(),
						'description' => $document->getError()->getError()
					]);
				} catch (Error $e) {
					wp_send_json(['valid' => 0, 'message' => $e->getMessage(), 'description' => $e->getError()]);
				}
			}
		} catch (Error $e) {
			wp_send_json(['valid' => 0, 'message' => $e->getMessage(), 'description' => $e->getError()]);
		}
	}
}
