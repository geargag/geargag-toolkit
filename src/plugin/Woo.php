<?php

namespace vnh_namespace;

use vnh_namespace\tools\contracts\Bootable;

class Woo implements Bootable {
	public function boot() {
		add_action('save_post_product', [$this, 'save_product']);
		add_action('delete_post', [$this, 'delete_products']);
		add_action('rest_api_init', [$this, 'register_routes']);
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

	/**
	 * @uses get_updated_products, get_deleted_products, empty_deleted_products_option
	 */
	public function register_routes() {
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

		register_rest_route('geargag/v1', '/update-elasticsearch', [
			'methods' => 'POST',
			'callback' => [$this, 'update_elasticsearch'],
		]);
	}

	public function update_elasticsearch($req) {
		$json = $req->get_json_params();
		$json = json_decode(json_encode($json));
		$consumer_key = $req->get_param('consumer_key');
		$consumer_secret = $req->get_param('consumer_secret');

		$url = $json->url ?? '';

		$import = new Import_Woo();
		$import->update_elastic($url, $consumer_key, $consumer_secret);
		exit();
	}

	/**
	 * @param $req \WP_REST_Request
	 */
	public function import_products($req) {
		$json = $req->get_json_params();
		$json = json_decode(json_encode($json));
		$consumer_key = $req->get_param('consumer_key');
		$consumer_secret = $req->get_param('consumer_secret');
		$import = new Import_Woo();
		$import->import($json, $consumer_key, $consumer_secret);
		exit();
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
}
