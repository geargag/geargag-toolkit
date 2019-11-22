<?php

namespace vnh_namespace\tools;

if (!defined('ABSPATH')) {
	wp_die(esc_html__('Direct access not permitted', 'vnh_textdomain'));
}

use vnh_namespace\tools\contracts\Bootable;
use vnh_namespace\tools\contracts\Initable;
use vnh_namespace\tools\contracts\Renderable;
use const vnh_namespace\PLUGIN_SLUG;

abstract class Register_Settings implements Initable, Bootable, Renderable {
	public $prefix;
	public $option_name;
	public $default_settings;
	public $setting_fields;

	public function __construct() {
		$this->option_name = sprintf('%s_%s_settings', PLUGIN_SLUG, $this->prefix);
		$this->setting_fields = $this->register_setting_fields();
		$this->setting_fields = apply_filters("$this->prefix/settings", $this->setting_fields);
	}

	public function init() {
		if (!empty($this->default_settings) && empty(get_option($this->option_name))) {
			add_option($this->option_name, apply_filters("$this->prefix/default_settings", $this->default_settings));
		}
	}

	public function boot() {
		add_action('admin_init', [$this, 'build_settings']);
	}

	public function __toString() {
		return $this->render();
	}

	public function render() {
		$html = '<div class="settings info-tab-content">';
		$html .= '<form method="post" action="options.php" id="settings-tab" enctype="multipart/form-data">';
		ob_start();
		foreach ($this->setting_fields as $section => $values) {
			$option_group = $this->prefix . '_settings_' . $section;
			$page = $option_group;
			settings_fields($option_group);
			do_settings_sections($page);
		}
		$html .= ob_get_clean();
		$html .= get_submit_button();
		$html .= '</form>';
		$html .= '</div>';
		$html .= '<div id="saveResult"></div>';

		return $html;
	}

	abstract public function register_setting_fields();

	public function build_settings() {
		if (empty($this->setting_fields)) {
			return;
		}

		foreach ($this->setting_fields as $section_id => $section_values) {
			$section = $option_group = $page = $this->prefix . '_settings_' . $section_id;

			$callback = function () use ($section_values) {
				if (!empty($section_values['description'])) {
					printf('<p class="subheading">%s</p>', esc_html($section_values['description']));
				}
			};
			$title = !empty($section_values['title']) ? $section_values['title'] : null;
			add_settings_section($section, $title, $callback, $page);

			register_setting($option_group, $this->option_name);

			if (empty($section_values['fields'])) {
				return;
			}

			foreach ($section_values['fields'] as $field) {
				$id = sprintf('%s[%s]', $this->option_name, $field['id']);
				$args['field'] = $field;

				add_settings_field($id, esc_html($field['name']), [$this, 'display_field'], $page, $section, $args);
			}
		}
	}

	public function display_field($args) {
		$field = $args['field'];

		$option = get_option($this->option_name);

		$output = '';
		switch ($field['type']) {
			case 'toggle':
				$tooltip = !empty($field['tooltip']) ? sprintf('<p class="tooltip">%s</p>', $field['tooltip']) : null;
				$label =
					'<label for="%1$s" class="toggle"><span><svg width="10px" height="10px" ><path d="M5,1 L5,1 C2.790861,1 1,2.790861 1,5 L1,5 C1,7.209139 2.790861,9 5,9 L5,9 C7.209139,9 9,7.209139 9,5 L9,5 C9,2.790861 7.209139,1 5,1 L5,9 L5,1 Z"></path></svg></span></label>';
				$output = sprintf(
					'<input type="checkbox" name="%1$s" class="input-toggle" id="%1$s" value="true" %2$s/>' . $label . $tooltip,
					sprintf('%s[%s]', $this->option_name, $field['id']),
					!empty($option[$field['id']]) ? 'checked' : null
				);
				break;

			case 'text':
				$output = sprintf(
					'<input type="text" name="%1$s" id="%1$s" value="%2$s"/>',
					sprintf('%s[%s]', $this->option_name, $field['id']),
					!empty($option[$field['id']]) ? $option[$field['id']] : null
				);
				break;
			case 'number':
				$output = sprintf(
					'<input type="number" min="0" max="100" name="%1$s" id="%1$s" value="%2$s"/>',
					sprintf('%s[%s]', $this->option_name, $field['id']),
					!empty($option[$field['id']]) ? $option[$field['id']] : null
				);
				break;
		}

		echo $output; //phpcs:disable
	}
}
