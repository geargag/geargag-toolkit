<?php

namespace GearGag_Toolkit;

use DateTime;
use DateTimeZone;
use GearGag_Toolkit\tools\Driver;
use PDO;

defined('WPINC') || die();

set_time_limit(120);
ini_set('memory_limit', -1);

class Import_Woo {
	protected $_prefix = 'wp_';

	public function __construct() {
		global $wpdb;
		$this->_prefix = $wpdb->prefix;
	}

	public function update_elastic($url, $consumer_key, $consumer_secret) {
		$db = new Driver('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASSWORD);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
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
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
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
				'post_author' => 1,
				'post_date' => '2019-08-28 06:43:48',
				'post_date_gmt' => '2019-08-28 06:43:48',
				'post_content' => '',
				'post_title' => 'dustin',
				'post_excerpt' => '',
				'post_status' => 'publish',
				'comment_status' => 'open',
				'ping_status' => 'closed',
				'post_password' => '',
				'post_name' => '',
				'to_ping' => '',
				'pinged' => '',
				/* "post_modified" => "2019-08-28 07:03:35",
				 "post_modified_gmt" => "2019-08-28 07:03:35",*/
				'post_content_filtered' => '',
				'post_parent' => 0,

				'menu_order' => 0,
				'post_type' => 'product',
				'post_mime_type' => '',
				'comment_count' => 0,
			];

			$slug = $this->slugify($json->name) . '-' . time();

			$date = new DateTime('now', new DateTimeZone(get_time_zone()));
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
				$item['meta_key'] = '_price';
				$item['meta_value'] = $json->price;
				$db->insert('postmeta', $item);
				$item2 = [];
				$item2['post_id'] = $product_id;
				$item2['meta_key'] = '_regular_price';
				$item2['meta_value'] = $json->price;
				$db->insert('postmeta', $item2);
			}

			$wp_term_relationships = [
				'object_id' => $product_id,
				'term_taxonomy_id' => $wp_term_taxonomy_id_variable,
				'term_order' => 0,
			];
			$db->insert('term_relationships', $wp_term_relationships);

			$tags = [];

			$id_tag_taxonomy = [];
			foreach ($json->tags as $tag) {
				$is_exits = false;
				$slug_tag = $this->slugify($tag->name);
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
						'name' => $tag->name,
						'slug' => $slug_tag,
					];
					$db->insert('terms', $wp_term);
					$id_tag = $db->lastInsertId();
					$tags[] = $id_tag;
				}
				if (count($wp_terms_cat) > 0) {
					foreach ($wp_terms_cat as $cat) {
						if (strcmp($tag->name, $cat->name) == 0) {
							$wp_term_relationships = [
								'object_id' => $product_id,
								'term_taxonomy_id' => $cat->term_taxonomy_id,
								'term_order' => 0,
							];
							$db->insert('term_relationships', $wp_term_relationships);
						}
					}
				}
			}

			foreach ($tags as $id) {
				$wp_term_taxonomy = [
					'term_id' => $id,
					'taxonomy' => 'product_tag',
					'description' => '',
				];
				$db->insert('term_taxonomy', $wp_term_taxonomy);
				$id_tag_taxonomy[] = $db->lastInsertId();
			}
			foreach ($id_tag_taxonomy as $id_tax) {
				$wp_term_relationships = [
					'object_id' => $product_id,
					'term_taxonomy_id' => $id_tax,
					'term_order' => 0,
				];
				$db->insert('term_relationships', $wp_term_relationships);
			}

			$wp_wc_product_meta_lookup = [
				'product_id' => $product_id,
				'sku' => $json->sku,
				'virtual' => 0,
				'downloadable' => 0,
				'onsale' => 0,
				'stock_quantity' => null,
				'stock_status' => 'instock',
				'rating_count' => 0,
				'average_rating' => 0.0,
				'total_sales' => 0,
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

	protected function slugify($text) {
		$text = str_replace(array('’', "'"), 's', $text);
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
	public function insert($json, $consumer_key, $consumer_secret) {
		$db = new Driver('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASSWORD);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
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

		$st = $db->query("SELECT * FROM {$this->_prefix}terms WHERE name = 'simple' LIMIT 1");
		$wp_terms = $st->fetchObject();
		if (!is_object($wp_terms)) {
			exit();
		}

		$st = $db->query("SELECT term.*,tax.term_taxonomy_id
									FROM {$this->_prefix}term_taxonomy tax join {$this->_prefix}terms term
									ON tax.term_id = term.term_id
									WHERE term.name = 'variable'
					    ");

		$wp_term_taxonomy = $st->fetchObject();
		if (!is_object($wp_term_taxonomy)) {
			exit();
		}

		$wp_term_taxonomy_id_variable = $wp_term_taxonomy->term_taxonomy_id;

		$db->beginTransaction();

		try {

			//Todo : insert product to wp_posts
			$wp_posts = [
				'post_author' => 1,
				'post_content' => '',
				'post_title' => $json->product->name ? $json->product->name : "",
				'post_excerpt' => $json->product->description ? $json->product->description : "",
				'post_status' => 'publish',
				'comment_status' => 'open',
				'ping_status' => 'closed',
				'post_password' => '',
				'post_name' => '',
				'to_ping' => '',
				'pinged' => '',
				'post_content_filtered' => '',
				'post_parent' => 0,
				'menu_order' => 0,
				'post_type' => 'product',
				'post_mime_type' => '',
				'comment_count' => 0,
			];

			$slug = $this->slugify($json->name) . '-' . time();
			$date = new DateTime('now', new DateTimeZone(get_time_zone()));
			$wp_posts['post_name'] = $slug;
			$wp_posts['post_date'] = $date->format('Y-m-d H:i:s');
			$wp_posts['post_modified'] = $date->format('Y-m-d H:i:s');
			$wp_posts['post_date_gmt'] =$date->format('Y-m-d H:i:s');
			$wp_posts['post_modified_gmt'] = $date->format('Y-m-d H:i:s');
			$db->insert('posts', $wp_posts);

			//Todo : insert product variant to wp_posts
			$product_id = $db->lastInsertId();
			foreach ($json->variant as $variant)
			{
				$slug = $this->slugify($variant->title) . '-' . time();
				$wp_posts_vartiant = [
					'post_author' => 1,
					'post_date' => $date->format('Y-m-d H:i:s'),
					'post_modified' => $date->format('Y-m-d H:i:s'),
					'post_date_gmt' =>$date->format('Y-m-d H:i:s'),
					'post_modified_gmt' =>$date->format('Y-m-d H:i:s'),
					'post_content' => '',
					'post_title' =>$variant->title ? $variant->title : "",
					'post_excerpt' => '',
					'post_status' => 'publish',
					'comment_status' => 'open',
					'ping_status' => 'closed',
					'post_password' => '',
					'post_name' => $slug,
					'to_ping' => '',
					'pinged' => '',
					'post_content_filtered' => '',
					'post_parent' => $product_id,
					'menu_order' => 0,
					'post_type' => 'product_variation',
					'post_mime_type' => '',
				];
				$db->insert('posts', $wp_posts_vartiant);

			}

			$attributes = [];
			$i=0;
			foreach ($json->attributes as $key=>$val)
			{
				$attributes[$key] = [
					'name'=> $key,
					'value'=> implode('|', $val),
					'position'=> $i,
					'is_visible'=> $i,
					'is_variation'=> 1,
					'is_taxonomy'=> 0,
				];
				$i++;
			}

			//Todo : insert product to wp_postmeta
			$meta_keys = [
				'product_updated' => 1,
				'_edit_last' => '1',
				'_edit_lock' => '1',
				'total_sales' => '0',
				'_tax_status' => 'taxable',
				'_tax_class' => '',
				'_manage_stock' => 'no',
				'_backorders' => 'no',
				'_sold_individually' => 'no',
				'_virtual' => 'no',
				'_downloadable' => 'no',
				'_download_limit' => '-1',
				'_download_expiry' => '-1',
				'_stock' => NULL,
				'_stock_status' => 'instock',
				'_wc_average_rating' => 'no',
				'_wc_review_count' => 'no',
				'_product_version' => 'no',
				'_price' => $json->product->price,
				'_product_attributes'=>serialize($attributes)
			];

			foreach ($meta_keys as $key => $val) {
				$item = [];
				$item['post_id'] = $product_id;
				$item['meta_key'] = $key;
				$item['meta_value'] = $val;
				$db->insert('postmeta', $item);
			}

			if($json->product->image){
				$item = [];
				$item['post_id'] = $product_id;
				$item['meta_key'] = 'geargag_image_url';
				$item['meta_value'] =$json->product->image;
				$db->insert('postmeta', $item);
			}

			//Todo : insert product type is variant to term_relationships
			$item = [];
			$item['object_id'] = $product_id;
			$item['term_taxonomy_id'] = $wp_term_taxonomy_id_variable;
			$item['term_order'] = 0;
			$db->insert('term_relationships', $item);

			$st = $db->query("SELECT ID FROM {$this->_prefix}posts WHERE post_parent = '$product_id'");
			$product_vartiants =$st->fetchAll(\PDO::FETCH_OBJ);
			$product_vartiant_ids = array_column($product_vartiants,"ID" );

			//Todo : insert product variant to postmeta
			$meta_keys_v = [
				'_variation_description'=> '',
				'total_sales'=> '0',
				'_tax_status'=> 'taxable',
				'_tax_class'=> 'parent',
				'_manage_stock'=> 'no',
				'_backorders'=> 'no',
				'_sold_individually'=> 'no',
				'_virtual'=> 'no',
				'_downloadable'=> 'no',
				'_download_limit'=> '-1',
				'_download_expiry'=> '-1',
				'_stock'=> null,
				'_stock_status'=> 'instock',
				'_wc_average_rating'=> '0',
				'_wc_review_count'=> '0',
				'_product_version'=> '4.2.0',
//				'_regular_price'=> '0',
//				'_sale_price'=> '0',
//				'_price'=> '0',
			];

			$attributeOption1 = array_column($json->variant,"option1");
			$attributeOption2 = array_column($json->variant,"option2");
			$attributeOption3 = array_column($json->variant,"option3");
			$variantImages = array_column($json->variant,"image");
			$variantPrice = array_column($json->variant,"price");
//			echo "<pre>";
//			print_r($variantPrice); die;

			foreach ($product_vartiant_ids as $key => $id) {

				foreach ($meta_keys_v as $meta_key => $val_key) {

					$item = [];
					$item['post_id'] = $id;
					$item['meta_key'] = $meta_key;
					$item['meta_value'] = $val_key;
					$db->insert('postmeta', $item);
				}
				foreach ($attributes as $k => $val) {

					if ($k == $json->product->option2) {
						$item = [];
						$item['post_id'] = $id;
						$item['meta_key'] = 'attribute_' . $k;
						$item['meta_value'] = isset($attributeOption2[$key]) ? trim ($attributeOption2[$key]) : '';
						$db->insert('postmeta', $item);

					}
					if ($k == $json->product->option1) {
						$item = [];
						$item['post_id'] = $id;
						$item['meta_key'] = 'attribute_' . $k;
						$item['meta_value'] = isset($attributeOption1[$key]) ? trim($attributeOption1[$key]) : '';
						$db->insert('postmeta', $item);

					}
					if ($k == $json->product->option3) {
						$item = [];
						$item['post_id'] = $id;
						$item['meta_key'] = 'attribute_' . $k;
						$item['meta_value'] = isset($attributeOption3[$key]) ? trim($attributeOption3[$key]) : '';
						$db->insert('postmeta', $item);
					}
				}

				if(!empty( $variantImages[$key])){
					$item = [];
					$item['post_id'] = $id;
					$item['meta_key'] = 'geargag_image_url';
					$item['meta_value'] = $variantImages[$key];
					$db->insert('postmeta', $item);
				}
				if(!empty($variantPrice[$key])){
					//echo 123;die;
					$item = [];
					$item['post_id'] = $id;
					$item['meta_key'] = '_price';
					$item['meta_value'] = $variantPrice[$key];
					$db->insert('postmeta', $item);

					$item = [];
					$item['post_id'] = $id;
					$item['meta_key'] = '_sale_price';
					$item['meta_value'] = $variantPrice[$key];
					$db->insert('postmeta', $item);

					$item = [];
					$item['post_id'] = $id;
					$item['meta_key'] = '_regular_price';
					$item['meta_value'] = $variantPrice[$key];
					$db->insert('postmeta', $item);
				}

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
