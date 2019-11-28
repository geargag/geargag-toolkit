<?php

namespace GearGag_Toolkit;

defined('WPINC') || die();

use GearGag_Toolkit\tools\contracts\Bootable;

class Batch_Delete_Products implements Bootable {
	public $nonce;

	public function __construct() {
		$this->nonce = 'geargag-remove-products_' . get_current_user_id();
	}

	public function boot() {
		add_action('admin_menu', [$this, 'batch_delete_menu']);
	}

	public function batch_delete_menu() {
		add_submenu_page(
			'edit.php?post_type=product',
			__('Batch Delete', 'vnh_textdomain'),
			__('Batch Delete', 'vnh_textdomain'),
			'manage_options',
			'batch_delete_products',
			[$this, 'batch_delete_page']
		);
	}

	public function batch_delete_page() {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'vnh_textdomain'));
		}

		$html = sprintf('<h2>%s</h2>', esc_html__('Woo Product Batch Delete', 'vnh_textdomain'));
		if (isset($_POST['delete_process']) && check_admin_referer($this->nonce, $this->nonce)) {
			$this->remove_products_db();
			$html .= esc_html__('All products deleted!', 'vnh_textdomain');
		} else {
			$html .= '<div class="wrap">';
			$html .= '<form method="post">';
			$html .= '<p>';
			$html .= esc_html__(
				'This is as simple as it gets! Just click the button below to remove all woocommerce products.',
				'vnh_textdomain'
			);
			$html .= '</p>';
			$html .= '<p>';
			$html .= esc_html__('Please be cautious as this action is irreversible!', 'vnh_textdomain');
			$html .= '</p>';
			$html .= '<input type="hidden" id="delete_process" name="delete_process">'; //adding nonce support
			$html .= wp_nonce_field($this->nonce, $this->nonce, true, false);
			$html .= '<input type="submit" class="button-secondary" value="Delete All">';
			$html .= '</form>';
			$html .= '</div>';
		}
		echo $html; //phpcs:disable
	}

	public function remove_products_db() {
		global $wpdb;

		// Remove all attributes from WooCommerce
		$wpdb->query("DELETE FROM $wpdb->terms WHERE term_id IN (SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy LIKE 'pa_%')");
		$wpdb->query("DELETE FROM $wpdb->term_taxonomy WHERE taxonomy LIKE 'pa_%'");
		$wpdb->query("DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id not IN (SELECT term_taxonomy_id FROM $wpdb->term_taxonomy)");

		// Delete all WooCommerce products
		$wpdb->query(
			"DELETE FROM $wpdb->term_relationships WHERE object_id IN (SELECT ID FROM $wpdb->posts WHERE post_type IN ('product','product_variation'))"
		);
		$wpdb->query(
			"DELETE FROM $wpdb->postmeta WHERE post_id IN (SELECT ID FROM $wpdb->posts WHERE post_type IN ('product','product_variation'))"
		);
		$wpdb->query("DELETE FROM $wpdb->posts WHERE post_type IN ('product','product_variation')");

		// Delete orphaned postmeta
		$wpdb->query("DELETE pm FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL");
	}
}
