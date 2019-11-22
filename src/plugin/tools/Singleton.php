<?php

namespace vnh_namespace\tools;

if (!defined('ABSPATH')) {
	wp_die(esc_html__('Direct access not permitted', 'vnh_textdomain'));
}

abstract class Singleton {
	protected static $_instance;

	abstract public static function instance();

	/**
	 * Clone.
	 *
	 * Disable class cloning and throw an error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object. Therefore, we don't want the object to be cloned.
	 */
	private function __clone() {
		_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'vnh_textdomain'), null);
	}

	/**
	 * Wakeup.
	 *
	 * Disable unserializing of the class.
	 */
	public function __wakeup() {
		_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'vnh_textdomain'), null);
	}
}
