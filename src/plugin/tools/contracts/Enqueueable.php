<?php
/**
 * Enqueueable Contract.
 *
 * Enqueueable classes should implement a `enqueue()` method.
 */

namespace vnh_namespace\tools\contracts;

interface Enqueueable extends Bootable {
	/**
	 * Enqueue CSS/JS
	 * @return void
	 */
	public function enqueue();
}
