<?php

namespace GearGag_Toolkit;

use GearGag_Toolkit\tools\contracts\Bootable;

class Woo_Gallery implements Bootable {
	public function boot() {
		add_filter('woocommerce_available_variation', [$this, 'add_more_variation'], 10, 3);
		add_filter('woocommerce_single_product_image_thumbnail_html', [$this, 'default_image']);
		add_filter('woocommerce_product_get_image', [$this, 'woocommerce_product_get_image'], 10, 5);
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

		$post_thumbnail_id = $product->get_image_id();
		$product_id = $product->get_id();

		if ($product->get_image_id()) {
			$html = wc_get_gallery_image_html($post_thumbnail_id, true);
		} elseif (!empty(get_post_meta($product_id, 'geargag_image_url'))) {
			$html = '<div class="woocommerce-product-gallery__image--placeholder">';
			$html .= sprintf(
				'<img src="%s" alt="%s" class="wp-post-image" />',
				get_post_meta($product_id, 'geargag_image_url')[0],
				esc_html__('Geargag image', 'woocommerce')
			);
			$html .= '</div>';
		} else {
			$html = '<div class="woocommerce-product-gallery__image--placeholder">';
			$html .= sprintf(
				'<img src="%s" alt="%s" class="wp-post-image" />',
				esc_url(wc_placeholder_img_src('woocommerce_single')),
				esc_html__('Awaiting product image', 'woocommerce')
			);
			$html .= '</div>';
		}

		return $html;
	}

   public function woocommerce_product_get_image($image, $object, $size, $attr, $placeholder) {
      if ( $object->get_image_id() ) {
	     $image = wp_get_attachment_image( $object->get_image_id(), $size, false, $attr );
      } elseif ( $object->get_parent_id() ) {
	     $parent_product = wc_get_product( $object->get_parent_id() );
	     if ( $parent_product ) {
		    $image = $parent_product->get_image( $size, $attr, $placeholder );
	     }
      } elseif ( !empty(get_post_meta($object->get_id(), 'geargag_image_url'))) {
         $image = sprintf(
            '<img src="%s" alt="%s" class="wp-post-image" />',
            get_post_meta($object->get_id(), 'geargag_image_url')[0],
            esc_html__('Geargag image', 'woocommerce')
         );
      }

      if ( ! $image && $placeholder ) {
	     $image = wc_placeholder_img( $size, $attr );
      }

      return $image;

   }
}
