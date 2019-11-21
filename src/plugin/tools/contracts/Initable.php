<?php
/**
 * Initable Contract.
 *
 * Defines the contract that bootable classes should utilize. Initable classes
 * should have a `init()` method with the singular purpose of "initing" the class.
 */

namespace vnh_namespace\tools\contracts;

interface Initable {
	public function init();
}
