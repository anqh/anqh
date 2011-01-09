<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Core {

	/**
	 * Anqh version
	 */
	const VERSION = 0.5;

	/**
	 * @var  array  Static local cache in front of external cache
	 */
	private static $_cache = array();

	/**
	 * @var  Cache  Cache instance for default cache
	 */
	private static $_cache_instance;


	/**
	 * Delete a cache entry based on id
	 *
	 * @param   string  $id  id to remove from cache
	 * @return  boolean
	 */
	public static function cache_delete($id) {
		!self::$_cache_instance and self::$_cache_instance = Cache::instance();

		unset(self::$_cache[$id]);

		return self::$_cache_instance->delete_($id);
	}


	/**
	 * Retrieve a cached value entry by id.
	 *
	 * @param   string  $id       id of cache to entry
	 * @param   string  $default  default value to return if cache miss
	 * @return  mixed
	 * @throws  Kohana_Cache_Exception
	 */
	public static function cache_get($id, $default = null) {
		!self::$_cache_instance and self::$_cache_instance = Cache::instance();

		if (!isset(self::$_cache[$id])) {
			self::$_cache[$id] = self::$_cache_instance->get_($id, $default);
		}

		return Arr::get(self::$_cache, $id, $default);
	}


	/**
	 * Set a value to cache with id and lifetime
	 *
	 * @param   string   $id        id of cache entry
	 * @param   string   $data      data to set to cache
	 * @param   integer  $lifetime  in seconds
	 * @return  boolean
	 */
	public static function cache_set($id, $data, $lifetime = 3600) {
		!self::$_cache_instance and self::$_cache_instance = Cache::instance();

		if (self::$_cache_instance->set_($id, $data, $lifetime)) {
			self::$_cache[$id] = $data;

		  return true;
		}

		return false;
	}


	/**
	 * Get/set Open Graph tags
	 *
	 * @static
	 * @param   string  $key    Null to get all
	 * @param   string  $value  Null to get, false to clear
	 * @return  mixed
	 */
	public static function open_graph($key = null, $value = null) {
		static $og;

		// Initialize required Open Graph tags when setting first value
	  if ($value && !is_array($og)) {
		  if ($app_id = Kohana::config('site.facebook')) {
				$og = array(
					'og:title'     => Kohana::config('site.site_name'),
					'og:type'      => 'article',
					'og:image'     => URL::site('/ui/opengraph.jpg', true),
					'og:url'       => URL::site('', true),
					'og:site_name' => Kohana::config('site.site_name'),
					'fb:app_id'    => $app_id,
				);
		  }
	  }

	  if (!is_array($og)) {

		  // Facebook/Open Graph disabled
		  return;


	  } else if (is_null($value)) {

		  // Get
		  return is_null($key) ? $og : Arr::get($og, 'og:' . $key);

	  } else if ($value === false) {

		  // Delete
		  unset($og['og:' . $key]);

	  } else {

		  // Set
		  $og['og:' . $key] = $value;

	  }
	}


	/**
	 * Get/set shareability
	 *
	 * @static
	 * @param   boolean  $shareable  boolean to set, null to get
	 * @return  boolean
	 */
	public static function share($shareable = null) {
		static $share;

	  if (is_bool($shareable)) {

		  // Set shareability
		  $share = $shareable;

	  } else {

		  // Get shareability
		  return (bool)$share;

	  }
	}

}
