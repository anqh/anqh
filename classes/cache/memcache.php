<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Cache_Memcache
 *
 * @package    Kohana
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Cache_Memcache extends Kohana_Cache_Memcache {

	/**
	 * Callback method for Memcache::failure_callback to use if any Memcache call
	 * on a particular server fails. This method switches off that instance of the
	 * server if the configuration setting `instant_death` is set to `TRUE`.
	 *
	 * @param   string   hostname
	 * @param   integer  port
	 * @return  void|boolean
	 * @todo    Broken in 3.0.8
	 */
	public function _failed_request($hostname, $port) {
		parent::_failed_request($hostname, $port);
	}

}
