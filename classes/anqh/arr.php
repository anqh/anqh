<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Array helper
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Arr extends Kohana_Arr {

	/**
	 * Get a value from array and delete key or default if not found
	 *
	 * @param   array   $array
	 * @param   string  $key
	 * @param   mixed   $default
	 * @return  mixed
	 */
	public static function get_once(array &$array, $key, $default = null) {
		$value = self::get($array, $key, $default);
		unset($array[$key]);

		return $value;
	}

}
