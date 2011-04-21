<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Favorite model
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Favorite extends AutoModeler_ORM implements Permission_Interface {

	protected $_table_name = 'favorites';

	protected $_data = array(
		'id'       => null,
		'user_id'  => null,
		'event_id' => null,
		'created'  => null,
	);

	protected $_rules = array(
		'user_id'  => array('not_empty', 'digit'),
		'event_id' => array('not_empty', 'digit')
	);


	/**
	 * Check permission
	 *
	 * @param   string      $permission
	 * @param   Model_User  $user
	 * @return  boolean
	 */
	public function has_permission($permission, $user) {
		switch ($permission) {
			case self::PERMISSION_CREATE:
			case self::PERMISSION_DELETE:
			case self::PERMISSION_READ:
			case self::PERMISSION_UPDATE:
		}

		return false;
	}

}
