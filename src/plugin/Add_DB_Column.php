<?php

namespace GearGag_Toolkit;

defined('WPINC') || die();

use GearGag_Toolkit\tools\contracts\Bootable;

class Add_DB_Column implements Bootable {
	public function boot() {
		register_activation_hook(PLUGIN_FILE, [$this, 'add_column']);
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
