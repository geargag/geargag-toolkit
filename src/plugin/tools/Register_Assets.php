<?php

namespace vnh_namespace\tools;

use vnh_namespace\tools\contracts\Bootable;
use function vnh_namespace\flatten_version;

class Register_Assets implements Bootable {
	public $scripts;
	public $styles;
	public $context;

	public function __construct($assets, $context = 'frontend') {
		$this->context = $context;
		$this->scripts = apply_filters("$context/register/scripts", $assets['scripts']);
		$this->styles = apply_filters("$context/register/styles", $assets['styles']);
	}

	public function boot() {
		if ($this->context === 'backend') {
			add_action('admin_enqueue_scripts', [$this, 'register_scripts']);
			add_action('admin_enqueue_scripts', [$this, 'register_styles']);
		} else {
			add_action('wp_enqueue_scripts', [$this, 'register_scripts']);
			add_action('wp_enqueue_scripts', [$this, 'register_styles']);
		}
	}

	public function register_scripts() {
		if (empty($this->scripts)) {
			return;
		}

		foreach ($this->scripts as $handle => $args) {
			$args = wp_parse_args($args, [
				'deps' => [],
				'version' => null,
				'in_footer' => true,
				'have_min' => false,
				'inline_script_position' => 'after',
			]);
			$args = apply_filters("$this->context/register/scripts/args", $args);
			$args = apply_filters("$this->context/register/scripts/$handle/args", $args);

			if ($args['have_min'] === true) {
				$src = preg_replace('/\.js$/', '.min.js', $args['src']);
			} else {
				$src = $args['src'];
			}

			wp_register_script($handle, esc_url($src), $args['deps'], flatten_version($args['version']), $args['in_footer']);

			if (!empty($args['inline_script'])) {
				wp_add_inline_script($handle, $args['inline_script'], $args['inline_script_position']);
			}

			if (!empty($args['localize_script'])) {
				foreach ($args['localize_script'] as $object_name => $data) {
					wp_localize_script($handle, $object_name, $data);
				}
			}
		}
	}

	public function register_styles() {
		if (empty($this->styles)) {
			return;
		}

		foreach ($this->styles as $handle => $args) {
			$args = wp_parse_args($args, [
				'deps' => false,
				'version' => null,
				'media' => 'all',
				'has_rtl' => false,
			]);
			$args = apply_filters("$this->context/register/styles/args", $args);
			$args = apply_filters("$this->context/register/styles/$handle/args", $args);

			wp_register_style($handle, esc_url($args['src']), $args['deps'], flatten_version($args['version']), $args['media']);

			if ($args['has_rtl']) {
				wp_style_add_data($handle, 'rtl', 'replace');
			}
		}
	}
}
