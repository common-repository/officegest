<?php

namespace OfficeGest;
use Curl\Curl;
use WC_Product;
use WC_Product_Simple;
use WP_REST_Request;
use function MongoDB\BSON\toJSON;

class OfficegestProduct {

	public static function check_attribute($name){
		global $wpdb;
		$query = "SELECT attribute_id FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = "."'".($name)."'";
		$attribute = $wpdb->get_row($query,ARRAY_A);
		if (!empty($attribute)){
			return $attribute['attribute_id'];
		}
		return -1;
	}

	public static function checkTerm($taxonomy,$name){
		global $wpdb;
		$query = "SELECT t.term_id FROM {$wpdb->prefix}terms t where t.term_id in (select term_taxonomy_id from  {$wpdb->prefix}term_taxonomy where taxonomy ="."'".($taxonomy)."') and t.name="."'".($name)."'";
		$term = $wpdb->get_row($query,ARRAY_A);
		if (!empty($term)){
			return $term['term_id'];
		}
		return -1;
	}

	public static function create_product_attributes($attributes){
		$atributos = array_keys($attributes);
		foreach ($atributos as $k=>$v){
			$id = self::check_attribute($v);
			if ($id==-1){
				$request = new WP_REST_Request( 'POST', '/wc/v3/products/attributes');
			}
			else{
				$request = new WP_REST_Request( 'PUT', '/wc/v3/products/attributes/'.$id);
			}
			$data=[
				'name'         => $v,
				'type'         => 'select',
				'slug'         =>'pa_'.strtolower($v),
				'order_by'     => 'menu_order',
				'has_archives' => false
			];
			$request->set_body_params($data);
			$response = rest_do_request( $request );
			$server = rest_get_server();
			$data = $server->response_to_data( $response, false );
			$terms = array_filter($attributes[$v]);
			self::create_attributes_terms($data['id'],'pa_'.strtolower($v),$terms);
		}
	}

	public static function create_attributes_terms($id,$att,$terms){
		foreach ($terms as $k=>$v){
			$term_id = self::checkTerm($att,$v);
			$request = $term_id === - 1 ? new WP_REST_Request( 'POST', '/wc/v3/products/attributes/' . $id . '/terms' ) : new WP_REST_Request( 'PUT', '/wc/v3/products/attributes/' . $id . '/terms/' . $term_id );
			$terms=[
				'name'=>$v
			];
			$request->set_body_params($terms);
			$response = rest_do_request( $request );
			$server = rest_get_server();
			$data = $server->response_to_data( $response, false);
		}
	}

	public static function create_parent_product_variation($data_parent) {
		$artigo = wc_get_product_id_by_sku($data_parent['sku']);
		if (empty($artigo)){
			$attributes = $data_parent['attributes'];
			$atributos = [];
			$att_id = 0;
			foreach ($attributes as $k=>$v){
				$atributos[]=[
					'name'=>$k,
					'options'=>array_values($v),
					'position'=>$att_id++,
					'visible'=>true,
					'variation'=>true
				];
			}
			if ( $data_parent['vatid'] === null ){
				$taxa =0;
			}
			else{
				$taxa  = OfficeGestDBModel::findTaxByValue($data_parent['vatid'])['value'];
				if ($taxa==false){
					$taxa =0;
				}
			}
			$price = $data_parent['regular_price']+( $data_parent['regular_price'] * ( $taxa / 100 ) );
			$metadata=[
				[
					'key'=>'offficegest_ean',
					'value'=>$data_parent['barcode']
				],
				[
					'key'=>OfficeGestDBModel::getOption('artigos_ean'),
					'value'=>$data_parent['barcode']
				],
				[
					'key'=>'officegest_brand',
					'value'=>OfficeGestDBModel::getDBBrandsDescription($data_parent['brand'])
				],
				[
					'key'=>OfficeGestDBModel::getOption('artigos_marca'),
					'value'=>OfficeGestDBModel::getDBBrandsDescription($data_parent['brand'])
				]
			];
			$post_data = [
				'name' => $data_parent['title'],
				'type' => 'variable',
				'sku'=>$data_parent['sku'],
				'description' => $data_parent['title'],
				'short_description' => $data_parent['title'],
				'attributes' => $atributos,
				'price'=>wc_price($price),
				'sale_price'=>wc_price($price),
				'regular_price'=>wc_price($price),
				'stock_quantity'=>(string)$data_parent['stock_quantity'],
				'meta_data'=>$metadata
			];
			$artigo = wc_get_product_id_by_sku($data_parent['sku']);
			$request   = empty( $artigo ) ? new WP_REST_Request( 'POST', '/wc/v3/products' ) : new WP_REST_Request( 'POST', '/wc/v3/products/' . $artigo );

			$request->set_body_params($post_data);
			$response = rest_do_request( $request );
			$server = rest_get_server();
			$data = $server->response_to_data( $response, false);
			Log::write(json_encode($data,true));
			$artigo = $data['id'];
			$data_parent['sellingprice'] = $data_parent['regular_price'];
			$data_parent['id'] = $data_parent['sku'];
			OfficeGestDBModel::setWooID($artigo,$data_parent['id']);
		}
		return $artigo;
	}

	public static function create_product_variation($parent,$data_kid){
		$pai = wc_get_product_id_by_sku($parent);
		if (!empty($pai)){
			$artigo_filho = wc_get_product_id_by_sku($data_kid['sku']);
			if (empty($artigo_filho)) {
				$request = new WP_REST_Request( 'POST', '/wc/v3/products/' . $pai . '/variations' );
			}
			else {
				$request = new WP_REST_Request( 'PUT', '/wc/v3/products/' . $pai . '/variations/'.$artigo_filho );
			}
			$attributes = $data_kid['attributes'];
			$atributos = [];
			$att_id=0;
			foreach ($attributes as $k=>$v){
				$atributos[]=[
					'name'=>$k,
					'option'=>$v[0]
				];
			}
			if (is_null($data_kid['vatid'])){
				$taxa =0;
			}
			else{
				$taxa  = OfficeGestDBModel::findTaxByValue($data_kid['vatid'])['value'];
				if ($taxa==false){
					$taxa =0;
				}
			}
			$price = $data_kid['regular_price']+( $data_kid['regular_price'] * ( $taxa / 100 ) );
			$metadata=[
				[
					'key'=>'offficegest_ean',
					'value'=>$data_kid['barcode']
				],
				[
					'key'=>OfficeGestDBModel::getOption('artigos_ean'),
					'value'=>$data_kid['barcode']
				],
				[
					'key'=>'officegest_brand',
					'value'=>OfficeGestDBModel::getDBBrandsDescription($data_kid['brand'])
				],
				[
					'key'=>OfficeGestDBModel::getOption('artigos_marca'),
					'value'=>OfficeGestDBModel::getDBBrandsDescription($data_kid['brand'])
				]
			];
			$post_filho_data = [
				'name' => $data_kid['title'],
				'sku'=>$data_kid['sku'],
				'price'=>wc_price($price),
				'regular_price'=>wc_price($price),
				'sale_price'=>wc_price($price),
				'description' => wc_trim_string($data_kid['title']),
				'short_description' => $data_kid['title'],
				'attributes' =>$atributos,
				'parent_id'=>$pai,
				'manage_stock'=>true,
				'stock_quantity'=>$data_kid['stock_quantity'],
				'meta_data'=>$metadata
			];
			$request->set_body_params($post_filho_data);
			$response = rest_do_request( $request );
			$server = rest_get_server();
			$data = $server->response_to_data( $response, false);
			Log::write(json_encode($data,true));
			$artigo = $data['id'];
			$data_kid['sellingprice'] = $data_kid['regular_price'];
			$data_kid['id'] = $data_kid['sku'];
			OfficeGestDBModel::setWooID($artigo,$data_kid['id']);
			return $artigo;
		}
	}

	public static function create_batch($pecas){
		$lista_pecas = array_chunk($pecas,10);
		$url = get_site_url().'/wp-json/wc/v3/products/batch';
		foreach ($lista_pecas as $k=>$v){
			$request = new Curl();
			$request->post($url,['create'=>array_values($v)]);
			$res =  json_decode( json_encode( $request->response ), true );
			if (isset($res['create'])){
				$result = $res['create'];
				foreach ($result as $key=>$vis){
					OfficeGestDBModel::setWooIdEcoAutoParts($vis['sku'],$vis['id']);
				}
			}
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
     * Create individual article
     *
     * @param $data
     * @return int
     */
    public static function createArticle($data){
        try {
            global $wpdb;

            $id = wc_get_product_id_by_sku($data['id']);
            $images = [];
            $metadata = [];
            $metadata[] = [
                'key' => '_offigest_article',
                'value' => 1,
            ];
            $metadata[] = [
                'key' => '_article_article',
                'value' => 1,
            ];
            $metadata[] = [
                'key' => '_article_id',
                'value' => $data['id']
            ];
            $metadata[] = [
                'key' => '_article_barcode',
                'value' => $data['barcode']
            ];
            $brandyName = '';
            if(!empty($data['brand'])){
                $brandyName = OfficeGestDBModel::getBrandName($data['brand']);
            }
            $metadata[] = [
                'key' => '_article_brand',
                'value' => $brandyName
            ];
            $metadata[]=[
                'key'=>'_ecoauto_status_desc',
                'value'=> 'Não se aplica'
            ];

            $main_cat = term_exists('artigos', 'product_cat')['term_id'];

            $familyName = OfficeGestDBModel::getCategoryName($data['family']);
            $family = term_exists(htmlspecialchars($familyName), 'product_cat', $main_cat)['term_id'];

            $subFamilyName = OfficeGestDBModel::getCategoryName($data['subfamily']);
            $subFamily = term_exists(htmlspecialchars($subFamilyName), 'product_cat', $family)['term_id'];

            $categories = [$main_cat, $family, $subFamily];

            $product_data = [
                'name' => OfficeGestDBModel::createArticleName($data,$brandyName),
                'slug' => Tools::slugify($data['description']),
                'type' => 'simple',
                'sku' => $data['id'],
                'regular_price' => wc_price($data['sellingprice']),
                'sale_price' => wc_price($data['sellingprice']),
                'description' => wc_trim_string($data['description']),
                'short_description' => wc_trim_string($data['short_description']),
                'manage_stock' => true,
                'stock_quantity' => $data['stock_quantity'],
                'meta_data' => $metadata,
                'categories' => $categories
            ];
            $product = new WC_Product_Simple($id);
            $product->set_name($product_data['name']);
            $product->set_description(OfficeGestDBModel::createArticleDescription($data,$brandyName) ?? '');
            $product->set_slug($product_data['slug']);
            $product->set_sku($product_data['sku']);
            $product->set_regular_price($product_data['regular_price']);
            $product->set_sale_price($product_data['sale_price']);
            $product->set_manage_stock(true);
            $product->set_stock_quantity($product_data['stock_quantity']);
            $product->set_category_ids($categories);
            $product->save();
            $id = wc_get_product_id_by_sku($product_data['sku']);
            foreach ($product_data['meta_data'] as $k => $a) {
                update_post_meta($id, $a['key'], $a['value']);
            }
            return $id;
        }catch(\Exception $e){
            Log::write("[OfficeGestDBModel: createArticle] Error: ".$e->getMessage());
            return null;
        }
    }

    /**
     * Create ecoauto part
     *
     * @param $data
     * @return int|null
     */
    public static function createEcoAutoPart($data){
        global $wpdb;
        try {
            $ecoauto_description = OfficeGestDBModel::getOption('ecoauto_pecas_description')==1;
            $id = wc_get_product_id_by_sku($data['id']);
            $metadata=[];
            $metadata[]=[
                'key'=>'_ecoauto_id',
                'value'=>$data['id']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_part_id',
                'value'=>$data['id']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_barcode',
                'value'=>$data['barcode']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_year',
                'value'=>$data['year']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_engine_num',
                'value'=>$data['engine_num']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_brand',
                'value'=>$data['brand']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_model',
                'value'=>$data['model']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_version',
                'value'=>$data['version']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_status_desc',
                'value'=>$data['status_desc']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_type_desc',
                'value'=>$data['type_desc']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_article_id',
                'value'=>$data['article_id']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_engine_num',
                'value'=>$data['engine_num']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_cod_oem',
                'value'=>$data['codoem']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_fuel',
                'value'=>$data['fuel']
            ];
            $main_cat = term_exists( 'pecas', 'product_cat' )['term_id'];
            $brand = term_exists( $data['brand'],'product_cat',$main_cat)['term_id'];
            $model = term_exists( $data['model'],'product_cat',$brand)['term_id'];
            $cat_cat = term_exists( $data['category'], 'product_cat',$model )['term_id'];
            $comp_cat =term_exists( $data['component'], 'product_cat',$cat_cat )['term_id'];
            $categories=[$main_cat,$brand,$model,$cat_cat, $comp_cat];
            $product_data =  [
                'name' => OfficeGestDBModel::createEcoAutoPartDescription($data),
                'slug'=> Tools::slugify(OfficeGestDBModel::createEcoAutoPartDescription($data)),
                'type'=>'simple',
                'sku'=>$data['id'],
                'regular_price'=>wc_price($data['selling_price_withvat']),
                'sale_price'=>wc_price($data['selling_price_withvat']),
                'description' => wc_trim_string(OfficeGestDBModel::createEcoAutoPartObs($data)),
                'short_description' => OfficeGestDBModel::createEcoAutoPartObs($data),
                'manage_stock'=>true,
                'stock_quantity'=>1,
                'meta_data'=>$metadata,
                'categories'=>$categories,
                'weight'=>$data['weight'],
                'height'=>$data['height']
            ];

            $product = new WC_Product_Simple($id);
            $product->set_name($product_data['name']);
            if ($ecoauto_description){
                $product->set_description($product_data['description']);
            }
            else{
                $product->set_description('');
            }
            $product->set_slug($product_data['slug']);
            $product->set_sku($product_data['sku']);
            $product->set_regular_price($product_data['regular_price']);
            $product->set_sale_price($product_data['sale_price']);
            $product->set_manage_stock(true);
            $product->set_stock_quantity(1);
            $product->set_category_ids($categories);
            $product->set_height($product_data['height']);
            $product->set_weight($product_data['weight']);
            $product->save();
            $id = wc_get_product_id_by_sku($product_data['sku']);
            foreach ($product_data['meta_data'] as $k=>$v){
                update_post_meta( $id, $v['key'], $v['value'] );
            }
            return $id;
        }catch(\Exception $e){
            Log::write("[OfficeGestProduct: createEcoAutoPart] Error: ".$e->getMessage());
            return null;
        }
    }

    /**
     * Create ecoauto process
     *
     * @param $data
     * @return int|null
     */
    public static function createEcoautoProcess($data){
        global $wpdb;
        try {
            $ecoauto_description = OfficeGestDBModel::getOption('ecoauto_viaturas_description')==1;
            $id = wc_get_product_id_by_sku($data['id']);
            $images = [];
            $metadata=[];
            $metadata[]=[
                'key'=>'_ecoauto_viatura',
                'value'=>1,
            ];
            $metadata[]=[
                'key'=>'_ecoauto_id',
                'value'=>$data['id']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_process_id',
                'value'=>$data['id']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_plate',
                'value'=>$data['plate']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_barcode',
                'value'=>$data['barcode']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_model',
                'value'=>$data['model']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_brand',
                'value'=>$data['brand']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_version',
                'value'=>$data['version']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_fuel',
                'value'=>$data['fuel']
            ];
            $metadata[]=[
                'key'=>'_ecoauto_status_desc',
                'value'=>OfficeGestDBModel::getProcessesStates($data['state'])
            ];
            $main_cat = term_exists( 'viaturas', 'product_cat' )['term_id'];
            $brand = term_exists( $data['brand'],'product_cat',$main_cat)['term_id'];
            $model = term_exists( $data['model'],'product_cat',$brand)['term_id'];
            $categories=[$main_cat, $brand,$model];
            $product_data =  [
                'name' => OfficeGestDBModel::createProcessDescription($data),
                'slug'=> Tools::slugify(OfficeGestDBModel::createProcessDescription($data)),
                'type'=>'simple',
                'sku'=>$data['id'],
                'regular_price'=>wc_price($data['price']),
                'sale_price'=>wc_price($data['price']),
                'description' => wc_trim_string($data['obs']),
                'short_description' =>  wc_trim_string($data['obs']),
                'manage_stock'=>false,
                'stock_quantity'=>1,
                'meta_data'=>$metadata,
                'categories'=>$categories
            ];

            $product = new WC_Product_Simple($id);
            $product->set_name($product_data['name']);
            if ($ecoauto_description){
                $product->set_description($product_data['description']);
            }
            else{
                $product->set_description('');
            }
            $product->set_slug($product_data['slug']);
            $product->set_sku($product_data['sku']);
            $product->set_regular_price($product_data['regular_price']);
            $product->set_sale_price($product_data['sale_price']);
            $product->set_manage_stock(true);
            $product->set_stock_quantity(1);
            $product->set_category_ids($categories);
            $product->save();
            $id = wc_get_product_id_by_sku($product_data['sku']);
            foreach ($product_data['meta_data'] as $k=>$v){
                update_post_meta( $id, $v['key'], $v['value'] );
            }
            return $id;
        }catch(\Exception $e){
            Log::write("[OfficeGestProduct: createEcoautoProcess] Error: ".$e->getMessage());
            return null;
        }
    }

    /**
     * Update ecoauto parts images
     *
     * @param $data
     * @return int|null
     */
    public static function updateEcoautoPartImage($data){
        global $wpdb;
        try {
            $id = wc_get_product_id_by_sku($data['id']);
            $photos = $wpdb->get_results('SELECT * FROM ' . TABLE_OFFICEGEST_ECO_PHOTOS. ' WHERE component_id="'.$data['id'].'"',ARRAY_A);
            $images = [];
            foreach ($photos as $key=>$vis){
                $images[]=[
                    'src'=>$vis['photo']
                ];
            }

            foreach ($images as $ki=>$vi){
                self::addImages($id,$vi);
            }
            OfficeGestDBModel::setWooIDEcoAutoAttachs($data['id']);

            return $id;
        }catch(\Exception $e){
            Log::write("[OfficeGestProduct: updateEcoautoPartImage] Error: ".$e->getMessage());
            return null;
        }
    }

    /**
     * Update officegest article images
     *
     * @param $data
     * @return int|null
     */
    public static function updateOfficeGestArticleImage($data){
        global $wpdb;
        try {
            $id = wc_get_product_id_by_sku($data['id']);
            $photos = $wpdb->get_results('SELECT * FROM ' . TABLE_OFFICEGEST_ARTICLE_PHOTOS. ' WHERE component_id="'.$data['id'].'"',ARRAY_A);
            $images = [];
            foreach ($photos as $key=>$vis){
                $images[]=[
                    'src'=>$vis['photo']
                ];
            }

            foreach ($images as $ki=>$vi){
                self::addArticleImages($id,$vi,$data['id']);
            }
            OfficeGestDBModel::setWooIDArticleAttachs($data['id']);

            return $id;
        }catch(\Exception $e){
            Log::write("[OfficeGestProduct: updateOfficeGestArticleImage] Error: ".$e->getMessage());
            return null;
        }
    }

    /**
     * Add image file
     *
     * @param $post_id
     * @param $images
     */
    private static function addImages($post_id,$images){
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $thumb_url = $images['src'];
        $singlePhoto = OfficeGestDBModel::singlePhoto($thumb_url);
        if ($singlePhoto===0){
            // Download file to temp location
            $tmp = download_url($thumb_url);

            // Set variables for storage
            // fix file name for query strings
            preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $thumb_url, $matches);
            $file_array['name'] = basename($matches[0]);
            $file_array['tmp_name'] = $tmp;

            // If error storing temporarily, unlink
            if ( is_wp_error( $tmp ) ) {
                @unlink($file_array['tmp_name']);
                $file_array['tmp_name'] = '';
            }

            //use media_handle_sideload to upload img:
            $thumbid = media_handle_sideload( $file_array, $post_id, 'gallery desc' );
            if ( is_wp_error($thumbid) ) {
                @unlink($file_array['tmp_name']);
            }

            OfficeGestDBModel::photoFind($thumb_url,$thumbid);
            $singlePhoto = $thumbid;
        }
        set_post_thumbnail($post_id, $singlePhoto);
    }

    /**
     * Add article image file
     *
     * @param $post_id
     * @param $images
     */
    private static function addArticleImages($post_id,$images,$id){
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $thumb_url = $images['src'];
        $singlePhoto = OfficeGestDBModel::singleArticlePhoto($thumb_url);
        if ($singlePhoto===0){
            // Download file to temp location
            $tmp = download_url($thumb_url);

            $get = wp_remote_get( $thumb_url );

            $type = wp_remote_retrieve_header( $get, 'content-type' );

            $sType = '.jpg';
            if($type == 'image/png'){
                $sType = '.png';
            } elseif($type == 'image/jpg'){
                $sType = '.jpg';
            } elseif($type == 'image/jpeg'){
                $sType = '.jpg';
            }

            // Set variables for storage
            // fix file name for query strings
            preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $thumb_url, $matches);
            $file_array['name'] = base64_encode($id).$sType;
            $file_array['tmp_name'] = $tmp;

            // If error storing temporarily, unlink
            if ( is_wp_error( $tmp ) ) {
                @unlink($file_array['tmp_name']);
                $file_array['tmp_name'] = '';
            }

            //use media_handle_sideload to upload img:
            $thumbid = media_handle_sideload( $file_array, $post_id, 'gallery desc' );

            if ( is_wp_error($thumbid) ) {
                @unlink($file_array['tmp_name']);
            }

            OfficeGestDBModel::photoArticleFind($id,$thumbid);
            $singlePhoto = $thumbid;
        }
        set_post_thumbnail($post_id, $singlePhoto);
    }
}