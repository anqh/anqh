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
			$xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><' . $root . ' />');
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
				$xml->addChild($key, htmlentities($value));
			}

		}

		return $xml->asXML();
	}

}
