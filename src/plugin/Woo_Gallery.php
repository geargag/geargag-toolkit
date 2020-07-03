<?php


namespace GearGag_Toolkit;


use GearGag_Toolkit\tools\contracts\Bootable;

class Woo_Gallery implements Bootable {
	public function boot() {
		add_filter('woocommerce_available_variation', [$this, 'add_more_variation'], 10, 3);
		add_filter('woocommerce_single_product_image_thumbnail_html', [$this, 'default_image']);
	}

	public function add_more_variation($data, $product, $variation) {
		if (!empty(get_post_meta($variation->get_id(), 'geargag_image_url', true))) {
			$data['image']['src'] = get_post_meta($variation->get_id(), 'geargag_image_url', true);
		}

		return $data;
	}

   public function default_image($html) {
      global $product;

      $post_thumbnail_id = $product->get_image_id();
      $product_id = $product->get_id();

      if ( $product->get_image_id() ) {
	     $html = wc_get_gallery_image_html( $post_thumbnail_id, true );
      } elseif (!empty(get_post_meta($product_id, 'geargag_image_url'))) {
	     $html  = '<div class="woocommerce-product-gallery__image--placeholder">';
	     $html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', get_post_meta( $product_id, 'geargag_image_url')[0], esc_html__( 'Default image', 'woocommerce' ) );
	     $html .= '</div>';
      } else {
	     $html  = '<div class="woocommerce-product-gallery__image--placeholder">';
	     $html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
	     $html .= '</div>';
      }

      return $html;
	}
}
