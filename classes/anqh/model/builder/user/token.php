<?php defined('SYSPATH') or die('No direct script access.');
/**
 * User_Token model builder
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Builder_User_Token extends Jelly_Builder {

	/**
	 * Load token by key, expire if necessary
	 *
	 * @param   mixed  $key
	 * @return  Model_User_Token
	 */
	public function load($key = null) {
		$object = parent::load($key);

		if ($object->loaded() && $object->expires < time()) {
			$object->delete();
		}

		return $object;
	}
	
}
