<?php

namespace OfficeGest;

use PHPMailer\PHPMailer\Exception;
use WC_Product;
use WC_Product_Attribute;
use WC_Product_External;
use WC_Product_Grouped;
use WC_Product_Simple;
use WC_Product_Variable;
use WC_Product_Variation;
use WP_Error;
use WP_Http;
use WP_Query;

class OfficeGestDBModel
{
    /**
     * OfficeGestDBModel constructor.
     */
    public function __construct()
    {

    }

    /**
     * Get all available custom fields
     *
     * @return array
     */
    public static function getCustomFields()
    {
        global $wpdb;

        $results = $wpdb->get_results(
	        'SELECT DISTINCT meta_key FROM ' . $wpdb->prefix . "postmeta ORDER BY `" . $wpdb->prefix . "postmeta`.`meta_key` ASC",
            ARRAY_A
        );
        $customFields = [];
        if ($results && is_array($results)) {
            foreach ($results as $result) {
                $customFields[] = $result;
            }
        }
        return $customFields;
    }

	public static function getSubCategories($familia){
		global $wpdb;
		return $wpdb->get_results('SELECT * from officegest_categories where subfamilia="'.$familia.'"',ARRAY_A);
	}

    public static function create_update_product($data,$parent=[]){
		$post_id = wc_get_product_id_by_sku($data['id']);
		if (empty($post_id)){
			$post_id = wp_insert_post(
				array(
					'post_title' => $data['description'],
					'post_type' => 'product',
					'post_status' => 'publish',
					'comment_status' => 'closed',
					'post_name'=>Tools::slugify($data['description']),
					'post_content' => $data['description'],
					'post_excerpt' => $data['description']
				)
			);
			self::update_product_meta($post_id,$data);
			self::setWooID($post_id,$data['id']);
		}
		else {
			wp_update_post( array(
				'ID'          => $post_id,
				'post_status' => 'publish',
				'post_name'   => Tools::slugify( $data['description'] )
			) );
			self::update_product_meta( $post_id, $data );
			self::setWooID( $post_id, $data['id'] );
		}
	    return $post_id;

    }

    public static function update_product_meta($post_id,$data,$set=true){
    	if (is_null($data['vatid'])){
		    $taxa =0;
	    }
    	else{
		    $taxa  = self::findTaxByValue($data['vatid'])['value'];
		    if ($taxa==false){
			    $taxa =0;
		    }
	    }
    	$data['sellingprice'] += ( $data['sellingprice'] * ( $taxa / 100 ) );
    	if ($set===true){
		    wp_set_object_terms( $post_id, 'simple', 'product_type' );
	    }
	    update_post_meta( $post_id, '_visibility', 'visible' );
	    if ( isset($data['barcode']) && $data['barcode'] !== null ) {
		    update_post_meta( $post_id, self::getOption( 'artigos_ean' ), $data['barcode'] );
		    update_post_meta( $post_id, '_officegest_ean', $data['barcode'] );
	    }
	    if ( isset($data['brand']) && $data['brand'] !== null ){
		    $brands = self::getDBBrands($data['brand']);
		    update_post_meta( $post_id, self::getOption('artigos_marca'), $brands['description'] );
		    update_post_meta( $post_id,'_officegest_brand', $brands['description'] );
	    }
	    update_post_meta( $post_id, '_visibility', 'visible' );
	    update_post_meta( $post_id, '_stock_status', 'instock');
	    update_post_meta( $post_id, '_total_sales', '0' );
	    update_post_meta( $post_id, '_downloadable', 'no' );
	    update_post_meta( $post_id, '_virtual', 'no' );
	    update_post_meta( $post_id, '_regular_price', $data['sellingprice'] );
	    update_post_meta( $post_id, '_sale_price', $data['sellingprice'] );
	    update_post_meta( $post_id, '_visibility', 'visible' );
	    update_post_meta( $post_id, '_purchase_note', '');
	    update_post_meta( $post_id, '_featured', 'no' );
	    update_post_meta( $post_id, '_sku', $data['id'] );
	    update_post_meta( $post_id, '_taxable', false );
	    update_post_meta( $post_id, '_product_attributes', array() );
	    update_post_meta( $post_id, '_sale_price_dates_from', '' );
	    update_post_meta( $post_id, '_sale_price_dates_to', '' );
	    update_post_meta( $post_id, '_price', $data['sellingprice'] );
	    update_post_meta( $post_id, '_sold_individually', '');
	    update_post_meta( $post_id, '_manage_stock', 'yes' );
	    wc_update_product_stock($post_id, $data['stock_quantity'], 'set');
	    update_post_meta( $post_id, '_backorders', 'no' );
    }

	public static function getCarBrands(  ) {
		global $wpdb;
		$brands = OfficeGestCurl::getWorkShopBrands();
		foreach ($brands as $k=>$v){
			$data=[
				'id'=>$v['id'],
				'description'=>$v['description']
			];
			$wpdb->replace(TABLE_OFFICEGEST_CAR_BRANDS,$data);
		}
		return $wpdb->get_results( 'select id,description from ' . TABLE_OFFICEGEST_CAR_BRANDS,ARRAY_A);
	}

	public static function getFamilies(  ) {
		global $wpdb;
		$brands = OfficeGestCurl::getStocksFamilies();
		foreach ($brands as $k=>$v){
			$data=[
				'id'=>$v['id'],
				'description'=>$v['description']
			];
			$wpdb->replace(TABLE_OFFICEGEST_FAMILIES,$data);
		}
	}

	public static function getEcoautoCategories(){
		global $wpdb;
		$cats = [];
		$cats = OfficeGestCurl::ecoauto_parts_categories();
		if ($cats['total']>0){
			foreach ($cats['categories'] as $k=>$v){
				$data=[
					'id'=>$v['id'],
					'description'=>$v['description']
				];
				$wpdb->replace(TABLE_OFFICEGEST_ECO_CATS,$data);
			}
		}
		return true;
	}

	public static function DBEcoautoCategories(){
		global $wpdb;
		self::getEcoautoCategories();
		return $wpdb->get_results( 'select * from ' . TABLE_OFFICEGEST_ECO_CATS,ARRAY_A);
	}

	public static function DBEcoaAutoComponents(){
		global $wpdb;
		self::getEcoautoComponents();
		return $wpdb->get_results( 'select * from ' . TABLE_OFFICEGEST_ECO_COMPS,ARRAY_A);
	}

	public static function getEcoautoComponents(){
		global $wpdb;
		$cats = OfficeGestCurl::ecoauto_part_components();
		if ($cats['total']>0){
			foreach ($cats['components'] as $k=>$v){
				$data=[
					'id'=>$v['id'],
					'category_id'=>$v['category_id'],
					'description'=>$v['description'],
					'value'=>$v['value'],
					'article_id'=>$v['article_id'],
					'active'=>$v['active']
				];
				$wpdb->replace(TABLE_OFFICEGEST_ECO_COMPS,$data);
			}
		}
		return true;
	}

	public static function prepare_articles_external_tables(){
		self::getBrands();
		self::getFamilies();
		//self::getOfficeGestPriceTables();
	}

	public static  function getAllOfficeGestProducts($data=null){
		return OfficeGestCurl::getArticlesList($data);
	}

	public static function getDBBrandsDescription($filtro) {
		global $wpdb;
		$query = 'SELECT * FROM  '.TABLE_OFFICEGEST_BRANDS;
		$query .= ' where id=' . $filtro;
		$data =  $wpdb->get_row($query,ARRAY_A);
		if (!empty($data)){
			return $data['description'];
		}
		return null;
	}

	public static function getDBBrands($filtro=null) {
    	global $wpdb;
		self::getBrands();
		$query = 'SELECT * FROM  '.TABLE_OFFICEGEST_BRANDS;
		if ($filtro!=null){
			$query .= ' where id=' . $filtro;
			return $wpdb->get_row($query,ARRAY_A);
		}
		return $wpdb->get_results($query,ARRAY_A);
    }

	public static function getDBCarBrands($filtro=null) {
		global $wpdb;
		self::getDBCarBrands();
		$query = 'SELECT * FROM  '.TABLE_OFFICEGEST_CAR_BRANDS;
		if ($filtro!=null){
			$query .= ' where id=' . $filtro;
			return $wpdb->get_row($query,ARRAY_A);
		}
		return $wpdb->get_results($query,ARRAY_A);
	}

	public static function getDBFamilies($filtro=null) {
		global $wpdb;
		$query = 'SELECT * FROM  '.TABLE_OFFICEGEST_FAMILIES;
		if ($filtro!=null){
			$query .= ' where id=' . $filtro;
		}
		return $wpdb->get_results($query,ARRAY_A);

	}

	public static function getArticleType( $artigo ) {
		return $artigo['spaces_dimensions'] === 'N' ? ( $artigo['id_spaces_dimensions'] === '' || $artigo['id_spaces_dimensions'] === null ? 'simple' : 'variable' ) : 'variant';
	}

	public static function getArticleDB($artigo){
    	global $wpdb;
    	$query = 'SELECT * from ' . TABLE_OFFICEGEST_ARTICLES . ' where id="' . $artigo.'"';
		return $wpdb->get_row($query,ARRAY_A);
	}

	public static function setWooID($id,$product) {
    	global $wpdb;
    	$data=[
    	    'woo_id'=>$id
	    ];
    	$wpdb->update(TABLE_OFFICEGEST_ARTICLES,$data,['id'=>$product]);
	}

	public static function getAllAttributesParent($artigo){
		$opcoes=[];
		$final=[];
		if (isset($artigo['children'])){
			$filhos = $artigo['children'];
			foreach ($filhos as $k=>$v){
				$opcoes[] = $v['children_options'];
			}

			foreach ($opcoes as $opcao){
				foreach ($opcao as $value) {
					$final[$value['name']][$value['value']] = $value['value'];
				}

			}
			foreach ($final as $k => $value) {
				$final[$k] = array_values($final[$k]);
			}
			return $final;
		}
		return $final;
	}

	public static function getAllAttributesChildren($artigo){
		$final=[];
		if (isset($artigo['parent_id'])){
			if (isset($artigo['children_options'])){
				$opcoes = $artigo['children_options'];
				foreach ($opcoes as $opcao){
					$final[$opcao['name']][$opcao['value']] = $opcao['value'];
				}
				foreach ($final as $k => $value) {
					$final[$k] = array_values($final[$k]);
				}
				return $final;
			}
		}
		return $final;
	}

	public static function getOfficeGestPriceTables(){
    	global $wpdb;
    	$prices_tables = OfficeGestCurl::getPricesTables();
    	foreach ($prices_tables as $data){
    		$wpdb->replace(TABLE_OFFICEGEST_PRICE_TABLES,$data);
	    }
	}

	public static function getAllEcoautoPartsTypes() {
		$types = OfficeGestCurl::ecoauto_part_types();
		if (!empty($types)){
			return $types['types'];
		}
		return [];
	}

	public static function getAllEcoautoPartsStatus() {
		$status = OfficeGestCurl::ecoauto_part_status();
		if (!empty($status)){
			return $status['status'];
		}
		return [];
	}

	public static function getAllEcoautoPartsDB($update=true,$filtro=null) {
		global $wpdb;
		if ($update){
			self::getAllEcoautoParts();
		}
		$query = "Select * from ".TABLE_OFFICEGEST_ECO_PARTS." where (woo_id = 0 or woo_id is null)";
		if ($filtro!=null){
			if ($filtro['tipos_pecas']==2){
				$query.=' and status_desc = "Desmantelado"';
			}
			if ($filtro['tipos_pecas']==3){
				$query.=' and status_desc = "Em Parque"';
			}
		}
		return $wpdb->get_results($query,ARRAY_A);
	}

	public static function update_product_meta_ecoauto($post_id, $data ) {
    	$chave = array_keys($data);
		foreach ($chave as $k=>$v){
			update_post_meta( $post_id, '_ecoauto_'.$v, $data[$v] );
		}
		update_post_meta( $post_id, '_stock_status', 'instock');
		update_post_meta( $post_id, '_total_sales', '0' );
		update_post_meta( $post_id, '_downloadable', 'no' );
		update_post_meta( $post_id, '_virtual', 'no' );
		update_post_meta( $post_id, '_regular_price', $data['selling_price'] );
		update_post_meta( $post_id, '_sale_price', $data['selling_price'] );
		update_post_meta( $post_id, '_visibility', 'visible');
		update_post_meta( $post_id, '_purchase_note', '');
		update_post_meta( $post_id, '_featured', 'no' );
		update_post_meta( $post_id, '_sku', $data['id'] );
		update_post_meta( $post_id, '_taxable', false );
		update_post_meta( $post_id, '_product_attributes', array() );
		update_post_meta( $post_id, '_sale_price_dates_from', '' );
		update_post_meta( $post_id, '_sale_price_dates_to', '' );
		update_post_meta( $post_id, '_price', $data['selling_price_withvat'] );
		update_post_meta( $post_id, '_sold_individually', '');
		update_post_meta( $post_id, '_manage_stock', 'yes' );
		wc_update_product_stock($post_id, 1, 'set');
		update_post_meta( $post_id, '_backorders', 'no' );
	}

	public static function cria_peca($lista){
    	$counter=0;
		foreach ($lista as $k=>$v){
			$data = $v;
			$id = OfficegestProduct::createEcoAutoPart($data);
			if ($id!=0){
				OfficeGestDBModel::setWooIdEcoAutoParts($id,$data['id']);
			}
            $counter++;
		}
		return $counter;
	}

	public static function getFilteredDBBrandsEcoAuto() {
		global $wpdb;
		$query = 'SELECT distinct brand_id as id, brand as description from ' . TABLE_OFFICEGEST_ECO_PARTS;
		return $wpdb->get_results($query,ARRAY_A);
	}

	public static function getFilteredCategoryEcoAuto() {
		global $wpdb;
		$query = 'SELECT distinct category_id as id, category  as description from ' . TABLE_OFFICEGEST_ECO_PARTS;
		return $wpdb->get_results($query,ARRAY_A);
	}

	public static function getFilteredComponentsEcoAuto() {
		global $wpdb;
		$query = 'SELECT distinct component_id as id, component  as description from ' . TABLE_OFFICEGEST_ECO_PARTS;
		return $wpdb->get_results($query,ARRAY_A);
	}

	public static function getFilteredDBVersionsEcoAuto($brand_id=null) {
		global $wpdb;
		$query = 'SELECT distinct version_id as id, version as description from ' . TABLE_OFFICEGEST_ECO_PARTS;
		if ($brand_id!=null){
			$query .= ' where brand_id = ' . $brand_id;
		}
		return $wpdb->get_results($query,ARRAY_A);
	}

	public static function getFilteredYearEcoAuto() {
		global $wpdb;
		$query = 'SELECT distinct year as id from ' . TABLE_OFFICEGEST_ECO_PARTS;
		return $wpdb->get_results($query,ARRAY_A);
	}

	public static function create_category_ecoauto($data,$parent=null){
		$res = $parent ? wp_insert_term(
			$data['description'], // the term
			'product_cat', // the taxonomy
			array(
				'description' => $data['description'],
				'slug'        => Tools::slugify( $data['description'] ),
				'parent'      => $parent
			)
		) : wp_insert_term(
			$data['description'], // the term
			'product_cat', // the taxonomy
			array(
				'description' => $data['description'],
				'slug'        => Tools::slugify( $data['description'] )
			)
		);
		return Tools::object_to_array($res);
	}

	public static function findEcoCatCategoryID( $name ){
		global $wpdb;
		$result = $wpdb->get_row("select * from ".$wpdb->prefix."term_taxonomy where taxonomy='product_cat' and parent=0 and description='".$name."' LIMIT 1",ARRAY_A);

		return $result['term_taxonomy_id'];
	}

	public static function findEcoCompCategoryID( $name, $cat ){
		global $wpdb;
		$result = $wpdb->get_row("select * from ".$wpdb->prefix."term_taxonomy where taxonomy='product_cat' and parent='".$cat."' and description='".$name."' LIMIT 1",ARRAY_A);
		return $result['term_taxonomy_id'];
	}

	private static function insert_term_relation($post_id,$categoria){
    	global $wpdb;
    	$data=[
    	    'object_id'=>$post_id,
		    'term_taxonomy_id'=>get_term_by('description', $categoria, 'product_cat')->term_id,
		    'term_order'=>0
	    ];
    	$wpdb->insert($wpdb->prefix.'term_relationship',$data);
	}

	public static function set_image(){
    	global $wpdb;
		$query = 'SELECT * from ' . TABLE_OFFICEGEST_ECO_PARTS. ' where woo_id is not null and woo_id>0 and photos>0';
		$lista =  $wpdb->get_results($query,ARRAY_A);
		foreach ($lista as $k=>$data){
			if ($data['photos']>0){
				$photos = $wpdb->get_results('SELECT * from ' . TABLE_OFFICEGEST_ECO_PHOTOS. ' where component_id='.$data['id'],ARRAY_A);
				foreach ($photos as $kd=>$vd){
					self::generateFeaturedImage($vd['photo'],$data['woo_id'],$vd['attach_num']);
				}
			}
		}
	}

	public static function getAllWooCategories() {
		$taxonomy     = 'product_cat';
		$orderby      = 'id';
		$show_count   = 0;      // 1 for yes, 0 for no
		$pad_counts   = 0;      // 1 for yes, 0 for no
		$hierarchical = 1;      // 1 for yes, 0 for no
		$title        = '';
		$empty        = 1;

		$args = array(
			'taxonomy'     => $taxonomy,
			'orderby'      => $orderby,
			'show_count'   => $show_count,
			'pad_counts'   => $pad_counts,
			'hierarchical' => $hierarchical,
			'title_li'     => $title,
			'hide_empty'   => $empty
		);
		return get_categories( $args );
	}

    /**
     * Create articles for WooCommerce
     *
     * @param $list
     * @return int
     */
    public static function createArticles($list){
        try{
            $counter = 0;
            foreach ($list as $k => $a){
                $id = OfficegestProduct::createArticle($a);

                if ($id!=0){
                    OfficeGestDBModel::setWooIDArticles($id,$a['id']);

    //                if ($a['photos']>0){
    //                    self::generateFeaturedImage($a['photo'],$id,0);
    //                }
                }
                $counter++;
            }
            return $counter;
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: createArticles] Error: ".$e->getMessage());
            return 0;
        }
    }

    /*****************************************************************************************************************/

    /*****************************************************************************************************************/

    /*****************************************************************************************************************/

    /*****************************************************************************************************************/

    /*****************************************************************************************************************/

    /*****************************************************************************************************************/

    /*****************************************************************************************************************/

    /**
     * Define constants from database
     */
    public static function defineValues()
    {
        $tokensRow = self::getTokensRow();

        self::defineConfigs();
    }

    /**
     * Get OfficeGest domain
     *
     * @return mixed
     */
    public static function getOFFICEGESTDOMAIN(){
        $tokensRow = self::getTokensRow();
        return $tokensRow['domain'];
    }

    /**
     * Get OfficeGest username
     *
     * @return mixed
     */
    public static function getOFFICEGESTUSERNAME(){
        $tokensRow = self::getTokensRow();
        return $tokensRow['username'];
    }

    /**
     * Get OfficeGest api key
     *
     * @return mixed
     */
    public static function getOFFICEGESTAPIKEY(){
        $tokensRow = self::getTokensRow();
        return $tokensRow['api_key'];
    }

    /**
     * Get OfficeGest company id
     *
     * @return mixed
     */
    public static function getOFFICEGESTCOMPANYID(){
        $tokensRow = self::getTokensRow();
        return $tokensRow['company_id'];
    }

    /**
     * Define company selected settings
     */
    public static function defineConfigs()
    {
        global $wpdb;
        $results = $wpdb->get_results( 'SELECT * FROM officegest_api_config ORDER BY id DESC', ARRAY_A);
        foreach ($results as $result) {
            $setting = strtoupper($result['config']);
            if (!defined($setting)) {
                define( $setting, $result['selected'] );
            }
        }
    }

    /**
     * Return the row of officegest_api table with all the session details
     *
     * @return array|false
     * @global $wpdb
     */
    public static function getTokensRow()
    {
        global $wpdb;
        $results = $wpdb->get_row( 'SELECT * FROM officegest_api ORDER BY id DESC', ARRAY_A);
        return $results;
    }

    /**
     * Reset tokens
     *
     * @return array|false
     */
    public static function resetTokens()
    {
        global $wpdb;
        $wpdb->query("TRUNCATE officegest_api");
        return self::getTokensRow();
    }

    /**
     * Clear officegest_api and set new access and refresh token
     *
     * @return array|false
     * @global $wpdb
     */
    public static function setTokens($domain,$username, $password,$apikey)
    {
        global $wpdb;
        $wpdb->query("TRUNCATE officegest_api");
        $wpdb->insert('officegest_api', ['domain'=>$domain,'username' => $username, 'password' => $password,'api_key'=>$apikey,'company_id'=>1]);
        return self::getTokensRow();
    }

    /**
     * Get option value from OfficeGest api configurations
     *
     * @param $option
     * @return mixed
     */
    public static function getOption($option)
    {
        global $wpdb;
        $setting = $wpdb->get_row($wpdb->prepare( 'SELECT * FROM officegest_api_config WHERE upper(config) = upper(%s)', $option), ARRAY_A);
        if (empty($setting)) {
            $wpdb->insert('officegest_api_config', ['selected' => null, 'config' => $option]);
            $setting = $wpdb->get_row($wpdb->prepare( 'SELECT * FROM officegest_api_config WHERE upper(config) = upper(%s)', $option), ARRAY_A);
        }
        return $setting['selected'];
    }

    /**
     * Check if a setting exists on database and update it or create it
     * @param string $option
     * @param string $value
     * @return int
     * @global $wpdb
     */
    public static function setOption($option, $value)
    {
        global $wpdb;

        $setting = $wpdb->get_row($wpdb->prepare( 'SELECT * FROM officegest_api_config WHERE config = %s', $option), ARRAY_A);
        if (!empty($setting)) {
            $wpdb->update('officegest_api_config', ['selected' => $value], ['config' => $option]);
        } else {
            $wpdb->insert('officegest_api_config', ['selected' => $value, 'config' => $option]);
        }

        return $wpdb->insert_id;
    }

    /**
     * Find officegest tax by id
     *
     * @param $id
     * @return array|object|void
     */
    public static function findTaxById($id) {
        global $wpdb;
        $tax = $wpdb->get_row($wpdb->prepare( 'SELECT * FROM  officegest_vats WHERE id = %s', $id), ARRAY_A);
        if (empty($tax)){
            return [];
        }
        return $tax;
    }

    /**
     * Find officegest tax by value
     *
     * @param $value
     * @return mixed|string
     */
    public static function findTaxByValue($value) {
        global $wpdb;
        $tax = $wpdb->get_row($wpdb->prepare( 'SELECT * FROM  officegest_vats WHERE value = %s', $value), ARRAY_A);
        if (empty($tax)){
            return 'N';
        }
        return $tax['id'];
    }

    /**
     * Find woocommerce tax from id
     *
     * @param $id
     * @return array|false|object|void
     */
    public static function findTax($id) {
        global $wpdb;
        $tax = $wpdb->get_row($wpdb->prepare( 'SELECT * FROM  '.$wpdb->prefix.'woocommerce_tax_rates WHERE tax_rate_class = upper(%s)', $id), ARRAY_A);
        if (empty($tax)){
            return false;
        }
        return $tax;
    }

    /**
     * Find officegest article from id
     *
     * @param $idArticle
     * @return array|bool|mixed|object|void
     */
    public static function findArticle($idArticle) {
        try{
            global $wpdb;
            $query = 'SELECT * FROM  '.TABLE_OFFICEGEST_ARTICLES.' WHERE id = %s';
            $article = $wpdb->get_row($wpdb->prepare($query, $idArticle), ARRAY_A);
            if ($article != false){
                return $article;
            }
            $article = OfficeGestCurl::getArticle($idArticle);
            if ( $article != false){
                $data=[
                    'id'=>$article['id'],
                    'description'=>$article['description'],
                    'articletype'=>$article['articletype'],
                    'purchasingprice'=>$article['purchasingprice'],
                    'sellingprice'=>$article['sellingprice'],
                    'vatid'=>$article['vatid'],
                    'unit'=>$article['unit'],
                    'stock_quantity'=>$article['stock_quantity'],
                    'family'=>$article['family'],
                    'subfamily'=>$article['subfamily'],
                    'barcode'=>$article['barcode'],
                    'brand'=>$article['brand'],
                    'active'=>$article['active'],
                    'spaces_dimensions'=>$article['tipoespdim'],
                    'id_spaces_dimensions'=>$article['codespdim'],
                    'activeforweb'=>$article['activeforweb'],
                    'alterationdate'=>$article['alterationdate'],
                    're'=>$article['re'],
                    'idforweb'=>$article['idforweb'],
                    'priceforweb'=>$article['priceforweb'],
                    'referenceforweb'=>$article['referenceforweb'],
                    'long_description'=>$article['long_description'],
                    'short_description'=>$article['short_description']
                ];
                $wpdb->insert(TABLE_OFFICEGEST_ARTICLES,$data);
            }
            return $article;
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: findArticle] Error: ".$e->getMessage());
            return null;
        }
    }

    /**
     * Check if category exist
     * Return empty if doesn't exist
     * Return id if found
     *
     * @param $id
     * @return array
     */
    public static function getSingleCategory($id){
        global $wpdb;
        $res =  $wpdb->get_row('SELECT * from officegest_categories where id="'.$id.'"',ARRAY_A);
        if (!empty($res)){
            return [
                'res'=>$res,
                'total'=>1
            ];
        }
        return [
            'res'=>[],
            'total'=>0
        ];
    }

    /**
     * Get article parent categories from officegest tables
     *
     * @param $only_fams
     * @return array|object|null
     */
    public static function getCategories($only_fams){
        global $wpdb;
        if ($only_fams==true){
            return $wpdb->get_results('SELECT * from officegest_categories where subfamilia="^"',ARRAY_A);
        }
        return $wpdb->get_results('SELECT * from officegest_categories',ARRAY_A);
    }

    /**
     * Add articles category;
     * Create new category if nonexistent;
     *
     * @param $parent_term_id
     * @return void
     */
    public static function syncArticleCategories($parent_term_id){
        try{
            global $wpdb;

            $sql_categories = "SELECT * from ".TABLE_OFFICEGEST_CATEGORIES." where subfamilia = '^'";
            $parentCategories = $wpdb->get_results($sql_categories,ARRAY_A);
            foreach($parentCategories as $key => $category) {
                $parent_term = term_exists( htmlspecialchars($category['description']), 'product_cat', $parent_term_id);
                if (!isset($parent_term['term_id'])) {
                    OfficeGestDBModel::createProductCategory( [
                        'description' => $category['description'],
                        'parent' => $parent_term_id
                    ], true );
                    $parent_term = term_exists( htmlspecialchars($category['description']), 'product_cat', $parent_term_id );
                    OfficeGestDBModel::setTermIdCategories($parent_term['term_id'],$category['id']);
                }
                if (isset($parent_term['term_id'])) {
                    $sql_subCategories = 'SELECT * from '.TABLE_OFFICEGEST_CATEGORIES.' where subfamilia = "'.$category['id'].'"';
                    $subCategories = $wpdb->get_results($sql_subCategories,ARRAY_A);
                    if(!empty($subCategories)){
                        foreach($subCategories as $k => $subCategory) {
                            $subCategory_term = term_exists( htmlspecialchars($subCategory['description']), 'product_cat', $parent_term['term_id']);
                            if (!isset($subCategory_term['term_id'])) {
                                OfficeGestDBModel::createProductCategory([
                                    'description' => $subCategory['description'],
                                    'parent' => $parent_term['term_id']
                                ], true);
                                $subCategory_term = term_exists( htmlspecialchars($subCategory['description']), 'product_cat', $parent_term['term_id'] );
                                OfficeGestDBModel::setTermIdCategories($subCategory_term['term_id'],$subCategory['id']);
                            }
                        }
                    }
                }
            }
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: syncArticleCategories] Error: ".$e->getMessage());
        }
    }

    /**
     * Helper to get article category;
     *
     * @return mixed|null
     */
    public static function getArticleCategory(){
        try{
            $parent_term = term_exists( 'artigos', 'product_cat');
            return $parent_term['term_id'];
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: getArticleCategory] Error: ".$e->getMessage());
            return null;
        }
    }

    /**
     * Helper to populate articles array;
     *
     * @param $p
     * @param false $update
     * @return array|bool
     */
    public static function createArticlesArray($p, $update = false){
        try{
            $timestamp = date("Y-m-d H:i:s");

            $data=[
                'id'                    => $p['id'],
                'description'           => $p['description'],
                'articletype'           => $p['articletype'],
                'purchasingprice'       => $p['purchasingprice'],
                'sellingprice'          => $p['sellingprice'],
                'vatid'                 => $p['vatid'],
                'unit'                  => $p['unit'],
                'stock_quantity'        => $p['stock_quantity'],
                'family'                => $p['family'],
                'subfamily'             => $p['subfamily'],
                'barcode'               => $p['barcode'],
                'brand'                 => $p['brand'],
                'active'                => $p['active'],
                'spaces_dimensions'     => $p['spaces_dimensions'],
                'id_spaces_dimensions'  => $p['id_spaces_dimensions'],
                'activeforweb'          => $p['activeforweb'],
                'alterationdate'        => $p['alterationdate'],
                're'                    => $p['re'],
                'idforweb'              => empty($p['idforweb']) ? '' : $p['idforweb'],
                'priceforweb'           => empty($p['priceforweb']) ? '' : $p['priceforweb'],
                'referenceforweb'       => empty($p['referenceforweb']) ? '' : $p['referenceforweb'],
                'long_description'      => empty($p['long_description']) ? '' : $p['long_description'],
                'short_description'     => empty($p['short_description']) ? '' : $p['short_description'],
                'article_imported'      => 1
            ];

            if($update == true){
                $data['updated_at'] = $timestamp;
            } else {
                $data['created_at'] = $timestamp;
                $data['updated_at'] = $timestamp;
            }

            return $data;
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: createArticlesArray] Error: ".$e->getMessage());
            return true;
        }
    }

    /**
     * Validate if article have all required data for import;
     * Fields for verification: id;
     *
     * @param $p
     * @return bool
     */
    public static function validateArticleCompleteData($p){
        try{
            $verify = [
                'id'
            ];
            foreach ($verify as $ver){
                if(empty($p[$ver])){
                    return true;
                }
            }
            return false;
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: validateArticleCompleteData] Error: ".$e->getMessage());
            return true;
        }

    }

    /**
     * Get single article from id;
     *
     * @param $id
     * @return array|object|void
     */
    private static function getSingleArticle( $id ) {
        try{
            global $wpdb;
            $query = 'SELECT * from '.TABLE_OFFICEGEST_ARTICLES.' where id="'.$id.'"';
            $single =  $wpdb->get_row($query,ARRAY_A);
            if (!empty($single)){
                return $single;
            }
            return [];
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: getSingleArticle] Error: ".$e->getMessage());
            return [];
        }
    }

    /**
     * Create or Update articles received from OfficeGest API;
     *
     * @param int $offset
     * @param int $limit
     */
    public static function getAllArticles($offset=0,$limit=1000) {
        try{
            global $wpdb;
            $allProducts = OfficeGestCurl::getArticles($offset, $limit);

            if ($allProducts['total']>0){
                $products = $allProducts['articles'];
                foreach ($products as $k=>$p){
                    if (empty(self::getSingleArticle($p['id']))){
                        $incomplete_data = self::validateArticleCompleteData($p);

                        if($incomplete_data){
                            Log::write('Incomplete data for article - id: '. $p['id']);
                        } else {
                            $insert_data = self::createArticlesArray($p, false);
                            $wpdb->insert(TABLE_OFFICEGEST_ARTICLES,$insert_data);
                            $parts_id[]  = $p['id'];
                        }
                    } else {
                        $update_data = self::createArticlesArray($p, true);
                        $data=[
                            'id'=>$p['id']
                        ];
                        $parts_id[]  = $p['id'];
                        $wpdb->update(TABLE_OFFICEGEST_ARTICLES,$update_data,$data);
                    }
                }
                if ( $allProducts['total'] == $limit ) {
                    $offset = $limit + $offset;
                    self::getAllArticles( $offset, $limit);
                }
            }
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: getAllArticles] Error: ".$e->getMessage());
        }
    }

    /**
     * Get single brand from id;
     *
     * @param $id
     * @return array|object|void
     */
    private static function getSingleBrand( $id ) {
        try{
            global $wpdb;
            $query = 'SELECT * from '.TABLE_OFFICEGEST_BRANDS.' where id="'.$id.'"';
            $single =  $wpdb->get_row($query,ARRAY_A);
            if (!empty($single)){
                return $single;
            }
            return [];
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: getSingleBrand] Error: ".$e->getMessage());
            return [];
        }
    }

    /**
     * Sync all brands;
     *
     */
    public static function getBrands() {
        try{
            global $wpdb;
            $brands = OfficeGestCurl::getStocksBrands();
            foreach ($brands as $k=>$b){
                $data=[
                    'id'=>$b['id'],
                    'description'=>$b['description']
                ];
                $wpdb->replace(TABLE_OFFICEGEST_BRANDS,$data);

                if (empty(self::getSingleBrand($b['id']))){
                    $insert_data = [
                        'id'=>$b['id'],
                        'description'=>$b['description']
                    ];
                    $wpdb->insert(TABLE_OFFICEGEST_ARTICLES,$insert_data);
                } else {
                    $update_data = [
                        'id'=>$b['id'],
                        'description'=>$b['description']
                    ];
                    $data=[
                        'id'=>$b['id']
                    ];
                    $wpdb->update(TABLE_OFFICEGEST_ARTICLES,$update_data,$data);
                }
            }
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: getBrands] Error: ".$e->getMessage());
        }
    }

    /**
     * Create product category in WP
     *
     * @param $data
     * @param false $parent
     * @return array|int[]|WP_Error
     */
    public static function createProductCategory($data,$parent=false){
        try{
            $res=[];
            if ($parent==true){
                $res=wp_insert_term(
                    $data['description'], // the term
                    'product_cat', // the taxonomy
                    array(
                        'description'=> $data['description'],
                        'slug' => Tools::slugify($data['description']),
                        'parent'=> $data['parent']
                    )
                );
            }
            else{
                $res=wp_insert_term(
                    $data['description'], // the term
                    'product_cat', // the taxonomy
                    array(
                        'description'=> $data['description'],
                        'slug' => Tools::slugify($data['description'])
                    )
                );

            }
            return $res;
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: createProductCategory] Error: ".$e->getMessage());
            return null;
        }
    }

    /**
     * Validate if parts have all required data for import;
     * Fields for verification: process_id, category_id, component_id;
     *
     * @param $v
     * @return bool
     */
    public static function validatePartsCompleteData($v){
        try{
            $verify = [
                'process_id',
                'category_id',
                'component_id'
            ];
            foreach ($verify as $ver){
                if(empty($v[$ver])){
                    return true;
                }
            }
            return false;
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: validatePartsCompleteData] Error: ".$e->getMessage());
            return true;
        }
    }

    /**
     * Helper to populate ecoauto parts array;
     *
     * @param $v
     * @param false $update
     * @return array|bool
     */
    public static function createEcoAutoPartsArray($v, $update = false){
        try{
            $timestamp = date("Y-m-d H:i:s");

            $data=[
                'id'=>$v['id'],
                'process_id'=>$v['process_id'],
                'cod_process'=>$v['cod_process'],
                'plate'=>$v['plate'],
                'category_id'=>$v['category_id'],
                'category'=>$v['category'],
                'component_id'=>$v['component_id'],
                'component'=>$v['component'],
                'article_id'=>$v['article_id'],
                'barcode'=>$v['barcode'],
                'brand_id'=>$v['brand_id'],
                'model_id'=>$v['model_id'],
                'version_id'=>$v['version_id'],
                'fuel_id'=>$v['fuel_id'],
                'year'=>$v['year'],
                'engine_num'=>$v['engine_num'],
                'location_id'=>$v['location_id'],
                'document_type'=>$v['document_type'],
                'document_num'=>$v['document_num'],
                'obs'=>$v['obs'],
                'cost_price'=>$v['cost_price'],
                'selling_price'=>$v['selling_price'],
                'checked'=>$v['checked'],
                'color'=>$v['color'],
                'type'=>$v['type'],
                'deadline'=>$v['deadline'],
                'weight'=>$v['weight'],
                'height'=>$v['height'],
                'width'=>$v['width'],
                'depth'=>$v['depth'],
                'codoem'=>$v['codoem'],
                'date_alter'=>$v['date_alter'],
                'vin'=>$v['vin'],
                'article'=>$v['article'],
                'brand'=>$v['brand'],
                'model'=>$v['model'],
                'version'=>$v['version'],
                'version_date_start'=>$v['version_date_start'],
                'version_date_end'=>$v['version_date_end'],
                'version_year_start'=>$v['version_year_start'],
                'version_year_end'=>$v['version_year_end'],
                'version_vin'=>$v['version_vin'],
                'fuel'=>$v['fuel'],
                'type_desc'=>$v['type_desc'],
                'status_desc'=>$v['status_desc'],
                'location_desc'=>$v['location_desc'],
                'location_barcode'=>$v['location_barcode'],
                'valuevat'=>$v['valuevat'],
                'selling_price_withvat'=>$v['selling_price_withvat'],
                'attach_num'=>$v['attach_num'],
                'photos'=>$v['photos'],
                'photo'=>$v['photo'],
            ];

            if($update == true){
                $data['updated_at'] = $timestamp;
            } else {
                $data['created_at'] = $timestamp;
                $data['updated_at'] = $timestamp;
            }

            return $data;
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: createEcoAutoPartsArray] Error: ".$e->getMessage());
            return true;
        }
    }

    /**
     * Get single ecoauto part
     *
     * @param $id
     * @return array|object|void
     */
    private static function getSingleEcoAutoPart( $id ) {
        global $wpdb;

        try{
            $query = "SELECT * from ".TABLE_OFFICEGEST_ECO_PARTS." where id='".$id."'";
            $single =  $wpdb->get_row($query,ARRAY_A);
            if (!empty($single)){
                return $single;
            }
            return [];
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: getSingleEcoAutoPart] Error: ".$e->getMessage());
            return [];
        }
    }

    /**
     * Get all parts from OfficeGest api and create it in WP OfficeGest tables
     *
     * @param int $offset
     * @param int $limit
     * @param array $parts_id
     */
    public static function getAllEcoautoParts($offset=0,$limit=1000,&$parts_id=[]) {
        global $wpdb;
        try{
            $timestamp = date("Y-m-d H:i:s");
            $justWithImage = OfficeGestDBModel::getOption('ecoauto_imagens');
            $parts = OfficeGestCurl::ecoautoPartsInventory($offset, $limit);
            if ($parts['total']>0){
                $pecas = $parts['parts'];
                foreach ($pecas as $k=>$v){
                    if (empty(self::getSingleEcoAutoPart($v['id']))){
                        $incomplete_data = self::validatePartsCompleteData($v);

                        if($incomplete_data){
                            Log::write('Incomplete data for part - id: '. $v['id'] .', barcode: '. $v['barcode'] . ', plate: ' . $v['plate']);
                        } else {
                            if($justWithImage == 1 && $v['attach_num'] == null){
                                unset($pecas[$k]);
                            }else{
                                $insert_data = self::createEcoAutoPartsArray($v, false);
                                $wpdb->insert(TABLE_OFFICEGEST_ECO_PARTS,$insert_data);
                                $parts_id[]  = $v['id'];
                            }
                        }
                    }
                    else{
                        if($justWithImage == 1 && $v['attach_num'] == null){
                            $wpdb->delete(TABLE_OFFICEGEST_ECO_PARTS,['id'=>$v['id']]);
                            unset($pecas[$k]);
                        }else{
                            $update_data = self::createEcoAutoPartsArray($v, true);
                            $data        =[
                                'id'=>$v['id']
                            ];
                            $parts_id[]  = $v['id'];
                            $wpdb->update(TABLE_OFFICEGEST_ECO_PARTS,$update_data,$data);
                        }
                    }

                }
                if ( $parts['total'] == $limit ) {
                    $offset = $limit + $offset;
                    self::getAllEcoautoParts( $offset, $limit,$parts_id );
                }
            }
            if (count($parts_id)>0){
                $wpdb->query('DELETE FROM '.TABLE_OFFICEGEST_ECOAUTO_INVENTORY.' WHERE part_id IS NOT NULL');
                foreach ($parts_id as $k){
                    $insert_data = [
                        'part_id'=>$k,
                        'process_id' => null
                    ];
                    $wpdb->insert(TABLE_OFFICEGEST_ECOAUTO_INVENTORY,$insert_data);
                }
            }
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: getAllEcoautoParts] Error: ".$e->getMessage());
        }
    }

    /**
     * Get ecoauto process from id
     *
     * @param $id
     * @return array|object|void
     */
    private static function getSingleProcess($id) {
        global $wpdb;
        try{
            $query = "SELECT * from ".TABLE_OFFICEGEST_ECO_PROCESSES." where id=".$id;
            $single =  $wpdb->get_row($query,ARRAY_A);
            if (!empty($single)){
                return $single;
            }
            return [];
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: getSingleProcess] Error: ".$e->getMessage());
            return [];
        }
    }

    /**
     * Validate if processes have all required data for import
     * Fields for verification: brand, model
     *
     * @param $v
     * @return bool
     */
    public static function validateProcessesCompleteData($v){
        try{
            $verify = [
                'brand',
                'model'
            ];
            foreach ($verify as $ver){
                if(empty($v[$ver])){
                    return true;
                }
            }
            return false;
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: validateProcessesCompleteData] Error: ".$e->getMessage());
            return true;
        }
    }

    /**
     * Helper to populate ecoauto processes array;
     *
     * @param $p
     * @param false $update
     * @return array|bool
     */
    public static function createEcoAutoProcessesArray($p, $update = false){
        try{
            $timestamp = date("Y-m-d H:i:s");

            $data=[
                'id'=>$p['id'],
                'id_process'=>$p['id_process'],
                'barcode'=>$p['barcode'],
                'plate'=>$p['plate'],
                'brand'=>$p['brand'],
                'brand_id'=>$p['brand_id'],
                'location_id'=>$p['location_id'],
                'model'=>$p['model'],
                'model_id'=>$p['model_id'],
                'version'=>$p['version'],
                'version_id'=>$p['version_id'],
                'fuel_id'=>$p['fuel_id'],
                'fuel'=>$p['fuel'],
                'date_alter'=>$p['date_alter'],
                'obs'=>empty($p['obs'])?'':$p['obs'],
                'state'=>$p['state'],
                'attach_num'=>$p['attach_num'],
                'photos'=>$p['photos'],
                'photo'=>$p['photo'],
            ];

            if($update == true){
                $data['updated_at'] = $timestamp;
            } else {
                $data['created_at'] = $timestamp;
                $data['updated_at'] = $timestamp;
            }

            return $data;
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: createEcoAutoProcessesArray] Error: ".$e->getMessage());
            return true;
        }
    }

    /**
     * Get all processes from OfficeGest api and create it in WP OfficeGest tables
     *
     * @param int $offset
     * @param int $limit
     */
    public static function getAllProcesses($offset=0,$limit=1000)
    {
        global $wpdb;

        try{
            $timestamp = date("Y-m-d H:i:s");
            $processos = OfficeGestCurl::getEcoAutoProcesses($offset, $limit);
            if ($processos['total']>0){
                $processes = $processos['processes'];
                foreach ($processes as $k=>$v){
                    if (empty(self::getSingleProcess($v['id']))){
                        $incomplete_data = self::validateProcessesCompleteData($v);

                        if($incomplete_data){
                            Log::write('Incomplete data for process - id: '. $v['id_process'] .', barcode: '. $v['barcode'] . ', plate: ' . $v['plate']);
                        } else {
                            $insert_data = self::createEcoAutoProcessesArray($v, false);
                            $wpdb->insert(TABLE_OFFICEGEST_ECO_PROCESSES,$insert_data);
                            $parts_id[]  = $v['id'];
                        }
                    }
                    else{
                        $update_data = self::createEcoAutoProcessesArray($v, true);
                        $data=[
                            'id'=>$v['id']
                        ];
                        $parts_id[]  = $v['id'];
                        $wpdb->update(TABLE_OFFICEGEST_ECO_PROCESSES,$update_data,$data);
                    }
                }
                if ( $processos['total'] == $limit ) {
                    $offset = $limit + $offset;
                    self::getAllProcesses( $offset, $limit);
                }
            }

            if (count($parts_id)>0){
                $wpdb->query('DELETE FROM '.TABLE_OFFICEGEST_ECOAUTO_INVENTORY.' WHERE process_id IS NOT NULL');
                foreach ($parts_id as $k){
                    $insert_data = [
                        'part_id'=> null,
                        'process_id' => $k
                    ];
                    $wpdb->insert(TABLE_OFFICEGEST_ECOAUTO_INVENTORY,$insert_data);
                }
            }
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: getAllProcesses] Error: ".$e->getMessage());
        }
    }

    /**
     * Update Ecoauto process woo ID
     *
     * @param $woo_id
     * @param $item
     */
    public static function setWooIdEcoAutoProcesses($woo_id,$item)
    {
        global $wpdb;
        $data=[
            'woo_id'=>$woo_id
        ];
        $wpdb->update(TABLE_OFFICEGEST_ECO_PROCESSES,$data,['id'=>$item]);
    }

    /**
     * Get processes available states
     *
     * @param null $estado
     * @return string|string[]
     */
    public static function getProcessesStates($selectedState=null)
    {
        $states = [
            'ABR' => 'Aberto',
            'DISM' => 'Desmantelado',
            'PDISM' => 'Em Parque',
            'PREN' => 'Prensado',
            'F' => 'Fechado'
        ];

        if ($selectedState<>null){
            return $states[$selectedState];
        }
        return $states;
    }

    /**
     * Helper to create processes description
     *
     * @param $data
     * @return string
     */
    public static function createProcessDescription($data)
    {
        return $data['brand'].' '.$data['model'];
    }

    /**
     * Generate featured image
     *
     * @param $image_url
     * @param $post_id
     * @param $contador
     */
    public static function generateFeaturedImage( $image_url, $post_id,$contador )
    {
        $filename = 'peca_'.$post_id.'_'.$contador.'.jpg';
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($image_url);
        $file = wp_mkdir_p( $upload_dir['path'] ) ? $upload_dir['path'] . '/' . $filename : $upload_dir['basedir'] . '/' . $filename;
        file_put_contents($file, $image_data);
        $wp_filetype = wp_check_filetype(basename($filename), null );
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => $filename,
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        wp_update_attachment_metadata( $attach_id,  $attach_data );
        set_post_thumbnail($post_id,$attach_id);
    }

    /**
     * Create article name
     *
     * @param $data
     * @param $brandName
     * @return string|string[]|null
     */
    public static function createArticleName($data,$brandName)
    {
        $part = self::getOption('article_name');
        if ( $part == '' ){
            $final =  $data['description'] .' '. $data['id'].' '.
                $brandName;
        }
        else{
            $final = str_replace( array(
                '{descricao}',
                '{codigo_artigo}',
                '{marca}'
            ), array(
                trim($data['description']),
                trim($data['id']),
                $brandName
            ), $part );
        }
        return  preg_replace('/\s+/', ' ',$final);
    }

    /**
     * Create article description
     *
     * @param $data
     * @param $brandName
     * @return string|string[]|null
     */
    public static function createArticleDescription($data,$brandName)
    {
        $part = self::getOption('article_description');
        if ( $part == '' ){
            $final =  $data['description'] .' '. $data['id'].' '.
                $brandName;
        }
        else{
            $final = str_replace( array(
                '{descricao}',
                '{codigo_artigo}',
                '{marca}'
            ), array(
                trim($data['description']),
                trim($data['id']),
                $brandName
            ), $part );
        }
        return  preg_replace('/\s+/', ' ',$final);
    }

    /**
     * Create ecoauto part description
     *
     * @param $data
     * @return string|string[]|null
     */
    public static function createEcoAutoPartDescription($data)
    {
        $part = self::getOption('ecoauto_nome_peca');
        if ( $part == '' ){
            $final =  $data['component'] .' '. $data['brand'].' '.
                trim($data['model']) .' '.
                trim($data['version']) .' '.
                trim($data['fuel']) .' '.
                trim($data['color']) .' '.
                trim($data['year']) .' '.
                trim($data['barcode']) .' '.
                trim($data['codoem']);
        }
        else{
            $final = str_replace( array(
                '{componente}',
                '{marca}',
                '{modelo}',
                '{versao}',
                '{combustivel}',
                '{cor}',
                '{ano_fabrico}',
                '{codigo_barras}',
                '{oem}'
            ), array(
                trim($data['component']),
                trim($data['brand']),
                trim($data['model']),
                trim($data['version']),
                trim($data['fuel']),
                trim($data['color']),
                trim($data['year']),
                trim($data['barcode']),
                trim($data['codoem'])
            ), $part );
        }
        return  preg_replace('/\s+/', ' ',$final);
    }

    /**
     * Create ecoauto part observation
     *
     * @param $data
     * @return string|string[]|null
     */
    public static function createEcoAutoPartObs( $data ) {
        $obs_peca = self::getOption('ecoauto_obs');
        $obs_peca = empty( $obs_peca ) ? $data['component'] .' '. $data['brand']  .' '.
            $data['model']  .' '.
            $data['version']  .' '.
            $data['fuel']  .' '.
            $data['color']  .' '.
            $data['year'] .' '.$data['barcode'].' '.$data['codoem'] : str_replace( array(
            '{componente}',
            '{marca}',
            '{modelo}',
            '{versao}',
            '{combustivel}',
            '{cor}',
            '{ano_fabrico}',
            '{codigo_barras}',
            '{oem}'
        ), array(
            $data['component'],
            $data['brand'],
            $data['model'],
            $data['version'],
            $data['fuel'],
            $data['color'],
            $data['year'],
            $data['barcode'],
            $data['codoem']
        ), $obs_peca );
        return  preg_replace('/\s+/', ' ',$obs_peca);
    }

    /**
     * Set woo id for ecoauto part
     *
     * @param $woo_id
     * @param $product
     */
    public static function setWooIdEcoAutoParts($woo_id,$product) {
        global $wpdb;
        $data=[
            'woo_id'=>$woo_id
        ];
        $wpdb->update(TABLE_OFFICEGEST_ECO_PARTS,$data,['id'=>$product]);
    }

    /**
     * Add parts categories;
     * Create new category if nonexistent;
     *
     * @param $parent_term_id
     * @return void
     */
    public static function syncEcoAutoPartsCategories($parent_term_id){
        try{
            global $wpdb;

            /** Brands */
            $sql_brand = "SELECT DISTINCT brand from ".TABLE_OFFICEGEST_ECO_PARTS;
            $brands = $wpdb->get_results($sql_brand,ARRAY_A);
            foreach($brands as $key => $brand) {
                $brand_term = term_exists( htmlspecialchars($brand['brand']), 'product_cat', $parent_term_id);
                if (!isset($brand_term['term_id'])) {
                    OfficeGestDBModel::createProductCategory( [
                        'description' => $brand['brand'],
                        'parent' => $parent_term_id
                    ], true );
                    $brand_term = term_exists( htmlspecialchars($brand['brand']), 'product_cat', $parent_term_id );
                }
                if (isset($brand_term['term_id'])) {
                    /** Models */
                    $sql_model = 'SELECT DISTINCT model from '.TABLE_OFFICEGEST_ECO_PARTS.' where brand = "'.$brand['brand'].'"';
                    $models = $wpdb->get_results($sql_model,ARRAY_A);

                    if(!empty($models)){
                        foreach($models as $k => $model) {
                            $model_term = term_exists( htmlspecialchars($model['model']), 'product_cat', $brand_term['term_id']);
                            if (!isset($model_term['term_id'])) {
                                OfficeGestDBModel::createProductCategory([
                                    'description' => $model['model'],
                                    'parent' => $brand_term['term_id']
                                ], true);
                                $model_term = term_exists( htmlspecialchars($model['model']), 'product_cat', $brand_term['term_id'] );
                            }

                            if (isset($model_term['term_id'])) {
                                /** Categories */
                                $sql_categories = 'SELECT DISTINCT category from '.TABLE_OFFICEGEST_ECO_PARTS.' where model = "'.$model['model'].'" AND brand = "'.$brand['brand'].'"';
                                $categories = $wpdb->get_results($sql_categories,ARRAY_A);

                                foreach($categories as $a => $category) {
                                    $category_term = term_exists( htmlspecialchars($category['category']), 'product_cat', $model_term['term_id']);
                                    if (!isset($category_term['term_id'])) {
                                        OfficeGestDBModel::createProductCategory([
                                            'description' => $category['category'],
                                            'parent' => $model_term['term_id']
                                        ], true);
                                        $category_term = term_exists( htmlspecialchars($category['category']), 'product_cat', $model_term['term_id'] );
                                    }

                                    if (isset($category_term['term_id'])) {
                                        /** Components */
                                        $sql_components = 'SELECT DISTINCT component from '.TABLE_OFFICEGEST_ECO_PARTS.' where model = "'.$model['model'].'" AND brand = "'.$brand['brand'].'" AND category = "'.$category['category'].'"';
                                        $components = $wpdb->get_results($sql_components,ARRAY_A);

                                        foreach($components as $b => $component) {
                                            $component_term = term_exists( htmlspecialchars($component['component']), 'product_cat', $category_term['term_id']);
                                            if (!isset($component_term['term_id'])) {
                                                OfficeGestDBModel::createProductCategory([
                                                    'description' => $component['component'],
                                                    'parent' => $category_term['term_id']
                                                ], true);
                                                $component_term = term_exists( htmlspecialchars($component['component']), 'product_cat', $category_term['term_id'] );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: syncEcoAutoPartsCategories] Error: ".$e->getMessage());
        }
    }

    /**
     * Query to get brand value;
     *
     * @param $value
     * @return mixed|null
     */
    public static function getBrandName($value){
        try{
            global $wpdb;

            $query = 'SELECT description from '.TABLE_OFFICEGEST_BRANDS.' WHERE id="'.$value.'"';
            $brand = $wpdb->get_results($query,ARRAY_A);

            return $brand[0]['description'] ? $brand[0]['description'] : null;
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: getBrandName] Error: ".$e->getMessage());
            return null;
        }
    }

    /**
     * Query to get categories value;
     *
     * @param $value
     * @return array|object|null
     */
    public static function getCategoryName($value){
        try{
            global $wpdb;

            $query = 'SELECT description from '.TABLE_OFFICEGEST_CATEGORIES.' WHERE id="'.$value.'"';
            $category = $wpdb->get_results($query,ARRAY_A);

            if(!empty($category)){
                if(!empty($category[0])){
                    if(!empty($category[0]['description'])){
                        return $category[0]['description'];
                    }
                }
            }
            return null;
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: getCategory] Error: ".$e->getMessage());
            return null;
        }
    }

    /**
     * Set woo id in articles;
     *
     * @param $term_id
     * @param $categoryId
     */
    public static function setTermIdCategories($term_id,$categoryId) {
        try{
            global $wpdb;
            $data=[
                'term_id'=>$term_id
            ];
            $wpdb->update(TABLE_OFFICEGEST_CATEGORIES,$data,['id'=>$categoryId]);
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: setTermIdCategories] Error: ".$e->getMessage());
        }
    }

    /**
     * Set woo id in articles;
     *
     * @param $woo_id
     * @param $articleId
     */
    public static function setWooIDArticles($woo_id,$articleId) {
        try{
            global $wpdb;
            $data=[
                'woo_id'=>$woo_id
            ];
            $wpdb->update(TABLE_OFFICEGEST_ARTICLES,$data,['id'=>$articleId]);
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: setWooIDArticles] Error: ".$e->getMessage());
        }
    }

    /**
     * Create Articles in WooCommerce based on cron job array;
     *
     * @param $items
     */
    public static function generateArticlesToWoo($items) {
        global $wpdb;
        try{
            foreach ($items as $a => $item){
                $partsQuery = "SELECT * FROM ".TABLE_OFFICEGEST_ARTICLES." WHERE id = '".$item."'";
                $itemToRun = $wpdb->get_results($partsQuery,ARRAY_A);

                if(!empty($itemToRun[0])){
                    $id = OfficegestProduct::createArticle($itemToRun[0]);

                    if ($id!=0){
                        OfficeGestDBModel::setWooIDArticles($id,$itemToRun[0]['id']);
                    }
                }
            }
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: generateArticlesToWoo] Error: ".$e->getMessage());
        }
    }

    /**
     * Create Ecoauto Parts in WooCommerce based on cron job array;
     *
     * @param $items
     */
    public static function generateEcoAutoPartsToWoo($items) {
        global $wpdb;
        try{
            foreach ($items as $a => $item){
                $partsQuery = "SELECT * FROM ".TABLE_OFFICEGEST_ECO_PARTS." WHERE id = '".$item."'";
                $itemToRun = $wpdb->get_results($partsQuery,ARRAY_A);

                if(!empty($itemToRun[0])){
                    $id = OfficegestProduct::createEcoAutoPart($itemToRun[0]);

                    if ($id!=0){
                        OfficeGestDBModel::setWooIdEcoAutoParts($id,$itemToRun[0]['id']);
                    }
                }
            }
            OfficeGestDBModel::clearEcoAutoParts();
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: generateEcoAutoPartsToWoo] Error: ".$e->getMessage());
        }
    }

    /**
     * Create Ecoauto Processes in WooCommerce based on cron job array;
     *
     * @param $items
     */
    public static function generateEcoAutoProcessesToWoo($items) {
        global $wpdb;
        try{
            foreach ($items as $a => $item){
                $processQuery = "SELECT * FROM ".TABLE_OFFICEGEST_ECO_PROCESSES." WHERE id = '".$item."'";
                $itemToRun = $wpdb->get_results($processQuery,ARRAY_A);

                if(!empty($itemToRun[0])){
                    $id = OfficegestProduct::createEcoautoProcess($itemToRun[0]);

                    if ($id!=0){
                        OfficeGestDBModel::setWooIdEcoAutoProcesses($id,$itemToRun[0]['id']);
                        if ($itemToRun[0]['photos']>0){
                            OfficeGestDBModel::generateFeaturedImage($itemToRun[0]['photo'],$id,0);
                        }
                    }
                }
            }
            OfficeGestDBModel::clearEcoAutoProcesses();
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: generateEcoAutoProcessesToWoo] Error: ".$e->getMessage());
        }
    }

    /**
     * Get most recent processes in queue for cron job
     * Limited to 50
     *
     * @return array
     */
    public static function getQueue()
    {
        global $wpdb;
        try{

            $query = "SELECT * FROM ".TABLE_OFFICEGEST_CRON_JOBS." WHERE running = 0 LIMIT 50";
            $items = $wpdb->get_results($query,ARRAY_A);
            $jobOnHold = [];
            foreach ($items as $item){
                $jobOnHold [] = [
                    'type'              => $item['cron_type'] == 'parts' ? 'Peças' : ($item['cron_type'] == 'articles' ? 'Artigos' : ($item['cron_type'] == 'processes' ? 'Viaturas' : ($item['cron_type'] == 'parts_images' ? 'Imagens de Peças' : ($item['cron_type'] == 'parts_images' ? 'Imagens de Artigos' : '')))),
                    'created_at'        => $item['created_at'],
                    'process_values'    => $item['process_values'],
                ];
            }

            return $jobOnHold;
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: getQueue] Error: ".$e->getMessage());
            return [];
        }
    }

    /**
     * Get post meta by post id
     *
     * @param $postId
     * @return array
     */
    public static function getPostMeta($postId)
    {
        $metas = [];
        $metaKeys = get_post_meta($postId);
        if (!empty($metaKeys) && is_array($metaKeys)) {
            foreach ($metaKeys as $key => $meta) {
                $metas[$key] = $meta[0];
            }
        }
        return $metas;
    }

    /**
     * Clear ecoauto parts inventory in woocommerce
     * @param int $offset
     * @param int $limit
     */
    public static function clearEcoAutoParts($offset = 0, $limit = 1000)
    {
        global $wpdb;
        try{
            $args = [
                'post_type' => 'product',
                'post_status' =>'publish',
                'orderby' => 'date',
                'order' => 'DESC',
                'meta_query' => [
                    'relation' => 'OR',
                    [
                        'key' => '_ecoauto_part_id',
                        'compare' => 'EXISTS'
                    ]
                ],
                'posts_per_page' => $limit,
                'offset' => $offset,
            ];
            $products = new WP_Query($args);
            foreach ($products->posts as $product) {
                $meta = self::getPostMeta($product->ID);
                $result = $wpdb->get_results('SELECT * from '.TABLE_OFFICEGEST_ECOAUTO_INVENTORY.' WHERE part_id='.$meta['_ecoauto_part_id']);
                if (empty($result)){
                    $p = new WC_Product_Simple($product->ID);
                    wp_update_post( array( 'ID' => $p->get_id(), 'post_status' => 'trash' ) );
                    $part = $wpdb->get_row('SELECT * from '.TABLE_OFFICEGEST_ECO_PARTS.' WHERE woo_id='.$product->ID);
                    $wpdb->delete(TABLE_OFFICEGEST_ECO_PARTS,['woo_id'=>$product->ID]);
                    $wpdb->delete(TABLE_OFFICEGEST_ECO_PHOTOS,['component_id'=>$part->id]);
                }
            }
            if ( $products->post_count == $limit ) {
                $offset = $limit + $offset;
                self::clearEcoAutoParts( $offset, $limit);
            }
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: clearEcoAutoParts] Error: ".$e->getMessage());
        }
    }

    /**
     * Clear ecoauto processes inventory in woocommerce
     * @param int $offset
     * @param int $limit
     */
    public static function clearEcoAutoProcesses($offset = 0, $limit = 1000)
    {
        global $wpdb;
        try{
            $args = [
                'post_type' => 'product',
                'post_status' =>'publish',
                'orderby' => 'date',
                'order' => 'DESC',
                'meta_query' => [
                    'relation' => 'OR',
                    [
                        'key' => '_ecoauto_proccess_id',
                        'compare' => 'EXISTS'
                    ]
                ],
                'posts_per_page' => $limit,
                'offset' => $offset,
            ];
            $products = new WP_Query($args);
            foreach ($products->posts as $product) {
                $meta = self::getPostMeta($product->ID);
                $result = $wpdb->get_results('SELECT * from '.TABLE_OFFICEGEST_ECOAUTO_INVENTORY.' WHERE process_id='.$meta['_ecoauto_proccess_id']);
                if (empty($result)){
                    $p = new WC_Product_Simple($product->ID);
                    wp_update_post( array( 'ID' => $p->get_id(), 'post_status' => 'trash' ) );
                    $wpdb->delete(TABLE_OFFICEGEST_ECO_PROCESSES,['woo_id'=>$product->ID]);
                }
            }
            if ( $products->post_count == $limit ) {
                $offset = $limit + $offset;
                self::clearEcoAutoProcesses( $offset, $limit);
            }
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: clearEcoAutoProcesses] Error: ".$e->getMessage());
        }
    }

    /**
     * Create ecoauto parts images in officegest tables
     *
     * @param int $offset
     * @param int $limit
     */
    public static function getAllEcoautoPartsPhotosDB($offset=0,$limit=1000) {
        global $wpdb;
        try{
            $response = OfficeGestCurl::getAllEcoautoPartsPhotos( $offset,$limit );
            if ( $response['total'] > 0 ) {
                $photos = $response['photos'];
                foreach ( $photos as $k => $v ) {
                    $insert_data = [
                        'component_id'=>$v['component_id'],
                        'attach_num'=>$v['attach_num'],
                        'main'=>$v['main'],
                        'photo'=>$v['photo']
                    ];
                    $id = self::getSingleEcoautoPartPhotoID($insert_data['component_id'],$insert_data['attach_num']);
                    if (empty($id)){
                        $wpdb->insert(TABLE_OFFICEGEST_ECO_PHOTOS,$insert_data);
                    }
                    else{
                        $wpdb->update(TABLE_OFFICEGEST_ECO_PHOTOS,$insert_data,$id);
                    }
                }
                if ( $response['total'] == $limit ) {
                    $offset = $limit + $offset;
                    self::getAllEcoautoPartsPhotosDB( $offset, $limit );
                }
            }
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: getAllEcoautoPartsPhotosDB] Error: ".$e->getMessage());
        }
    }

    /**
     * Create ecoauto parts images in officegest tables
     *
     * @param $article
     */
    public static function getAllOfficeGestArticlesPhotosDB($article) {
        global $wpdb;
        try{
            $response = OfficeGestCurl::getAllOfficeGestArticlePhotos($article);

            if ($response) {
                $insert_data = [
                    'component_id'=>$article['id'],
                    'attach_num'=>$article['id'],
                    'main'=>1,
                    'photo'=>$response
                ];
                $id = self::getSingleOfficeGestArticlePhotoID($insert_data['component_id'],$insert_data['attach_num']);
                if (empty($id)){
                    $wpdb->insert(TABLE_OFFICEGEST_ARTICLE_PHOTOS,$insert_data);
                }
                else{
                    $wpdb->update(TABLE_OFFICEGEST_ARTICLE_PHOTOS,$insert_data,$id);
                }
            }
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: getAllOfficeGestArticlesPhotosDB] Error: ".$e->getMessage());
        }
    }

    /**
     * Get ecoauto part photo from id
     *
     * @param $component_id
     * @param $attach_num
     * @return array
     */
    public static function getSingleEcoautoPartPhotoID($component_id,$attach_num){
        global $wpdb;
        try{
            $query = 'SELECT * from ' . TABLE_OFFICEGEST_ECO_PHOTOS. ' where component_id='.$component_id.' and attach_num='.$attach_num;
            $row = $wpdb->get_row($query,ARRAY_A);
            if (empty($row)){
                return [];
            }
            return [
                'component_id'=>$row['component_id'],
                'attach_num'=>$row['attach_num'],
            ];
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: getSingleEcoautoPartPhotoID] Error: ".$e->getMessage());
            return [];
        }
    }

    /**
     * Get officegest article photo from id
     *
     * @param $component_id
     * @param $attach_num
     * @return array
     */
    public static function getSingleOfficeGestArticlePhotoID($component_id,$attach_num){
        global $wpdb;
        try{
            $query = 'SELECT * FROM ' . TABLE_OFFICEGEST_ARTICLE_PHOTOS. ' WHERE component_id="'.$component_id.'" AND attach_num="'.$attach_num.'"';
            $row = $wpdb->get_row($query,ARRAY_A);
            if (empty($row)){
                return [];
            }
            return [
                'component_id'=>$row['component_id'],
                'attach_num'=>$row['attach_num'],
            ];
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: getSingleOfficeGestArticlePhotoID] Error: ".$e->getMessage());
            return [];
        }
    }

    /**
     * Set woo id to ecoauto part attach's
     *
     * @param $product
     */
    public static function setWooIDEcoAutoAttachs($product) {
        global $wpdb;
        $data=[
            'photos_imported'=>1
        ];
        $wpdb->update(TABLE_OFFICEGEST_ECO_PARTS,$data,['id'=>$product]);
    }

    /**
     * Set woo id to officegest article attach's
     *
     * @param $product
     */
    public static function setWooIDArticleAttachs($product) {
        global $wpdb;
        $data=[
            'photos_imported'=>1
        ];
        $wpdb->update(TABLE_OFFICEGEST_ARTICLES,$data,['id'=>$product]);
    }

    /**
     * Get states for officegest ecoauto processes
     *
     * @param $state
     * @return string
     */
    public static function getProcessesComponentState($state){
        $states = [
            0 => 'KO',
            1 => 'OK',
            2 => 'OK?',
            3 => 'N/A'
        ];
        return $states[$state];
    }

    /**
     * Get officegest ecoauto processes gearbox type
     *
     * @param $state
     * @return string
     */
    public static function getProcessesGearboxType($state){
        $states = [
            'M' => 'Manual',
            '1' => 'Automatic',
            '2' => 'Semi-automatic'
        ];
        return $states[$state];
    }

    /**
     * Create ecoauto parts images in WooCommerce based on cron job array;
     *
     * @param $items
     */
    public static function generateEcoautoPartsImages($items) {
        global $wpdb;
        try{
            foreach ($items as $a => $item){
                $partsQuery = "SELECT * FROM ".TABLE_OFFICEGEST_ECO_PARTS." WHERE id = '".$item."'";
                $itemToRun = $wpdb->get_results($partsQuery,ARRAY_A);

                if(!empty($itemToRun[0])){
                    $id = OfficegestProduct::updateEcoautoPartImage($itemToRun[0]);
                }
            }
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: generateEcoautoPartsImages] Error: ".$e->getMessage());
        }
    }

    /**
     * Create officegest article images in WooCommerce based on cron job array;
     *
     * @param $items
     */
    public static function generateOfficeGestArticlesImages($items) {
        global $wpdb;
        try{
            foreach ($items as $a => $item){
                $partsQuery = "SELECT * FROM ".TABLE_OFFICEGEST_ARTICLES." WHERE id = '".$item."'";
                $itemToRun = $wpdb->get_results($partsQuery,ARRAY_A);
                if(!empty($itemToRun[0])){
                    $id = OfficegestProduct::updateOfficeGestArticleImage($itemToRun[0]);
                }
            }
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: generateOfficeGestArticlesImages] Error: ".$e->getMessage());
        }
    }

    /**
     * Get photo
     *
     * @param $name
     * @return int|mixed
     */
    public static function singlePhoto($name){
        global $wpdb;
        $query = "SELECT woo_attach_id from ".TABLE_OFFICEGEST_ECO_PHOTOS." where woo_attach_id>0 and photo='".$name."'";
        $single =  $wpdb->get_row($query,ARRAY_A);
        if (!empty($single)){
            return $single['woo_attach_id'];
        }
        return 0;
    }

    /**
     * Get photo
     *
     * @param $name
     * @return int|mixed
     */
    public static function singleArticlePhoto($name){
        global $wpdb;
        $query = "SELECT woo_attach_id FROM ".TABLE_OFFICEGEST_ARTICLE_PHOTOS." WHERE woo_attach_id>0 AND photo='".$name."'";
        $single =  $wpdb->get_row($query,ARRAY_A);
        if (!empty($single)){
            return $single['woo_attach_id'];
        }
        return 0;
    }

    /**
     * Find photo
     *
     * @param $name
     * @param $thumbid
     * @return bool|int
     */
    public static function photoFind($name,$thumbid){
        global $wpdb;
        $where = [
            'photo'=>$name
        ];
        $data=[
            'woo_attach_id'=>$thumbid
        ];
        return $wpdb->update(TABLE_OFFICEGEST_ECO_PHOTOS,$data,$where);
    }

    /**
     * Find article photo
     *
     * @param $name
     * @param $thumbid
     * @return bool|int
     */
    public static function photoArticleFind($id,$thumbid){
        global $wpdb;
        $where = [
            'component_id'=>$id
        ];
        $data=[
            'woo_attach_id'=>$thumbid
        ];
        return $wpdb->update(TABLE_OFFICEGEST_ARTICLE_PHOTOS,$data,$where);
    }
}
