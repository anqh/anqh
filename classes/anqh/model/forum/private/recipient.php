<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Private Recipient model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Private_Recipient extends AutoModeler_ORM implements Permission_Interface {

	protected $_table_name = 'forum_private_recipients';

	protected $_data = array(
		'id'             => null,
		'forum_topic_id' => null,
		'forum_area_id'  => null,
		'user_id'        => null,
		'unread'         => null,
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
