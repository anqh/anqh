<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Validate
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Validate extends Kohana_Validate {

	/**
	 * Validate time
	 *
	 * @static
	 * @param   string  $time
	 * @return  boolean
	 */
	public static function time($time) {
		if (ctype_digit((string)$time)) {
			if (strlen($time) < 3) {

				// Only hours
				$hour = (int)$time;
				$minute = 0;

			} else if (strlen($time) == 4) {

				// Military format 0000-2359
				list($hour, $minute) = str_split($time, 2);

			}

		} else if (strlen($time) > 3) {

			// Normal format 0.00-23:59
			list($hour, $minute) = sscanf($time, '%d[:\.]%d');

		}

		if (isset($hour) && isset($minute)) {
			$hour = (int)$hour;
			$minute = (int)$minute;

			return $hour >= 0 && $hour <= 23 && $minute >= 0 && $minute < 59;
		}

		return false;
	}

}
