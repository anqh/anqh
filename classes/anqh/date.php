<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Date
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Date extends Kohana_Date {

	/**
	 * ISO8601 date
	 */
	const DATE_8601 = 'date_8601';

	/**
	 * SQL date
	 */
	const DATE_SQL = 'date_sql';

	/**
	 * ISO8601 time
	 */
	const TIME_8601 = 'time_8601';

	/**
	 * SQL time
	 */
	const TIME_SQL = 'time_sql';


	/**
	 * Returns age in human readable format with only the largest span
	 *
	 * @param		int|string	$time1
	 * @param		int|string	$time2
	 * @param		string			$output
	 * @return	string
	 */
	public static function age($time1, $time2 = null) {
		if (!is_numeric($time1)) $time1 = strtotime($time1);
		if (!is_null($time2) && !is_int($time2)) $time2 = strtotime($time2);

		if ($difference = Date::span($time1, $time2) and is_array($difference)) {
			foreach ($difference as $span => $amount) {
				if ($amount > 0) {
					return $amount . ' ' . __(Inflector::singular($span, $amount));
				}
			}
		}

		if (empty($difference)) {
			return '0 ' . __('seconds');
		}

		return __('some time');
	}


	/**
	 * Locale formatted date
	 *
	 * @param   string  $format
	 * @param   mixed   $date    defaults to now
	 * @return  strign
	 */
	public static function format($format, $date = null) {
		if (!$date) $date = time();
		if (!is_numeric($date)) $date = strtotime($date);
		switch ($format) {

			// ISO8601/SQL date
			case self::DATE_8601:
			case self::DATE_SQL:
				$format = 'Y-m-d';
				break;

			// ISO8601 time
			case self::TIME_8601:
				$format = 'c';
				break;

			// SQL time
			case self::TIME_SQL:
				$format = 'Y-m-d H:i:s';
				break;

			default:
				if (strpos($format, 'generic') === false) {
					switch ($format) {
						case 'DM': $format = 'j.n.'; break;
						case 'DDMM': $format = 'd.m.'; break;
						case 'DMYYYY': $format = 'j.n.Y'; break;
						case 'DDMMYYYY': $format = 'd.m.Y'; break;
						case 'DMYYYY_HM': $format = 'j.n.Y H:i'; break;
						case 'HHMM': $format = 'H:i'; break;
					}
				}
				break;

		}

		return date($format, $date);
	}


	/**
	 * Returns the difference between timestamps in a "fuzzy" way.
	 *
	 * @param   integer  $timestamp
	 * @param   interer  $timestamp2  Defaults to now
	 * @return  string
	 */
	public static function fuzzy_span($timestamp, $timestamp2 = null) {

		// If timestamp2 given, pad the actual timestamp to make up the time
		return parent::fuzzy_span($timestamp2 ? $timestamp - $timestamp2 + time() : $timestamp);

	}


	/**
	 * Get time list. Typically used as a shortcut for generating a
	 * list that can be used in a form.
	 *
	 *     $times = Date::hours_minutes(); // 01:00, 01:30, 02:00, ..., 11:00, 11:30, 12:00
	 *
	 * @param   integer  amount to increment each step by, minutes
	 * @param   boolean  use 24-hour time
	 * @param   integer  the hour to start at
	 * @return  array    A mirrored (foo => foo) array from start-12 or start-23.
	 */
	public static function hours_minutes($step = 30, $long = false, $start = null) {

		// Default values
		$step  = (int)$step;
		$long  = (bool)$long;
		$times = array();

		// Set the default start if none was specified.
		if ($start === null) {
			$start = $long ? 0 : 1;
		}

		// 24-hour time has 24 hours, instead of 12
		$hours = $long ? 24 : 12;

		$size = $hours * 60 - 1;

		for ($i = $start; $i <= $size; $i += $step) {
			$time = sprintf('%02d:%02d', floor($i / 60), $i % 60);
			$times[$time] = $time;
		}

		return $times;
	}


	/**
	 * Returns age/ago in short form
	 * e.g., <1min, yesterday, 3 months or 2004
	 *
	 * @static
	 * @param   integer  $time
	 * @param   boolean  $long  Include ago/in
	 * @param   boolean  $wrap  Wrap number in <var>
	 * @return  string
	 */
	public static function short_span($timestamp, $short = true, $wrap = false) {

		// Determine the difference in seconds
		$offset = abs(time() - $timestamp);
		$wrap   = $wrap ? '<var>%d</var>' : '%d';

		if ($offset < Date::MINUTE) {
			$span = __('< 1 min');
		} else if ($offset < Date::HOUR) {
			$span = __(':min min', array(':min' => sprintf($wrap, floor($offset / Date::MINUTE))));
		} else if ($offset > Date::HOUR * 6 && date('Ymd', $timestamp) == date('Ymd', strtotime('yesterday'))) {
			return __('yesterday');
		} else if ($offset > Date::HOUR * 6 && date('Ymd', $timestamp) == date('Ymd', strtotime('tomorrow'))) {
			return __('tomorrow');
		} else if ($offset < Date::DAY) {
			$span = __(':hour h', array(':hour' => sprintf($wrap, floor($offset / Date::HOUR))));
		} else if ($offset < Date::WEEK) {
			$span = __(':day d', array(':day' => sprintf($wrap, floor($offset / Date::DAY))));
		} else if ($offset < Date::MONTH) {
			$span = __(':week wk', array(':week' => sprintf($wrap, floor($offset / Date::WEEK))));
		} else if ($offset < (Date::YEAR)) {
			$span = __(':month mo', array(':month' => sprintf($wrap, floor($offset / Date::MONTH))));
		} else {
			return date('Y', $timestamp);
		}

		if ($short) {
			return $span;
		} else if ($timestamp <= time()) {
			return __(':span ago', array(':span' => $span));
		} else {
			return __('in :span', array(':span' => $span));
		}
	}

}
