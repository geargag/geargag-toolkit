<?php
/**
 * Plugin Name: vnh_name
 * Description: vnh_description
 * Version: vnh_version
 * Tags: vnh_tags
 * Author: vnh_author
 * Author URI: vnh_author_uri
 * License: vnh_license
 * License URI: vnh_license_uri
 * Document URI: vnh_document_uri
 * Text Domain: vnh_textdomain
 * Tested up to: WordPress vnh_tested_up_to
 * WC requires at least: vnh_wc_requires
 * WC tested up to: vnh_wc_tested_up_to
 */

namespace GearGag_Toolkit;

use GearGag_Toolkit\tools\KSES;

const PLUGIN_FILE = __FILE__;
const PLUGIN_DIR = __DIR__;

final class Plugin {
	public $add_db_column;
	public $batch_delete;
	public $woo;
	public $woo_gallery;

	public function __construct() {
		$this->load();
		$this->init();
		$this->core();
		$this->boot();
	}

	public function load() {
		require PLUGIN_DIR . '/vendor/autoload.php';
	}

	public function init() {
		new Constants();
		new Helpers();
		new KSES();
	}

	public function core() {
		$this->add_db_column = new Add_DB_Column();
		$this->add_db_column->boot();

		$this->batch_delete = new Batch_Delete_Products();
		$this->batch_delete->boot();

		$this->woo = new Woo();
		$this->woo->boot();

		$this->woo_gallery = new Woo_Gallery();
		$this->woo_gallery->boot();
	}

	public function boot() {
		add_action('plugin_loaded', [$this, 'load_plugin_textdomain']);
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain('vnh_textdomain');
	}
}

new Plugin();
