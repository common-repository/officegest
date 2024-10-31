<?php

namespace OfficeGest\Controllers;

use WC_Order;
use WP_Query;

class GeneratedOrders
{
	private static $ordersStatuses = ['wc-processing', 'wc-completed'];

	/**
	 * @return array
	 */
	public static function getAllAvailable()
	{
		$ordersList = [];
		$args = [
			'post_type' => 'shop_order',
			'post_status' => self::$ordersStatuses,
			'orderby' => 'date',
			'order' => 'DESC',
			'meta_query' => [
				'relation' => 'OR',
				[
					'key' => '_officegest_sent',
					'compare' => 'EXISTS'
				],
				[
					'key' => '_officegest_sent',
					'value' => '0',
					'compare' => '>'
				]
			],
			'nopaging' => true
		];


		$query = new WP_Query($args);
		foreach ($query->posts as $order) {
			$orderDetails = new WC_Order($order->ID);
			$meta = self::getPostMeta($order->ID);
			$status = get_post_status_object(get_post_status($order->ID));

			if (!isset($meta['_officegest_sent']) || (int)$meta['_officegest_sent'] > 0) {
				$ordersList[] = [
					'info' => $meta,
					'status' => $status ? $status->label : '',
					'number' => $orderDetails->get_order_number(),
					'id' => $order->ID
				];
			}
		}

		return $ordersList;
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