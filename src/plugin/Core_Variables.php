<?php

namespace vnh_namespace;

use vnh_namespace\batch_delete_products\Batch_Delete_Products;

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
