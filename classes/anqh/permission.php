<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Permission helper
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Permission {

	/**
	 * @var  array  Intenal permission cache
	 */
	private static $_permissions = array();


	/**
	 * Check permission for object
	 *
	 * @static
	 * @param   Permission_Interface  $model       Object implemeneting permission interface
	 * @param   string                $permission
	 * @param   mixed                 $user        Defaults to session user
	 * @return  boolean
	 */
	public static function has(Permission_Interface $model, $permission = Permission_Interface::PERMISSION_READ, $user = false) {

		// Make sure we have a valid user, if any
		$user = Model_User::find_user($user);

		// Create unique permission id for caching
		$permission_id = sprintf('%s:%d:%s:%d', get_class($model), $model->id(), $permission, $user && $user->loaded() ? $user->id : 0);

		// If permission check not found from cache ask the model
		if (!isset(self::$_permissions[$permission_id])) {
			self::$_permissions[$permission_id] = $model->has_permission($permission, $user);
		}

		return self::$_permissions[$permission_id];
	}


	/**
	 * Require permission for object, throw exception if not
	 *
	 * @static
	 * @param   Permission_Interface $model
	 * @param   string  $permission
	 * @param   mixed   $user
	 * @throws  Permission_Exception  on no permission
	 */
	public static function required(Permission_Interface $model, $permission = Permission_Interface::PERMISSION_READ, $user = false) {
		if (!self::has($model, $permission, $user)) {
			throw new Permission_Exception($model, $model->id(), $permission);
		}
	}

}
