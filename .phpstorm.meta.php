<?php

namespace PHPSTORM_META {
	registerArgumentsSet('textdomain_values', 'vnh_textdomain');
	expectedArguments(\__(), 1, argumentsSet("textdomain_values"));
	expectedArguments(\_nx(), 4, argumentsSet("textdomain_values"));
	expectedArguments(\esc_html__(), 1, argumentsSet("textdomain_values"));
	expectedArguments(\esc_attr__(), 1, argumentsSet("textdomain_values"));
	expectedArguments(\esc_attr_e(), 1, argumentsSet("textdomain_values"));
	expectedArguments(\esc_html_e(), 1, argumentsSet("textdomain_values"));
	expectedArguments(\_n(), 3, argumentsSet("textdomain_values"));
	expectedArguments(\load_plugin_textdomain(), 0, argumentsSet("textdomain_values"));

	expectedArguments(
		\get_option(),
		0,
		'siteurl',
		'stylesheet',
		'template',
		'thread_comments',
		'active_plugins',
		'timezone_string',
		'require_name_email',
		'gmt_offset',
		'date_format'
	);
	expectedArguments(\get_bloginfo(), 0, 'version');
	expectedArguments(
		\class_exists(),
		0,
		'WP_CLI',
		'Gutenberg_Ramp',
		'ACF',
		'OCDI_Plugin',
		'Kirki',
		'Jetpack',
		'TGM_Plugin_Activation',
		'Freemius'
	);
	expectedArguments(
		\wp_kses(),
		1,
		'default',
		'image',
		'widget_field',
		'svg',
		'svg_content',
		'breadcrumb',
		'title',
		'price',
		'heading',
		'link',
		'iframe'
	);
	registerArgumentsSet(
		'trailingslashit',
		get_template_directory(),
		get_template_directory_uri(),
		get_stylesheet_directory(),
		get_stylesheet_directory_uri()
	);
	expectedArguments(\trailingslashit(), 0, argumentsSet("trailingslashit"));
	expectedArguments(\untrailingslashit(), 0, argumentsSet("trailingslashit"));

	registerArgumentsSet(
		'genesis_layouts',
		'content-sidebar-sidebar',
		'sidebar-sidebar-content',
		'sidebar-content-sidebar',
		'full-width-content'
	);
	expectedArguments(\genesis_unregister_layout(), 0, argumentsSet("genesis_layouts"));

	expectedArguments(\unregister_sidebar(), 0, 'sidebar-alt', 'header-right');
	expectedArguments(
		\add_filter(),
		1,
		'__return_true',
		'__return_false',
		'__return_zero',
		'__return_empty_array',
		'__return_null',
		'__return_empty_string',
		'__genesis_return_full_width_content',
		'__genesis_return_sidebar_content_sidebar',
		'__genesis_return_sidebar_sidebar_content',
		'__genesis_return_content_sidebar_sidebar',
		'__genesis_return_sidebar_content',
		'__genesis_return_content_sidebar'
	);
}
