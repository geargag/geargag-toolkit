<?php

namespace vnh_namespace;

use vnh_namespace\tools\Singleton;
use vnh_namespace\tools\contracts\Bootable;
use vnh_namespace\tools\contracts\Initable;

abstract class Core extends Singleton implements Bootable, Initable {
	use Core_Variables;

	protected function __construct() {
		$this->prepare();
		$this->init();
		$this->boot();
		$this->load_core();
	}

	public function prepare() {
		if (empty($GLOBALS['wp_filesystem'])) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		if (!function_exists('get_plugin_data')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
	}

	public function init() {
		new Helpers();
		new Helpers_Global();
		new KSES();
		new Constants();
	}

	public function boot() {
		add_action('plugin_loaded', [$this, 'load_plugin_textdomain']);
		register_activation_hook(vnh_plugin_file, [$this, 'install']);
		register_deactivation_hook(vnh_plugin_file, [$this, 'uninstall']);
	}

	public function load_core() {
		$this->add_db_column = new Add_DB_Column();
		$this->add_db_column->boot();

		$this->batch_delete = new Batch_Delete_Products();
		$this->batch_delete->boot();

		$this->woo = new Woo();
		$this->woo->boot();
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain('vnh_textdomain');
	}

	public function install() {
		do_action('vnh_prefix_install');
	}

	public function uninstall() {
		do_action('vnh_prefix_uninstall');
	}
}
