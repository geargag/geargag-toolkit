<?php

namespace GearGag_Toolkit;

defined('WPINC') || die();

function is_woocommerce_active() {
	return is_plugin_active('woocommerce/woocommerce.php');
}

function get_plugin_details($plugin_path) {
	$plugin_data = get_plugin_data($plugin_path);
	$plugin_name = $plugin_data['Name'] ?: basename($plugin_path);
	$plugin_version = $plugin_data['Version'] ? sprintf(__('(v%s)', 'vnh_textdomain'), $plugin_data['Version']) : null;
	$plugin_author_name = $plugin_data['AuthorName'] ? sprintf(__(' by %s', 'vnh_textdomain'), $plugin_data['AuthorName']) : null;

	return sprintf('%s%s%s', $plugin_name, $plugin_version, $plugin_author_name);
}

function is_open_ssl_enabled() {
	if (defined('OPENSSL_VERSION_TEXT')) {
		return true;
	}

	return false;
}

function is_plugin_settings_page() {
	return strpos(get_current_screen()->id, PLUGIN_SLUG) !== false || strpos(get_current_screen()->id, 'extra') !== false;
}

function flatten_version($version) {
	if (empty($version)) {
		return null;
	}

	$parts = explode('.', $version);

	if (count($parts) === 2) {
		$parts[] = '0';
	}

	return implode('', $parts);
}

function get_plugin_path($dir = null) {
	if (empty($dir)) {
		return PLUGIN_PATH;
	}

	return PLUGIN_PATH . $dir;
}

function get_plugin_url($dir = null) {
	if (empty($dir)) {
		return PLUGIN_URL;
	}

	return PLUGIN_URL . $dir;
}

function is_dev() {
	return (defined(__NAMESPACE__ . '\DEV_MODE') && DEV_MODE !== 'disable') || isset($_GET['dev']);
}

class Helpers {
}
