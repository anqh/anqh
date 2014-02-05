<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Number helpers.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Num extends Kohana_Num {

	/**
	 * Get currency based on date. Used for currency chances such as Euro.
	 *
	 * @static
	 * @param   float    $amount
	 * @param   integer  $date
	 * @return  string
	 */
	public static function currency($amount, $date = null) {
		static $change;

		// Show decimals only if required
		$amount = ($amount == (int)$amount) ? (int)$amount : Num::format($amount, 2, true);

		// Finland switched to Euro on January 1st, 2002
		if (!$change) {
			$change = mktime(0, 0, 0, 1, 1, 2002);
		}

		$currency = (!$date || $date >= $change) ? '€' : 'mk';

		return $amount . $currency;
	}


	/**
	 * Get minutes from seconds.
	 *
	 * @param   integer  $seconds
	 * @return  string
	 */
	public static function minutes($seconds) {
		$hours   = floor($seconds / 3600);
		$minutes = floor($seconds / 60);
		$seconds = $seconds % 60;

		return ($hours ? $hours . ':' : '') . $minutes . ':' . ($seconds < 10 ? '0' . $seconds : $seconds);
	}


	/**
	 * Pad a number for total length.
	 *
	 * @param   integer  $number
	 * @param   integer  $length  max length
	 * @param   integer  $pad     number to pad with
	 * @return  string
	 */
	public static function pad($number, $length = 2, $pad = 0) {
		return str_pad((int)$number, $length, $pad, STR_PAD_LEFT);
	}


	/**
	 * Get seconds from minutes.
	 *
	 * @param   string  $minutes
	 * @return  integer
	 */
	public static function seconds($minutes) {
		if (preg_match('/([0-9]{1,2})?:?([0-9]{1,3}):([0-9]{1,2})/', $minutes, $seconds)) {
			return isset($seconds[3])
				? 3600 * $seconds[1] + 60 * $seconds[2] + $seconds[3]
				: 60 * $seconds[1] + $seconds[2];
		}

		return 0;
	}

}
