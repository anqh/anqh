<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Online User model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_User_Online extends Jelly_Model implements Permission_Interface {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta
			->table('online_users')
			->fields(array(
				'id'            => new Field_Primary,
				'user'          => new Field_BelongsTo,
				'last_activity' => new Field_Timestamp(array(
					'auto_now_create' => true,
					'auto_now_update' => true,
				)),
			));
	}


	/**
	 * Get online users
	 *
	 * @static
	 * @return  array
	 */
	public static function find_online_users() {
		self::gc();

		$online = array();
		$users = DB::select('user_id')->from('online_users')->where('user_id', 'IS NOT', null)->execute();
		foreach ($users as $user) {
			$online[(int)$user['user_id']] = (int)$user['user_id'];
		}

		return $online;
	}


	/**
	 * Garbage collect
	 *
	 * @static
	 */
	public static function gc() {
		static $collected = false;

		// Remove users idle for over 15 minutes
		if (!$collected) {
			$collected = true;
			DB::delete('online_users')->where('last_activity', '<', time() - 60 * 15)->execute();
		}

	}


	/**
	 * Get number of guests online
	 *
	 * @static
	 * @return  integer
	 */
	public static function get_guest_count() {
		self::gc();

		return (int)Jelly::select('user_online')->where('user_id', 'IS', null)->count();
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
