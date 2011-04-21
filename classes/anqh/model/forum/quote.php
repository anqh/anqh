<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Quote model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Quote extends AutoModeler implements Permission_Interface {

	protected $_table_name = 'forum_quotes';

	protected $_data = array(
		'id'             => null,
		'author_id'      => null,
		'user_id'        => null,
		'forum_topic_id' => null,
		'forum_post_id'  => null,
		'created'        => null,
	);


	/**
	 * Find quotes by quoted user
	 *
	 * @param   Model_User  $user
	 * @return  Model_Forum_Quote[]
	 */
	public function find_by_user(Model_User $user) {
		return $this->load(
			DB::select_array($this->fields())
				->where('user_id', '=', $user->id),
			null
		);
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
