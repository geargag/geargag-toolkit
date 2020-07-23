<?php

namespace GearGag_Toolkit;

use GearGag_Toolkit\tools\contracts\Bootable;
use WP_REST_Request;

defined('WPINC') || die();

class Woo implements Bootable {
	public function boot() {
		add_filter('woocommerce_product_is_in_stock', [$this, 'fix_is_in_stock'], 10, 2);
		add_action('save_post_product', [$this, 'save_product']);
		add_action('delete_post', [$this, 'delete_products']);
		add_action('rest_api_init', [$this, 'register_routes']);
	}

	public function fix_is_in_stock($status, $object) {
		if (get_post_meta($object->get_id(), 'geargag_image_url')) {
			return true;
		}
		return $status;
	}

	/**
	 * Update geargag_delete_products option when a product is deleted.
	 *
	 * @param $product_id
	 */
	public function delete_products($product_id) {
		$options = get_option('geargag_delete_products');
		$options[$product_id] = $product_id;
		update_option('geargag_delete_products', $options);
	}

	/**
	 * Update metadata when a product is updated.
	 *
	 * @param $post_id
	 */
	public function save_product($post_id) {
		update_post_meta($post_id, 'product_updated', true);
	}

	public function register_routes() {
		register_rest_route('geargag/v1', '/export-products', [
			'methods' => 'GET',
			'callback' => [$this, 'get_export_products'],
		]);

		register_rest_route('geargag/v1', '/updated-products', [
			'methods' => 'GET',
			'callback' => [$this, 'get_updated_products'],
		]);

		register_rest_route('geargag/v1', '/deleted-products', [
			'methods' => 'GET',
			'callback' => [$this, 'get_deleted_products'],
		]);

		register_rest_route('geargag/v1', '/deleted-products', [
			'methods' => 'DELETE',
			'callback' => [$this, 'empty_deleted_products_option'],
		]);

		register_rest_route('geargag/v1', '/import-products', [
			'methods' => 'POST',
			'callback' => [$this, 'import_products'],
		]);
		register_rest_route('geargag/v1', '/batch-insert-products', [
			'methods' => 'POST',
			'callback' => [$this, 'batch_insert_products'],
		]);
	}

	public function get_export_products(WP_REST_Request $req) {
		$last_id = $req->get_param('last_id') ? (int) $req->get_param('last_id') : 1;
		$export = new Export_Woo();

		return $export->export_products($last_id);
	}

	public function get_updated_products() {
		$posts = get_posts([
			'post_type' => 'product',
			'meta_query' => [
				[
					'key' => 'product_updated',
					'value' => true,
				],
			],
		]);

		if (empty($posts)) {
			return null;
		}

		$array = [];
		foreach ($posts as $post) {
			$product = wc_get_product($post->ID);
			$all_tags = get_the_terms($post->ID, 'product_tag');

			$tags = [];
			if (!empty($all_tags)) {
				foreach ($all_tags as $tag) {
					$tags[] = $tag->name;
				}
			} else {
				$tags = false;
			}

			$array[$post->ID] = [
				'title' => $product->get_title(),
				'date_modified' => $product->get_date_modified(),
				'tags' => $tags,
			];
		}

		return $array;
	}

	public function get_deleted_products() {
		$all_ids = [];

		if (!empty(get_option('geargag_delete_products'))) {
			foreach (get_option('geargag_delete_products') as $id) {
				$all_ids[] = $id;
			}
		}

		return $all_ids;
	}

	public function empty_deleted_products_option() {
		update_option('geargag_delete_products', []);
	}

	public function import_products(WP_REST_Request $req) {
		$json = $req->get_json_params();
		$json = json_decode(json_encode($json));
		$consumer_key = $req->get_param('consumer_key');
		$consumer_secret = $req->get_param('consumer_secret');
		$import = new Import_Woo();
		$import->import($json, $consumer_key, $consumer_secret);
		exit();
	}
	public function batch_insert_products(WP_REST_Request $req) {
		$json = $req->get_json_params();
		$json = json_decode(json_encode($json));
		$consumer_key = $req->get_param('consumer_key');
		$consumer_secret = $req->get_param('consumer_secret');
		$import = new Import_Woo();
		$import->insert($json, $consumer_key, $consumer_secret);
		exit();
	}
}
