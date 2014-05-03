<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Array helper
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Arr extends Kohana_Arr {

	/**
	 * Get a value from array and delete key or default if not found
	 *
	 * @param   array         $array
	 * @param   string|array  $key
	 * @param   mixed         $default
	 * @return  mixed
	 */
	public static function get_once(array &$array, $key, $default = null) {
		$value = is_array($key) ? self::extract($array, $key, $default) : self::get($array, $key, $default);
		$array = array_diff_key($array, (array)$key);

		return $value;
	}


	/**
	 * Get values by key from $array1 using values from $array2
	 *
	 * @static
	 * @param   array    $array1  Get values from this array
	 * @param   array    $array2  Using values from this array as keys
	 * @param   boolean  $once    Remove found values from $array1
	 * @return  array
	 */
	public static function intersect(array &$array1, array $array2, $once = false) {
		$intersect = array();
		foreach ($array2 as $key) {
			if (isset($array1[$key])) {
				$intersect[$key] = $array1[$key];
			  if ($once) unset($array1[$key]);
			}
		}

	  return $intersect;
	}


	/**
	 * Get first non-false value
	 *
	 * @static
	 * @param   mixed
	 * @param   mixed
	 * @return  mixed
	 */
	public static function pick() {
		$values = (func_num_args() == 1 && is_array(func_get_arg(0))) ? func_get_arg(0) : func_get_args();
		foreach ($values as $value) {
			if (!empty($value)) {
				return $value;
			}
		}
	}


	/**
	 * Get random array entry.
	 *
	 * @param   array  $array
	 * @return  mixed
	 */
	public static function rand($array) {
		return Arr::get($array, array_rand($array, 1));
	}


	/**
	 * Get an assoc array using the key and value from original array, reducing one dimension
	 *
	 * @static
	 * @param   array  $array
	 * @param   mixed  $key
	 * @param   mixed  $value
	 * @return  array
	 */
	public static function reduce(array $array, $key, $value) {
		$reduced = array();

		foreach ($array as $assoc) {
			isset($assoc[$key]) and $reduced[$assoc[$key]] = Arr::get($assoc, $value);
		}

		return $reduced;
	}


	/**
	* Unset a value from an array by path.
	*
	* @see  Arr::path()
	* @param  array   $array      Array to update
	* @param  string  $path       Path
	* @param  string  $delimiter  Path delimiter
	*/
	public static function unset_path(&$array, $path, $delimiter = NULL) {

		// Use the default delimiter?
		if (!$delimiter) {
			$delimiter = Arr::$delimiter;
		}

		// Split the keys by delimiter
		$keys = explode($delimiter, $path);

		// Set current $array to inner-most array path
		while (count($keys) > 1) {
			$key = array_shift($keys);

			// Make the key an integer?
			if (ctype_digit($key)) {
				$key = (int) $key;
			}

			if (!isset($array[$key])) {
				$array[$key] = array();
			}

			$array = &$array[$key];
		}

		// Unset key on inner-most array
		unset($array[array_shift($keys)]);
	}


	/**
	 * Convert an array to XML string
	 *
	 * @static
	 * @param   array   $array
	 * @param   string  $root
	 * @param   SimpleXMLElement  $xml
	 * @return  string
	 */
	public static function xml(array $array, $root = 'data', SimpleXMLElement &$xml = null) {

		// Initialize
		if (is_null($xml)) {
			$xml = simplexml_load_string('<?xml version="1.0" encoding="' . Kohana::$charset . '"?><' . $root . ' />');
		}

		foreach ($array as $key => $value) {

			// No numeric keys in our xml please!
			$numeric = false;
			if (is_numeric($key)) {
				$numeric = true;
				$key = Inflector::singular($root);
			}

			// Valid XML name
			$key = preg_replace('/[^a-z0-9\-\_\.\:]/i', '', $key);

			// Recursive call required for array values
			if (is_array($value)) {
				$node = true || Arr::is_assoc($value) || $numeric ? $xml->addChild($key) : $xml;
				self::xml($value, $key, $node);
			} else {
				$xml->addChild($key, htmlspecialchars($value));
			}

		}

		return $xml->asXML();
	}

}
