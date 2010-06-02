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
	 * Returns time difference in human readable format with only the largest span
	 *
	 * @param		int|string	$time1
	 * @param		int|string	$time2
	 * @param		string			$output
	 * @return	string
	 */
	public static function timespan_short($time1, $time2 = null) {
		if (!is_numeric($time1)) $time1 = strtotime($time1);
		if (!is_null($time2) && !is_int($time2)) $time2 = strtotime($time2);
		if ($difference = Date::span($time1, $time2) and is_array($difference)) {
			foreach ($difference as $span => $amount)
				if ($amount > 0)
					return $amount . ' ' . __(Inflector::singular($span, $amount));
		}

		if (empty($difference)) {
			return '0 ' . __('seconds');
		}

		return __('some time');
	}

}
