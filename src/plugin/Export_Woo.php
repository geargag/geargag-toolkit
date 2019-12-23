<?php

namespace GearGag_Toolkit;

use WP_Error;

class Export_Woo {
	public function query_products($last_id) {
		global $wpdb;

		$query = "SELECT product.ID AS product_id,
       product.post_title  AS product_name,
       MIN(IF(meta.meta_key = 'geargag_styles', true, null)) AS geargag_styles,
       MIN(IF(meta.meta_key = '_thumbnail_id', meta.meta_value, null)) AS product_thumbnail_id,
       MIN(IF(meta.meta_key = '_product_image_gallery', meta.meta_value, null)) AS product_image_gallery_ids
FROM wp_posts AS product
         LEFT JOIN wp_postmeta AS meta ON product.ID = meta.post_ID
WHERE product.post_type = 'product'
  AND product.post_status = 'publish'
  AND product.ID > %d
  AND meta.meta_key IN ('_thumbnail_id', '_product_image_gallery', 'geargag_styles')
GROUP BY product.ID
LIMIT 100";

		$query = str_replace(['wp_posts', 'wp_postmeta'], [$wpdb->posts, $wpdb->postmeta], $query);

		return $wpdb->get_results($wpdb->prepare($query, esc_sql($last_id), ARRAY_A));
	}

	public function export_products($last_id) {
		if (empty($this->query_products($last_id))) {
			return new WP_Error('rest_export_product_invalid', esc_html__('The product does not exist.', 'vnh_textdomain'), array(
				'status' => 404,
			));
		}

		$results = [];
		foreach ($this->query_products($last_id) as $product) {
			if ($product->geargag_styles === '1' || ($product->product_thumbnail_id === null && $product->product_image_gallery_ids === null)) {
				continue;
			}

			unset($product->geargag_styles);

			if (!empty($product->product_thumbnail_id)) {
				$product->product_image_url = wp_get_attachment_image_url($product->product_thumbnail_id, 'full');
			} else {
				$gallery = explode(',', $product->product_image_gallery_ids);
				$product->product_image_url = wp_get_attachment_image_url($gallery[0], 'full');
			}

			unset($product->product_thumbnail_id, $product->product_image_gallery_ids);

			$results[] = $product;
		}

		return $results;
	}
}
