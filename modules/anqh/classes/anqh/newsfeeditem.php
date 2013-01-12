<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * NewsfeedItem
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_NewsfeedItem implements NewsfeedItem_Interface {

	/**
	 * Add a Newsfeed item.
	 *
	 * @static
	 * @param   Model_User  $user
	 * @param   string      $class  e.g. 'user'
	 * @param   string      $type   e.g. 'login'
	 * @param   array       $data   Data to be user with item
	 * @return  boolean
	 */
	protected static function add(Model_User $user, $class, $type, array $data = null) {
		$item = AutoModeler::factory('newsfeeditem');
		$item->set_fields(array(
			'user_id' => $user->id,
			'class'   => $class,
			'type'    => $type,
			'data'    => $data,
			'stamp'   => time(),
		));
		$item->save();

		return $item->loaded();
	}

}
