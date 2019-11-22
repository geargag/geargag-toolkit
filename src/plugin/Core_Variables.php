<?php

namespace vnh_namespace;

if (!defined('ABSPATH')) {
	wp_die(esc_html__('Direct access not permitted', 'vnh_textdomain'));
}

trait Core_Variables {
	/**
	 * @var Batch_Delete_Products
	 */
	public $batch_delete;

	/**
	 * @var Add_DB_Column
	 */
	public $add_db_column;

	/**
	 * @var Woo
	 */
	public $woo;
}
