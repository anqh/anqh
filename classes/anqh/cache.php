<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Cache
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Cache extends Kohana_Cache {

	/**
	 * @var  array  Statistics
	 */
	public static $queries = array();


	/**
	 * Creates a singleton of a Kohana Cache group. If no group is supplied
	 * the __default__ cache group is used.
	 *
	 *     // Create an instance of the default group
	 *     $default_group = Cache::instance();
	 *
	 *     // Create an instance of a group
	 *     $foo_group = Cache::instance('foo');
	 *
	 *     // Access an instantiated group directly
	 *     $foo_group = Cache::$instances['default'];
	 *
	 * @param   string   the name of the cache group to use [Optional]
	 * @return  Anqh_Cache
	 * @throws  Kohana_Cache_Exception
	 */
	public static function instance($group = NULL) {
		return parent::instance($group);
	}


	/**
	 * Replaces troublesome characters with underscores and prefixes if necessary
	 *
	 * @param   string  $id  of cache to sanitize
	 * @return  string
	 */
	protected function _sanitize_id($id) {
		static $prefix = null;

		if (is_null($prefix)) {
			$prefix = Arr::get($this->_config, 'prefix', '');
		}

		return parent::_sanitize_id($prefix . $id);
	}


	/**
	 * Retrieve a cached value entry by id.
	 *
	 *     // Retrieve cache entry from default group
	 *     $data = Cache::instance()->get('foo');
	 *
	 *     // Retrieve cache entry from default group and return 'bar' if miss
	 *     $data = Cache::instance()->get('foo', 'bar');
	 *
	 *     // Retrieve cache entry from memcache group
	 *     $data = Cache::instance('memcache')->get('foo');
	 *
	 * @param   string   id of cache to entry
	 * @param   string   default value to return if cache miss
	 * @return  mixed
	 * @throws  Kohana_Cache_Exception
	 */
	public function get_($id, $default = null) {
		$stats = Arr::get(self::$queries, $id);
		$stats['get'] = Arr::get($stats, 'get', 0) + 1;
		self::$queries[$id] = $stats;

		return $this->get($id, $default);
	}


	/**
	 * Set a value to cache with id and lifetime
	 *
	 *     $data = 'bar';
	 *
	 *     // Set 'bar' to 'foo' in default group, using default expiry
	 *     Cache::instance()->set('foo', $data);
	 *
	 *     // Set 'bar' to 'foo' in default group for 30 seconds
	 *     Cache::instance()->set('foo', $data, 30);
	 *
	 *     // Set 'bar' to 'foo' in memcache group for 10 minutes
	 *     if (Cache::instance('memcache')->set('foo', $data, 600))
	 *     {
	 *          // Cache was set successfully
	 *          return
	 *     }
	 *
	 * @param   string   id of cache entry
	 * @param   string   data to set to cache
	 * @param   integer  lifetime in seconds
	 * @return  boolean
	 */
	public function set_($id, $data, $lifetime = 3600) {
		$stats = Arr::get(self::$queries, $id);
		$stats['set'] = Arr::get($stats, 'set', 0) + 1;
		self::$queries[$id] = $stats;

		return $this->set($id, $data, $lifetime);
	}


	/**
	 * Delete a cache entry based on id
	 *
	 *     // Delete 'foo' entry from the default group
	 *     Cache::instance()->delete('foo');
	 *
	 *     // Delete 'foo' entry from the memcache group
	 *     Cache::instance('memcache')->delete('foo')
	 *
	 * @param   string   id to remove from cache
	 * @return  boolean
	 */
	public function delete_($id) {
		$stats = Arr::get(self::$queries, $id);
		$stats['delete'] = Arr::get($stats, 'delete', 0) + 1;
		self::$queries[$id] = $stats;

		return $this->delete($id);
	}

}
