<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Online User model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_User_Online extends AutoModeler_ORM implements Permission_Interface {

	protected $_table_name = 'online_users';

	protected $_data = array(
		'id'            => null,
		'user_id'       => null,
		'last_activity' => null,
	);


	/**
	 * Get online users
	 *
	 * @static
	 * @return  array
	 */
	public static function find_online_users() {
		self::gc();

		$online = array();
		$users = DB::select('user_id')
			->from('online_users')
			->where('user_id', 'IS NOT', null)
			->execute();
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
			DB::delete('online_users')
				->where('last_activity', '<', time() - Date::MINUTE * 15)
				->execute();
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

		return (int)DB::select(array(DB::expr('COUNT(*)'), 'total_count'))
			->from('online_users')
			->where('user_id', 'IS', null)
			->execute()
			->get('total_count');
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
