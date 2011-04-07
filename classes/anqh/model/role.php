<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Role model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Role extends AutoModeler_ORM implements Permission_Interface {

	// Default roles for easier access
	const LOGIN = 1;
	const ADMIN = 2;

	protected $_table_name = 'roles';

	protected $_data = array(
		'id'          => null,
		'name'        => null,
		'description' => null,
	);

	protected $_rules = array(
		'name'        => array('not_empty', 'max_length' => array(':value', 32), 'AutoModeler::unique' => array(':model', ':value', ':field')),
	);

	protected $_belongs_to = array(
		'users'
	);


	/**
	 * Load role
	 *
	 * @param  integer|string  $id
	 */
	public function __construct($id = null) {
		parent::__construct();

		if ($id !== null) {
			$this->load(DB::select()->where(is_numeric($id) ? 'id' : 'name', '=', $id));
		}
	}


	/**
	 * Add a role to a user
	 *
	 * @static
	 * @param  integer|string  $role_id
	 * @param  Model_User      $user
	 */
	public static function add($role_id, Model_User $user) {
		try {
			$role = new Model_Role($role_id);
			if ($role->loaded()) {
				$role->user_id = $user->id;
				$role->created = time();
				$role->save();

				return true;
			}
		} catch (Exception $e) {}

		return false;
	}


	/**
	 * Find roles by user.
	 *
	 * @static
	 * @param   Model_User  $user
	 * @return  array
	 */
	public static function find_by_user(Model_User $user) {
		return (array)DB::select('roles.id', 'roles.name')
			->from('roles')
			->join('roles_users')
			->on('roles.id', '=', 'roles_users.role_id')
			->where('roles_users.user_id', '=', $user->id)
			->execute()
			->as_array('id', 'name');
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

			// Everybody can read roles
			case self::PERMISSION_READ:
				return true;

			// Don't allow to delete nor rename of critical roles
			case self::PERMISSION_DELETE:
				if (in_array($this->id, array(self::LOGIN, self::ADMIN))) {
					return false;
				}

			default:
				return $user && $user->has_role('admin');

		}
	}

}
