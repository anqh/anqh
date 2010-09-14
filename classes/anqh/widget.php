<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Widget helper for adding content to views.
 *
 * Based on widget helper in YurikoCMS by Lorenzo Pisani,
 * see official site for more info: http://github.com/Zeelot/yuriko_cms/tree
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Widget {

	/**
	 * Widget weights
	 */
	const TOP    = -20;
	const MIDDLE = -10;
	const BOTTOM = 0;

	/**
	 * Weights
	 *
	 * @var  array
	 */
	protected static $weights = array();

	/**
	 * Widgets
	 *
	 * @var  array
	 */
	protected static $widgets = array();


	/**
	 * Add new content for a region.
	 * Optionally specifying a weight to determine its output placement.
	 * Lower weight is higher: $weight = -100 gets output before 100.
	 *
	 * @param  string  $name
	 * @param  string  $content
	 * @param  int     $weight
	 */
	public static function add($name, $content, $weight = self::BOTTOM) {
	  while (in_array($weight, self::$weights)) {
	    $weight++;
	  }
	  self::$weights[] = $weight;
	  self::$widgets[$name][$weight] = $content;
	}


	/**
	 * Clear content from region.
	 *
	 * @param  string  $name
	 */
	public static function clear($name) {
		unset(self::$widgets[$name]);
	}


	/**
	 * Get the contents of a widget region, output is "first in first out" unless
	 * a weight was specified during set().
	 *
	 * @param   string  $name
	 * @param   string  $glue
	 * @return  string
	 */
	public static function get($name, $glue = '') {
	  if (isset(self::$widgets[$name])) {
	    ksort(self::$widgets[$name]);
	    return implode($glue, self::$widgets[$name]);
	  } else {
	    return '';
	  }
	}


	/**
	 * Checks to see if there is content within a widget region.
	 *
	 * @param   string  $name
	 * @return  boolean
	 */
	public static function is_set($name) {
	  return !empty(self::$widgets[$name]);
	}


	/**
	 * Override content to region
	 * Optionally specifying a weight to determine its output placement.
	 * Lower weight is higher: $weight = -100 gets output before 100.
	 *
	 * @param  string  $name
	 * @param  string  $content
	 * @param  int     $weight
	 */
	public static function set($name, $content, $weight = self::BOTTOM) {
		$weights = self::$widgets[$name];
		self::$weights = array_diff(self::$weights, $weights);
		self::$widgets[$name] = array();

		self::add($name, $content, $weight);
	}

}
