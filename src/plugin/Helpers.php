<?php

namespace vnh_namespace;

if (!defined('ABSPATH')) {
	wp_die(esc_html__('Direct access not permitted', 'vnh_textdomain'));
}

function get_time_zone() {
	$tz_text = get_option('timezone_string');
	if (!empty($tz_text)) {
		$tz = timezone_open($tz_text);
	} else {
		$gmt_offset = get_option('gmt_offset');
		if ($gmt_offset !== null) {
			$tz_text = guess_timezone_from_offset($gmt_offset);
			$tz = timezone_open($tz_text);
		} else {
			$tz = timezone_open(date_default_timezone_get()); // this will give UTC as WordPress  ALWAYS uses UTC
		}
	}

	return $tz->getName();
}

function guess_timezone_from_offset($offset) {
	$timezones = [
		'-12' => 'Pacific/Kwajalein', // calculated way does not find this, it's actually +12
		'-11:30' => 'Pacific/Samoa', // doesn't exist https://en.wikipedia.org/wiki/UTC%E2%88%9211:30
		'-11' => 'Pacific/Samoa',
		'-10:30' => 'Pacific/Honolulu', //https://en.wikipedia.org/wiki/UTC%E2%88%9210:30
		'-10' => 'Pacific/Honolulu',
		'-9:30' => 'Pacific/Marquesas', //https://en.wikipedia.org/wiki/UTC%E2%88%9209:30
		'-9' => 'America/Juneau',
		'-8:30' => 'Pacific/Pitcairn', //pitcairn islands https://en.wikipedia.org/wiki/UTC%E2%88%9208:30
		'-8' => 'America/Los_Angeles',
		'-7:30' => 'America/Denver', // does not exist https://en.wikipedia.org/wiki/UTC%E2%88%9207:30
		'-7' => 'America/Denver',
		'-6:30' => 'America/Mexico_City', //does not exist https://en.wikipedia.org/wiki/UTC%E2%88%9206:30
		'-6' => 'America/Mexico_City',
		'-5:30' => 'America/New_York', //doesn't exist https://en.wikipedia.org/wiki/UTC%E2%88%9205:30
		'-5' => 'America/New_York',
		'-4:30' => 'America/Caracas',
		'-4' => 'America/Manaus',
		'-3:30' => 'America/St_Johns',
		'-3' => 'America/Argentina/Buenos_Aires',
		'-2:30' => 'America/St_Johns', //https://en.wikipedia.org/wiki/UTC%E2%88%9202:30
		'-2' => 'Brazil/DeNoronha',
		'-1:30' => 'Atlantic/Azores', // doesn't exist
		'-1' => 'Atlantic/Azores',
		'-0:30' => 'Europe/London', // doesn't exist
		'0' => 'Europe/London',
		'0:30' => 'Europe/London', //approx //https://en.wikipedia.org/wiki/UTC%2B00:30
		'1:30' => 'Africa/Johannesburg', // approx - very old https://en.wikipedia.org/wiki/UTC%2B01:30
		'1' => 'Europe/Paris',
		'2' => 'Europe/Helsinki',
		'2:30' => 'Europe/Moscow', //old
		'3' => 'Europe/Moscow',
		'3:30' => 'Asia/Tehran',
		'4' => 'Asia/Baku',
		'4:30' => 'Asia/Kabul',
		'5' => 'Asia/Karachi',
		'5:30' => 'Asia/Calcutta',
		'5:45' => 'Asia/Katmandu',
		'6' => 'Asia/Colombo',
		'6:30' => 'Asia/Rangoon',
		'7' => 'Asia/Bangkok',
		'7:30' => 'Asia/Singapore', // old
		'8' => 'Asia/Singapore',
		'8:30' => 'Asia/Harbin', //approx, was North KOrea etc
		'8:45' => 'Australia/Eucla',
		'9' => 'Asia/Tokyo',
		'9:30' => 'Australia/Darwin',
		'10' => 'Pacific/Guam',
		'10:30' => 'Australia/Lord_Howe',
		'11' => 'Australia/Sydney',
		'11:30' => 'Pacific/Norfolk',
		'12' => 'Asia/Kamchatka',
		'12:45' => 'Pacific/Chatham', //https://en.wikipedia.org/wiki/UTC%2B12:45
		'13' => 'Pacific/Enderbury',
		'13:45' => 'Pacific/Chatham', // https://en.wikipedia.org/wiki/UTC%2B13:45
		'14' => 'Pacific/Kiritimati',
	];
	$int_offset = (int) $offset; /*  to cope with +01.00 */
	$str_offset = (string) $int_offset;

	if (isset($timezones[$str_offset])) {
		return $timezones[$str_offset];
	}

	return false;
}

function flatten_version($version) {
	if (empty($version)) {
		return null;
	}

	$parts = explode('.', $version);

	if (count($parts) === 2) {
		$parts[] = '0';
	}

	return implode('', $parts);
}

function get_plugin_path($dir = null) {
	if (empty($dir)) {
		return plugin_dir_path(vnh_plugin_file);
	}

	return plugin_dir_path(vnh_plugin_file) . $dir;
}

function get_plugin_url($dir = null) {
	if (empty($dir)) {
		return plugin_dir_url(vnh_plugin_file);
	}

	return plugin_dir_url(vnh_plugin_file) . $dir;
}

function is_dev() {
	return (defined(__NAMESPACE__ . '\DEV_MODE') && DEV_MODE !== 'disable') || isset($_GET['dev']);
}

class Helpers {
}
