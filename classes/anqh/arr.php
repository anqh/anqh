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
