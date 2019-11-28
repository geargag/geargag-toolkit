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

define('vnh_plugin_file', __FILE__);
define('vnh_plugin_dir', __DIR__);

require __DIR__ . '/vendor/autoload.php';
GearGag_Toolkit\Plugin::instance();
