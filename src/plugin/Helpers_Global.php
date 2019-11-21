<?php

namespace vnh_namespace;

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
