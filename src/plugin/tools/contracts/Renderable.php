<?php
/**
 * Renderable Contract.
 *
 * Renderable classes should implement a `render()` method that returns an HTML
 * string ready for output to the screen. While there's no way to ensure this
 * via the contract, the intent here is for anything that's renderable to already
 * be escaped. For clarity in the code, when returning raw data, it is
 * recommended to use an alternate method name, such as `get()`, and not use
 * this contract.
 */

namespace vnh_namespace\tools\contracts;

interface Renderable {
	/**
	 * Returns an HTML string for output.
	 *
	 * @return string
	 */
	public function render();
}
