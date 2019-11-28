<?php

namespace GearGag_Toolkit;

if (!defined('ABSPATH')) {
	wp_die(esc_html__('Direct access not permitted', 'vnh_textdomain'));
}

function wpdb() {
	global $wpdb;

	return $wpdb;
}

function wp_query() {
	global $wp_query;

	return $wp_query;
}

function post() {
	global $post;

	return $post;
}

function fs() {
	global $wp_filesystem;

	return $wp_filesystem;
}

class Helpers_Global {
}
