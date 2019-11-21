<?php

namespace vnh_namespace;

use vnh_namespace\tools\Driver;

set_time_limit(120);
ini_set("memory_limit", -1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function geargag_slugify($text) {
	$text = str_replace(array("’", "'"), "s", $text);
	// replace non letter or digits by -
	$text = preg_replace('~[^\pL\d]+~u', '-', $text);

	// transliterate
	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	// remove unwanted characters
	$text = preg_replace('~[^-\w]+~', '', $text);

	// trim
	$text = trim($text, '-');

	// remove duplicate -
	$text = preg_replace('~-+~', '-', $text);

	// lowercase
	$text = strtolower($text);

	if (empty($text)) {
		return 'n-a';
	}

	return $text;
}

class Import_Woo {
	protected $_prefix = 'wp_';

	public function __construct() {
		$this->_prefix = wpdb()->prefix;
	}

	public function test() {
		echo 'hello';
	}

	public function update_elastic($url, $consumer_key, $consumer_secret) {
		$db = new Driver('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASSWORD);
		$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
		$db->setPrefix($this->_prefix);

		$st = $db->prepare(
			"SELECT * FROM {$this->_prefix}woocommerce_api_keys WHERE consumer_key = :consumer_key  AND consumer_secret = :consumer_secret LIMIT 1"
		);
		$st->execute([':consumer_key' => wc_api_hash($consumer_key), ':consumer_secret' => $consumer_secret]);
		$checkToken = $st->fetchObject();
		if (!is_object($checkToken)) {
			header('Content-Type: application/json');
			echo json_encode(['status' => false, 'error' => 'invalid token']);
			exit();
		}

		update_option('geargag_search', $url, true);
	}

	public function import($json, $consumer_key, $consumer_secret) {
		$db = new Driver('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASSWORD);
		$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
		$db->setPrefix($this->_prefix);

		$st = $db->prepare(
			"SELECT * FROM {$this->_prefix}woocommerce_api_keys WHERE consumer_key = :consumer_key  AND consumer_secret = :consumer_secret LIMIT 1"
		);

		$st = $db->prepare(
			"SELECT * FROM {$this->_prefix}woocommerce_api_keys WHERE consumer_key = :consumer_key  AND consumer_secret = :consumer_secret LIMIT 1"
		);
		$st->execute([':consumer_key' => wc_api_hash($consumer_key), ':consumer_secret' => $consumer_secret]);
		$checkToken = $st->fetchObject();
		if (!is_object($checkToken)) {
			header('Content-Type: application/json');
			echo json_encode(['status' => false, 'error' => 'invalid token']);
			exit();
		}

		$st = $db->query("SELECT * FROM {$this->_prefix}terms WHERE name = 'simple' LIMIT 1");
		$wp_terms = $st->fetchObject();
		if (!is_object($wp_terms)) {
			exit();
		}

		$st = $db->query("SELECT term.*,tax.term_taxonomy_id
									FROM {$this->_prefix}term_taxonomy tax join {$this->_prefix}terms term 
									ON tax.term_id = term.term_id 
									WHERE tax.taxonomy = 'product_cat'
					    ");
		$wp_terms_cat = $st->fetchAll(5);

		$st = $db->query("SELECT term.*,tax.term_taxonomy_id
									FROM {$this->_prefix}term_taxonomy tax join {$this->_prefix}terms term 
									ON tax.term_id = term.term_id 
									WHERE tax.taxonomy = 'product_tag'
					    ");
		$wp_terms_tags = $st->fetchAll(5);

		$st = $db->query(sprintf("SELECT * FROM {$this->_prefix}term_taxonomy WHERE term_id = %d LIMIT 1", $wp_terms->term_id));
		$wp_term_taxonomy = $st->fetchObject();
		if (!is_object($wp_term_taxonomy)) {
			exit();
		}

		$wp_term_taxonomy_id_variable = $wp_term_taxonomy->term_taxonomy_id;

		$db->beginTransaction();

		try {
			$out = [];
			$wp_posts = [
				/*"ID" => 5541,*/
				"post_author" => 1,
				"post_date" => "2019-08-28 06:43:48",
				"post_date_gmt" => "2019-08-28 06:43:48",
				"post_content" => "",
				"post_title" => "dustin",
				"post_excerpt" => "",
				"post_status" => "publish",
				"comment_status" => "open",
				"ping_status" => "closed",
				"post_password" => "",
				"post_name" => "",
				"to_ping" => "",
				"pinged" => "",
				/* "post_modified" => "2019-08-28 07:03:35",
				 "post_modified_gmt" => "2019-08-28 07:03:35",*/
				"post_content_filtered" => "",
				"post_parent" => 0,

				"menu_order" => 0,
				"post_type" => "product",
				"post_mime_type" => "",
				"comment_count" => 0,
			];

			$slug = geargag_slugify($json->name) . '-' . time();

			$date = new \DateTime('now', new \DateTimeZone(get_time_zone()));
			$wp_posts['geargag_defaults'] = json_encode($json->geargag_defaults, JSON_UNESCAPED_SLASHES);
			$wp_posts['post_title'] = $json->name;
			$wp_posts['post_excerpt'] = $json->description;
			$wp_posts['post_name'] = $slug;
			$wp_posts['post_date'] = $date->format('Y-m-d H:i:s');
			$wp_posts['post_modified'] = $date->format('Y-m-d H:i:s');
			$wp_posts['post_date_gmt'] = date('Y-m-d H:i:s', gmdate(strtotime('-1 days')));
			$wp_posts['post_modified_gmt'] = date('Y-m-d H:i:s', gmdate(strtotime('-1 days')));

			$db->insert('posts', $wp_posts);

			$product_id = $db->lastInsertId();

			//file_put_contents($product_id.'.json', json_encode($json));

			foreach ($json->meta_data as $meta) {
				if ($meta->key == 'geargag_styles') {
					$meta->value = serialize(json_decode(json_encode($meta->value), true));
				}
				$item = [];
				$item['post_id'] = $product_id;
				$item['meta_key'] = $meta->key;
				$item['meta_value'] = $meta->value;
				$db->insert('postmeta', $item);
			}

			if (isset($json->price)) {
				$item = [];
				$item['post_id'] = $product_id;
				$item['meta_key'] = "_price";
				$item['meta_value'] = $json->price;
				$db->insert('postmeta', $item);
				$item2 = [];
				$item2['post_id'] = $product_id;
				$item2['meta_key'] = "_regular_price";
				$item2['meta_value'] = $json->price;
				$db->insert('postmeta', $item2);
			}

			$wp_term_relationships = [
				"object_id" => $product_id,
				"term_taxonomy_id" => $wp_term_taxonomy_id_variable,
				"term_order" => 0,
			];
			$db->insert('term_relationships', $wp_term_relationships);

			$tags = [];

			$id_tag_taxonomy = [];
			foreach ($json->tags as $tag) {
				$is_exits = false;
				$slug_tag = geargag_slugify($tag->name);
				if (count($wp_terms_tags) > 0) {
					foreach ($wp_terms_tags as $wp_tag) {
						if (strcmp($slug_tag, $wp_tag->slug) == 0) {
							$id_tag_taxonomy[] = $wp_tag->term_taxonomy_id;
							$is_exits = true;
							break;
						}
					}
				}
				if (!$is_exits) {
					$wp_term = [
						"name" => $tag->name,
						"slug" => $slug_tag,
					];
					$db->insert('terms', $wp_term);
					$id_tag = $db->lastInsertId();
					$tags[] = $id_tag;
				}
				if (count($wp_terms_cat) > 0) {
					foreach ($wp_terms_cat as $cat) {
						if (strcmp($tag->name, $cat->name) == 0) {
							$wp_term_relationships = [
								"object_id" => $product_id,
								"term_taxonomy_id" => $cat->term_taxonomy_id,
								"term_order" => 0,
							];
							$db->insert('term_relationships', $wp_term_relationships);
						}
					}
				}
			}

			foreach ($tags as $id) {
				$wp_term_taxonomy = [
					"term_id" => $id,
					"taxonomy" => "product_tag",
					"description" => "",
				];
				$db->insert('term_taxonomy', $wp_term_taxonomy);
				$id_tag_taxonomy[] = $db->lastInsertId();
			}
			foreach ($id_tag_taxonomy as $id_tax) {
				$wp_term_relationships = [
					"object_id" => $product_id,
					"term_taxonomy_id" => $id_tax,
					"term_order" => 0,
				];
				$db->insert('term_relationships', $wp_term_relationships);
			}

			$wp_wc_product_meta_lookup = [
				"product_id" => $product_id,
				"sku" => $json->sku,
				"virtual" => 0,
				"downloadable" => 0,
				"onsale" => 0,
				"stock_quantity" => null,
				"stock_status" => "instock",
				"rating_count" => 0,
				"average_rating" => 0.0,
				"total_sales" => 0,
			];
			$db->insert('wc_product_meta_lookup', $wp_wc_product_meta_lookup);

			$meta_keys = [
				'_sku' => $json->sku,
				'total_sales' => '0',
				'_tax_status' => 'none',
				'_tax_class' => '',
				'_manage_stock' => 'no',
				'_backorders' => 'no',
				'_sold_individually' => 'no',
				'_virtual' => 'no',
				'_downloadable' => 'no',
				'_download_limit' => '0',
				'_download_expiry' => 0,
				'_stock' => null,
				'_stock_status' => 'instock',
				'_wc_average_rating' => '0',
				'_wc_review_count' => '0',
				'_product_attributes' => '',
				'_product_version' => '3.7.0',
			];

			foreach ($meta_keys as $key => $val) {
				$item = [];
				$item['post_id'] = $product_id;
				$item['meta_key'] = $key;
				$item['meta_value'] = $val;
				$db->insert('postmeta', $item);
			}

			$db->commit();

			header('Content-Type: application/json');
			echo json_encode(['status' => true, 'id' => $product_id]);
		} catch (\Exception $e) {
			$db->rollBack();
			header('Content-Type: application/json');
			echo json_encode(['status' => false, 'error' => $e->getMessage()]);
		}
	}
}
