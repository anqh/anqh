<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Private Recipient model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Private_Recipient extends Jelly_Model implements Permission_Interface {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id' => new Jelly_Field_Primary,
			'topic' => new Jelly_Field_BelongsTo(array(
				'column'  => 'forum_topic_id',
				'foreign' => 'forum_private_topic'
			)),
			'area' => new Jelly_Field_BelongsTo(array(
				'column'  => 'forum_area_id',
				'foreign' => 'forum_area'
			)),
			'user' => new Jelly_Field_BelongsTo,
			'unread' => new Jelly_Field_Integer
		));
	}


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
