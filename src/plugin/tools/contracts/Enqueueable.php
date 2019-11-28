<?php
/**
 * Enqueueable Contract.
 *
 * Enqueueable classes should implement a `enqueue()` method.
 */

namespace GearGag_Toolkit\tools\contracts;

interface Enqueueable extends Bootable {
	/**
	 * Enqueue CSS/JS
	 * @return void
	 */
	public function enqueue();
}
