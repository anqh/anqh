<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Validate
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Valid extends Kohana_Valid {

	/**
	 * Checks if a date is after another date.
	 *
	 * @param   array           $array
	 * @param   string|integer  $stamp
	 * @param   string|integer  $stamp2
	 * @return  boolean
	 */
	public static function after($array, $stamp, $stamp2) {
		$from  = is_numeric($array[$stamp]) ? $array[$stamp] : strtotime($array[$stamp]);
		$to    = is_numeric($array[$stamp2]) ? $array[$stamp2] : strtotime($array[$stamp2]);

		return $from > $to;
	}


	/**
	 * Checks if a date is before another date.
	 *
	 * @param   array           $array
	 * @param   string|integer  $stamp
	 * @param   string|integer  $stamp2
	 * @return  boolean
	 */
	public static function before($array, $stamp, $stamp2) {
		$from  = is_numeric($array[$stamp]) ? $array[$stamp] : strtotime($array[$stamp]);
		$to    = is_numeric($array[$stamp2]) ? $array[$stamp2] : strtotime($array[$stamp2]);

		return $from < $to;
	}


	/**
	 * Checks if a string is a valid date string.
	 *
	 * @static
	 * @param   string|integer  $date
	 * @return  boolean
	 */
	public static function date($date) {
		return is_numeric($date) || (strtotime($date) !== FALSE);
	}


	/**
	 * Checks if a string lenght is in range.
	 *
	 * @static
	 * @param   string   $value
	 * @param   integer  $min
	 * @param   integer  $max
	 * @return  boolean
	 */
	public static function length($value, $min, $max) {
		$length = UTF8::strlen($value);

		return $length >= $min && $length <= $max;
	}


	/**
	 * Checks for valid time.
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
			list($hour, $minute) = preg_split('/[:\.]/', $time, 2);

		}

		if (isset($hour) && isset($minute)) {
			$hour = (int)$hour;
			$minute = (int)$minute;

			return $hour >= 0 && $hour <= 23 && $minute >= 0 && $minute < 59;
		}

		return false;
	}

}
