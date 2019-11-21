<?php
/**
 * Plugin Name: vnh_title
 * Description: vnh_description
 * Version: vnh_version
 * Tags: vnh_tags
 * Author: vnh_author
 * Author URI: vnh_author_uri
 * License: vnh_license
 * License URI: vnh_license_uri
 * Document URI: vnh_document_uri
 * Text Domain: vnh_textdomain
 */

spl_autoload_register(function ($class_name) {
	if (stripos($class_name, 'vnh_namespace') !== 0) {
		return;
	}

	$file_name = preg_replace('/^vnh_namespace\\\\/', '', $class_name);
	$file_name = str_replace('\\', '/', $file_name);

	$file_path = sprintf('%s/%s.php', __DIR__, $file_name);

	if (file_exists($file_path)) {
		require_once $file_path;
	}
});

define('vnh_plugin_file', __FILE__);
define('vnh_plugin_dir', __DIR__);

vnh_namespace\Plugin::instance();
