<?php

namespace vnh_namespace;

if (!defined('ABSPATH')) {
	wp_die(esc_html__('Direct access not permitted', 'vnh_textdomain'));
}

use vnh_namespace\tools\contracts\Bootable;

class Add_DB_Column implements Bootable {
	public function boot() {
		add_action('vnh_prefix_install', [$this, 'add_column']);
	}

	public function add_column() {
		global $wpdb;

		$modes = ["SET SESSION sql_mode = 'TRADITIONAL'"];
		$wpdb->set_sql_mode($modes);

		$row = $wpdb->get_row("SELECT * FROM $wpdb->posts");

		if (!isset($row->geargag_defaults)) {
			$wpdb->query("ALTER TABLE $wpdb->posts ADD geargag_defaults longtext");
		}
	}
}
