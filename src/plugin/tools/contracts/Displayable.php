<?php
/**
 * Displayable Contract.
 *
 * Displayable classes should implement a `display()` method. The intent of this
 * method is to output an HTML string to the screen. This data should already be
 * escaped prior to being output.
 */

namespace vnh_namespace\tools\contracts;

interface Displayable {
	/**
	 * Prints the HTML string.
	 *
	 * @return void
	 */
	public function display();
}
