<?php

namespace OfficeGest\Controllers;
use OfficeGest\OfficeGestDBModel;

class ProductsList
{

	/**
	 * @return array
	 */
	public static function getAllAvailable()
	{
		global $wpdb;
		$data = OfficeGestDBModel::getOption('last_article_sync');
		$updatedProducts = OfficeGestDBModel::getAllOfficeGestProducts($data);
		foreach ( $updatedProducts as $product ) {
			$cat = $wpdb->get_row( 'SELECT * FROM ' . TABLE_OFFICEGEST_ARTICLES . ' where id="' . $product['id'] . '"', ARRAY_A );
			if ( ! empty( $cat ) ) {
				$wpdb->update( TABLE_OFFICEGEST_ARTICLES, $product, [
					'id' => $product['id']
				] );
			} else {
				$wpdb->insert( TABLE_OFFICEGEST_ARTICLES, $product );
			}
		}
		OfficeGestDBModel::setOption('last_article_sync',date( 'Y-m-d H:i:s' ));
		$args = 'select a.*,f.description as familia, b.description as marca from officegest_articles a 
    left join officegest_brands b on (b.id=a.brand) 
    left join officegest_categories f on (f.id=a.family) where a.articletype ="N" ';
		return $wpdb->get_results($args,ARRAY_A);

	}





	/**
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
}