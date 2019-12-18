<?php

namespace GearGag_Toolkit;

defined('WPINC') || die();

if (!function_exists('get_plugin_data')) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$plugin = get_plugin_data(PLUGIN_FILE);

define(__NAMESPACE__ . '\PLUGIN_NAME', $plugin['Name']);
define(__NAMESPACE__ . '\PLUGIN_DESCRIPTION', $plugin['Description']);
define(__NAMESPACE__ . '\PLUGIN_URI', $plugin['PluginURI']);
define(__NAMESPACE__ . '\PLUGIN_VERSION', $plugin['Version']);
define(__NAMESPACE__ . '\PLUGIN_AUTHOR', $plugin['Author']);
define(__NAMESPACE__ . '\PLUGIN_AUTHOR_URI', $plugin['AuthorURI']);
define(__NAMESPACE__ . '\PLUGIN_TEXT_DOMAIN', $plugin['TextDomain']);
define(__NAMESPACE__ . '\PLUGIN_SLUG', basename(PLUGIN_DIR));
define(__NAMESPACE__ . '\PLUGIN_BASE', plugin_basename(PLUGIN_FILE));
define(__NAMESPACE__ . '\PLUGIN_PATH', trailingslashit(PLUGIN_DIR));
define(__NAMESPACE__ . '\PLUGIN_URL', plugin_dir_url(PLUGIN_FILE));
define(__NAMESPACE__ . '\PLUGIN_DOCUMENT_URI', get_file_data(PLUGIN_FILE, ['Document URI'])[0]);

const DS = '/';
const DEV_MODE = 'vnh_dev_mode';
const WPORG = true;

class Constants {
}
