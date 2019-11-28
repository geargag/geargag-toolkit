<?php

namespace GearGag_Toolkit;

defined('WPINC') || die();

final class Plugin extends Core {
	public static function instance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}
