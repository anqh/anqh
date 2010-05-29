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

}
