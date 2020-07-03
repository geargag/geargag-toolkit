<?php


namespace GearGag_Toolkit;


use GearGag_Toolkit\tools\contracts\Bootable;

class Woo_Gallery implements Bootable {
	public function boot() {
		add_filter('woocommerce_available_variation', [$this, 'add_more_variation'], 10, 3);
	}

	public function add_more_variation($data, $product, $variation) {
		if (!empty(get_post_meta($variation->get_id(), 'geargag_image_url', true))) {
			$data['image']['src'] = get_post_meta($variation->get_id(), 'geargag_image_url', true);
		}

		return $data;
	}
}
