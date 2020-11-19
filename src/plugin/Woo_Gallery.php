<?php

namespace GearGag_Toolkit;

use GearGag_Toolkit\tools\contracts\Bootable;

class Woo_Gallery implements Bootable {
	public function boot() {
		add_filter('woocommerce_available_variation', [$this, 'add_more_variation'], 10, 3);
		add_filter('woocommerce_single_product_image_thumbnail_html', [$this, 'default_image']);
		add_filter('woocommerce_product_get_image', [$this, 'woocommerce_product_get_image'], 10, 5);
		add_filter('manage_edit-product_columns', [$this, 'remove_default_product_thumb'], 10, 2);
		add_filter('manage_product_posts_custom_column', [$this, 'add_product_thumb'], 10, 2);
		add_action('admin_enqueue_scripts', [$this, 'custom_css']);
	}

	public function add_more_variation($data, $product, $variation) {
		if ($variation->get_image_id()) {
			$data['image'] = wc_get_product_attachment_props($variation->get_image_id());
		} elseif (!empty(get_post_meta($variation->get_id(), 'geargag_image_url', true))) {
			$data['image']['src'] = get_post_meta($variation->get_id(), 'geargag_image_url', true);
		}

		return $data;
	}

	public function default_image($html) {
		global $product;

		$product_id = $product->get_id();

		if (!empty(get_post_meta($product_id, 'geargag_image_url'))) {
		   $html = sprintf(
			  "<div data-thumb='%s' data-thumb-alt='geargag thumbnail' class='woocommerce-product-gallery__image'><a href='%s'>%s</a></div>",
			  esc_url(get_post_meta($product_id, 'geargag_image_url')[0]),
			  esc_url(get_post_meta($product_id, 'geargag_image_url')[0]),
			  sprintf('<img width="510" height="510" src="%1$s" class="wp-post-image" alt="" title="pennant-1.jpg" data-caption="" data-src="%1$s" data-large_image="%1$s" data-large_image_width="800" data-large_image_height="800" >',
				 get_post_meta($product_id, 'geargag_image_url')[0]
				)
		   );

		   return $html;
		}

		return $html;
	}

	public function woocommerce_product_get_image($image, $object, $size, $attr, $placeholder) {
		if ($object->get_image_id()) {
			$image = wp_get_attachment_image($object->get_image_id(), $size, false, $attr);
		} elseif ($object->get_parent_id()) {
			$parent_product = wc_get_product($object->get_parent_id());
			if ($parent_product) {
				$image = $parent_product->get_image($size, $attr, $placeholder);
			}
		} elseif (!empty(get_post_meta($object->get_id(), 'geargag_image_url'))) {
			$image = sprintf(
				'<img src="%s" alt="%s" class="wp-post-image" />',
				get_post_meta($object->get_id(), 'geargag_image_url')[0],
				esc_html__('Geargag image', 'woocommerce')
			);
		}

		if (!$image && $placeholder) {
			$image = wc_placeholder_img($size, $attr);
		}

		return $image;
	}

	public function remove_default_product_thumb($cols) {
		unset($cols['thumb']);

		$thumbnail = [
			'thumbnail' => sprintf(
				'<span class="wc-image tips" data-tip="%s">%s',
				esc_attr__('Image', 'vnh_textdomain'),
				__('Image', 'vnh_textdomain')
			),
		];

		return array_slice($cols, 0, 1, true) + $thumbnail + array_slice($cols, 1, null, true);
	}

	public function custom_css() {
		$custom_css =
			'table.wp-list-table .column-thumbnail { width: 52px; text-align: center; white-space: nowrap;}table.wp-list-table img{max-width: 100%;height: auto;}';
		wp_add_inline_style('list-tables', $custom_css);
	}

	public function add_product_thumb($column, $post_id) {
		if ($column === 'thumbnail') {
			if (!empty(get_post_meta($post_id, 'geargag_image_url'))) {
				$src = get_post_meta($post_id, 'geargag_image_url')[0];
				printf('<a href="%s"><img src="%s" alt=""></a>', esc_url(get_edit_post_link($post_id)), esc_url($src));
			} elseif (has_post_thumbnail()) {
				printf('<a href="%s">%s</a>', esc_url(get_edit_post_link($post_id)), the_post_thumbnail()); //phpcs:disable
			} else {
				printf(
					'<a href="%s"><img src="%s" alt=""></a>',
					esc_url(get_edit_post_link($post_id)),
					esc_url(wc_placeholder_img_src('thumbnail'))
				);
			}
		}
	}
}
