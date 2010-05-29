<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Date
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Date {

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

}
